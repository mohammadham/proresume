<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use App\Models\Transaction;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use App\Models\Language;
use App\Http\Controllers\Payment\Concerns\GrantsPurchase;

class NextPayController extends Controller
{
    use GrantsPurchase;
    protected $gateway;
    protected string $apiUrl = 'https://nextpay.org/nx/gateway/token';
    protected string $verifyUrl = 'https://nextpay.org/nx/gateway/verify';

    public function __construct()
    {
        $this->gateway = PaymentGateway::where('keyword', 'nextpay')->first();
    }

    public function paymentProcess(Request $request, $_amount, $_title, $_success_url, $_cancel_url)
    {
        $request->merge(['price' => $_amount]);
        return $this->payment($request);
    }

    public function payment(Request $request)
    {
        $request->validate([
            'package_id' => 'required|integer|exists:packages,id',
        ]);

        $package = Package::findOrFail($request->package_id);

        // Detect base currency (P1-2). NextPay accepts both IRR and IRT natively.
        $currentLang = session()->has('lang')
            ? \App\Models\Language::where('code', session()->get('lang'))->first()
            : \App\Models\Language::where('is_default', 1)->first();
        $baseCurrency = strtoupper($currentLang->basic_extended->base_currency_text ?? 'IRT');
        if (!in_array($baseCurrency, ['IRR', 'IRT'])) {
            return back()->with('error', 'ارز پایه سایت با درگاه ایرانی سازگار نیست.');
        }

        $amount = (int) round($package->price);
        $currency = $baseCurrency;

        // Min amount check for NextPay: 1,000 Rial or 100 Toman (P1-3)
        $minAmount = $currency === 'IRT' ? 100 : 1000;
        if ($amount < $minAmount) {
            return back()->with('error', 'مبلغ کمتر از حداقل مجاز درگاه است.');
        }

        // grantPurchase() reads the checkout payload from the session after the
        // bank calls back. Every other Iranian gateway (ZarinPal, Zibal, Mellat,
        // IDPay, Pay.ir) stashes it here; this controller never did, so a fully
        // verified NextPay payment still ended with "checkout payload missing"
        // and no membership.
        Session::put('request', $request->all());
        Session::put('amount', $amount);
        Session::put('paymentFor', Session::get('paymentFor'));

        $orderId = 'NEXTPAY_' . Str::uuid()->toString();
        $gatewayInfo = json_decode($this->gateway->information, true);
        $callbackUrl = !empty($gatewayInfo['callback_url'] ?? null)
            ? $gatewayInfo['callback_url']
            : route('membership.nextpay.success');

        $user = auth()->user();
        $apiKey = $gatewayInfo['api_key'] ?? '';
        $sandbox = $gatewayInfo['sandbox_status'] ?? 0;

        if (!$apiKey) {
            return back()->with('error', 'درگاه NextPay تنظیم نشده است.');
        }

        $data = [
            'api_key' => $apiKey,
            'amount' => $amount,
            'order_id' => $orderId,
            'callback_url' => $callbackUrl,
            'payer_name' => $user->name ?? 'کاربر',
            'payer_mobile' => $user->phone ?? '',
            'payer_email' => $user->email ?? '',
            'description' => 'پرداخت از طریق NextPay',
            'currency' => $currency,
        ];

        try {
            $response = Http::asForm()->timeout(30)->post($this->apiUrl, $data);
            $result = $response->json();

            Log::channel('payment')->info('NextPay payment initiation (admin)', [
                'order_id' => $orderId,
                'amount' => $amount,
                'sandbox' => $sandbox,
                'response_code' => $result['code'] ?? 'unknown',
                'has_trans_id' => isset($result['trans_id']),
            ]);

            if ($response->successful() && isset($result['code']) && $result['code'] == -1 && isset($result['trans_id'])) {
                $paymentId = $result['trans_id'];
                $link = 'https://nextpay.org/nx/gateway/payment/' . $paymentId;

                // Save transaction with idempotency key
                Transaction::create([
                    'user_id' => $user->id ?? null,
                    'gateway_id' => $this->gateway->id,
                    'amount' => $amount,
                    'transaction_id' => $paymentId,
                    'order_id' => $orderId,
                    'status' => 'pending',
                    'currency' => $currency,
                    'ip' => $request->ip(),
                    'payment_url' => $link,
                ]);

                return redirect($link);
            } else {
                $error = $result['message'] ?? $result['code'] ?? 'خطا در ارتباط با درگاه';
                return back()->with('error', $error);
            }
        } catch (\Exception $e) {
            Log::channel('payment')->error('NextPay payment initiation error (admin)', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'خطا در پرداخت. لطفاً مجدداً تلاش کنید.');
        }
    }

    public function success(Request $request)
    {
        $paymentId = $request->input('trans_id');
        $status = $request->input('status');

        // Idempotency: lock the row and check it's still pending (P1-5)
        try {
            $transaction = DB::transaction(function () use ($paymentId) {
                $t = Transaction::where('transaction_id', $paymentId)
                    ->where('gateway_id', $this->gateway->id)
                    ->lockForUpdate()
                    ->first();
                if (!$t) {
                    throw new \DomainException('TRANSACTION_NOT_FOUND');
                }
                if ($t->status !== 'pending') {
                    throw new \DomainException('ALREADY_PROCESSED:' . $t->status);
                }
                $t->update(['status' => 'processing']);
                return $t;
            });
        } catch (\DomainException $e) {
            $msg = $e->getMessage();
            if ($msg === 'TRANSACTION_NOT_FOUND') {
                Log::channel('payment')->warning('NextPay callback: transaction not found', ['payment_id' => $paymentId]);
                return redirect()->route('front.pricing')->with('error', 'تراکنش یافت نشد.');
            }
            Log::channel('payment')->info('NextPay callback: duplicate/already processed', ['payment_id' => $paymentId, 'state' => $msg]);
            return redirect()->route('front.pricing')->with('warning', 'این تراکنش قبلاً پردازش شده است.');
        }

        // NextPay returns the buyer to the callback with status=OK (NOK when
        // cancelled). "0" is a verify-API response code, never a callback value,
        // so the old `== '0'` check made every successful callback skip
        // verification entirely: the order was marked failed, the customer got
        // no membership, and the money had already been taken. The official
        // NextPay plugin likewise keys off trans_id + verify code, not status.
        if ($paymentId && strcasecmp((string) $status, 'OK') === 0) {
            // Payment successful, verify
            $gatewayInfo = json_decode($this->gateway->information, true);
            $apiKey = $gatewayInfo['api_key'] ?? '';
            $sandbox = $gatewayInfo['sandbox_status'] ?? 0;

            try {
                $response = Http::asForm()->timeout(30)->post($this->verifyUrl, [
                    'api_key' => $apiKey,
                    'trans_id' => $paymentId,
                    'order_id' => $transaction->order_id,
                    'amount' => $transaction->amount,
                    'currency' => $transaction->currency ?? 'IRT',
                ]);

                $result = $response->json();

                Log::channel('payment')->info('NextPay payment verification (admin)', [
                    'trans_id' => $paymentId,
                    'order_id' => $transaction->order_id,
                    'code' => $result['code'] ?? 'unknown',
                    'amount' => $result['amount'] ?? null,
                ]);

                $amountOk = !isset($result['amount']) || (int) $result['amount'] === (int) $transaction->amount;
                if ($response->successful() && isset($result['code']) && $result['code'] == 0 && $amountOk) {
                    $transaction->update([
                        'status' => 'success',
                        'tracking_code' => $paymentId,
                    ]);

                    // Verified - now actually deliver what was paid for. This
                    // used to stop at "transaction is successful" and drop the
                    // customer on the pricing page, so a paid NextPay order never
                    // produced a membership.
                    $currentLang = session()->has('lang')
                        ? Language::where('code', session()->get('lang'))->first()
                        : Language::where('is_default', 1)->first();
                    $requestData = session()->get('request');

                    return $this->grantPurchase(
                        is_array($requestData) ? $requestData : [],
                        $transaction,
                        'NextPay',
                        $currentLang->basic_extended,
                        $currentLang->basic_setting
                    );
                } else {
                    $transaction->update(['status' => 'failed']);
                    $error_message = !$amountOk
                        ? 'مبلغ تایید شده با سفارش هم‌خوانی ندارد.'
                        : ($result['message'] ?? 'پرداخت تایید نشد.');
                    return redirect()->route('front.pricing')
                        ->with('error', $error_message);
                }
            } catch (\Exception $e) {
                $transaction->update(['status' => 'failed']);
                Log::channel('payment')->error('NextPay payment verification error (admin)', [
                    'trans_id' => $paymentId,
                    'error' => $e->getMessage(),
                ]);
                return redirect()->route('front.pricing')
                    ->with('error', 'خطا در تایید پرداخت. لطفاً مجدداً تلاش کنید.');
            }
        } else {
            $transaction->update(['status' => 'failed']);
            $error_messages = [
                '1' => 'مبلغ کمتر از حداقل مجاز است.',
                '2' => 'مبلغ بیشتر از حداکثر مجاز است.',
                '3' => 'IP مسدود شده است.',
                '4' => 'تراکنش تکراری است.',
                '5' => 'اطلاعات ارسال شده نامعتبر است.',
                '6' => 'درگاه غیر فعال است.',
                '7' => 'پرداخت لغو شده توسط کاربر.',
                '8' => 'خطای داخلی سیستم.',
            ];
            $error = $error_messages[$status] ?? 'پرداخت ناموفق بود. کد وضعیت: ' . $status;
            return redirect()->route('front.pricing')->with('error', $error);
        }
    }

    public function cancel(Request $request)
    {
        // The cancel URL is a public gateway callback: it can be opened cold (no
        // session) or long after the checkout session expired. `user.gateways`
        // (the tenant's settings page) is not a route at all, so this used to
        // throw a RouteNotFoundException - a 500 on every abandoned payment.
        $requestData = session()->get('request');
        $requestData = is_array($requestData) ? $requestData : [];
        $paymentFor = session()->get('paymentFor');

        session()->flash('warning', __('cancel_payment'));

        if ($paymentFor == 'membership' && !empty($requestData['package_id'])) {
            return redirect()
                ->route('front.register.view', ['status' => $requestData['package_type'] ?? 'default', 'id' => $requestData['package_id']])
                ->withInput($requestData);
        } elseif (!empty($requestData['package_id'])) {
            return redirect()
                ->route('user.plan.extend.checkout', ['package_id' => $requestData['package_id']])
                ->withInput($requestData);
        }

        return redirect()->route('front.pricing');
    }

    /**
     * Refund a payment
     *
     * @param string $transId The transId from original payment
     * @param float|null $amount Amount to refund (null = full refund)
     * @param string $reason Reason for refund
     * @return array Result with success status and message
     */
    public function refund($transId, $amount = null, $reason = 'Refund requested')
    {
        $apiUrl = 'https://nextpay.org/nx/gateway/refund';

        $gatewayInfo = json_decode($this->gateway->information, true);
        $apiKey = $gatewayInfo['api_key'] ?? '';
        $sandbox = $gatewayInfo['sandbox_status'] ?? 0;

        if (!$apiKey) {
            return [
                'success' => false,
                'message' => 'درگاه NextPay تنظیم نشده است.',
            ];
        }

        $payload = [
            'api_key' => $apiKey,
            'trans_id' => $transId,
        ];

        if ($amount !== null) {
            $payload['amount'] = $amount;
        }

        try {
            $response = Http::asForm()->timeout(30)->post($apiUrl, $payload);

            $result = $response->json();

            Log::channel('payment')->info('NextPay refund request (admin)', [
                'trans_id' => $transId,
                'amount' => $amount,
                'code' => $result['code'] ?? 'unknown',
            ]);

            if ($response->successful() && isset($result['code']) && $result['code'] == 0) {
                return [
                    'success' => true,
                    'message' => 'بازپرداخت با موفقیت انجام شد.',
                    'ref_id' => $transId,
                ];
            } else {
                $error_message = $result['message'] ?? 'خطا در بازپرداخت';
                
                Log::channel('payment')->warning('NextPay refund failed (admin)', [
                    'trans_id' => $transId,
                    'error_message' => $error_message,
                ]);

                return [
                    'success' => false,
                    'message' => $error_message,
                ];
            }
        } catch (\Exception $e) {
            Log::channel('payment')->error('NextPay refund error (admin)', [
                'trans_id' => $transId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'success' => false,
                'message' => 'خطا در بازپرداخت. لطفاً مجدداً تلاش کنید.',
            ];
        }
    }

    /**
     * Void a payment (cancel before settlement)
     *
     * @param string $transId The transId from original payment
     * @return array Result with success status and message
     */
    public function void($transId)
    {
        // NextPay doesn't have a direct void API, but we can attempt refund with full amount
        // if the payment is still in a voidable state
        return $this->refund($transId, null, 'Payment voided');
    }
}

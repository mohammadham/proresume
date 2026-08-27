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
use Illuminate\Support\Str;

class NextPayController extends Controller
{
    protected $gateway;
    protected string $apiUrl = 'https://nextpay.org/nx/gateway/token';
    protected string $verifyUrl = 'https://nextpay.org/nx/gateway/verify';

    public function __construct()
    {
        $this->gateway = PaymentGateway::where('keyword', 'nextpay')->first();
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
                return redirect()->route('user.gateways')->with('error', 'تراکنش یافت نشد.');
            }
            Log::channel('payment')->info('NextPay callback: duplicate/already processed', ['payment_id' => $paymentId, 'state' => $msg]);
            return redirect()->route('user.gateways')->with('warning', 'این تراکنش قبلاً پردازش شده است.');
        }

        if ($status == '0') {
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

                    return redirect()->route('user.gateways')
                        ->with('success', 'پرداخت با موفقیت انجام شد. کد رهگیری: ' . $paymentId);
                } else {
                    $transaction->update(['status' => 'failed']);
                    $error_message = !$amountOk
                        ? 'مبلغ تایید شده با سفارش هم‌خوانی ندارد.'
                        : ($result['message'] ?? 'پرداخت تایید نشد.');
                    return redirect()->route('user.gateways')
                        ->with('error', $error_message);
                }
            } catch (\Exception $e) {
                $transaction->update(['status' => 'failed']);
                Log::channel('payment')->error('NextPay payment verification error (admin)', [
                    'trans_id' => $paymentId,
                    'error' => $e->getMessage(),
                ]);
                return redirect()->route('user.gateways')
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
            return redirect()->route('user.gateways')->with('error', $error);
        }
    }

    public function cancel(Request $request)
    {
        return redirect()->route('user.gateways')->with('error', 'پرداخت توسط کاربر لغو شد.');
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

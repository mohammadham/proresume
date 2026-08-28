<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Package;

class IdPayController extends Controller
{
    protected $gateway;
    protected $apiUrl = 'https://api.idpay.ir/v1.1/payment';
    protected $verifyUrl = 'https://api.idpay.ir/v1.1/payment/verify';

    public function __construct()
    {
        $this->gateway = PaymentGateway::where('keyword', 'idpay')->first();
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

        // Detect base currency from language settings (P1-2)
        $currentLang = session()->has('lang')
            ? \App\Models\Language::where('code', session()->get('lang'))->first()
            : \App\Models\Language::where('is_default', 1)->first();
        $baseCurrency = strtoupper($currentLang->basic_extended->base_currency_text ?? 'IRR');
        if (!in_array($baseCurrency, ['IRR', 'IRT'])) {
            return back()->with('error', 'ارز پایه سایت با درگاه ایرانی سازگار نیست.');
        }

        // IDPay requires Rial (IRR). Convert Toman → Rial if needed.
        $amount = (int) round($baseCurrency === 'IRT' ? $package->price * 10 : $package->price);

        // Min amount check for IDPay: 10,000 Rial (P1-3)
        if ($amount < 10000) {
            return back()->with('error', 'حداقل مبلغ قابل پرداخت ۱۰،۰۰۰ ریال است.');
        }

        $orderId = 'IDPAY_' . Str::uuid()->toString();
        $gatewayInfo = json_decode($this->gateway->information, true);
        $callbackUrl = !empty($gatewayInfo['callback_url'] ?? null)
            ? $gatewayInfo['callback_url']
            : route('membership.idpay.success');

        $user = auth()->user();
        $apiKey = $gatewayInfo['api_key'] ?? '';
        $sandbox = $gatewayInfo['sandbox_status'] ?? 0;

        if (!$apiKey) {
            return back()->with('error', 'درگاه IDPay تنظیم نشده است.');
        }

        $data = [
            'order_id' => $orderId,
            'amount' => $amount,
            'name' => $user->name ?? 'کاربر',
            'phone' => $user->phone ?? '',
            'mail' => $user->email ?? '',
            'desc' => 'پرداخت از طریق IDPay',
            'callback' => $callbackUrl,
        ];

        try {
            $response = Http::timeout(30)->withHeaders([
                'Content-Type' => 'application/json',
                'X-API-KEY' => $apiKey,
                'X-SANDBOX' => $sandbox ? '1' : '0',
            ])->post($this->apiUrl, $data);

            $result = $response->json();

            Log::channel('payment')->info('IDPay payment initiation (admin)', [
                'order_id' => $orderId,
                'amount' => $amount,
                'sandbox' => $sandbox,
                'response_code' => $result['error_code'] ?? $result['status'] ?? 'unknown',
                'has_link' => isset($result['link']),
            ]);

            if ($response->successful() && isset($result['id'])) {
                $paymentId = $result['id'];
                $link = $result['link'];

                // Save transaction with idempotency key
                Transaction::create([
                    'user_id' => $user->id ?? null,
                    'gateway_id' => $this->gateway->id,
                    'amount' => $amount,
                    'transaction_id' => $paymentId,
                    'order_id' => $orderId,
                    'status' => 'pending',
                    'currency' => 'IRR',
                    'ip' => $request->ip(),
                    'payment_url' => $link,
                ]);

                return redirect($link);
            } else {
                $error = $result['error_message'] ?? $result['error_code'] ?? 'خطا در ارتباط با درگاه';
                return back()->with('error', $error);
            }
        } catch (\Exception $e) {
            Log::channel('payment')->error('IDPay payment initiation error (admin)', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);
             return back()->with('error', 'خطا در پرداخت. لطفاً مجدداً تلاش کنید.');
        }
    
    }

    public function success(Request $request)
    {
        $paymentId = $request->input('id');
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
                Log::channel('payment')->warning('IDPay callback: transaction not found', ['payment_id' => $paymentId]);
                return redirect()->route('user.gateways')->with('error', 'تراکنش یافت نشد.');
            }
            Log::channel('payment')->info('IDPay callback: duplicate/already processed', ['payment_id' => $paymentId, 'state' => $msg]);
            return redirect()->route('user.gateways')->with('warning', 'این تراکنش قبلاً پردازش شده است.');
        }

        if ($status == '10') {
            // Payment successful, verify
            $gatewayInfo = json_decode($this->gateway->information, true);
            $apiKey = $gatewayInfo['api_key'] ?? '';
            $sandbox = $gatewayInfo['sandbox_status'] ?? 0;

            try {
                $response = Http::timeout(30)->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-API-KEY' => $apiKey,
                    'X-SANDBOX' => $sandbox ? '1' : '0',
                ])->post($this->verifyUrl, [
                    'id' => $paymentId,
                    'order_id' => $transaction->order_id,
                ]);

                $result = $response->json();

                Log::channel('payment')->info('IDPay payment verification (admin)', [
                    'payment_id' => $paymentId,
                    'order_id' => $transaction->order_id,
                    'status' => $result['status'] ?? 'unknown',
                    'track_id' => $result['track_id'] ?? null,
                ]);

                if ($response->successful() && isset($result['status']) && $result['status'] == '100'
                    && isset($result['amount']) && (int) $result['amount'] === (int) $transaction->amount) {
                    $transaction->update([
                        'status' => 'success',
                        'tracking_code' => $result['track_id'] ?? null,
                    ]);

                    return redirect()->route('user.gateways')
                        ->with('success', 'پرداخت با موفقیت انجام شد. کد رهگیری: ' . ($result['track_id'] ?? ''));
                } else {
                    $transaction->update(['status' => 'failed']);
                    $mismatch = isset($result['amount']) && (int) $result['amount'] !== (int) $transaction->amount;
                    Log::channel('payment')->warning('IDPay verify failed', [
                        'payment_id' => $paymentId,
                        'expected_amount' => $transaction->amount,
                        'received_amount' => $result['amount'] ?? null,
                        'result' => $result,
                    ]);
                    $error_message = $mismatch
                        ? 'مبلغ تایید شده با سفارش هم‌خوانی ندارد.'
                        : ($result['error_message'] ?? 'پرداخت تایید نشد.');
                    return redirect()->route('user.gateways')->with('error', $error_message);
                }
            } catch (\Exception $e) {
                $transaction->update(['status' => 'failed']);
                Log::channel('payment')->error('IDPay payment verification error (admin)', [
                    'payment_id' => $paymentId,
                    'error' => $e->getMessage(),
                ]);
                return redirect()->route('user.gateways')
                    ->with('error', 'خطا در تایید پرداخت. لطفاً مجدداً تلاش کنید.');
            }
        } else {
            $transaction->update(['status' => 'failed']);
            $error_messages = [
                '-1' => 'ارسال اطلاعات ناموفق بود.',
                '-2' => 'خطای داخلی سیستم.',
                '-3' => 'اطلاعات ارسال شده نامعتبر است.',
                '-4' => 'مبلغ کمتر از حداقل مجاز است.',
                '-5' => 'مبلغ بیشتر از حداکثر مجاز است.',
                '-6' => 'تراکنش تکراری است.',
                '-7' => 'IP Address شما مسدود شده است.',
                '-8' => 'حداقل مبلغ برای این درگاه 10,000 ریال است.',
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
     * @param string $paymentId The payment ID from original payment
     * @param float|null $amount Amount to refund (null = full refund)
     * @param string $reason Reason for refund
     * @return array Result with success status and message
     */
    public function refund($paymentId, $amount = null, $reason = 'Refund requested')
    {
        $apiUrl = 'https://api.idpay.ir/v1.1/payment/refund';

        $gatewayInfo = json_decode($this->gateway->information, true);
        $apiKey = $gatewayInfo['api_key'] ?? '';
        $sandbox = $gatewayInfo['sandbox_status'] ?? 0;

        if (!$apiKey) {
            return [
                'success' => false,
                'message' => 'درگاه IDPay تنظیم نشده است.',
            ];
        }

        $payload = [
            'id' => $paymentId,
        ];

        if ($amount !== null) {
            $payload['amount'] = $amount;
        }

        try {
            $response = Http::timeout(30)->withHeaders([
                'Content-Type' => 'application/json',
                'X-API-KEY' => $apiKey,
                'X-SANDBOX' => $sandbox ? '1' : '0',
            ])->post($apiUrl, $payload);

            $result = $response->json();

            Log::channel('payment')->info('IDPay refund request (admin)', [
                'payment_id' => $paymentId,
                'amount' => $amount,
                'status' => $result['status'] ?? 'unknown',
                'refund_id' => $result['refund_id'] ?? null,
            ]);

            if ($response->successful() && isset($result['status']) && $result['status'] == 100) {
                return [
                    'success' => true,
                    'message' => 'بازپرداخت با موفقیت انجام شد.',
                    'ref_id' => $result['refund_id'] ?? null,
                ];
            } else {
                $error_message = $result['error_message'] ?? 'خطا در بازپرداخت';
                
                Log::channel('payment')->warning('IDPay refund failed (admin)', [
                    'payment_id' => $paymentId,
                    'error_message' => $error_message,
                ]);

                return [
                    'success' => false,
                    'message' => $error_message,
                ];
            }
        } catch (\Exception $e) {
            Log::channel('payment')->error('IDPay refund error (admin)', [
                'payment_id' => $paymentId,
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
     * @param string $paymentId The payment ID from original payment
     * @return array Result with success status and message
     */
    public function void($paymentId)
    {
        // IDPay doesn't have a direct void API, but we can attempt refund with full amount
        // if the payment is still in a voidable state
        return $this->refund($paymentId, null, 'Payment voided');
    }
}
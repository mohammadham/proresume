<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NextPayController extends Controller
{
    protected $gateway;
    protected $apiUrl = 'https://api.nextpay.org/v1/payments/create';
    protected $verifyUrl = 'https://api.nextpay.org/v1/payments/verify';

    public function __construct()
    {
        $this->gateway = PaymentGateway::where('keyword', 'nextpay')->first();
    }

    public function payment(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1000',
        ]);

        $amount = $request->amount;
        $orderId = 'NEXTPAY_' . Str::uuid()->toString();
        $callbackUrl = route('membership.nextpay.success');

        $user = auth()->user();
        $gatewayInfo = json_decode($this->gateway->information, true);
        $apiKey = $gatewayInfo['api_key'] ?? '';
        $sandbox = $gatewayInfo['sandbox'] ?? 0;

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
        ];

        try {
            $response = Http::timeout(30)->withHeaders([
                'Content-Type' => 'application/json',
            ])->post($sandbox ? 'https://api.sandbox.nextpay.org/v1/payments/create' : $this->apiUrl, $data);

            $result = $response->json();

            Log::channel('payment')->info('NextPay payment initiation (admin)', [
                'order_id' => $orderId,
                'amount' => $amount,
                'sandbox' => $sandbox,
                'response_code' => $result['code'] ?? 'unknown',
                'has_trans_id' => isset($result['trans_id']),
            ]);

            if ($response->successful() && isset($result['code']) && $result['code'] == 0 && isset($result['trans_id'])) {
                $paymentId = $result['trans_id'];
                $link = $result['payment_link'] ?? ($sandbox
                    ? 'https://api.sandbox.nextpay.org/v1/payments/pay/' . $paymentId
                    : 'https://api.nextpay.org/v1/payments/pay/' . $paymentId);

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
                $error = $result['message'] ?? $result['code'] ?? 'خطا در ارتباط با درگاه';
                return back()->with('error', $error);
            }
        } catch (\Exception $e) {
            Log::channel('payment')->error('NextPay payment initiation error (admin)', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'خطا در پرداخت: ' . $e->getMessage());
        }
    }

    public function success(Request $request)
    {
        $paymentId = $request->input('trans_id');
        $status = $request->input('status');

        $transaction = Transaction::where('transaction_id', $paymentId)
            ->where('gateway_id', $this->gateway->id)
            ->first();

        if (!$transaction) {
            Log::channel('payment')->warning('NextPay callback: transaction not found', [
                'payment_id' => $paymentId,
            ]);
            return redirect()->route('user.gateways')->with('error', 'تراکنش یافت نشد.');
        }

        if ($status == '0') {
            // Payment successful, verify
            $gatewayInfo = json_decode($this->gateway->information, true);
            $apiKey = $gatewayInfo['api_key'] ?? '';
            $sandbox = $gatewayInfo['sandbox'] ?? 0;

            try {
                $response = Http::timeout(30)->withHeaders([
                    'Content-Type' => 'application/json',
                ])->post($sandbox ? 'https://api.sandbox.nextpay.org/v1/payments/verify' : $this->verifyUrl, [
                    'api_key' => $apiKey,
                    'trans_id' => $paymentId,
                    'order_id' => $transaction->order_id,
                ]);

                $result = $response->json();

                Log::channel('payment')->info('NextPay payment verification (admin)', [
                    'trans_id' => $paymentId,
                    'order_id' => $transaction->order_id,
                    'code' => $result['code'] ?? 'unknown',
                    'amount' => $result['amount'] ?? null,
                ]);

                if ($response->successful() && isset($result['code']) && $result['code'] == 0) {
                    $transaction->update([
                        'status' => 'success',
                        'tracking_code' => $paymentId,
                    ]);

                    return redirect()->route('user.gateways')
                        ->with('success', 'پرداخت با موفقیت انجام شد. کد رهگیری: ' . $paymentId);
                } else {
                    $transaction->update(['status' => 'failed']);
                    $error_message = $result['message'] ?? 'پرداخت تایید نشد.';
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
                    ->with('error', 'خطا در تایید پرداخت: ' . $e->getMessage());
            }
        } else {
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
        $apiUrl = 'https://api.nextpay.org/v1/payments/refund';

        $gatewayInfo = json_decode($this->gateway->information, true);
        $apiKey = $gatewayInfo['api_key'] ?? '';
        $sandbox = $gatewayInfo['sandbox'] ?? 0;

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
            $response = Http::timeout(30)->withHeaders([
                'Content-Type' => 'application/json',
            ])->post($sandbox ? 'https://api.sandbox.nextpay.org/v1/payments/refund' : $apiUrl, $payload);

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
        } catch (\\Exception $e) {
            Log::channel('payment')->error('NextPay refund error (admin)', [
                'trans_id' => $transId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'success' => false,
                'message' => 'خطا در بازپرداخت: ' . $e->getMessage(),
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
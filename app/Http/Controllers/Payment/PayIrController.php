<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PayIrController extends Controller
{
    protected $gateway;
    protected $apiUrl = 'https://pay.ir/payment/send';
    protected $verifyUrl = 'https://pay.ir/payment/verify';

    public function __construct()
    {
        $this->gateway = PaymentGateway::where('keyword', 'payir')->first();
    }

    public function payment(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1000',
        ]);

        $amount = $request->amount;
        $orderId = 'PAYIR_' . Str::uuid()->toString();
        $callbackUrl = route('membership.payir.success');

        $user = auth()->user();
        $gatewayInfo = json_decode($this->gateway->information, true);
        $apiKey = $gatewayInfo['api_key'] ?? '';
        $sandbox = $gatewayInfo['sandbox'] ?? 0;

        if (!$apiKey) {
            return back()->with('error', 'درگاه Pay.ir تنظیم نشده است.');
        }

        $data = [
            'api' => $apiKey,
            'amount' => $amount,
            'redirect' => $callbackUrl,
            'mobile' => $user->phone ?? '',
            'factorNumber' => $orderId,
            'description' => 'پرداخت از طریق Pay.ir',
        ];

        try {
            $response = Http::timeout(30)->withHeaders([
                'Content-Type' => 'application/json',
            ])->post($sandbox ? 'https://pay.ir/payment/sandbox/send' : $this->apiUrl, $data);

            $result = $response->json();

            Log::channel('payment')->info('Pay.ir payment initiation (admin)', [
                'order_id' => $orderId,
                'amount' => $amount,
                'sandbox' => $sandbox,
                'status' => $result['status'] ?? 'unknown',
                'has_trans_id' => isset($result['transId']),
            ]);

            if ($response->successful() && isset($result['status']) && $result['status'] == 1 && isset($result['transId'])) {
                $paymentId = $result['transId'];
                $link = $sandbox
                    ? 'https://pay.ir/payment/sandbox/' . $paymentId
                    : 'https://pay.ir/payment/' . $paymentId;

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
                $error = $result['errorMessage'] ?? $result['status'] ?? 'خطا در ارتباط با درگاه';
                return back()->with('error', $error);
            }
        } catch (\Exception $e) {
            Log::channel('payment')->error('Pay.ir payment initiation error (admin)', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'خطا در پرداخت: ' . $e->getMessage());
        }
    }

    public function success(Request $request)
    {
        $paymentId = $request->input('transId');
        $status = $request->input('status');

        $transaction = Transaction::where('transaction_id', $paymentId)
            ->where('gateway_id', $this->gateway->id)
            ->first();

        if (!$transaction) {
            Log::channel('payment')->warning('Pay.ir callback: transaction not found', [
                'payment_id' => $paymentId,
            ]);
            return redirect()->route('user.gateways')->with('error', 'تراکنش یافت نشد.');
        }

        if ($status == '1' || $status == 1) {
            // Payment successful, verify
            $gatewayInfo = json_decode($this->gateway->information, true);
            $apiKey = $gatewayInfo['api_key'] ?? '';
            $sandbox = $gatewayInfo['sandbox'] ?? 0;

            try {
                $response = Http::timeout(30)->withHeaders([
                    'Content-Type' => 'application/json',
                ])->post($sandbox ? 'https://pay.ir/payment/sandbox/verify' : $this->verifyUrl, [
                    'api' => $apiKey,
                    'transId' => $paymentId,
                ]);

                $result = $response->json();

                Log::channel('payment')->info('Pay.ir payment verification (admin)', [
                    'trans_id' => $paymentId,
                    'status' => $result['status'] ?? 'unknown',
                    'amount' => $result['amount'] ?? null,
                ]);

                if ($response->successful() && isset($result['status']) && $result['status'] == 1) {
                    $transaction->update([
                        'status' => 'success',
                        'tracking_code' => $paymentId,
                    ]);

                    return redirect()->route('user.gateways')
                        ->with('success', 'پرداخت با موفقیت انجام شد. کد رهگیری: ' . $paymentId);
                } else {
                    $transaction->update(['status' => 'failed']);
                    $error_message = $result['errorMessage'] ?? 'پرداخت تایید نشد.';
                    return redirect()->route('user.gateways')
                        ->with('error', $error_message);
                }
            } catch (\Exception $e) {
                $transaction->update(['status' => 'failed']);
                Log::channel('payment')->error('Pay.ir payment verification error (admin)', [
                    'trans_id' => $paymentId,
                    'error' => $e->getMessage(),
                ]);
                return redirect()->route('user.gateways')
                    ->with('error', 'خطا در تایید پرداخت: ' . $e->getMessage());
            }
        } else {
            $error_messages = [
                '0' => 'پرداخت ناموفق بود.',
                '-1' => 'مبلغ کمتر از حداقل مجاز است.',
                '-2' => 'مبلغ بیشتر از حداکثر مجاز است.',
                '-3' => 'IP مسدود شده است.',
                '-4' => 'تراکنش تکراری است.',
                '-5' => 'اطلاعات ارسال شده نامعتبر است.',
                '-6' => 'درگاه غیر فعال است.',
                '-7' => 'پرداخت لغو شده توسط کاربر.',
                '-8' => 'خطای داخلی سیستم.',
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
        $apiUrl = 'https://pay.ir/payment/refund';

        $gatewayInfo = json_decode($this->gateway->information, true);
        $apiKey = $gatewayInfo['api_key'] ?? '';
        $sandbox = $gatewayInfo['sandbox'] ?? 0;

        if (!$apiKey) {
            return [
                'success' => false,
                'message' => 'درگاه Pay.ir تنظیم نشده است.',
            ];
        }

        $payload = [
            'api' => $apiKey,
            'transId' => $transId,
        ];

        if ($amount !== null) {
            $payload['amount'] = $amount;
        }

        try {
            $response = Http::timeout(30)->withHeaders([
                'Content-Type' => 'application/json',
            ])->post($sandbox ? 'https://pay.ir/payment/sandbox/refund' : $apiUrl, $payload);

            $result = $response->json();

            Log::channel('payment')->info('Pay.ir refund request (admin)', [
                'trans_id' => $transId,
                'amount' => $amount,
                'status' => $result['status'] ?? 'unknown',
            ]);

            if ($response->successful() && isset($result['status']) && $result['status'] == 1) {
                return [
                    'success' => true,
                    'message' => 'بازپرداخت با موفقیت انجام شد.',
                    'ref_id' => $transId,
                ];
            } else {
                $error_message = $result['errorMessage'] ?? 'خطا در بازپرداخت';
                
                Log::channel('payment')->warning('Pay.ir refund failed (admin)', [
                    'trans_id' => $transId,
                    'error_message' => $error_message,
                ]);

                return [
                    'success' => false,
                    'message' => $error_message,
                ];
            }
        } catch (\\Exception $e) {
            Log::channel('payment')->error('Pay.ir refund error (admin)', [
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
        // Pay.ir doesn't have a direct void API, but we can attempt refund with full amount
        // if the payment is still in a voidable state
        return $this->refund($transId, null, 'Payment voided');
    }
}
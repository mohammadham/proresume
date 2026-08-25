<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class IdPayController extends Controller
{
    protected $gateway;
    protected $apiUrl = 'https://api.idpay.ir/v1.1/payment';
    protected $verifyUrl = 'https://api.idpay.ir/v1.1/payment/verify';

    public function __construct()
    {
        $this->gateway = PaymentGateway::where('keyword', 'idpay')->first();
    }

    public function payment(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1000',
        ]);

        $amount = $request->amount;
        $orderId = 'IDPAY_' . Str::uuid()->toString();
        $callbackUrl = route('membership.idpay.success');

        $user = auth()->user();
        $gatewayInfo = json_decode($this->gateway->information, true);
        $apiKey = $gatewayInfo['api_key'] ?? '';
        $sandbox = $gatewayInfo['sandbox'] ?? 0;

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
            return back()->with('error', 'خطا در پرداخت: ' . $e->getMessage());
        }
    }

    public function success(Request $request)
    {
        $paymentId = $request->input('id');
        $status = $request->input('status');

        $transaction = Transaction::where('transaction_id', $paymentId)
            ->where('gateway_id', $this->gateway->id)
            ->first();

        if (!$transaction) {
            Log::channel('payment')->warning('IDPay callback: transaction not found', [
                'payment_id' => $paymentId,
            ]);
            return redirect()->route('user.gateways')->with('error', 'تراکنش یافت نشد.');
        }

        if ($status == '10') {
            // Payment successful, verify
            $gatewayInfo = json_decode($this->gateway->information, true);
            $apiKey = $gatewayInfo['api_key'] ?? '';
            $sandbox = $gatewayInfo['sandbox'] ?? 0;

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

                if ($response->successful() && isset($result['status']) && $result['status'] == '100') {
                    $transaction->update([
                        'status' => 'success',
                        'tracking_code' => $result['track_id'] ?? null,
                    ]);

                    return redirect()->route('user.gateways')
                        ->with('success', 'پرداخت با موفقیت انجام شد. کد رهگیری: ' . ($result['track_id'] ?? ''));
                } else {
                    $transaction->update(['status' => 'failed']);
                    $error_message = $result['error_message'] ?? 'پرداخت تایید نشد.';
                    return redirect()->route('user.gateways')
                        ->with('error', $error_message);
                }
            } catch (\Exception $e) {
                $transaction->update(['status' => 'failed']);
                Log::channel('payment')->error('IDPay payment verification error (admin)', [
                    'payment_id' => $paymentId,
                    'error' => $e->getMessage(),
                ]);
                return redirect()->route('user.gateways')
                    ->with('error', 'خطا در تایید پرداخت: ' . $e->getMessage());
            }
        } else {
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
}
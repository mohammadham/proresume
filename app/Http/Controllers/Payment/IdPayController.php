<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class IdPayController extends Controller
{
    protected $gateway;
    protected $apiUrl = 'https://api.idpay.ir/v1.1/payment';
    protected $sandboxUrl = 'https://api.idpay.ir/v1.1/payment';

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
        $orderId = time() . rand(1000, 9999);
        $callbackUrl = route('idpay.success');

        $user = auth()->user();
        $gatewayInfo = json_decode($this->gateway->information, true);
        $apiKey = $gatewayInfo['api_key'];
        $sandbox = $gatewayInfo['sandbox'];

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
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-API-KEY' => $apiKey,
                'X-SANDBOX' => $sandbox ? '1' : '0',
            ])->post($this->apiUrl, $data);

            if ($response->successful() && isset($response->json()['id'])) {
                $paymentId = $response->json()['id'];
                $link = $response->json()['link'];

                // Save transaction
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
                $error = $response->json()['error_message'] ?? 'خطا در ارتباط با درگاه';
                return back()->with('error', $error);
            }
        } catch (\Exception $e) {
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
            return redirect()->route('user.gateways')->with('error', 'تراکنش یافت نشد.');
        }

        if ($status == '10') {
            // Payment successful, verify
            $gatewayInfo = json_decode($this->gateway->information, true);
            $apiKey = $gatewayInfo['api_key'];
            $sandbox = $gatewayInfo['sandbox'];

            try {
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'X-API-KEY' => $apiKey,
                    'X-SANDBOX' => $sandbox ? '1' : '0',
                ])->post("https://api.idpay.ir/v1.1/payment/verify", [
                    'id' => $paymentId,
                    'order_id' => $transaction->order_id,
                ]);

                if ($response->successful() && $response->json()['status'] == '100') {
                    $transaction->update([
                        'status' => 'success',
                        'tracking_code' => $response->json()['track_id'] ?? null,
                    ]);

                    return redirect()->route('user.gateways')
                        ->with('success', 'پرداخت با موفقیت انجام شد. کد رهگیری: ' . ($response->json()['track_id'] ?? ''));
                } else {
                    $transaction->update(['status' => 'failed']);
                    return redirect()->route('user.gateways')
                        ->with('error', 'پرداخت تایید نشد.');
                }
            } catch (\Exception $e) {
                $transaction->update(['status' => 'failed']);
                return redirect()->route('user.gateways')
                    ->with('error', 'خطا در تایید پرداخت: ' . $e->getMessage());
            }
        } elseif ($status == '-1') {
            return redirect()->route('user.gateways')->with('error', 'ارسال اطلاعات ناموفق بود.');
        } elseif ($status == '-2') {
            return redirect()->route('user.gateways')->with('error', 'خطای داخلی سیستم.');
        } elseif ($status == '-3') {
            return redirect()->route('user.gateways')->with('error', 'اطلاعات ارسال شده نامعتبر است.');
        } elseif ($status == '-4') {
            return redirect()->route('user.gateways')->with('error', 'مبلغ کمتر از حداقل مجاز است.');
        } elseif ($status == '-5') {
            return redirect()->route('user.gateways')->with('error', 'مبلغ بیشتر از حداکثر مجاز است.');
        } elseif ($status == '-6') {
            return redirect()->route('user.gateways')->with('error', 'تراکنش تکراری است.');
        } elseif ($status == '-7') {
            return redirect()->route('user.gateways')->with('error', 'IP Address شما مسدود شده است.');
        } elseif ($status == '-8') {
            return redirect()->route('user.gateways')->with('error', 'حداقل مبلغ برای این درگاه 10,000 ریال است.');
        } else {
            return redirect()->route('user.gateways')->with('error', 'پرداخت ناموفق بود.');
        }
    }

    public function cancel(Request $request)
    {
        return redirect()->route('user.gateways')->with('error', 'پرداخت توسط کاربر لغو شد.');
    }
}
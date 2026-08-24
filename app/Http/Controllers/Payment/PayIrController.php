<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PayIrController extends Controller
{
    protected $gateway;
    protected $apiUrl = 'https://pay.ir/payment/send';

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
        $orderId = time() . rand(1000, 9999);
        $callbackUrl = route('payir.success');

        $user = auth()->user();
        $gatewayInfo = json_decode($this->gateway->information, true);
        $apiKey = $gatewayInfo['api_key'];
        $sandbox = $gatewayInfo['sandbox'];

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
            $response = Http::asForm()->post($this->apiUrl, $data);

            if ($response->successful() && $response->json()['status'] == 1) {
                $paymentId = $response->json()['transId'];
                $link = $response->json()['paymentLink'];

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
                $error = $response->json()['errorMessage'] ?? 'خطا در ارتباط با درگاه';
                return back()->with('error', $error);
            }
        } catch (\Exception $e) {
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
            return redirect()->route('user.gateways')->with('error', 'تراکنش یافت نشد.');
        }

        if ($status == 1) {
            // Payment successful, verify
            $gatewayInfo = json_decode($this->gateway->information, true);
            $apiKey = $gatewayInfo['api_key'];

            try {
                $response = Http::asForm()->post('https://pay.ir/payment/verify', [
                    'api' => $apiKey,
                    'transId' => $paymentId,
                ]);

                if ($response->successful() && $response->json()['status'] == 1) {
                    $transaction->update([
                        'status' => 'success',
                        'tracking_code' => $paymentId,
                    ]);

                    return redirect()->route('user.gateways')
                        ->with('success', 'پرداخت با موفقیت انجام شد. کد رهگیری: ' . $paymentId);
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
        } elseif ($status == 2) {
            return redirect()->route('user.gateways')->with('error', 'پرداخت توسط کاربر لغو شد.');
        } else {
            return redirect()->route('user.gateways')->with('error', 'پرداخت ناموفق بود.');
        }
    }

    public function cancel(Request $request)
    {
        return redirect()->route('user.gateways')->with('error', 'پرداخت توسط کاربر لغو شد.');
    }
}
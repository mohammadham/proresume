<?php
namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Package;
use Illuminate\Support\Str;

/**
 * Pay.ir Payment Gateway (API v2 — token based).
 *
 * Docs: https://github.com/aminsaedi/payir-v2
 * - Send:     POST https://pay.ir/pg/send
 * - Redirect: https://pay.ir/pg/{token}
 * - Verify:   POST https://pay.ir/pg/verify   (body: {api, token})
 * - Callback: POST with fields "token" and "status"
 */
class PayIrController extends Controller
{
    protected $gateway;
    protected $apiUrl        = 'https://pay.ir/pg/send';
    protected $verifyUrl     = 'https://pay.ir/pg/verify';
    protected $sandboxSend   = 'https://pay.ir/pg/sandbox/send';
    protected $sandboxVerify = 'https://pay.ir/pg/sandbox/verify';

    public function __construct()
    {
        $this->gateway = PaymentGateway::where('keyword', 'payir')->first();
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

        // P1-2: Base currency check. Pay.ir requires Rial (IRR).
        $currentLang = session()->has('lang')
            ? \App\Models\Language::where('code', session()->get('lang'))->first()
            : \App\Models\Language::where('is_default', 1)->first();
        $baseCurrency = strtoupper($currentLang->basic_extended->base_currency_text ?? 'IRR');
        if (!in_array($baseCurrency, ['IRR', 'IRT'])) {
            return back()->with('error', 'ارز پایه سایت با درگاه ایرانی سازگار نیست.');
        }

        // Convert Toman → Rial if needed
        $amount = (int) round($baseCurrency === 'IRT' ? $package->price * 10 : $package->price);

        // P1-3: Min amount check for Pay.ir: 10,000 Rial
        if ($amount < 10000) {
            return back()->with('error', 'حداقل مبلغ قابل پرداخت ۱۰،۰۰۰ ریال است.');
        }

        $orderId = 'PAYIR_' . Str::uuid()->toString();
        $user = auth()->user();
        $gatewayInfo = json_decode($this->gateway->information, true);
        $apiKey  = $gatewayInfo['api_key'] ?? '';
        $sandbox = $gatewayInfo['sandbox_status'] ?? 0;

        // P1-7: honor admin-configured callback_url when present
        $callbackUrl = !empty($gatewayInfo['callback_url'] ?? null)
            ? $gatewayInfo['callback_url']
            : route('membership.payir.success');

        if (!$apiKey) {
            return back()->with('error', 'درگاه Pay.ir تنظیم نشده است.');
        }

        // Pay.ir v2 send payload
        $data = [
            'api'          => $apiKey,
            'amount'       => $amount,
            'redirect'     => $callbackUrl,
            'mobile'       => $user->phone ?? '',
            'factorNumber' => $orderId,
            'description'  => 'پرداخت از طریق Pay.ir',
        ];

        try {
            $endpoint = $sandbox ? $this->sandboxSend : $this->apiUrl;
            $response = Http::timeout(30)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($endpoint, $data);

            $result = $response->json();

            Log::channel('payment')->info('Pay.ir payment initiation (admin, v2)', [
                'order_id'       => $orderId,
                'amount'         => $amount,
                'sandbox_status' => $sandbox,
                'status'         => $result['status'] ?? 'unknown',
                'has_token'      => isset($result['token']),
            ]);

            // v2 success: status == 1 AND token present
            if ($response->successful() && isset($result['status']) && $result['status'] == 1 && !empty($result['token'])) {
                $token = $result['token'];
                $link  = ($sandbox ? 'https://pay.ir/pg/sandbox/' : 'https://pay.ir/pg/') . $token;

                Transaction::create([
                    'user_id'        => $user->id ?? null,
                    'gateway_id'     => $this->gateway->id,
                    'amount'         => $amount,
                    'transaction_id' => $token,
                    'order_id'       => $orderId,
                    'status'         => 'pending',
                    'currency'       => 'IRR',
                    'ip'             => $request->ip(),
                    'payment_url'    => $link,
                ]);

                return redirect($link);
            }

            $error = $result['errorMessage'] ?? $result['errorCode'] ?? 'خطا در ارتباط با درگاه';
            Log::channel('payment')->warning('Pay.ir send failed (admin, v2)', [
                'order_id' => $orderId,
                'error'    => $error,
            ]);
            return back()->with('error', 'خطا در اتصال به درگاه پرداخت.');
        } catch (\Exception $e) {
            Log::channel('payment')->error('Pay.ir payment initiation error (admin, v2)', [
                'order_id' => $orderId,
                'error'    => $e->getMessage(),
            ]);
            return back()->with('error', 'خطا در پرداخت. لطفاً بعداً تلاش کنید.');
        }
    }

    public function success(Request $request)
    {
        // Pay.ir v2 sends "token" (not transId) via POST
        $token  = $request->input('token');
        $status = $request->input('status');

        if (!$token) {
            Log::channel('payment')->warning('Pay.ir callback: missing token');
            return redirect()->route('user.gateways')->with('error', 'اطلاعات پرداخت ناقص است.');
        }

        // P1-5: Idempotency — lockForUpdate + status check
        try {
            $transaction = DB::transaction(function () use ($token) {
                $t = Transaction::where('transaction_id', $token)
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
                Log::channel('payment')->warning('Pay.ir callback: transaction not found', ['token' => $token]);
                return redirect()->route('user.gateways')->with('error', 'تراکنش یافت نشد.');
            }
            Log::channel('payment')->info('Pay.ir callback: duplicate/already processed', ['token' => $token, 'state' => $msg]);
            return redirect()->route('user.gateways')->with('warning', 'این تراکنش قبلاً پردازش شده است.');
        }

        if ($status == '1' || $status == 1) {
            $gatewayInfo = json_decode($this->gateway->information, true);
            $apiKey  = $gatewayInfo['api_key'] ?? '';
            $sandbox = $gatewayInfo['sandbox_status'] ?? 0;

            try {
                $endpoint = $sandbox ? $this->sandboxVerify : $this->verifyUrl;
                $response = Http::timeout(30)
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->post($endpoint, [
                        'api'   => $apiKey,
                        'token' => $token,
                    ]);

                $result = $response->json();

                Log::channel('payment')->info('Pay.ir verification (admin, v2)', [
                    'token'  => $token,
                    'status' => $result['status'] ?? 'unknown',
                    'amount' => $result['amount'] ?? null,
                ]);

                if ($response->successful() && isset($result['status']) && $result['status'] == 1) {
                    // P1-4: amount mismatch protection
                    $verifiedAmount = isset($result['amount']) ? (int) $result['amount'] : null;
                    $expectedAmount = (int) $transaction->amount;
                    if ($verifiedAmount !== null && $verifiedAmount !== $expectedAmount) {
                        $transaction->update(['status' => 'failed']);
                        Log::channel('payment')->warning('Pay.ir amount mismatch (admin, v2)', [
                            'token'    => $token,
                            'verified' => $verifiedAmount,
                            'expected' => $expectedAmount,
                        ]);
                        return redirect()->route('user.gateways')
                            ->with('error', 'مبلغ تایید شده با سفارش هم‌خوانی ندارد.');
                    }

                    $transaction->update([
                        'status'        => 'success',
                        'tracking_code' => $result['transId'] ?? $token,
                    ]);

                    return redirect()->route('user.gateways')
                        ->with('success', 'پرداخت با موفقیت انجام شد. کد رهگیری: ' . ($result['transId'] ?? $token));
                }

                $transaction->update(['status' => 'failed']);
                Log::channel('payment')->warning('Pay.ir verify failed (admin, v2)', [
                    'token'  => $token,
                    'result' => $result,
                ]);
                return redirect()->route('user.gateways')->with('error', 'پرداخت تایید نشد.');
            } catch (\Exception $e) {
                $transaction->update(['status' => 'failed']);
                Log::channel('payment')->error('Pay.ir verification error (admin, v2)', [
                    'token' => $token,
                    'error' => $e->getMessage(),
                ]);
                return redirect()->route('user.gateways')
                    ->with('error', 'خطا در تایید پرداخت. لطفاً با پشتیبانی تماس بگیرید.');
            }
        }

        // Non-success status codes
        $transaction->update(['status' => 'failed']);
        $error_messages = [
            '0'  => 'پرداخت ناموفق بود.',
            '-1' => 'مبلغ کمتر از حداقل مجاز است.',
            '-2' => 'مبلغ بیشتر از حداکثر مجاز است.',
            '-3' => 'IP مسدود شده است.',
            '-4' => 'تراکنش تکراری است.',
            '-5' => 'اطلاعات ارسال شده نامعتبر است.',
            '-6' => 'درگاه غیر فعال است.',
            '-7' => 'پرداخت لغو شده توسط کاربر.',
            '-8' => 'خطای داخلی سیستم.',
        ];
        $error = $error_messages[$status] ?? ('پرداخت ناموفق بود. کد وضعیت: ' . $status);
        return redirect()->route('user.gateways')->with('error', $error);
    }

    public function cancel(Request $request)
    {
        return redirect()->route('user.gateways')->with('error', 'پرداخت توسط کاربر لغو شد.');
    }

    /**
     * Refund a payment (Pay.ir v2)
     * Note: Pay.ir refund API is limited and may require merchant-side settlement setup.
     */
    public function refund($token, $amount = null, $reason = 'Refund requested')
    {
        $apiUrl = 'https://pay.ir/pg/refund';

        $gatewayInfo = json_decode($this->gateway->information, true);
        $apiKey  = $gatewayInfo['api_key'] ?? '';
        $sandbox = $gatewayInfo['sandbox_status'] ?? 0;

        if (!$apiKey) {
            return ['success' => false, 'message' => 'درگاه Pay.ir تنظیم نشده است.'];
        }

        $payload = ['api' => $apiKey, 'token' => $token];
        if ($amount !== null) {
            $payload['amount'] = $amount;
        }

        try {
            $endpoint = $sandbox ? 'https://pay.ir/pg/sandbox/refund' : $apiUrl;
            $response = Http::timeout(30)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($endpoint, $payload);

            $result = $response->json();

            Log::channel('payment')->info('Pay.ir refund request (admin, v2)', [
                'token'  => $token,
                'amount' => $amount,
                'status' => $result['status'] ?? 'unknown',
            ]);

            if ($response->successful() && isset($result['status']) && $result['status'] == 1) {
                return [
                    'success' => true,
                    'message' => 'بازپرداخت با موفقیت انجام شد.',
                    'ref_id'  => $token,
                ];
            }

            $error_message = $result['errorMessage'] ?? 'خطا در بازپرداخت';
            Log::channel('payment')->warning('Pay.ir refund failed (admin, v2)', [
                'token'         => $token,
                'error_message' => $error_message,
            ]);
            return ['success' => false, 'message' => $error_message];
        } catch (\Exception $e) {
            Log::channel('payment')->error('Pay.ir refund error (admin, v2)', [
                'token' => $token,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return ['success' => false, 'message' => 'خطا در بازپرداخت. لطفاً مجدداً تلاش کنید.'];
        }
    }

    /**
     * Void a payment (best-effort — Pay.ir has no dedicated void API).
     */
    public function void($token)
    {
        return $this->refund($token, null, 'Payment voided');
    }
}
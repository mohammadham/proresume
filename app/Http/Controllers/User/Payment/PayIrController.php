<?php

namespace App\Http\Controllers\User\Payment;

use App\Http\Controllers\Front\UserCheckoutController;
use App\Http\Helpers\MegaMailer;
use App\Models\Package;
use App\Models\User\UserPaymentGateway;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redirect;
use App\Models\Language;
use App\Models\User\BasicSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;

class PayIrController extends Controller
{
    private $api_key;
    private $sandbox_mode;
    private $callback_url;
    private $description;

    public function __construct()
    {
        $data = UserPaymentGateway::whereKeyword('payir')
            ->where('user_id', getUser()->id)
            ->first();

        if ($data) {
            $paydata = $data->convertAutoData();
            $this->api_key = $paydata['api_key'] ?? '';
            $this->sandbox_mode = $paydata['sandbox_status'] ?? 1;
            $this->description = $paydata['text'] ?? 'پرداخت اشتراک';
            $this->callback_url = $paydata['callback_url'] ?? route('customer.appointment.payir.notify');
        }
    }

    public function paymentProcess(Request $request, $_amount, $_title, $_success_url, $_cancel_url)
    {
        $title = $_title;
        $price = $_amount;
        $price = round($price);
        $cancel_url = $_cancel_url;
        $success_url = $_success_url;

        Session::put('request', $request->all());
        Session::put('amount', $_amount);
        Session::put('paymentFor', Session::get('paymentFor'));

        $api_url = $this->sandbox_mode == 1
            ? 'https://pay.ir/pg/sandbox/send'
            : 'https://pay.ir/pg/send';

        $order_id = 'PAYIR_' . Str::uuid()->toString();

        $currentLang = session()->has('lang')
            ? Language::where('code', session()->get('lang'))->first()
            : Language::where('is_default', 1)->first();
        $baseCurrency = strtoupper($currentLang->basic_extended->base_currency_text ?? 'IRR');
        if (!in_array($baseCurrency, ['IRR', 'IRT'])) {
            return redirect($cancel_url)->with('error', 'ارز پایه سایت با درگاه ایرانی سازگار نیست.');
        }

        $amount = (int) round($baseCurrency === 'IRT' ? $price * 10 : $price);
        if ($amount < 10000) {
            return redirect($cancel_url)->with('error', 'حداقل مبلغ قابل پرداخت ۱۰،۰۰۰ ریال است.');
        }

        $payload = [
            'api' => $this->api_key,
            'amount' => $amount,
            'redirect' => $this->callback_url,
            'factorNumber' => $order_id,
            'mobile' => $request->phone ?? '',
            'description' => $this->description,
        ];

        try {
            $response = Http::timeout(30)->withHeaders([
                'Content-Type' => 'application/json',
            ])->post($api_url, $payload);

            $result = $response->json();

            Log::channel('payment')->info('Pay.ir payment initiation (appointment, v2)', [
                'order_id' => $order_id,
                'amount' => $amount,
                'sandbox' => $this->sandbox_mode,
                'status' => $result['status'] ?? 'unknown',
                'has_token' => isset($result['token']),
            ]);

            if (isset($result['status']) && $result['status'] == 1 && !empty($result['token'])) {
                $token = $result['token'];
                $payment_link = $this->sandbox_mode == 1
                    ? 'https://pay.ir/pg/sandbox/' . $token
                    : 'https://pay.ir/pg/' . $token;

                Session::put('payir_token', $token);
                Session::put('payir_order_id', $order_id);

                return Redirect::away($payment_link);
            } else {
                $error_message = $result['errorMessage'] ?? $result['status'] ?? 'خطا در اتصال به درگاه پرداخت';
                return redirect($cancel_url)->with('error', $error_message);
            }
        } catch (\Exception $e) {
            Log::channel('payment')->error('Pay.ir payment initiation error (appointment)', [
                'order_id' => $order_id,
                'error' => $e->getMessage(),
            ]);
            return redirect($cancel_url)->with('error', 'خطا در پرداخت. لطفاً مجدداً تلاش کنید.');
        }
    }

    public function successPayment(Request $request)
    {
        $requestData = Session::get('request');
        $currentLang = session()->has('lang') ? Language::where('code', session()->get('lang'))->first() : Language::where('is_default', 1)->first();
        $be = $currentLang->basic_extended;
        $bs = $currentLang->basic_setting;
        $cancel_url = Session::get('cancel_url') ?? route('front.register.view', ['status' => $requestData['package_type'] ?? 'regular', 'id' => $requestData['package_id'] ?? 1]);

        $token = $request->input('token');
        $status = $request->input('status');

        $session_token = Session::get('payir_token');
        $session_order_id = Session::get('payir_order_id');

        // P1-5: Idempotency - lockForUpdate to prevent race conditions
        try {
            $transaction = DB::transaction(function () use ($token) {
                $t = Transaction::where('transaction_id', $token)
                    ->whereHas('gateway', function($q) { $q->where('keyword', 'payir'); })
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
                return redirect($cancel_url)->with('error', 'شناسه تراکنش نامعتبر است.');
            }
            Log::channel('payment')->info('Pay.ir callback: duplicate/already processed', ['token' => $token, 'state' => $msg]);
            return redirect($cancel_url)->with('warning', 'این تراکنش قبلاً پردازش شده است.');
        }

        if ($status == '1' || $status == 1) {
            try {
                $api_url = $this->sandbox_mode == 1
                    ? 'https://pay.ir/pg/sandbox/verify'
                    : 'https://pay.ir/pg/verify';

                $response = Http::timeout(30)->withHeaders([
                    'Content-Type' => 'application/json',
                ])->post($api_url, [
                    'api' => $this->api_key,
                    'token' => $token,
                ]);

                $result = $response->json();

                Log::channel('payment')->info('Pay.ir payment verification (appointment, v2)', [
                    'token' => $token,
                    'status' => $result['status'] ?? 'unknown',
                    'amount' => $result['amount'] ?? null,
                ]);

                if (isset($result['status']) && $result['status'] == 1) {
                    $transaction_id = 'PAYIR_' . $token;

                    // P1-4: Amount mismatch guard
                    $verifiedAmount = isset($result['amount']) ? (int) $result['amount'] : null;
                    $expectedAmount = (int) Session::get('amount');
                    if ($verifiedAmount !== null && $verifiedAmount !== $expectedAmount) {
                        Log::channel('payment')->warning('Pay.ir amount mismatch', [
                            'token' => $token,
                            'expected' => $expectedAmount,
                            'received' => $verifiedAmount,
                        ]);
                        return redirect($cancel_url)->with('error', 'مبلغ تایید شده با سفارش هم‌خوانی ندارد.');
                    }

                    $transaction_details = json_encode([
                        'token' => $token,
                        'order_id' => $session_order_id,
                        'amount' => $result['amount'] ?? null,
                        'card_number' => $result['cardNumber'] ?? null,
                        'date' => now()->toDateTimeString(),
                    ]);
                    $amount = Session::get('amount');
                    $paymentFor = Session::get('paymentFor');

                    $checkout = new UserCheckoutController();
                    $user = $checkout->store($requestData, $transaction_id, $transaction_details, $amount, $be, uniqid('qrcode'));

                    if ($paymentFor == 'membership') {
                        return $this->handleMembershipSuccess($user, $requestData, $transaction_id, $transaction_details, $amount, $be, $bs);
                    } elseif ($paymentFor == 'extend') {
                        return $this->handleExtendSuccess($user, $requestData, $transaction_id, $transaction_details, $amount, $be, $bs);
                    }
                } else {
                    $error_message = $result['errorMessage'] ?? 'خطا در تایید پرداخت';
                    return redirect($cancel_url)->with('error', $error_message);
                }
            } catch (\Exception $e) {
                Log::channel('payment')->error('Pay.ir payment verification error (appointment)', [
                    'token' => $token,
                    'error' => $e->getMessage(),
                ]);
                return redirect($cancel_url)->with('error', 'خطا در تایید پرداخت.');
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
            return redirect($cancel_url)->with('error', $error_messages[$status] ?? 'پرداخت ناموفق بود. کد وضعیت: ' . $status);
        }

        return redirect($cancel_url);
    }

    public function cancelPayment()
    {
        $requestData = Session::get('request');
        $paymentFor = Session::get('paymentFor');
        session()->flash('warning', __('cancel_payment'));
        Session::forget('payir_token');
        Session::forget('payir_order_id');

        if ($paymentFor == 'membership') {
            return redirect()
                ->route('front.register.view', ['status' => $requestData['package_type'] ?? 'regular', 'id' => $requestData['package_id'] ?? 1])
                ->withInput($requestData);
        } else {
            return redirect()
                ->route('user.plan.extend.checkout', ['package_id' => $requestData['package_id'] ?? 1])
                ->withInput($requestData);
        }
    }

    private function handleMembershipSuccess($user, $requestData, $transaction_id, $transaction_details, $amount, $be, $bs)
    {
        $package = Package::findOrFail($requestData['package_id']);
        $lastMemb = $user->memberships()->orderBy('id', 'DESC')->first();
        $activation = Carbon::parse($lastMemb->start_date);
        $expire = Carbon::parse($lastMemb->expire_date);
        $file_name = $this->makeInvoice($requestData, 'registration', $user, uniqid('qrcode'), $amount, $requestData['payment_method'], $user->phone_number, $be->base_currency_symbol_position, $be->base_currency_symbol, $be->base_currency_text, $transaction_id, $package->title);
        $mailer = new MegaMailer();
        $data = [
            'toMail' => $user->email,
            'toName' => $user->fname,
            'username' => $user->username,
            'package_title' => $package->title,
            'package_price' => ($be->base_currency_text_position == 'left' ? $be->base_currency_text . ' ' : '') . $package->price . ($be->base_currency_text_position == 'right' ? ' ' . $be->base_currency_text : ''),
            'activation_date' => $activation->toFormattedDateString(),
            'expire_date' => Carbon::parse($expire->toFormattedDateString())->format('Y') == '9999' ? 'Lifetime' : $expire->toFormattedDateString(),
            'membership_invoice' => $file_name,
            'website_title' => $bs->website_title,
            'templateType' => 'registration_with_premium_package',
            'type' => 'registrationWithPremiumPackage',
        ];
        $mailer->mailFromAdmin($data);
        session()->flash('success', __('successful payment'));
        Session::forget('request');
        Session::forget('paymentFor');
        Session::forget('payir_token');
        Session::forget('payir_order_id');
        return redirect()->route('success.page');
    }

    private function handleExtendSuccess($user, $requestData, $transaction_id, $transaction_details, $amount, $be, $bs)
    {
        $package = Package::findOrFail($requestData['package_id']);
        $password = uniqid('qrcode');
        $checkout = new UserCheckoutController();
        $user = $checkout->store($requestData, $transaction_id, $transaction_details, $amount, $be, $password);
        $lastMemb = $user->memberships()->orderBy('id', 'DESC')->first();
        $activation = Carbon::parse($lastMemb->start_date);
        $expire = Carbon::parse($lastMemb->expire_date);
        $file_name = $this->makeInvoice($requestData, 'extend', $user, $password, $amount, $requestData['payment_method'], $user->phone_number, $be->base_currency_symbol_position, $be->base_currency_symbol, $be->base_currency_text, $transaction_id, $package->title);
        $mailer = new MegaMailer();
        $data = [
            'toMail' => $user->email,
            'toName' => $user->fname,
            'username' => $user->username,
            'package_title' => $package->title,
            'package_price' => ($be->base_currency_text_position == 'left' ? $be->base_currency_text . ' ' : '') . $package->price . ($be->base_currency_text_position == 'right' ? ' ' . $be->base_currency_text : ''),
            'activation_date' => $activation->toFormattedDateString(),
            'expire_date' => Carbon::parse($expire->toFormattedDateString())->format('Y') == '9999' ? 'Lifetime' : $expire->toFormattedDateString(),
            'membership_invoice' => $file_name,
            'website_title' => $bs->website_title,
            'templateType' => 'membership_extend',
            'type' => 'membershipExtend',
        ];
        $mailer->mailFromAdmin($data);
        session()->flash('success', __('successful payment'));
        Session::forget('request');
        Session::forget('paymentFor');
        Session::forget('payir_token');
        Session::forget('payir_order_id');
        return redirect()->route('success.page');
    }

    /**
     * Refund a payment (Pay.ir v2)
     */
    public function refund($token, $amount = null, $reason = 'Refund requested')
    {
        $apiUrl = 'https://pay.ir/pg/refund';

        $gateway = UserPaymentGateway::whereKeyword('payir')->where('user_id', getUser()->id)->first();
        $gatewayInfo = json_decode($gateway->information, true);
        $apiKey = $gatewayInfo['api_key'] ?? '';
        $sandbox = $gatewayInfo['sandbox_status'] ?? 0;

        if (!$apiKey) {
            return [
                'success' => false,
                'message' => 'درگاه Pay.ir تنظیم نشده است.',
            ];
        }

        $payload = [
            'api' => $apiKey,
            'token' => $token,
        ];

        if ($amount !== null) {
            $payload['amount'] = $amount;
        }

        try {
            $endpoint = $sandbox ? 'https://pay.ir/pg/sandbox/refund' : $apiUrl;
            $response = Http::timeout(30)->withHeaders([
                'Content-Type' => 'application/json',
            ])->post($endpoint, $payload);

            $result = $response->json();

            Log::channel('payment')->info('Pay.ir refund request (appointment, v2)', [
                'token' => $token,
                'amount' => $amount,
                'status' => $result['status'] ?? 'unknown',
            ]);

            if ($response->successful() && isset($result['status']) && $result['status'] == 1) {
                return [
                    'success' => true,
                    'message' => 'بازپرداخت با موفقیت انجام شد.',
                    'ref_id' => $token,
                ];
            }

            $error_message = $result['errorMessage'] ?? 'خطا در بازپرداخت';
            return [
                'success' => false,
                'message' => $error_message,
            ];
        } catch (\Exception $e) {
            Log::channel('payment')->error('Pay.ir refund error (appointment)', [
                'token' => $token,
                'error' => $e->getMessage(),
            ]);
            return [
                'success' => false,
                'message' => 'خطا در بازپرداخت. لطفاً مجدداً تلاش کنید.',
            ];
        }
    }

    /**
     * Void a payment
     */
    public function void($token)
    {
        return $this->refund($token, null, 'Payment voided');
    }
}

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

class IdPayController extends Controller
{
    private $api_key;
    private $sandbox_mode;
    private $callback_url;
    private $description;

    public function __construct()
    {
        $data = UserPaymentGateway::whereKeyword('idpay')
            ->where('user_id', getUser()->id)
            ->first();

        if ($data) {
            $paydata = $data->convertAutoData();
            $this->api_key = $paydata['api_key'] ?? '';
            $this->sandbox_mode = $paydata['sandbox_status'] ?? 1;
            $this->description = $paydata['text'] ?? 'پرداخت اشتراک';
            $this->callback_url = $paydata['callback_url'] ?? route('customer.appointment.idpay.notify');
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
            ? 'https://api.idpay.ir/v1.1/payment'
            : 'https://api.idpay.ir/v1.1/payment';

        $order_id = 'IDPAY_' . Str::uuid()->toString();

        $payload = [
            'order_id' => $order_id,
            'amount' => $price,
            'name' => $request->fname . ' ' . $request->lname ?? 'کاربر',
            'phone' => $request->phone ?? '',
            'mail' => $request->email ?? '',
            'desc' => $this->description,
            'callback' => $this->callback_url,
        ];

        try {
            $response = Http::timeout(30)->withHeaders([
                'Content-Type' => 'application/json',
                'X-API-KEY' => $this->api_key,
                'X-SANDBOX' => $this->sandbox_mode == 1 ? '1' : '0',
            ])->post($api_url, $payload);

            $result = $response->json();

            Log::channel('payment')->info('IDPay payment initiation', [
                'order_id' => $order_id,
                'amount' => $price,
                'sandbox' => $this->sandbox_mode,
                'response_code' => $result['error_code'] ?? $result['status'] ?? 'unknown',
                'has_link' => isset($result['link']),
            ]);

            if (isset($result['id']) && isset($result['link'])) {
                $payment_id = $result['id'];
                $payment_link = $result['link'];

                Session::put('idpay_payment_id', $payment_id);
                Session::put('idpay_order_id', $order_id);

                return Redirect::away($payment_link);
            } else {
                $error_message = $result['error_message'] ?? $result['error_code'] ?? 'خطa در اتصال به درگاه پرداخت';
                return redirect($cancel_url)->with('error', $error_message);
            }
        } catch (\Exception $e) {
            Log::channel('payment')->error('IDPay payment initiation error', [
                'order_id' => $order_id,
                'error' => $e->getMessage(),
            ]);
            return redirect($cancel_url)->with('error', 'خطa در اتصال به درگاه پرداخت: ' . $e->getMessage());
        }
    }

    public function successPayment(Request $request)
    {
        $requestData = Session::get('request');
        $currentLang = session()->has('lang') ? Language::where('code', session()->get('lang'))->first() : Language::where('is_default', 1)->first();
        $be = $currentLang->basic_extended;
        $bs = $currentLang->basic_setting;
        $cancel_url = Session::get('cancel_url') ?? route('front.register.view', ['status' => $requestData['package_type'] ?? 'regular', 'id' => $requestData['package_id'] ?? 1]);

        $payment_id = $request->input('id');
        $status = $request->input('status');
        $order_id = $request->input('order_id');

        $session_payment_id = Session::get('idpay_payment_id');
        $session_order_id = Session::get('idpay_order_id');

        if (!$payment_id || $payment_id !== $session_payment_id) {
            Log::channel('payment')->warning('IDPay callback: payment_id mismatch', [
                'request_payment_id' => $payment_id,
                'session_payment_id' => $session_payment_id,
            ]);
            return redirect($cancel_url)->with('error', 'شناسه پرداخت نامعتبر است.');
        }

        if ($status == '10') {
            try {
                $api_url = $this->sandbox_mode == 1
                    ? 'https://api.idpay.ir/v1.1/payment/verify'
                    : 'https://api.idpay.ir/v1.1/payment/verify';

                $response = Http::timeout(30)->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-API-KEY' => $this->api_key,
                    'X-SANDBOX' => $this->sandbox_mode == 1 ? '1' : '0',
                ])->post($api_url, [
                    'id' => $payment_id,
                    'order_id' => $session_order_id,
                ]);

                $result = $response->json();

                Log::channel('payment')->info('IDPay payment verification', [
                    'payment_id' => $payment_id,
                    'order_id' => $session_order_id,
                    'status' => $result['status'] ?? 'unknown',
                    'track_id' => $result['track_id'] ?? null,
                ]);

                if (isset($result['status']) && $result['status'] == '100') {
                    $transaction_id = $result['track_id'] ?? 'IDPAY_' . Str::uuid()->toString();
                    $transaction_details = json_encode([
                        'payment_id' => $payment_id,
                        'order_id' => $session_order_id,
                        'track_id' => $result['track_id'] ?? null,
                        'date' => $result['date'] ?? now()->toDateTimeString(),
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
                    $error_message = $result['error_message'] ?? 'خطa در تایید پرداخت';
                    return redirect($cancel_url)->with('error', $error_message);
                }
            } catch (\Exception $e) {
                Log::channel('payment')->error('IDPay payment verification error', [
                    'payment_id' => $payment_id,
                    'error' => $e->getMessage(),
                ]);
                return redirect($cancel_url)->with('error', 'خطa در تایید پرداخت: ' . $e->getMessage());
            }
        } else {
            $error_messages = [
                '-1' => 'ارسال اطلاعات ناموفق بود.',
                '-2' => 'خطa داخلی سیستم.',
                '-3' => 'اطلاعات ارسال شده نامعتبر است.',
                '-4' => 'مبلغ کمتر از حداقل مجاز است.',
                '-5' => 'مبلغ بیشتر از حداکثر مجاز است.',
                '-6' => 'تراکنش تکراری است.',
                '-7' => 'IP Address شما مسدود شده است.',
                '-8' => 'حداقل مبلغ برای این درگاه 10,000 ریال است.',
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
        Session::forget('idpay_payment_id');
        Session::forget('idpay_order_id');

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

    private function makeInvoice($requestData, $type, $user, $password, $amount, $payment_method, $phone, $currency_symbol_position, $currency_symbol, $currency_text, $transaction_id, $package_title)
    {
        $file_name = 'invoice_' . $transaction_id . '.pdf';
        return $file_name;
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
        Session::forget('idpay_payment_id');
        Session::forget('idpay_order_id');
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
        Session::forget('idpay_payment_id');
        Session::forget('idpay_order_id');
        return redirect()->route('success.page');
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

        $gateway = UserPaymentGateway::whereKeyword('idpay')->where('user_id', getUser()->id)->first();
        $gatewayInfo = json_decode($gateway->information, true);
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

            if ($response->successful() && isset($result['status']) && $result['status'] == 100) {
                return [
                    'success' => true,
                    'message' => 'بازپرداخت با موفقیت انجام شد.',
                    'ref_id' => $result['refund_id'] ?? null,
                ];
            } else {
                $error_message = $result['error_message'] ?? 'خطa در بازپرداخت';
                return [
                    'success' => false,
                    'message' => $error_message,
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'خطa در بازپرداخت: ' . $e->getMessage(),
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
        return $this->refund($paymentId, null, 'Payment voided');
    }
}

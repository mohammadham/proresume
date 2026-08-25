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
            ? 'https://pay.ir/payment/sandbox/send'
            : 'https://pay.ir/payment/send';

        $order_id = 'PAYIR_' . Str::uuid()->toString();

        $payload = [
            'api' => $this->api_key,
            'amount' => $price,
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

            Log::channel('payment')->info('Pay.ir payment initiation', [
                'order_id' => $order_id,
                'amount' => $price,
                'sandbox' => $this->sandbox_mode,
                'status' => $result['status'] ?? 'unknown',
                'has_trans_id' => isset($result['transId']),
            ]);

            if (isset($result['status']) && $result['status'] == 1 && isset($result['transId'])) {
                $trans_id = $result['transId'];
                $payment_link = $this->sandbox_mode == 1
                    ? 'https://pay.ir/payment/sandbox/' . $trans_id
                    : 'https://pay.ir/payment/' . $trans_id;

                Session::put('payir_trans_id', $trans_id);
                Session::put('payir_order_id', $order_id);

                return Redirect::away($payment_link);
            } else {
                $error_message = $result['errorMessage'] ?? $result['status'] ?? 'خطا در اتصال به درگاه پرداخت';
                return redirect($cancel_url)->with('error', $error_message);
            }
        } catch (\Exception $e) {
            Log::channel('payment')->error('Pay.ir payment initiation error', [
                'order_id' => $order_id,
                'error' => $e->getMessage(),
            ]);
            return redirect($cancel_url)->with('error', 'خطا در اتصال به درگاه پرداخت: ' . $e->getMessage());
        }
    }
public function successPayment(Request $request)
    {
        $requestData = Session::get('request');
        $currentLang = session()->has('lang') ? Language::where('code', session()->get('lang'))->first() : Language::where('is_default', 1)->first();
        $be = $currentLang->basic_extended;
        $bs = $currentLang->basic_setting;
        $cancel_url = Session::get('cancel_url') ?? route('front.register.view', ['status' => $requestData['package_type'] ?? 'regular', 'id' => $requestData['package_id'] ?? 1]);

        $trans_id = $request->input('transId');
        $status = $request->input('status');
        $factor_number = $request->input('factorNumber');

        $session_trans_id = Session::get('payir_trans_id');
        $session_order_id = Session::get('payir_order_id');

        if (!$trans_id || $trans_id !== $session_trans_id) {
            Log::channel('payment')->warning('Pay.ir callback: trans_id mismatch', [
                'request_trans_id' => $trans_id,
                'session_trans_id' => $session_trans_id,
            ]);
            return redirect($cancel_url)->with('error', 'شناسه تراکنش نامعتبر است.');
        }

        if ($status == '1' || $status == 1) {
            try {
                $api_url = $this->sandbox_mode == 1
                    ? 'https://pay.ir/payment/sandbox/verify'
                    : 'https://pay.ir/payment/verify';

                $response = Http::timeout(30)->withHeaders([
                    'Content-Type' => 'application/json',
                ])->post($api_url, [
                    'api' => $this->api_key,
                    'transId' => $trans_id,
                ]);

                $result = $response->json();

                Log::channel('payment')->info('Pay.ir payment verification', [
                    'trans_id' => $trans_id,
                    'status' => $result['status'] ?? 'unknown',
                    'amount' => $result['amount'] ?? null,
                ]);

                if (isset($result['status']) && $result['status'] == 1) {
                    $transaction_id = 'PAYIR_' . $trans_id;
                    $transaction_details = json_encode([
                        'trans_id' => $trans_id,
                        'factor_number' => $factor_number,
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
                Log::channel('payment')->error('Pay.ir payment verification error', [
                    'trans_id' => $trans_id,
                    'error' => $e->getMessage(),
                ]);
                return redirect($cancel_url)->with('error', 'خطا در تایید پرداخت: ' . $e->getMessage());
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
        Session::forget('payir_trans_id');
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
        Session::forget('payir_trans_id');
        Session::forget('payir_order_id');
        return redirect()->route('success.page');
    }

    public function cancelPayment()
    {
        $requestData = Session::get('request');
        $paymentFor = Session::get('paymentFor');
        session()->flash('warning', __('cancel_payment'));
        Session::forget('payir_trans_id');
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

    private function makeInvoice($requestData, $type, $user, $password, $amount, $payment_method, $phone, $currency_symbol_position, $currency_symbol, $currency_text, $transaction_id, $package_title)
    {
        $file_name = 'invoice_' . $transaction_id . '.pdf';
        return $file_name;
    }
}
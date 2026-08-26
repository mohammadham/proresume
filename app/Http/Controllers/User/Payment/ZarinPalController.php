<?php

namespace App\Http\Controllers\User\Payment;

use App\Http\Controllers\Front\UsercheckoutController;
use App\Http\Helpers\MegaMailer;
use App\Models\Package;
use App\Models\Transaction;
use App\Models\User\UserPaymentGateway;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redirect;
use App\Models\Language;
use App\Models\User\BasicSetting;
use App\Http\Helpers\UserPermissionHelper;
use Illuminate\Support\Str;

class ZarinPalController extends Controller
{
    private $merchant_id;
    private $sandbox_mode;
    private $callback_url;
    private $description;

    public function __construct()
    {
        $data = UserPaymentGateway::whereKeyword('zarinpal')
            ->where('user_id', getUser()->id)
            ->first();
        
        if ($data) {
            $paydata = $data->convertAutoData();
            $this->merchant_id = $paydata['merchant_id'] ?? '';
            $this->sandbox_mode = $paydata['sandbox_status'] ?? 1;
            $this->description = $paydata['text'] ?? 'پرداخت اشتراک';
            $this->callback_url = $paydata['callback_url'] ?? route('customer.appointment.zarinpal.notify');
        }
    }

    public function paymentProcess(Request $request, $_amount, $_title, $_success_url, $_cancel_url)
    {
        $title = $_title;
        $price = $_amount;
        $price = round($price);
        $cancel_url = $_cancel_url;
        $success_url = $_success_url;

        // Generate unique order ID for idempotency
        $orderId = 'ZARINPAL_' . Str::uuid()->toString();

        // Store request data in session for later use
        Session::put('request', $request->all());
        Session::put('amount', $_amount);
        Session::put('paymentFor', Session::get('paymentFor'));
        Session::put('zarinpal_order_id', $orderId);

        // Prepare data for ZarinPal API
        $api_url = $this->sandbox_mode == 1 
            ? 'https://sandbox.zarinpal.com/pg/v4/payment/request.json'
            : 'https://api.zarinpal.com/pg/v4/payment/request.json';

        $payload = [
            'merchant_id' => $this->merchant_id,
            'amount' => $price, // Amount in Tomans
            'callback_url' => $this->callback_url,
            'description' => $this->description,
            'metadata' => [
                'mobile' => $request->phone ?? '',
                'email' => $request->email ?? '',
                'order_id' => $orderId
            ]
        ];

        try {
            $response = Http::timeout(30)->post($api_url, $payload);
            $result = $response->json();

            if (isset($result['data']['code']) && $result['data']['code'] == 100) {
                $authority = $result['data']['authority'];
                Session::put('zarinpal_authority', $authority);
                
// Save transaction with idempotency key
                Transaction::create([
                    'user_id' => auth()->id() ?? null,
                    'gateway_id' => UserPaymentGateway::whereKeyword('zarinpal')->where('user_id', getUser()->id)->value('id'),
                    'amount' => $price,
                    'transaction_id' => $authority,
                    'order_id' => $orderId,
                    'status' => 'pending',
                    'currency' => 'IRR',
                    'ip' => $request->ip(),
                ]);
                $payment_url = $this->sandbox_mode == 1
                    ? 'https://sandbox.zarinpal.com/pg/StartPay/' . $authority
                    : 'https://www.zarinpal.com/pg/StartPay/' . $authority;

                return Redirect::away($payment_url);
            } else {
                $error_message = $result['errors']['message'] ?? 'خطا در اتصال به درگاه پرداخت';
                return redirect($cancel_url)->with('error', $error_message);
            }
        } catch (\Exception $e) {
            return redirect($cancel_url)->with('error', 'خطا در اتصال به درگاه پرداخت: ' . $e->getMessage());
        }
    }

    public function successPayment(Request $request)
    {
        $requestData = Session::get('request');
        $currentLang = session()->has('lang') ? Language::where('code', session()->get('lang'))->first() : Language::where('is_default', 1)->first();
        $be = $currentLang->basic_extended;
        $bs = $currentLang->basic_setting;
        
        $authority = $request->get('Authority');
        $status = $request->get('Status');
        $cancel_url = route('customer.appointment.zarinpal.cancel');

        // Find transaction by authority (stored as transaction_id)
        $transaction = Transaction::where('transaction_id', $authority)
            ->whereHas('gateway', function($q) { $q->where('keyword', 'zarinpal'); })
            ->first();

        if (!$transaction) {
            return redirect($cancel_url)->with('error', 'کد مرجع پرداخت معتبر نیست');
        }

        // Check if payment was successful on ZarinPal side
        if ($status != 'OK') {
            $transaction->update(['status' => 'failed']);
            return redirect($cancel_url)->with('error', 'پرداخت توسط کاربر لغو شد یا ناموفق بود');
        }

        // Verify payment with ZarinPal API
        $api_url = $this->sandbox_mode == 1
            ? 'https://sandbox.zarinpal.com/pg/v4/payment/verify.json'
            : 'https://api.zarinpal.com/pg/v4/payment/verify.json';

        $payload = [
            'merchant_id' => $this->merchant_id,
            'amount' => $transaction->amount,
            'authority' => $authority
        ];

        try {
            $response = Http::timeout(30)->post($api_url, $payload);
            $result = $response->json();

            // Code 100 = Success, 101 = Already verified
            if (isset($result['data']['code']) && in_array($result['data']['code'], [100, 101])) {
                $ref_id = $result['data']['ref_id'] ?? '';
                $paymentFor = Session::get('paymentFor');
                $package = Package::find($requestData['package_id']);
                $transaction_id = UserPermissionHelper::uniqidReal(8);
                $transaction_details = json_encode([
                    'authority' => $authority,
                    'ref_id' => $ref_id,
                    'code' => $result['data']['code']
                ]);

                // Update transaction status
                $transaction->update([
                    'status' => 'success',
                    'tracking_code' => $ref_id,
                ]);

                if ($paymentFor == 'membership') {
                    $amount = $requestData['price'];
                    $password = $requestData['password'];
                    $checkout = new UsercheckoutController();
                    $user = $checkout->store($requestData, $transaction_id, $transaction_details, $amount, $be, $password);

                    $lastMemb = $user->memberships()->orderBy('id', 'DESC')->first();
                    $activation = Carbon::parse($lastMemb->start_date);
                    $expire = Carbon::parse($lastMemb->expire_date);
                    $file_name = $this->makeInvoice($requestData, 'membership', $user, $password, $amount, 'ZarinPal', $requestData['phone'], $be->base_currency_symbol_position, $be->base_currency_symbol, $be->base_currency_text, $transaction_id, $package->title);

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
                    Session::forget('zarinpal_authority');
                    return redirect()->route('success.page');
                } elseif ($paymentFor == 'extend') {
                    $amount = $requestData['price'];
                    $password = uniqid('qrcode');
                    $checkout = new UsercheckoutController();
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
                    Session::forget('zarinpal_authority');
                    return redirect()->route('success.page');
                }
            } else {
// Update transaction status to failed
                $transaction->update(['status' => 'failed']);
                $error_message = $result['errors']['message'] ?? 'خطا در تایید پرداخت';
                return redirect($cancel_url)->with('error', $error_message);
            }
        } catch (\Exception $e) {
// Update transaction status to failed
            $transaction->update(['status' => 'failed']);
            return redirect($cancel_url)->with('error', 'خطا در تایید پرداخت: ' . $e->getMessage());
        }

        return redirect($cancel_url);
    }

    public function cancelPayment()
$authority = Session::get('zarinpal_authority');
        
        // Update transaction status if authority exists
        if ($authority) {
            $transaction = Transaction::where('transaction_id', $authority)
                ->whereHas('gateway', function($q) { $q->where('keyword', 'zarinpal'); })
                ->first();
            if ($transaction) {
                $transaction->update(['status' => 'cancelled']);
            }
        }
    {
        $requestData = Session::get('request');
        $paymentFor = Session::get('paymentFor');
        session()->flash('warning', __('cancel_payment'));
        Session::forget('zarinpal_authority');
        
        if ($paymentFor == 'membership') {
            return redirect()
                ->route('front.register.view', ['status' => $requestData['package_type'], 'id' => $requestData['package_id']])
                ->withInput($requestData);
        } else {
            return redirect()
                ->route('user.plan.extend.checkout', ['package_id' => $requestData['package_id']])
                ->withInput($requestData);
        }
    }

    // Helper method to generate invoice
    private function makeInvoice($requestData, $type, $user, $password, $amount, $payment_method, $phone, $currency_symbol_position, $currency_symbol, $currency_text, $transaction_id, $package_title)
    {
        // This is a simplified version - you may need to adjust based on actual implementation
        $file_name = 'invoice_' . $transaction_id . '.pdf';
        // Invoice generation logic would go here
        return $file_name;
    }
}
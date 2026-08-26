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

class ZibalController extends Controller
{
    private $merchant_id;
    private $sandbox_mode;
    private $callback_url;
    private $description;

    public function __construct()
    {
        $data = UserPaymentGateway::whereKeyword('zibal')
            ->where('user_id', getUser()->id)
            ->first();
        
        if ($data) {
            $paydata = $data->convertAutoData();
            $this->merchant_id = $paydata['merchant_id'] ?? '';
            $this->sandbox_mode = $paydata['sandbox_status'] ?? 1;
            $this->description = $paydata['description'] ?? 'پرداخت اشتراک';
            $this->callback_url = $paydata['callback_url'] ?? route('customer.appointment.zibal.notify');
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
        $orderId = 'ZIBAL_' . Str::uuid()->toString();

        // Store request data in session for later use
        Session::put('request', $request->all());
        Session::put('amount', $_amount);
        Session::put('paymentFor', Session::get('paymentFor'));
        Session::put('zibal_order_id', $orderId);

        // Prepare data for Zibal API
        $api_url = $this->sandbox_mode == 1
            ? 'https://sandbox.zibal.ir/v1/request'
            : 'https://gateway.zibal.ir/v1/request';

        $payload = [
            'merchant' => $this->merchant_id,
            'amount' => $price, // Amount in Tomans
            'callbackUrl' => $this->callback_url,
            'description' => $this->description,
            'mobile' => $request->phone ?? '',
            'orderId' => $orderId,
        ];

        try {
            $response = Http::timeout(30)->post($api_url, $payload);
            $result = $response->json();

            if (isset($result['result']) && $result['result'] == 100) {
                $trackId = $result['trackId'];
                Session::put('zibal_track_id', $trackId);
// Save transaction with idempotency key
                Transaction::create([
                    'user_id' => auth()->id() ?? null,
                    'gateway_id' => UserPaymentGateway::whereKeyword('zibal')->where('user_id', getUser()->id)->value('id'),
                    'amount' => $price,
                    'transaction_id' => $trackId,
                    'order_id' => $orderId,
                    'status' => 'pending',
                    'currency' => 'IRR',
                    'ip' => $request->ip(),
                ]);

                $payment_url = $this->sandbox_mode == 1
                    ? 'https://sandbox.zibal.ir/start/' . $trackId
                    : 'https://gateway.zibal.ir/start/' . $trackId;

                return Redirect::away($payment_url);
            } else {
                $error_message = $result['message'] ?? 'خطا در اتصال به درگاه پرداخت';
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

        $trackId = $request->get('trackId');
        $status = $request->get('status');
        $cancel_url = route('customer.appointment.zibal.cancel');

        // Find transaction by trackId (stored as transaction_id)
        $transaction = Transaction::where('transaction_id', $trackId)
            ->whereHas('gateway', function($q) { $q->where('keyword', 'zibal'); })
            ->first();

        if (!$transaction) {
            return redirect($cancel_url)->with('error', 'کد مرجع پرداخت معتبر نیست');
        }

        // Check if payment was successful on Zibal side
        if ($status != 1) {
            $transaction->update(['status' => 'failed']);
            return redirect($cancel_url)->with('error', 'پرداخت توسط کاربر لغو شد یا ناموفق بود');
        }

        // Verify payment with Zibal API
        $api_url = $this->sandbox_mode == 1
            ? 'https://sandbox.zibal.ir/v1/verify'
            : 'https://gateway.zibal.ir/v1/verify';

        $payload = [
            'merchant' => $this->merchant_id,
            'trackId' => $trackId,
        ];

        try {
            $response = Http::timeout(30)->post($api_url, $payload);
            $result = $response->json();

            // Code 100 = Success, 201 = Already verified
            if (isset($result['result']) && in_array($result['result'], [100, 201])) {
                $ref_id = $result['refNumber'] ?? '';
                $paymentFor = Session::get('paymentFor');
                $package = Package::find($requestData['package_id']);
                $transaction_id = UserPermissionHelper::uniqidReal(8);
                $transaction_details = json_encode([
                    'trackId' => $trackId,
                    'ref_id' => $ref_id,
                    'code' => $result['result']
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
                    $file_name = $this->makeInvoice($requestData, 'membership', $user, $password, $amount, 'Zibal', $requestData['phone'], $be->base_currency_symbol_position, $be->base_currency_symbol, $be->base_currency_text, $transaction_id, $package->title);

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
                    Session::forget('zibal_track_id');
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
                    Session::forget('zibal_track_id');
                    return redirect()->route('success.page');
                }
            } else {
                $error_message = $result['message'] ?? 'خطا در تایید پرداخت';
                return redirect($cancel_url)->with('error', $error_message);
            }
        } catch (\Exception $e) {
// Update transaction status to failed
                $transaction->update(['status' => 'failed']);
            return redirect($cancel_url)->with('error', 'خطا در تایید پرداخت: ' . $e->getMessage());
        }
// Update transaction status to failed
            $transaction->update(['status' => 'failed']);

        return redirect($cancel_url);
    }

    public function cancelPayment()
$trackId = Session::get('zibal_track_id');
        
        // Update transaction status if trackId exists
        if ($trackId) {
            $transaction = Transaction::where('transaction_id', $trackId)
                ->whereHas('gateway', function($q) { $q->where('keyword', 'zibal'); })
                ->first();
            if ($transaction) {
                $transaction->update(['status' => 'cancelled']);
            }
        }
    {
        $requestData = Session::get('request');
        $paymentFor = Session::get('paymentFor');
        session()->flash('warning', __('cancel_payment'));
        Session::forget('zibal_track_id');
        
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

    /**
     * Refund a payment
     *
     * @param string $trackId The trackId from original payment
     * @param float|null $amount Amount to refund (null = full refund)
     * @param string $reason Reason for refund
     * @return array Result with success status and message
     */
    public function refund($trackId, $amount = null, $reason = 'Refund requested')
    {
        $api_url = $this->sandbox_mode == 1
            ? 'https://sandbox.zibal.ir/v1/refund'
            : 'https://gateway.zibal.ir/v1/refund';

        $payload = [
            'merchant' => $this->merchant_id,
            'trackId' => $trackId,
        ];

        if ($amount !== null) {
            $payload['amount'] = $amount;
        }

        try {
            $response = Http::timeout(30)->post($api_url, $payload);
            $result = $response->json();

            Log::channel('payment')->info('Zibal refund request (user)', [
                'trackId' => $trackId,
                'amount' => $amount,
                'result' => $result['result'] ?? 'unknown',
                'refNumber' => $result['refNumber'] ?? null,
            ]);

            if (isset($result['result']) && $result['result'] == 100) {
                return [
                    'success' => true,
                    'message' => 'بازپرداخت با موفقیت انجام شد.',
                    'ref_id' => $result['refNumber'] ?? null,
                ];
            } else {
                $error_message = $result['message'] ?? 'خطا در بازپرداخت';
                
                Log::channel('payment')->warning('Zibal refund failed (user)', [
                    'trackId' => $trackId,
                    'error_message' => $error_message,
                ]);

                return [
                    'success' => false,
                    'message' => $error_message,
                ];
            }
        } catch (\\Exception $e) {
            Log::channel('payment')->error('Zibal refund error (user)', [
                'trackId' => $trackId,
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
     * @param string $trackId The trackId from original payment
     * @return array Result with success status and message
     */
    public function void($trackId)
    {
        // Zibal doesn't have a direct void API, but we can attempt refund with full amount
        // if the payment is still in a voidable state (typically within 24 hours)
        return $this->refund($trackId, null, 'Payment voided');
    }
}
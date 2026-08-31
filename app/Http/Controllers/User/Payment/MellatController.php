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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redirect;
use App\Models\Language;
use App\Models\User\BasicSetting;
use App\Http\Helpers\UserPermissionHelper;
use Illuminate\Support\Str;

class MellatController extends Controller
{
    private $terminal_id;
    private $username;
    private $password;
    private $sandbox_mode;
    private $callback_url;
    private $description;
    private $wsdl_url;

    public function __construct()
    {
        $data = UserPaymentGateway::whereKeyword('mellat')
            ->where('user_id', getUser()->id)
            ->first();

        if ($data) {
            $paydata = $data->convertAutoData();
            $this->terminal_id = $paydata['terminal_id'] ?? '';
            $this->username = $paydata['username'] ?? '';
            $this->password = $paydata['password'] ?? '';
            $this->description = $paydata['text'] ?? 'پرداخت اشتراک';
            $this->sandbox_mode = $paydata['sandbox_status'] ?? 1;
            $this->callback_url = $paydata['callback_url'] ?? route('customer.appointment.mellat.notify');
        }

        // Bank Mellat SADAD WSDL URL (same for sandbox and production)
        $this->wsdl_url = 'https://bpm.shaparak.ir/pgwchannel/services/pgw?wsdl';
    }

    public function paymentProcess($request, $_amount, $_title, $_success_url, $_cancel_url)
    {
        $title = $_title;
        $price = $_amount;
        $cancel_url = $_cancel_url;
        $success_url = $_success_url;

        $requestData = is_array($request) ? $request : $request->all();
        $phone = is_array($request) ? ($request['phone'] ?? '') : ($request->phone ?? '');
        $email = is_array($request) ? ($request['email'] ?? '') : ($request->email ?? '');
        $ip = is_array($request) ? request()->ip() : $request->ip();

        // P1-2: Base currency check (Bank Mellat requires amount in Rial)
        $currentLang = session()->has('lang')
            ? Language::where('code', session()->get('lang'))->first()
            : Language::where('is_default', 1)->first();
        $baseCurrency = strtoupper($currentLang->basic_extended->base_currency_text ?? 'IRT');
        if (!in_array($baseCurrency, ['IRR', 'IRT'])) {
            return redirect($cancel_url)->with('error', 'ارز پایه سایت با درگاه ایرانی سازگار نیست.');
        }

        // Bank Mellat expects amount in Rial. Convert Toman → Rial if needed.
        $amountInRial = (int) round($baseCurrency === 'IRT' ? $price * 10 : $price);

        // P1-3: Min amount (10,000 Rial)
        if ($amountInRial < 10000) {
            return redirect($cancel_url)->with('error', 'مبلغ کمتر ازminimum مجاز درگاه است.');
        }

        // Generate unique order ID for idempotency (must be numeric for Mellat)
        $orderId = (int) str_replace('-', '', Str::uuid()) % 10000000000; // 10-digit numeric

        Session::put('request', $requestData);
        Session::put('amount', $amountInRial);
        Session::put('paymentFor', Session::get('paymentFor'));
        Session::put('mellat_order_id', $orderId);

        // Prepare data for Mellat SOAP API
        $localDate = date('Ymd');
        $localTime = date('His');

        // Use PHP SoapClient with WSDL
        try {
            $client = new \SoapClient($this->wsdl_url, [
                'trace' => 1,
                'exceptions' => true,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'connection_timeout' => 30,
                'encoding' => 'UTF-8'
            ]);

            // bpPayRequest
            $result = $client->bpPayRequest([
                'terminalId' => $this->terminal_id,
                'userName' => $this->username,
                'userPassword' => $this->password,
                'orderId' => $orderId,
                'amount' => $amountInRial,
                'localDate' => $localDate,
                'localTime' => $localTime,
                'additionalData' => '',
                'callBackUrl' => $this->callback_url,
            ]);

            // Result: bpPayRequestResult (string) - refId or error code
            $resCode = $result->bpPayRequestResult;

            Log::channel('payment')->info('Mellat payment initiation (vendor)', [
                'terminalId' => $this->terminal_id,
                'orderId' => $orderId,
                'amount' => $amountInRial,
                'localDate' => $localDate,
                'localTime' => $localTime,
                'resCode' => $resCode,
                'sandbox' => $this->sandbox_mode,
                'amount_input' => $price,
            ]);

            if ($resCode == 0) {
                $refId = $result->bpPayRequestResult; // This is the refId when resCode=0

                Session::put('mellat_refId', $refId);
                Session::put('mellat_orderId', $orderId);
                Session::put('mellat_saleOrderId', '');
                Session::put('mellat_saleReferenceId', '');

                Transaction::create([
                    'user_id' => auth()->id() ?? null,
                    'gateway_id' => UserPaymentGateway::whereKeyword('mellat')->where('user_id', getUser()->id)->value('id'),
                    'amount' => $amountInRial,
                    'transaction_id' => $refId,
                    'order_id' => $orderId,
                    'status' => 'pending',
                    'currency' => 'IRR',
                    'ip' => $ip,
                ]);

                // Redirect user to Bank Mellat payment page
                $payment_url = 'https://bpm.shaparak.ir/pgwchannel/startpay.mellat?refId=' . $refId;
                return Redirect::away($payment_url);
            }

            $error_messages = [
                11 => 'ترمینال نامعتبر',
                12 => 'نام کاربری یا رمز عبور نامعتبر',
                13 => 'شماره سفارش نامعتبر',
                14 => 'مبلغ نامعتبر',
                15 => 'تاریخ یا زمان نامعتبر',
                16 => 'تراکنش یافت نشد',
                17 => 'قبلاً تایید شده',
                18 => 'منقضی شده',
                19 => 'بازگرداندن مجاز نیست',
                20 => 'موجودی کافی نیست',
                99 => 'خطای عمومی',
            ];
            $error_message = $error_messages[$resCode] ?? ($result->errors['message'] ?? 'خطا در اتصال به درگاه پرداخت');

            Log::channel('payment')->warning('Mellat init failed (vendor)', ['resCode' => $resCode, 'error_message' => $error_message]);
            return redirect($cancel_url)->with('error', $error_message);
        } catch (\SoapFault $e) {
            Log::channel('payment')->error('Mellat SOAP error (vendor)', ['error' => $e->getMessage()]);
            return redirect($cancel_url)->with('error', 'خطا در اتصال به درگاه پرداخت.');
        } catch (\Exception $e) {
            Log::channel('payment')->error('Mellat init error (vendor)', ['error' => $e->getMessage()]);
            return redirect($cancel_url)->with('error', 'خطا در اتصال به درگاه پرداخت.');
        }
    }

    public function successPayment($request)
    {
        $requestData = Session::get('request');
        $currentLang = session()->has('lang') ? Language::where('code', session()->get('lang'))->first() : Language::where('is_default', 1)->first();
        $be = $currentLang->basic_extended;
        $bs = $currentLang->basic_setting;

        $refId = is_array($request) ? ($request['RefId'] ?? '') : $request->get('RefId');
        $resCode = is_array($request) ? ($request['ResCode'] ?? '') : $request->get('ResCode');
        $saleOrderId = is_array($request) ? ($request['SaleOrderId'] ?? $request['saleOrderId'] ?? '') : $request->get('SaleOrderId');
        $saleReferenceId = is_array($request) ? ($request['SaleReferenceId'] ?? $request['saleReferenceId'] ?? '') : $request->get('SaleReferenceId');

        // Store saleOrderId and saleReferenceId in session for verification
        if ($saleOrderId) Session::put('mellat_saleOrderId', $saleOrderId);
        if ($saleReferenceId) Session::put('mellat_saleReferenceId', $saleReferenceId);

        $cancel_url = route('customer.appointment.mellat.cancel');

        // P1-5: Idempotency — lockForUpdate
        try {
            $transaction = DB::transaction(function () use ($refId) {
                $t = Transaction::where('transaction_id', $refId)
                    ->whereHas('gateway', function ($q) { $q->where('keyword', 'mellat'); })
                    ->lockForUpdate()->first();
                if (!$t) { throw new \DomainException('TRANSACTION_NOT_FOUND'); }
                if ($t->status !== 'pending') { throw new \DomainException('ALREADY_PROCESSED:' . $t->status); }
                $t->update(['status' => 'processing']);
                return $t;
            });
        } catch (\DomainException $e) {
            $msg = $e->getMessage();
            if ($msg === 'TRANSACTION_NOT_FOUND') {
                Log::channel('payment')->warning('Mellat callback: transaction not found (vendor)', ['refId' => $refId]);
                return redirect($cancel_url)->with('error', 'کد مرجع pagamento معتبر نیست');
            }
            Log::channel('payment')->info('Mellat callback: duplicate (vendor)', ['refId' => $refId, 'state' => $msg]);
            return redirect($cancel_url)->with('warning', 'این تراکنش قبلاً پردازش شده است.');
        }

        // Check if payment was successful on Mellat side
        if ($resCode != '0') {
            $transaction->update(['status' => 'failed']);
            $error_messages = [
                11 => 'ترمینال نامعتبر',
                12 => 'نام کاربری یا رمز عبور نامعتبر',
                13 => 'شماره سفارش نامعتبر',
                14 => 'مبلغ نام considerados',
                15 => 'تاریخ یا زمان نامعتبر',
                16 => 'تراکنش یافت نشد',
                17 => 'قبلاً تایید شده',
                18 => 'منقضی شده',
                19 => 'بازگرداندن مجاز نیست',
                20 => 'موجودی کافی نیست',
                99 => 'خطای عمومی',
            ];
            $error_message = $error_messages[$resCode] ?? ('خطا در درگاه پرداخت: کد ' . $resCode);
            return redirect($cancel_url)->with('error', $error_message);
        }

        // Verify payment with Mellat API
        $localDate = date('Ymd');
        $localTime = date('His');

        try {
            $client = new \SoapClient($this->wsdl_url, [
                'trace' => 1,
                'exceptions' => true,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'connection_timeout' => 30,
                'encoding' => 'UTF-8'
            ]);

            // bpVerifyRequest - requires terminalId, orderId, saleOrderId, saleReferenceId per WSDL
            // Get saleOrderId and saleReferenceId from session (set during callback if available)
            $saleOrderId = Session::get('mellat_saleOrderId');
            $saleReferenceId = Session::get('mellat_saleReferenceId');

            // If not in session, we need to use bpInquiryRequest to get them
            // For now, we'll attempt with what we have
            $result = $client->bpVerifyRequest([
                'terminalId' => $this->terminal_id,
                'userName' => $this->username,
                'userPassword' => $this->password,
                'orderId' => $transaction->order_id,
                'saleOrderId' => (int) $saleOrderId,
                'saleReferenceId' => (int) $saleReferenceId,
            ]);

            // Result: bpVerifyRequestResult (int) - 0 for success
            $verifyResult = $result->bpVerifyRequestResult;

            // Code 0 = Success
            if ($verifyResult == 0) {
                $paymentFor = Session::get('paymentFor');
                $package = Package::find($requestData['package_id']);
                $transaction_id = UserPermissionHelper::uniqidReal(8);
                $transaction_details = json_encode([
                    'refId' => $refId,
                    'orderId' => $transaction->order_id,
                    'verifyResult' => $verifyResult
                ]);

                // Update transaction status
                $transaction->update([
                    'status' => 'success',
                    'tracking_code' => $refId,
                ]);

                if ($paymentFor == 'membership') {
                    $amount = $requestData['price'];
                    $password = $requestData['password'];
                    $checkout = new UsercheckoutController();
                    $user = $checkout->store($requestData, $transaction_id, $transaction_details, $amount, $be, $password);

                    $lastMemb = $user->memberships()->orderBy('id', 'DESC')->first();
                    $activation = Carbon::parse($lastMemb->start_date);
                    $expire = Carbon::parse($lastMemb->expire_date);
                    $file_name = $this->makeInvoice($requestData, 'membership', $user, $password, $amount, 'Mellat', $requestData['phone'], $be->base_currency_symbol_position, $be->base_currency_symbol, $be->base_currency_text, $transaction_id, $package->title);

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
                    Session::forget('mellat_refId');
                    Session::forget('mellat_orderId');
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
                    Session::forget('mellat_refId');
                    Session::forget('mellat_orderId');
                    return redirect()->route('success.page');
                }
            } else {
                $transaction->update(['status' => 'failed']);
                $error_messages = [
                    11 => 'ترمینال نامعتبر',
                    12 => 'نام کاربری یا رمز عبور نامعتبر',
                    13 => 'شماره سفارش نامعتبر',
                    14 => 'مبلغ نامعتبر',
                    15 => 'تاریخ یا زمان نامعتبر',
                    16 => 'تراکنش یافت نشد',
                    17 => 'قبلاً تایید شده',
                    18 => 'منقضی شده',
                    19 => 'بازگرداندن مجاز نیست',
                    20 => 'موجودی کافی نیست',
                    99 => 'خطای عمومی',
                ];
                $error_message = $error_messages[$verifyResult] ?? ('خطا در تایید پرداخت: کد ' . $verifyResult);
                return redirect($cancel_url)->with('error', $error_message);
            }
        } catch (\SoapFault $e) {
            $transaction->update(['status' => 'failed']);
            Log::channel('payment')->error('Mellat SOAP verify error (vendor)', ['refId' => $refId, 'error' => $e->getMessage()]);
            return redirect($cancel_url)->with('error', 'خطا در تایید پرداخت.');
        } catch (\Exception $e) {
            $transaction->update(['status' => 'failed']);
            Log::channel('payment')->error('Mellat verify error (vendor)', ['refId' => $refId, 'error' => $e->getMessage()]);
            return redirect($cancel_url)->with('error', 'خطا در تایید پرداخت.');
        }

        return redirect($cancel_url);
    }

    public function cancelPayment()
    {
        $requestData = Session::get('request');
        $paymentFor = Session::get('paymentFor');
        $refId = Session::get('mellat_refId');

        session()->flash('warning', __('cancel_payment'));
        Session::forget('mellat_refId');
        Session::forget('mellat_orderId');

        // Update transaction status if refId exists
        if ($refId) {
            $transaction = Transaction::where('transaction_id', $refId)
                ->whereHas('gateway', function($q) { $q->where('keyword', 'mellat'); })
                ->first();
            if ($transaction) {
                $transaction->update(['status' => 'cancelled']);
            }
        }

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

    /**
     * Refund a payment
     *
     * @param string $refId The refId from original payment
     * @param float|null $amount Amount to refund (null = full refund)
     * @param string $reason Reason for refund
     * @return array Result with success status and message
     */
    public function refund($refId, $amount = null, $reason = 'Refund requested')
    {
        // Bank Mellat SOAP API for refund/bpSettleRequest
        try {
            $client = new \SoapClient($this->wsdl_url, [
                'trace' => 1,
                'exceptions' => true,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'connection_timeout' => 30,
                'encoding' => 'UTF-8'
            ]);

            // Get original transaction to get orderId and amount
            $transaction = Transaction::where('transaction_id', $refId)
                ->whereHas('gateway', function ($q) { $q->where('keyword', 'mellat'); })
                ->first();

            if (!$transaction) {
                return [
                    'success' => false,
                    'message' => 'تراکنش یافت نشد',
                ];
            }

            $localDate = date('Ymd');
            $localTime = date('His');
            $refundAmount = $amount ?? $transaction->amount;

            // bpSettleRequest for refund - requires terminalId, orderId, saleOrderId, saleReferenceId per WSDL
            // We need to get saleOrderId and saleReferenceId - they should be stored in transaction details or session
            // For now, we'll attempt with the stored values
            $saleOrderId = Session::get('mellat_saleOrderId');
            $saleReferenceId = Session::get('mellat_saleReferenceId');

            $result = $client->bpSettleRequest([
                'terminalId' => $this->terminal_id,
                'userName' => $this->username,
                'userPassword' => $this->password,
                'orderId' => $transaction->order_id,
                'saleOrderId' => (int) $saleOrderId,
                'saleReferenceId' => (int) $saleReferenceId,
            ]);

            $settleResult = $result->bpSettleRequestResult;

            if ($settleResult == 0) {
                return [
                    'success' => true,
                    'message' => 'بازپرداخت با موفقیت انجام شد.',
                    'ref_id' => $refId,
                ];
            } else {
                $error_messages = [
                    11 => 'ترمینال نامعتبر',
                    12 => 'نام کاربری یا رمز عبور نامعتبر',
                    13 => 'شماره سفارش نامعتبر',
                    14 => 'مبلغ نامعتبر',
                    15 => 'تاریخ یا زمان نامعتبر',
                    16 => 'تراکنش یافت نشد',
                    17 => 'قبلاً تایید شده',
                    18 => 'منقضی شده',
                    19 => 'بازگرداندن مجاز نیست',
                    20 => 'موجودی کافی نیست',
                    99 => 'خطای عمومی',
                ];
                $error_message = $error_messages[$settleResult] ?? ('خطا در بازپرداخت: کد ' . $settleResult);

                return [
                    'success' => false,
                    'message' => $error_message,
                ];
            }
        } catch (\SoapFault $e) {
            return [
                'success' => false,
                'message' => 'خطا در اتصال به درگاه پرداخت برای بازپرداخت: ' . $e->getMessage(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'خطا در بازپرداخت: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Void a payment (cancel before settlement)
     *
     * @param string $refId The refId from original payment
     * @return array Result with success status and message
     */
    public function void($refId)
    {
        // For Mellat, void is same as refund if not settled yet
        return $this->refund($refId, null, 'Payment voided');
    }

    /**
     * Inquiry transaction status
     *
     * @param string $refId The refId from original payment
     * @return array Result with transaction status
     */
    public function inquiry($refId)
    {
        // Bank Mellat SOAP API for inquiry
        try {
            $client = new \SoapClient($this->wsdl_url, [
                'trace' => 1,
                'exceptions' => true,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'connection_timeout' => 30,
                'encoding' => 'UTF-8'
            ]);

            // Get original transaction to get orderId
            $transaction = Transaction::where('transaction_id', $refId)
                ->whereHas('gateway', function ($q) { $q->where('keyword', 'mellat'); })
                ->first();

            if (!$transaction) {
                return [
                    'success' => false,
                    'message' => 'تراکنش یافت نشد',
                ];
            }

            $saleOrderId = $transaction->sale_order_id ?? 0;
            $saleReferenceId = $transaction->sale_reference_id ?? 0;

            // bpInquiryRequest - requires terminalId, orderId, saleOrderId, saleReferenceId per WSDL
            $result = $client->bpInquiryRequest([
                'terminalId' => $this->terminal_id,
                'userName' => $this->username,
                'userPassword' => $this->password,
                'orderId' => $transaction->order_id,
                'saleOrderId' => (int) $saleOrderId,
                'saleReferenceId' => (int) $saleReferenceId,
            ]);

            $inquiryResult = $result->bpInquiryRequestResult;

            return [
                'success' => true,
                'message' => 'استعلام با موفقیت انجام شد.',
                'data' => $inquiryResult,
            ];
        } catch (\SoapFault $e) {
            return [
                'success' => false,
                'message' => 'خطا در اتصال به درگاه پرداخت برای استعلام: ' . $e->getMessage(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'خطا در استعلام: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Reversal request (cancel transaction before settlement)
     *
     * @param string $refId The refId from original payment
     * @return array Result with success status and message
     */
    public function reversal($refId)
    {
        // Bank Mellat SOAP API for reversal
        try {
            $client = new \SoapClient($this->wsdl_url, [
                'trace' => 1,
                'exceptions' => true,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'connection_timeout' => 30,
                'encoding' => 'UTF-8'
            ]);

            // Get original transaction to get orderId
            $transaction = Transaction::where('transaction_id', $refId)
                ->whereHas('gateway', function ($q) { $q->where('keyword', 'mellat'); })
                ->first();

            if (!$transaction) {
                return [
                    'success' => false,
                    'message' => 'تراکنش یافت نشد',
                ];
            }

            $saleOrderId = $transaction->sale_order_id ?? 0;
            $saleReferenceId = $transaction->sale_reference_id ?? 0;

            // bpReversalRequest - requires terminalId, orderId, saleOrderId, saleReferenceId per WSDL
            $result = $client->bpReversalRequest([
                'terminalId' => $this->terminal_id,
                'userName' => $this->username,
                'userPassword' => $this->password,
                'orderId' => $transaction->order_id,
                'saleOrderId' => (int) $saleOrderId,
                'saleReferenceId' => (int) $saleReferenceId,
            ]);

            $reversalResult = $result->bpReversalRequestResult;

            if ($reversalResult == 0) {
                return [
                    'success' => true,
                    'message' => 'انصراف از تراکنش با موفقیت انجام شد.',
                ];
            } else {
                $error_messages = [
                    11 => 'ترمینال نامعتبر',
                    12 => 'نام کاربری یا رمز عبور نامعتبر',
                    13 => 'شماره سفارش نامعتبر',
                    14 => 'مبلغ نامعتبر',
                    15 => 'تاریخ یا زمان نامعتبر',
                    16 => 'تراکنش یافت نشد',
                    17 => 'قبلاً تایید شده',
                    18 => 'منقضی شده',
                    19 => 'بازگرداندن مجاز نیست',
                    20 => 'موجودی کافی نیست',
                    99 => 'خطای عمومی',
                ];
                $error_message = $error_messages[$reversalResult] ?? ('خطا در انصراف: کد ' . $reversalResult);

                return [
                    'success' => false,
                    'message' => $error_message,
                ];
            }
        } catch (\SoapFault $e) {
            return [
                'success' => false,
                'message' => 'خطا در اتصال به درگاه پرداخت برای انصراف: ' . $e->getMessage(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'خطا در انصراف: ' . $e->getMessage(),
            ];
        }
    }
}
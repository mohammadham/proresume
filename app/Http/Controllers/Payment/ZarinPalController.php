<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\User\UserCheckoutController;
use App\Http\Controllers\Front\CheckoutController;
use App\Http\Helpers\UserPermissionHelper;
use App\Models\Package;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Helpers\MegaMailer;
use App\Models\Language;
use App\Models\PaymentGateway;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Redirect;

class ZarinPalController extends Controller
{
    private $merchant_id;
    private $sandbox_mode;
    private $callback_url;
    private $description;

    public function __construct()
    {
        $data = PaymentGateway::whereKeyword('zarinpal')->first();
        if ($data) {
            $paydata = $data->convertAutoData();
            $this->merchant_id = $paydata['merchant_id'] ?? '';
            $this->description = $paydata['description'] ?? 'پرداخت اشتراک';
            $this->sandbox_mode = $paydata['sandbox_status'] ?? 1;
            // P1-8: honor admin-configured callback_url when present, fallback to route
            $this->callback_url = !empty($paydata['callback_url'])
                ? $paydata['callback_url']
                : route('membership.zarinpal.success');
        }
    }

    public function paymentProcess(Request $request, $_amount, $_title, $_success_url, $_cancel_url)
    {
        $title = $_title;
        $price = $_amount;
        $cancel_url = $_cancel_url;
        $success_url = $_success_url;

        // P1-2: Base currency check. ZarinPal accepts both IRR and IRT natively.
        $currentLang = session()->has('lang')
            ? Language::where('code', session()->get('lang'))->first()
            : Language::where('is_default', 1)->first();
        $baseCurrency = strtoupper($currentLang->basic_extended->base_currency_text ?? 'IRT');
        if (!in_array($baseCurrency, ['IRR', 'IRT'])) {
            return redirect($cancel_url)->with('error', 'ارز پایه سایت با درگاه ایرانی سازگار نیست.');
        }

        // ZarinPal supports IRT natively; if base is IRR, keep as IRR (send currency=IRR).
        $price = (int) round($price);
        $currency = $baseCurrency;

        // P1-3: Min amount check for ZarinPal (1,000 Rial = 100 Toman)
        $minAmount = $currency === 'IRT' ? 100 : 1000;
        if ($price < $minAmount) {
            return redirect($cancel_url)->with('error', 'مبلغ کمتر از حداقل مجاز درگاه است.');
        }

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
            'amount' => $price,
            'currency' => $currency,
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

            Log::channel('payment')->info('ZarinPal payment initiation (admin)', [
                'amount' => $price,
                'sandbox' => $this->sandbox_mode,
                'status' => $result['data']['code'] ?? 'unknown',
                'has_authority' => isset($result['data']['authority']),
                'order_id' => $orderId,
            ]);

            if (isset($result['data']['code']) && $result['data']['code'] == 100) {
                $authority = $result['data']['authority'];

                // Save transaction with idempotency key
                Transaction::create([
                    'user_id' => auth()->id() ?? null,
                    'gateway_id' => PaymentGateway::whereKeyword('zarinpal')->value('id'),
                    'amount' => $price,
                    'transaction_id' => $authority,
                    'order_id' => $orderId,
                    'status' => 'pending',
                    'currency' => $currency,
                    'ip' => $request->ip(),
                ]);

                Session::put('zarinpal_authority', $authority);

                $payment_url = $this->sandbox_mode == 1
                    ? 'https://sandbox.zarinpal.com/pg/StartPay/' . $authority
                    : 'https://www.zarinpal.com/pg/StartPay/' . $authority;

                return Redirect::away($payment_url);
            } else {
                $error_code = $result['data']['code'] ?? 0;
                $error_messages = [
                    -9 => 'خطای اعتبارسنجی داده‌ها',
                    -10 => 'مرچنت کد یافت نشد',
                    -11 => 'مرچنت غیرفعال است',
                    -12 => 'مبلغ نامعتبر است',
                    -13 => 'مبلغ کمتر از حداقل مجاز',
                    -14 => 'مبلغ بیشتر از حداکثر مجاز',
                    -15 => 'تراکنش تکراری',
                    -16 => 'خطای داخلی',
                    -17 => 'IP مسدود شده',
                    -18 => 'مرچنت تایید نشده',
                    -19 => 'Callback URL نامعتبر',
                    -20 => 'Description نامعتبر',
                    -21 => 'موبایل نامعتبر',
                    -22 => 'ایمیل نامعتبر',
                ];
                $error_message = $error_messages[$error_code] ?? ($result['errors']['message'] ?? 'خطا در اتصال به درگاه پرداخت');

                Log::channel('payment')->warning('ZarinPal payment initiation failed (admin)', [
                    'error_code' => $error_code,
                    'error_message' => $error_message,
                ]);

                return redirect($cancel_url)->with('error', $error_message);
            }
        } catch (\Exception $e) {
            Log::channel('payment')->error('ZarinPal payment initiation error (admin)', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect($cancel_url)->with('error', 'خطا در اتصال به درگاه پرداخت.');
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

        $cancel_url = route('front.register.view', ['status' => $requestData['package_type'] ?? 'regular', 'id' => $requestData['package_id'] ?? 1]);

        // P1-5: Idempotency — lock the row and check it's still pending
        try {
            $transaction = DB::transaction(function () use ($authority) {
                $t = Transaction::where('transaction_id', $authority)
                    ->whereHas('gateway', function ($q) { $q->where('keyword', 'zarinpal'); })
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
                Log::channel('payment')->warning('ZarinPal callback: transaction not found', ['authority' => $authority]);
                return redirect($cancel_url)->with('error', 'تراکنش یافت نشد. لطفاً مجدداً تلاش کنید.');
            }
            Log::channel('payment')->info('ZarinPal callback: duplicate/already processed', ['authority' => $authority, 'state' => $msg]);
            return redirect($cancel_url)->with('warning', 'این تراکنش قبلاً پردازش شده است.');
        }

        if ($status == 'OK' && $authority) {
            // Prepare data for ZarinPal verification API
            $api_url = $this->sandbox_mode == 1
                ? 'https://sandbox.zarinpal.com/pg/v4/payment/verify.json'
                : 'https://api.zarinpal.com/pg/v4/payment/verify.json';

            $payload = [
                'merchant_id' => $this->merchant_id,
                'authority' => $authority,
                'amount' => $transaction->amount,
                'currency' => $transaction->currency ?? 'IRT',
            ];

            try {
                $response = Http::timeout(30)->post($api_url, $payload);
                $result = $response->json();

                Log::channel('payment')->info('ZarinPal payment verification (admin)', [
                    'authority' => $authority,
                    'status' => $result['data']['code'] ?? 'unknown',
                    'ref_id' => $result['data']['ref_id'] ?? null,
                ]);

                if (isset($result['data']['code']) && ($result['data']['code'] == 100 || $result['data']['code'] == 101)) {
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
                    $checkout = new CheckoutController();
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
                    Session::forget('zarinpal_authority');
                    return redirect()->route('success.page');
                }
            } else {
                $error_code = $result['data']['code'] ?? 0;
                
                // Update transaction status to failed
                // $transaction->update(['status' => 'failed']);
                
                $error_messages = [
                    -9 => 'خطای اعتبارسنجی داده‌ها',
                    -10 => 'مرچنت کد یافت نشد',
                    -11 => 'مرچنت غیرفعال است',
                    -12 => 'مبلغ نامعتبر است',
                    -13 => 'مبلغ کمتر از حداقل مجاز',
                    -14 => 'مبلغ بیشتر از حداکثر مجاز',
                    -15 => 'تراکنش تکراری',
                    -16 => 'خطای داخلی',
                    -17 => 'IP مسدود شده',
                    -18 => 'مرچنت تایید نشده',
                    -19 => 'Callback URL نامعتبر',
                    -20 => 'Description نامعتبر',
                    -21 => 'موبایل نامعتبر',
                    -22 => 'ایمیل نامعتبر',
                    -30 => 'تراکنش یافت نشد',
                    -31 => 'تراکنش تایید شده است',
                    -32 => 'مبلغ تایید شده با مبلغ درخواستی متفاوت است',
                    -33 => 'تراکنش انقضا یافته',
                    -34 => 'تراکنش لغو شده',
                    -35 => 'تراکنش نامعتبر',
                    -36 => 'تراکنش تکراری',
                    -40 => 'خطای سیستمی',
                    -41 => 'مرچنت تایید نشده',
                    -42 => 'تراکنش در انتظار تایید',
                    -50 => 'خطای بانک',
                    -51 => 'بانک در دسترس نیست',
                    -52 => 'خطای شبکه',
                    -53 => 'مبلغ کمتر از حداقل',
                    -54 => 'مبلغ بیشتر از حداکثر',
                ];
                $error_message = $error_messages[$error_code] ?? ($result['errors']['message'] ?? 'خطا در تایید پرداخت');

                Log::channel('payment')->warning('ZarinPal payment verification failed (admin)', [
                    'authority' => $authority,
                    'error_code' => $error_code,
                    'error_message' => $error_message,
                ]);
                // Update transaction status to failed
                // $transaction->update(['status' => 'failed']);
                return redirect($cancel_url)->with('error', $error_message);

            }
        } catch (\Exception $e) {
            Log::channel('payment')->error('ZarinPal payment verification error (admin)', [
                'authority' => $authority,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect($cancel_url)->with('error', 'خطا در تایید پرداخت.');
        
        }
    } else {
        $error_message = ($status == 'NOK') ? 'پرداخت توسط کاربر لغو شد یا ناموفق بود.' : 'پرداخت ناموفق بود.';
        Log::channel('payment')->warning('ZarinPal payment cancelled/failed (admin)', [
            'status' => $status,
            'authority' => $authority,
        ]);
        return redirect($cancel_url)->with('error', $error_message);
    }

    return redirect($cancel_url);
    }

    public function cancelPayment()
    {
        $requestData = Session::get('request');
        $paymentFor = Session::get('paymentFor');
        $authority = Session::get('zarinpal_authority');
        
        session()->flash('warning', __('cancel_payment'));
        Session::forget('zarinpal_authority');
        
        // Update transaction status if authority exists
        if ($authority) {
            $transaction = Transaction::where('transaction_id', $authority)
                ->whereHas('gateway', function($q) { $q->where('keyword', 'zarinpal'); })
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

    // Note: makeInvoice() is inherited from App\Http\Controllers\Controller (real implementation)

    /**
     * Refund a payment
     *
     * @param string $authority The authority from original payment
     * @param float|null $amount Amount to refund (null = full refund)
     * @param string $reason Reason for refund
     * @return array Result with success status and message
     */
    public function refund($authority, $amount = null, $reason = 'Refund requested')
    {
        // NOTE: ZarinPal V4 refund actually requires OAuth Bearer access token + session_id
        // (not merchant_id + authority). This endpoint call will fail with the current signature.
        // Until OAuth is wired up, throw so the UI does not silently pretend success.
        throw new \RuntimeException('ZarinPal refund is not configured. Please contact support.');

        // Unreachable — kept for future OAuth-based refund implementation.
        // phpcs:disable
        $api_url = $this->sandbox_mode == 1
            ? 'https://sandbox.zarinpal.com/pg/v4/payment/refund.json'
            : 'https://api.zarinpal.com/pg/v4/payment/refund.json';

        $payload = [
            'merchant_id' => $this->merchant_id,
            'authority' => $authority,
            // 'currency' => $currency,
        ];

        if ($amount !== null) {
            $payload['amount'] = $amount;
        }

        try {
            $response = Http::timeout(30)->post($api_url, $payload);
            $result = $response->json();

            Log::channel('payment')->info('ZarinPal refund request (admin)', [
                'authority' => $authority,
                'amount' => $amount,
                'status' => $result['data']['code'] ?? 'unknown',
                'ref_id' => $result['data']['ref_id'] ?? null,
            ]);

            if (isset($result['data']['code']) && $result['data']['code'] == 100) {
                return [
                    'success' => true,
                    'message' => 'بازپرداخت با موفقیت انجام شد.',
                    'ref_id' => $result['data']['ref_id'] ?? null,
                ];
            } else {
                $error_code = $result['data']['code'] ?? 0;
                $error_messages = [
                    -9 => 'خطای اعتبارسنجی داده‌ها',
                    -10 => 'مرچنت کد یافت نشد',
                    -11 => 'مرچنت غیرفعال است',
                    -12 => 'مبلغ نامعتبر است',
                    -13 => 'مبلغ کمتر از حداقل مجاز',
                    -14 => 'مبلغ بیشتر از حداکثر مجاز',
                    -15 => 'تراکنش تکراری',
                    -16 => 'خطای داخلی',
                    -17 => 'IP مسدود شده',
                    -18 => 'مرچنت تایید نشده',
                    -19 => 'Callback URL نامعتبر',
                    -20 => 'Description نامعتبر',
                    -21 => 'موبایل نامعتبر',
                    -22 => 'ایمیل نامعتبر',
                    -30 => 'تراکنش یافت نشد',
                    -31 => 'تراکنش تایید شده است',
                    -32 => 'مبلغ تایید شده با مبلغ درخواستی متفاوت است',
                    -33 => 'تراکنش انقضا یافته',
                    -34 => 'تراکنش لغو شده',
                    -35 => 'تراکنش نامعتبر',
                    -36 => 'تراکنش تکراری',
                    -40 => 'خطای سیستمی',
                    -41 => 'مرچنت تایید نشده',
                    -42 => 'تراکنش در انتظار تایید',
                    -50 => 'خطای بانک',
                    -51 => 'بانک در دسترس نیست',
                    -52 => 'خطای شبکه',
                    -53 => 'مبلغ کمتر از حداقل',
                    -54 => 'مبلغ بیشتر از حداکثر',
                    -60 => 'بازپرداخت امکان‌پذیر نیست',
                    -61 => 'مبلغ بازپرداخت بیشتر از مبلغ تراکنش',
                ];
                $error_message = $error_messages[$error_code] ?? ($result['errors']['message'] ?? 'خطا در بازپرداخت');

                Log::channel('payment')->warning('ZarinPal refund failed (admin)', [
                    'authority' => $authority,
                    'error_code' => $error_code,
                    'error_message' => $error_message,
                ]);

                return [
                    'success' => false,
                    'message' => $error_message,
                ];
            }
        } catch (\Exception $e) {
            Log::channel('payment')->error('ZarinPal refund error (admin)', [
                'authority' => $authority,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'success' => false,
                'message' => 'خطا در بازپرداخت. لطفاً مجدداً تلاش کنید.',
            ];
        }
    }

    /**
     * Void a payment (cancel before settlement)
     *
     * @param string $authority The authority from original payment
     * @return array Result with success status and message
     */
    public function void($authority)
    {
        // ZarinPal doesn't have a direct void API, but we can attempt refund with full amount
        // if the payment is still in a voidable state (typically within 24 hours)
        return $this->refund($authority, null, 'Payment voided');
    }
}



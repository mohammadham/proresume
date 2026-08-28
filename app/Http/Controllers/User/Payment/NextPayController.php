<?php

namespace App\Http\Controllers\User\Payment;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\User\BasicSetting;
use App\Models\User\UserPaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;

class NextPayController extends Controller
{
    private $api_key;
    private $sandbox_mode;
    private $callback_url;
    private $description;

    public function __construct()
    {
        $data = UserPaymentGateway::whereKeyword('nextpay')
            ->where('user_id', getUser()->id)
            ->first();

        if ($data) {
            $paydata = $data->convertAutoData();
            $this->api_key = $paydata['api_key'] ?? '';
            $this->sandbox_mode = $paydata['sandbox_status'] ?? 1;
            $this->description = $paydata['text'] ?? 'پرداخت اشتراک';
            $this->callback_url = $paydata['callback_url'] ?? route('customer.appointment.nextpay.notify');
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
            ? 'https://nextpay.org/nx/gateway/token'
            : 'https://nextpay.org/nx/gateway/token';

        $order_id = 'NEXTPAY_' . Str::uuid()->toString();

        $currentLang = session()->has('lang')
            ? Language::where('code', session()->get('lang'))->first()
            : Language::where('is_default', 1)->first();
        $baseCurrency = strtoupper($currentLang->basic_extended->base_currency_text ?? 'IRT');
        if (!in_array($baseCurrency, ['IRR', 'IRT'])) {
            return redirect($cancel_url)->with('error', 'ارز پایه سایت با درگاه ایرانی سازگار نیست.');
        }

        $amount = (int) round($price);
        $currency = $baseCurrency;
        $minAmount = $currency === 'IRT' ? 100 : 1000;
        if ($amount < $minAmount) {
            return redirect($cancel_url)->with('error', 'مبلغ کمتر از حداقل مجاز درگاه است.');
        }

        $payload = [
            'api_key' => $this->api_key,
            'amount' => $amount,
            'order_id' => $order_id,
            'callback_url' => $this->callback_url,
            'payer_name' => $request->fname . ' ' . $request->lname ?? 'کاربر',
            'payer_mobile' => $request->phone ?? '',
            'payer_email' => $request->email ?? '',
            'description' => $this->description,
            'currency' => $currency,
        ];

        try {
            $response = Http::asForm()->timeout(30)->post($api_url, $payload);
            $result = $response->json();

            Log::channel('payment')->info('NextPay payment initiation (appointment)', [
                'order_id' => $order_id,
                'amount' => $amount,
                'sandbox' => $this->sandbox_mode,
                'response_code' => $result['code'] ?? 'unknown',
                'has_trans_id' => isset($result['trans_id']),
            ]);

            if (isset($result['code']) && $result['code'] == -1 && isset($result['trans_id'])) {
                $trans_id = $result['trans_id'];
                $payment_link = 'https://nextpay.org/nx/gateway/payment/' . $trans_id;

                Session::put('nextpay_trans_id', $trans_id);
                Session::put('nextpay_order_id', $order_id);

                return Redirect::away($payment_link);
            } else {
                $error_message = $result['message'] ?? $result['code'] ?? 'خطا در اتصال به درگاه پرداخت';
                return redirect($cancel_url)->with('error', $error_message);
            }
        } catch (\Exception $e) {
            Log::channel('payment')->error('NextPay payment initiation error (appointment)', [
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

        $trans_id = $request->input('trans_id');
        $status = $request->input('status');
        $order_id = $request->input('order_id');

        $session_trans_id = Session::get('nextpay_trans_id');
        $session_order_id = Session::get('nextpay_order_id');

        // P1-5: Idempotency - lockForUpdate to prevent race conditions
        try {
            $transaction = DB::transaction(function () use ($trans_id) {
                $t = Transaction::where('transaction_id', $trans_id)
                    ->whereHas('gateway', function($q) { $q->where('keyword', 'nextpay'); })
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
                Log::channel('payment')->warning('NextPay callback: transaction not found', ['trans_id' => $trans_id]);
                return redirect($cancel_url)->with('error', 'شناسه تراکنش نامعتبر است.');
            }
            Log::channel('payment')->info('NextPay callback: duplicate/already processed', ['trans_id' => $trans_id, 'state' => $msg]);
            return redirect($cancel_url)->with('warning', 'این تراکنش قبلاً پردازش شده است.');
        }

        if ($status == '0') {
            try {
                $api_url = 'https://nextpay.org/nx/gateway/verify';

                $response = Http::asForm()->timeout(30)->post($api_url, [
                    'api_key' => $this->api_key,
                    'trans_id' => $trans_id,
                    'order_id' => $session_order_id,
                    'amount' => Session::get('amount'),
                ]);

                $result = $response->json();

                Log::channel('payment')->info('NextPay payment verification (appointment)', [
                    'trans_id' => $trans_id,
                    'order_id' => $session_order_id,
                    'code' => $result['code'] ?? 'unknown',
                    'amount' => $result['amount'] ?? null,
                ]);

                if (isset($result['code']) && $result['code'] == 0) {
                    $transaction_id = 'NEXTPAY_' . $trans_id;

                    // P1-4: Amount mismatch guard
                    $verifiedAmount = isset($result['amount']) ? (int) $result['amount'] : null;
                    $expectedAmount = (int) Session::get('amount');
                    if ($verifiedAmount !== null && $verifiedAmount !== $expectedAmount) {
                        Log::channel('payment')->warning('NextPay amount mismatch', [
                            'trans_id' => $trans_id,
                            'expected' => $expectedAmount,
                            'received' => $verifiedAmount,
                        ]);
                        return redirect($cancel_url)->with('error', 'مبلغ تایید شده با سفارش هم‌خوانی ندارد.');
                    }

                    $transaction_details = json_encode([
                        'trans_id' => $trans_id,
                        'order_id' => $session_order_id,
                        'amount' => $result['amount'] ?? null,
                        'card_number' => $result['card_number'] ?? null,
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
                    $error_message = $result['message'] ?? 'خطا در تایید پرداخت';
                    return redirect($cancel_url)->with('error', $error_message);
                }
            } catch (\Exception $e) {
                Log::channel('payment')->error('NextPay payment verification error (appointment)', [
                    'trans_id' => $trans_id,
                    'error' => $e->getMessage(),
                ]);
                return redirect($cancel_url)->with('error', 'خطا در تایید پرداخت.');
            }
        } else {
            $error_messages = [
                '1' => 'مبلغ کمتر از حداقل مجاز است.',
                '2' => 'مبلغ بیشتر از حداکثر مجاز است.',
                '3' => 'IP مسدود شده است.',
                '4' => 'تراکنش تکراری است.',
                '5' => 'اطلاعات ارسال شده نامعتبر است.',
                '6' => 'درگاه غیر فعال است.',
                '7' => 'پرداخت لغو شده توسط کاربر.',
                '8' => 'خطای داخلی سیستم.',
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
        Session::forget('nextpay_trans_id');
        Session::forget('nextpay_order_id');

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
        Session::forget('nextpay_trans_id');
        Session::forget('nextpay_order_id');
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
        Session::forget('nextpay_trans_id');
        Session::forget('nextpay_order_id');
        return redirect()->route('success.page');
    }

    /**
     * Refund a payment
     */
    public function refund($transId, $amount = null, $reason = 'Refund requested')
    {
        $apiUrl = 'https://nextpay.org/nx/gateway/refund';

        $gateway = UserPaymentGateway::whereKeyword('nextpay')->where('user_id', getUser()->id)->first();
        $gatewayInfo = json_decode($gateway->information, true);
        $apiKey = $gatewayInfo['api_key'] ?? '';
        $sandbox = $gatewayInfo['sandbox_status'] ?? 0;

        if (!$apiKey) {
            return [
                'success' => false,
                'message' => 'درگاه NextPay تنظیم نشده است.',
            ];
        }

        $payload = [
            'api_key' => $apiKey,
            'trans_id' => $transId,
        ];

        if ($amount !== null) {
            $payload['amount'] = $amount;
        }

        try {
            $response = Http::asForm()->timeout(30)->post($apiUrl, $payload);

            $result = $response->json();

            Log::channel('payment')->info('NextPay refund request (appointment)', [
                'trans_id' => $transId,
                'amount' => $amount,
                'code' => $result['code'] ?? 'unknown',
            ]);

            if ($response->successful() && isset($result['code']) && $result['code'] == 0) {
                return [
                    'success' => true,
                    'message' => 'بازپرداخت با موفقیت انجام شد.',
                    'ref_id' => $transId,
                ];
            } else {
                $error_message = $result['message'] ?? 'خطا در بازپرداخت';
                return [
                    'success' => false,
                    'message' => $error_message,
                ];
            }
        } catch (\Exception $e) {
            Log::channel('payment')->error('NextPay refund error (appointment)', [
                'trans_id' => $transId,
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
    public function void($transId)
    {
        return $this->refund($transId, null, 'Payment voided');
    }
}

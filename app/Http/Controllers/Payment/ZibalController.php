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

class ZibalController extends Controller
{
    private $merchant_id;
    private $sandbox_mode;
    private $callback_url;
    private $description;

    public function __construct()
    {
        $data = PaymentGateway::whereKeyword('zibal')->first();
        if ($data) {
            $paydata = $data->convertAutoData();
            $this->merchant_id = $paydata['merchant_id'] ?? '';
            $this->sandbox_mode = $paydata['sandbox_status'] ?? 1;
            $this->description = $paydata['description'] ?? 'پرداخت اشتراک';
            // P1-8: honor admin-configured callback_url when present, fallback to route
            $this->callback_url = !empty($paydata['callback_url'])
                ? $paydata['callback_url']
                : route('membership.zibal.success');
        }
    }

    public function paymentProcess(Request $request, $_amount, $_title, $_success_url, $_cancel_url)
    {
        $title = $_title;
        $price = $_amount;
        $cancel_url = $_cancel_url;
        $success_url = $_success_url;

        // P1-2: Base currency check + convert Toman → Rial (Zibal requires IRR)
        $currentLang = session()->has('lang')
            ? Language::where('code', session()->get('lang'))->first()
            : Language::where('is_default', 1)->first();
        $baseCurrency = strtoupper($currentLang->basic_extended->base_currency_text ?? 'IRR');
        if (!in_array($baseCurrency, ['IRR', 'IRT'])) {
            return redirect($cancel_url)->with('error', 'ارز پایه سایت با درگاه ایرانی سازگار نیست.');
        }
        // Convert Toman → Rial if needed (Zibal expects Rial)
        $amountInRial = (int) round($baseCurrency === 'IRT' ? $price * 10 : $price);

        // P1-3: Min amount check for Zibal (1,000 Rial)
        if ($amountInRial < 1000) {
            return redirect($cancel_url)->with('error', 'حداقل مبلغ قابل پرداخت ۱،۰۰۰ ریال است.');
        }

        // Generate unique order ID for idempotency
        $orderId = 'ZIBAL_' . Str::uuid()->toString();

        // Store request data in session for later use
        Session::put('request', $request->all());
        Session::put('amount', $_amount);
        Session::put('paymentFor', Session::get('paymentFor'));
        Session::put('zibal_order_id', $orderId);

        // Prepare data for Zibal API - always use production URL
        $api_url = 'https://gateway.zibal.ir/v1/request';

        // Sandbox mode: use 'zibal' as merchant ID
        $merchant = $this->sandbox_mode == 1 ? 'zibal' : $this->merchant_id;
        // Amount in Rial (Zibal uses Rial)
        // $amountInRial = $price * 10;

        $payload = [
            'merchant' => $merchant,
            'amount' => $amountInRial, // Amount in Rial
            'callbackUrl' => $this->callback_url,
            'description' => $this->description,
            'mobile' => $request->phone ?? '',
            'orderId' => $orderId,
        ];

        try {
            $response = Http::timeout(30)->post($api_url, $payload);
            $result = $response->json();
            Log::channel('payment')->info('Zibal payment initiation (admin)', [
                'order_id' => $orderId,
                'amount' => $amountInRial,
                'sandbox' => $this->sandbox_mode,
                'result' => $result['result'] ?? 'unknown',
                'has_track_id' => isset($result['trackId']),
            ]);
            if (isset($result['result']) && $result['result'] == 100) {
                $trackId = $result['trackId'];

                // Save transaction with idempotency key
                Transaction::create([
                    'user_id' => auth()->id() ?? null,
                    'gateway_id' => PaymentGateway::whereKeyword('zibal')->value('id'),
                    'amount' => $amountInRial,
                    'transaction_id' => $trackId,
                    'order_id' => $orderId,
                    'status' => 'pending',
                    'currency' => 'IRR',
                    'ip' => $request->ip(),
                ]);

                Session::put('zibal_track_id', $trackId);

                // Always use production payment URL
                $payment_url = 'https://gateway.zibal.ir/start/' . $trackId;

                return Redirect::away($payment_url);
            } else {
                $error_message = $result['message'] ?? 'خطا در اتصال به درگاه پرداخت';
                Log::channel('payment')->warning('Zibal payment initiation failed (admin)', [
                    'order_id' => $orderId,
                    'result' => $result['result'] ?? null,
                    'error_message' => $error_message,
                ]);
                return redirect($cancel_url)->with('error', $error_message);
            }
        } catch (\Exception $e) {
            Log::channel('payment')->error('Zibal payment initiation error (admin)', [
                'order_id' => $orderId,
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

        $trackId = $request->get('trackId');
        $status = $request->get('status');
        $cancel_url = route('membership.zibal.cancel');

        // P1-5: Idempotency — lock the row and check it's still pending
        try {
            $transaction = DB::transaction(function () use ($trackId) {
                $t = Transaction::where('transaction_id', $trackId)
                    ->whereHas('gateway', function ($q) { $q->where('keyword', 'zibal'); })
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
                Log::channel('payment')->warning('Zibal callback: transaction not found', ['trackId' => $trackId]);
                return redirect($cancel_url)->with('error', 'کد مرجع پرداخت معتبر نیست');
            }
            Log::channel('payment')->info('Zibal callback: duplicate/already processed', ['trackId' => $trackId, 'state' => $msg]);
            return redirect($cancel_url)->with('warning', 'این تراکنش قبلاً پردازش شده است.');
        }

        // Check if payment was successful on Zibal side
        if ($status != 1) {
            $transaction->update(['status' => 'failed']);
            return redirect($cancel_url)->with('error', 'پرداخت توسط کاربر لغو شد یا ناموفق بود');
        }

        // Verify payment with Zibal API - always use production URL
        $api_url = 'https://gateway.zibal.ir/v1/verify';

        // P2-2: In sandbox mode Zibal expects merchant='zibal' for verify too
        $payload = [
            'merchant' => $this->sandbox_mode == 1 ? 'zibal' : $this->merchant_id,
            'trackId' => $trackId,
        ];

        try {
            $response = Http::timeout(30)->post($api_url, $payload);
            $result = $response->json();

            Log::channel('payment')->info('Zibal payment verification (admin)', [
                'trackId' => $trackId,
                'result' => $result['result'] ?? 'unknown',
                'amount' => $result['amount'] ?? null,
                'expected_amount' => $transaction->amount,
            ]);

            // P1-4: Amount mismatch guard (only check when Zibal returned an amount)
            $amountOk = !isset($result['amount']) || (int) $result['amount'] === (int) $transaction->amount;

            // Code 100 = Success, 201 = Already verified
            if (isset($result['result']) && in_array($result['result'], [100, 201]) && $amountOk) {
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
                    $checkout = new CheckoutController();
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
                    Session::forget('zibal_track_id');
                    return redirect()->route('success.page');
                }
            } else {
                $transaction->update(['status' => 'failed']);
                $error_message = !$amountOk
                    ? 'مبلغ تایید شده با سفارش هم‌خوانی ندارد.'
                    : ($result['message'] ?? 'خطا در تایید پرداخت');
                Log::channel('payment')->warning('Zibal verify failed', [
                    'trackId' => $trackId,
                    'result' => $result['result'] ?? null,
                    'expected_amount' => $transaction->amount,
                    'received_amount' => $result['amount'] ?? null,
                ]);
                return redirect($cancel_url)->with('error', $error_message);
            }
        } catch (\Exception $e) {
            // Update transaction status to failed (if resolved)
            if (isset($transaction) && $transaction) {
                $transaction->update(['status' => 'failed']);
            }
            Log::channel('payment')->error('Zibal payment verification error (admin)', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect($cancel_url)->with('error', 'خطا در تایید پرداخت.');
        }

        return redirect($cancel_url);
    }

    public function cancelPayment()
    {
        $requestData = Session::get('request');
        $paymentFor = Session::get('paymentFor');
        $trackId = Session::get('zibal_track_id');
        
        session()->flash('warning', __('cancel_payment'));
        Session::forget('zibal_track_id');
        
        // Update transaction status if trackId exists
        if ($trackId) {
            $transaction = Transaction::where('transaction_id', $trackId)
                ->whereHas('gateway', function($q) { $q->where('keyword', 'zibal'); })
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
     * @param string $trackId The trackId from original payment
     * @param float|null $amount Amount to refund (null = full refund)
     * @param string $reason Reason for refund
     * @return array Result with success status and message
     */
    public function refund($trackId, $amount = null, $reason = 'Refund requested')
    {
        $api_url = 'https://gateway.zibal.ir/v1/refund';

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

            Log::channel('payment')->info('Zibal refund request (admin)', [
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
                
                Log::channel('payment')->warning('Zibal refund failed (admin)', [
                    'trackId' => $trackId,
                    'error_message' => $error_message,
                ]);

                return [
                    'success' => false,
                    'message' => $error_message,
                ];
            }
        } catch (\Exception $e) {
            Log::channel('payment')->error('Zibal refund error (admin)', [
                'trackId' => $trackId,
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
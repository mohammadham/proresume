<?php

namespace App\Http\Controllers\Payment\Concerns;

use App\Http\Controllers\Front\CheckoutController;
use App\Http\Controllers\User\UserCheckoutController;
use App\Http\Helpers\MegaMailer;
use App\Http\Helpers\UserPermissionHelper;
use App\Models\Package;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

/**
 * Turns a verified provider callback into the thing the customer actually paid
 * for: the membership, or the plan extension.
 *
 * ZarinPal, Zibal and Mellat each grew their own copy of this flow, but the
 * IDPay / NextPay / Pay.ir controllers only flipped the transaction to
 * 'success' and redirected to the pricing page. A customer who paid through
 * those three gateways got no package and no email - the money was taken and
 * nothing was delivered, with no error anywhere.
 */
trait GrantsPurchase
{
    /**
     * Grant what the customer bought and return the response to send them to.
     *
     * @param  array  $requestData  the checkout payload stashed in the session
     * @param  Transaction  $transaction  the now-verified transaction row
     * @param  string  $gatewayName  display name used on the invoice
     * @param  mixed  $be  the BasicExtended row for the current language
     * @param  mixed  $bs  the BasicSetting row for the current language
     */
    protected function grantPurchase(array $requestData, Transaction $transaction, string $gatewayName, $be, $bs)
    {
        $paymentFor = Session::get('paymentFor');

        if (empty($requestData['package_id'])) {
            Log::channel('payment')->error('Verified payment without a checkout payload', [
                'gateway' => $gatewayName,
                'transaction_id' => $transaction->transaction_id,
                'payment_for' => $paymentFor,
            ]);

            return redirect()->route('front.pricing')
                ->with('error', 'پرداخت تأیید شد اما اطلاعات خرید در سشن موجود نبود. با پشتیبانی تماس بگیرید.');
        }

        $package = Package::find($requestData['package_id']);
        if (empty($package)) {
            Log::channel('payment')->error('Verified payment for a package that no longer exists', [
                'gateway' => $gatewayName,
                'package_id' => $requestData['package_id'],
            ]);

            return redirect()->route('front.pricing')
                ->with('error', 'پرداخت تأیید شد اما بسته انتخابی در دسترس نیست. با پشتیبانی تماس بگیرید.');
        }

        $transactionId = UserPermissionHelper::uniqidReal(8);
        $transactionDetails = json_encode([
            'transaction_id' => $transaction->transaction_id,
            'tracking_code' => $transaction->tracking_code,
            'gateway' => $gatewayName,
        ]);
        $amount = $requestData['price'] ?? $transaction->amount;

        if ($paymentFor == 'membership') {
            $password = $requestData['password'];
            $checkout = new CheckoutController();
            $user = $checkout->store($requestData, $transactionId, $transactionDetails, $amount, $be, $password);
        } elseif ($paymentFor == 'extend') {
            $password = uniqid('qrcode');
            $checkout = new UserCheckoutController();
            $user = $checkout->store($requestData, $transactionId, $transactionDetails, $amount, $be, $password);
        } else {
            Log::channel('payment')->error('Verified payment outside a membership/extend checkout', [
                'gateway' => $gatewayName,
                'payment_for' => $paymentFor,
            ]);

            return redirect()->route('front.pricing')
                ->with('error', 'پرداخت تأیید شد اما نوع خرید مشخص نیست. با پشتیبانی تماس بگیرید.');
        }

        $lastMemb = $user->memberships()->orderBy('id', 'DESC')->first();
        $activation = Carbon::parse($lastMemb->start_date);
        $expire = Carbon::parse($lastMemb->expire_date);
        $expireLabel = $expire->format('Y') == '9999' ? 'Lifetime' : $expire->toFormattedDateString();

        $fileName = $this->makeInvoice(
            $requestData,
            $paymentFor,
            $user,
            $password,
            $amount,
            $gatewayName,
            $requestData['phone'] ?? $user->phone_number,
            $be->base_currency_symbol_position,
            $be->base_currency_symbol,
            $be->base_currency_text,
            $transactionId,
            $package->title
        );

        $mailer = new MegaMailer();
        $mailer->mailFromAdmin([
            'toMail' => $user->email,
            'toName' => $user->fname,
            'username' => $user->username,
            'package_title' => $package->title,
            'package_price' => ($be->base_currency_text_position == 'left' ? $be->base_currency_text . ' ' : '')
                . $package->price
                . ($be->base_currency_text_position == 'right' ? ' ' . $be->base_currency_text : ''),
            'activation_date' => $activation->toFormattedDateString(),
            'expire_date' => $expireLabel,
            'membership_invoice' => $fileName,
            'website_title' => $bs->website_title,
            'templateType' => $paymentFor == 'membership' ? 'registration_with_premium_package' : 'membership_extend',
            'type' => $paymentFor == 'membership' ? 'registrationWithPremiumPackage' : 'membershipExtend',
        ]);

        Session::forget('request');
        Session::forget('amount');
        Session::forget('paymentFor');

        session()->flash('success', __('successful payment'));

        return redirect()->route('success.page');
    }
}
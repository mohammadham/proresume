<?php

namespace App\Http\Controllers\User\Payment;

use Illuminate\Http\Request;
use App\Models\User\BasicSetting;
use Mollie\Laravel\Facades\Mollie;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use App\Models\User\UserPaymentGateway;
use Illuminate\Support\Facades\Session;
use App\Http\Helpers\UserPermissionHelper;
use App\Http\Controllers\Front\UserCheckoutController;

class MollieController extends Controller
{
    public function __construct()
    {
        // getUser() resolves the gateway owner from the request URL. It has
        // nothing to work with on the CLI, where Laravel builds the route
        // table by instantiating every controller (`artisan route:list`), so
        // this eager lookup used to abort the whole command. Bail out instead
        // of fataling; on a real request the owner is present and the rest of
        // the constructor runs exactly as before.
        try {
            $gatewayOwner = getUser();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // getUser() resolves the owner from the request URL and throws when
            // the first path segment is not a profile. Gateway callbacks such as
            // /zarinpal/notify are reached outside any profile URL, so treat
            // that as "no owner" rather than rendering a 404.
            $gatewayOwner = null;
        }

        if (empty($gatewayOwner)) {
            return;
        }
    }

    public function paymentProcess($request, $_amount, $_success_url, $_cancel_url, $_title, $bex)
    {

        $notify_url = $_success_url;
        $payment = Mollie::api()->payments()->create([
            'amount' => [
                'currency' => $bex->base_currency_text,
                'value' => '' . sprintf('%0.2f', $_amount) . '', // You must send the correct number of decimals, thus we enforce the use of strings
            ],
            'description' => $_title,
            'redirectUrl' => $notify_url,
        ]);

        /** add payment ID to session **/
        Session::put('user_amount', $_amount);
        Session::put('user_payment_id', $payment->id);
        Session::put('user_success_url', $_success_url);

        $payment = Mollie::api()->payments()->get($payment->id);

        return redirect($payment->getCheckoutUrl(), 303);
    }

    public function successPayment(Request $request)
    {
        $requestData = Session::get('user_request');
        $user_amount = Session::get('user_amount');
        $user  = getUser();
        $bs = BasicSetting::where('user_id', $user->id)->firstorFail();
        // $requestData['user_id'] = Auth::guard('customer')->user()->id ?? getUser()->id;
        $cancel_url = Session::get('cancel_url');
        $payment_id = Session::get('user_payment_id');
        /** Get the payment ID before session clear **/
        $payment = Mollie::api()->payments()->get($payment_id);
        if ($payment->status == 'paid') {
            $transaction_id = UserPermissionHelper::uniqidReal(8);
            $transaction_details = json_encode($payment);
            $amount = $user_amount;
            $checkout = new UserCheckoutController();
            $requestData['templateType'] = 'appointment_booking_notification';
            $appointment = $checkout->store($requestData, $transaction_id, $transaction_details, $amount, $bs);
            $checkout->mailToTanentUser($requestData, $appointment, $amount, "Paypal", $bs, $transaction_id);
            session()->flash('success', toastrMsg('successful_payment'));

            Session::forget('user_amount');
            Session::forget('user_payment_id');
            $onlinesuccess  = route('customer.success.page', [getParam(), $appointment->id]);
            return redirect($onlinesuccess);
        }
        return redirect($cancel_url);
    }

    public function cancelPayment()
    {
        session()->flash('warning', toastrMsg('cancel_payment'));
        return redirect()->route('front.user.appointment', getParam());
    }
}

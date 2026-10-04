<?php

namespace App\Http\Controllers\User\Payment;

use Illuminate\Http\Request;
use App\Models\User\UserPackage;
use App\Models\User\BasicSetting;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;
use App\Models\User\UserPaymentGateway;
use Illuminate\Support\Facades\Session;
use App\Http\Helpers\UserPermissionHelper;
use Cartalyst\Stripe\Laravel\Facades\Stripe;
use App\Http\Controllers\Front\UserCheckoutController;

class StripeController extends Controller
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

    public function paymentProcess($request, $_amount, $_title, $_success_url, $_cancel_url)
    {
        $title = $_title;
        $price = $_amount;
        $price = round($price, 2);
        $cancel_url = $_cancel_url;


        $stripe = Stripe::make(Config::get('services.stripe.secret'));

        $token = $request['stripeToken'];


        if (!isset($token)) {
            return back()->with('error', __('Token Problem With Your Token') . '.');
        }

            $charge = $stripe->charges()->create([
                'card' => $token,
                'currency' =>  "USD",
                'amount' => $price,
                'description' => $title,
            ]);
        
        $requestData = Session::get('user_request');
        if ($charge['status'] == 'succeeded') {
            $transaction_id = UserPermissionHelper::uniqidReal(8);
            $transaction_details = json_encode($charge);
            $user = getUser();
            $be = BasicSetting::where('user_id', $user->id)->firstorFail();
            $amount = $_amount;

            $checkout = new UsercheckoutController();
            $requestData['templateType'] = 'appointment_booking_notification';
            $appointment = $checkout->store($requestData, $transaction_id, $transaction_details, $amount, $be);
            $checkout->mailToTanentUser($requestData, $appointment, $amount, "Stripe", $be, $transaction_id);

            session()->flash('success', toastrMsg('successful_payment'));
            Session::forget('user_paymentFor');
            $onlinesuccess  = route('customer.success.page', [getParam(), $appointment->id]);
            return redirect($onlinesuccess);
        }
        return redirect($cancel_url)->with('error', 'Please Enter Valid Credit Card Informations.');
    }
    public function cancelPayment()
    {
        session()->flash('warning', toastrMsg('cancel_payment'));
        return redirect()->route('front.user.appointment', getParam());
    }
}

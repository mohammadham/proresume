<?php

namespace App\Http\Controllers\User\Payment;

use Omnipay\Omnipay;
use Illuminate\Http\Request;
use App\Models\User\BasicSetting;
use App\Http\Controllers\Controller;
use App\Models\User\UserPaymentGateway;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Front\UserCheckoutController;

class AuthorizenetController extends Controller
{
    public $gateway;
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

    public function paymentProcess($request, $_amount, $_cancel_url, $_title, $be)
    {
        if ($request['opaqueDataDescriptor'] && $request['opaqueDataValue']) {
            Session::put('user_request', $request);
            // Generate a unique merchant site transaction ID.
            $transactionId = rand(100000000, 999999999);
            $response = $this->gateway->authorize([
                'amount' => $_amount,
                'currency' => $be->base_currency_text,
                'transactionId' => $transactionId,
                'opaqueDataDescriptor' => $request['opaqueDataDescriptor'],
                'opaqueDataValue' => $request['opaqueDataValue'],
            ])->send();

            $transactionReference = $response->getTransactionReference();
            $response = $this->gateway->capture([
                'amount' => $_amount,
                'currency' => $be->base_currency_text,
                'transactionReference' => $transactionReference,
            ])->send();
            $transaction_id = $response->getTransactionReference();
            $requestData = Session::get('user_request');

            $user = getUser();
            $bs = BasicSetting::where('user_id', $user->id)->firstorFail();
            // Insert transaction data into the database
            $transaction_id = $transaction_id;
            $transaction_details = NULL;
            $amount = $_amount;
            $checkout = new UserCheckoutController();
            $request['templateType'] = 'appointment_booking_notification';
            $appointment = $checkout->store($requestData, $transaction_id, $transaction_details, $amount, $bs);
            $checkout->mailToTanentUser($requestData, $appointment, $amount, "Paypal", $bs, $transaction_id);
            session()->flash('success', toastrMsg('successful_payment'));
            Session::forget('user_amount');
            $onlinesuccess  = route('customer.success.page', [getParam(), $appointment->id]);
            return redirect($onlinesuccess);
        }
    }

    public function cancelPayment()
    {
        session()->flash('warning', toastrMsg('cancel_payment'));
        return redirect()->route('front.user.appointment', getParam());
    }
}

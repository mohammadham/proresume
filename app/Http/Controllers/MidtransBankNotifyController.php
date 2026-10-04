<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

/**
 * Callback endpoints for the Midtrans bank-transfer (virtual account) flow.
 *
 * These two URLs are rendered on /admin/gateways as the "Success URL" and
 * "Cancel URL" the merchant must paste into the Midtrans dashboard, so the
 * routes already exist (midtrans.bank_notify / midtrans.cancel) but the class
 * itself was never shipped with the project - every hit was a 500.
 *
 * A Midtrans bank-VA callback arrives long after the visitor's session is gone,
 * so it cannot finish an order by itself. We record the notification and send
 * the customer back to the front end with a clear message.
 */
class MidtransBankNotifyController extends Controller
{
    /**
     * Midtrans calls this when a bank transfer / VA payment settles.
     */
    public function onlineBankNotify(Request $request)
    {
        Log::info('Midtrans bank notification received.', $request->except(['signature_key', 'signature']));

        session()->flash('success', __('Your payment was received and is being verified.'));

        return redirect()->route('front.index');
    }

    /**
     * The customer abandoned the Midtrans payment screen.
     */
    public function cancel()
    {
        $requestData = Session::get('request');
        session()->flash('warning', __('cancel_payment'));

        if (!empty($requestData['package_type']) && !empty($requestData['package_id'])) {
            return redirect()->route('front.register.view', [
                'status' => $requestData['package_type'],
                'id' => $requestData['package_id'],
            ])->withInput($requestData);
        }

        return redirect()->route('front.index');
    }
}
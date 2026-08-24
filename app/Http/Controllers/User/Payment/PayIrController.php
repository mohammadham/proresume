<?php

namespace App\Http\Controllers\User\Payment;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use Illuminate\Http\Request;

class PayIrController extends Controller
{
    protected $gateway;

    public function __construct()
    {
        $this->gateway = PaymentGateway::where('keyword', 'payir')->first();
    }

    public function update(Request $request)
    {
        $request->validate([
            'api_key' => 'required|string',
            'sandbox' => 'nullable|boolean',
        ]);

        $gatewayInfo = json_decode($this->gateway->information, true);
        $gatewayInfo['api_key'] = $request->api_key;
        $gatewayInfo['sandbox'] = $request->has('sandbox') ? 1 : 0;

        $this->gateway->update([
            'information' => json_encode($gatewayInfo),
        ]);

        return back()->with('success', 'تنظیمات Pay.ir با موفقیت به‌روزرسانی شد.');
    }
}
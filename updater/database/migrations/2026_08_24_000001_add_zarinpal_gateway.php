<?php

use Illuminate\Database\Migrations\Migration;

class AddZarinpalGateway extends Migration
{
    public function up()
    {
        \DB::table('payment_gateways')->updateOrInsert(
            ['keyword' => 'zarinpal'],
            [
                'name'        => 'ZarinPal',
                'title'       => 'ZarinPal',
                'subtitle'    => 'Secure payment with ZarinPal',
                'details'     => 'ZarinPal Payment Gateway for Iranian users',
                'type'        => 'automatic',
                'information' => json_encode([
                    'merchant_id'    => '',
                    'sandbox_status' => 1,
                    'description'    => 'پرداخت اشتراک',
                    'text'           => 'پرداخت امن با زرین‌پال',
                ], JSON_UNESCAPED_UNICODE),
                'status' => 0,
            ]
        );
    }

    public function down()
    {
        \DB::table('payment_gateways')->where('keyword', 'zarinpal')->delete();
    }
}

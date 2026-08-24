<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddZarinpalGateway extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Check if zarinpal gateway already exists
        $exists = \DB::table('payment_gateways')->where('keyword', 'zarinpal')->exists();
        
        if (!$exists) {
            \DB::table('payment_gateways')->insert([
                'title' => 'ZarinPal',
                'details' => 'ZarinPal Payment Gateway for Iranian users',
                'subtitle' => 'Secure payment with ZarinPal',
                'name' => 'ZarinPal',
                'type' => 'online',
                'information' => json_encode([
                    'merchant_id' => '',
                    'sandbox_mode' => 1,
                    'description' => 'پرداخت اشتراک',
                    'text' => 'پرداخت امن با زرین‌پال'
                ]),
                'keyword' => 'zarinpal',
                'status' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \DB::table('payment_gateways')->where('keyword', 'zarinpal')->delete();
    }
}
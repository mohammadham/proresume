<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddZibalGateway extends Migration
{
    public function up()
    {
        \DB::table('payment_gateways')->updateOrInsert(
            ['keyword' => 'zibal'],
            [
                'name'        => 'Zibal',
                'title'       => 'Zibal',
                'subtitle'    => 'پرداخت با زیبال',
                'details'     => 'Zibal Payment Gateway',
                'type'        => 'automatic',
                'information' => json_encode([
                    'merchant_id'    => '',
                    'sandbox_status' => 1,
                    'description'    => 'پرداخت اشتراک',
                ], JSON_UNESCAPED_UNICODE),
                'status' => 0,
            ]
        );
    }

    public function down()
    {
        \DB::table('payment_gateways')->where('keyword', 'zibal')->delete();
    }
}
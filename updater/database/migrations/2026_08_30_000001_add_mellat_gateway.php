<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMellatGateway extends Migration
{
    public function up()
    {
        \DB::table('payment_gateways')->updateOrInsert(
            ['keyword' => 'mellat'],
            [
                'name'        => 'Bank Mellat',
                'title'       => 'Bank Mellat',
                'subtitle'    => 'Secure payment with Bank Mellat',
                'details'     => 'Bank Mellat Payment Gateway for Iranian users',
                'type'        => 'automatic',
                'information' => json_encode([
                    'terminal_id'    => '',
                    'username'       => '',
                    'password'       => '',
                    'sandbox_status' => 1,
                    'callback_url'   => '',
                    'description'    => 'پرداخت اشتراک با بانک ملت',
                    'text'           => 'پرداخت امن با بانک ملت',
                ], JSON_UNESCAPED_UNICODE),
                'status' => 0,
            ]
        );

        \DB::table('user_payment_gateways')->updateOrInsert(
            ['keyword' => 'mellat'],
            [
                'user_id'     => 1, // Default admin user, will be overridden per user
                'name'        => 'Bank Mellat',
                'title'       => null,
                'subtitle'    => null,
                'details'     => null,
                'type'        => 'automatic',
                'information' => json_encode([
                    'terminal_id'    => '',
                    'username'       => '',
                    'password'       => '',
                    'sandbox_status' => 1,
                    'callback_url'   => '',
                    'text'           => 'پرداخت امن با بانک ملت',
                ], JSON_UNESCAPED_UNICODE),
                'status' => 0,
            ]
        );
    }

    public function down()
    {
        \DB::table('payment_gateways')->where('keyword', 'mellat')->delete();
        \DB::table('user_payment_gateways')->where('keyword', 'mellat')->delete();
    }
}
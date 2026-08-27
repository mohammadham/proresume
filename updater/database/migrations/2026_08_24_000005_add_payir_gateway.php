<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        \DB::table('payment_gateways')->updateOrInsert(
            ['keyword' => 'payir'],
            [
                'name'        => 'Pay.ir',
                'title'       => 'Pay.ir',
                'subtitle'    => 'پرداخت با Pay.ir',
                'details'     => 'Pay.ir Payment Gateway',
                'type'        => 'automatic',
                'information' => json_encode([
                    'api_key'        => '',
                    'sandbox_status' => 1,
                ], JSON_UNESCAPED_UNICODE),
                'status' => 0,
            ]
        );
    }

    public function down(): void
    {
        \DB::table('payment_gateways')->where('keyword', 'payir')->delete();
    }
};

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        \DB::table('payment_gateways')->updateOrInsert(
            ['keyword' => 'idpay'],
            [
                'name'        => 'IDPay',
                'title'       => 'IDPay',
                'subtitle'    => 'پرداخت با آیدی‌پی',
                'details'     => 'IDPay Payment Gateway',
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
        \DB::table('payment_gateways')->where('keyword', 'idpay')->delete();
    }
};

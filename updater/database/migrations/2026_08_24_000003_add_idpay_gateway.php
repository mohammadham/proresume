<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if the gateway already exists
        $gateway = \App\Models\PaymentGateway::where('keyword', 'idpay')->first();
        
        if (!$gateway) {
            \App\Models\PaymentGateway::create([
                'name' => 'IDPay',
                'keyword' => 'idpay',
                'status' => 0,
                'information' => json_encode([
                    'api_key' => '',
                    'sandbox' => 0,
                ]),
                'supported_currencies' => '["IRR"]',
                'description' => 'IDPay Payment Gateway',
                'image' => 'idpay.png',
                'is_manual' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \App\Models\PaymentGateway::where('keyword', 'idpay')->delete();
    }
};
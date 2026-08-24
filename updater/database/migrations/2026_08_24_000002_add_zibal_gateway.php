<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddZibalGateway extends Migration
{
    public function up()
    {
        Schema::table('payment_gateways', function (Blueprint $table) {
            // Check if zibal record exists, if not insert it
            $exists = \DB::table('payment_gateways')->where('keyword', 'zibal')->exists();
            
            if (!$exists) {
                \DB::table('payment_gateways')->insert([
                    'keyword' => 'zibal',
                    'name' => 'Zibal',
                    'image' => 'zibal.png',
                    'information' => json_encode([
                        'merchant_id' => '',
                        'sandbox_status' => 1,
                        'description' => 'پرداخت اشتراک'
                    ]),
                    'status' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        });
    }

    public function down()
    {
        Schema::table('payment_gateways', function (Blueprint $table) {
            \DB::table('payment_gateways')->where('keyword', 'zibal')->delete();
        });
    }
}
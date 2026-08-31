<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMellatSaleFieldsToTransactions extends Migration
{
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('sale_order_id')->nullable()->after('order_id');
            $table->unsignedBigInteger('sale_reference_id')->nullable()->after('sale_order_id');
        });
    }

    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['sale_order_id', 'sale_reference_id']);
        });
    }
}
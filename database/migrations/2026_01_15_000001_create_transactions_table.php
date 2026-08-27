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
        if (Schema::hasTable('transactions')) {
            return;
        }

        Schema::create('transactions', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id')->nullable()->index();
            $t->unsignedBigInteger('gateway_id')->index();
            $t->decimal('amount', 20, 2);
            $t->string('currency', 8)->default('IRR');
            $t->string('transaction_id')->nullable();       // authority / trackId / trans_id / id / token
            $t->string('order_id')->nullable();             // idempotency key (nullable for legacy)
            $t->string('tracking_code')->nullable();
            $t->string('status', 20)->default('pending')->index();
            $t->string('ip', 45)->nullable();
            $t->text('payment_url')->nullable();
            $t->json('payload')->nullable();
            $t->timestamps();

            $t->unique(['gateway_id', 'transaction_id'], 'trx_gateway_txn_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'gateway_id',
        'amount',
        'currency',
        'transaction_id',
        'order_id',
        'sale_order_id',
        'sale_reference_id',
        'tracking_code',
        'status',
        'ip',
        'payment_url',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function gateway()
    {
        return $this->belongsTo(PaymentGateway::class, 'gateway_id');
    }
}

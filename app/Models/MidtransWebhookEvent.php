<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MidtransWebhookEvent extends Model
{
    protected $fillable = [
        'business_id',
        'payment_id',
        'order_id',
        'transaction_status',
        'signature_valid',
        'response_status',
        'payload',
    ];

    protected $casts = [
        'signature_valid' => 'boolean',
        'payload' => 'array',
    ];

    protected $table = 'midtrans_webhook_events';
}

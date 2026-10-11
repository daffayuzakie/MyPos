<?php

namespace App\Models;

use App\Traits\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    /** @use HasFactory<UserFactory> */
    use BelongsToBusiness, HasFactory;

    protected $fillable = [
        'business_id',
        'sale_id',
        'payment_id',
        'refund_payment_id',
        'amount',
        'method',
        'status',
        'reason',
        'refunded_at',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'refunded_at' => 'datetime',
    ];

    protected $table = 'refunds';

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function originalPayment()
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    public function refundPayment()
    {
        return $this->belongsTo(Payment::class, 'refund_payment_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function items()
    {
        return $this->hasMany(RefundItem::class);
    }
}

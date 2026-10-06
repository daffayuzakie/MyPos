<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToBusiness;

class Sale extends Model
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, BelongsToBusiness;

    protected $fillable = [
        'business_id',
        'user_id',
        'invoice_number',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'status',
        'transaction_date',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    protected $table = 'sales';

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all payments for this sale (including refunds, retries).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the latest/primary payment for this sale.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latestPayment()
    {
        return $this->hasOne(Payment::class)->latest();
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
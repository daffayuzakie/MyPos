<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToBusiness;

class StockReservation extends Model
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, BelongsToBusiness;

    protected $fillable = [
        'business_id',
        'sale_id',
        'product_id',
        'quantity',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'status' => 'string',
        'expires_at' => 'datetime',
    ];

    protected $table = 'stock_reservations';

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && !$this->expires_at->isPast();
    }

    public function isExpired(): bool
    {
        return $this->status === 'active' && $this->expires_at->isPast();
    }

    public function isConverted(): bool
    {
        return $this->status === 'converted';
    }

    public function isReleased(): bool
    {
        return $this->status === 'released';
    }
}
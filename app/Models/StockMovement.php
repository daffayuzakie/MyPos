<?php

namespace App\Models;

use App\Traits\BelongsToBusiness;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    /** @use HasFactory<StockMovementFactory> */
    use BelongsToBusiness, HasFactory;

    protected $fillable = [
        'business_id',
        'product_id',
        'user_id',
        'type',
        'quantity',
        'reference_type',
        'reference_id',
        'note',
        'reason',
        'stock_before',
        'stock_after',
    ];

    protected $casts = [
        'type' => 'string',
        'quantity' => 'integer',
        'stock_before' => 'integer',
        'stock_after' => 'integer',
    ];

    protected $table = 'stock_movements';

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeIn(Builder $query): Builder
    {
        return $query->where('type', 'in');
    }

    public function scopeOut(Builder $query): Builder
    {
        return $query->where('type', 'out');
    }

    public function scopeAdjustment(Builder $query): Builder
    {
        return $query->where('type', 'adjustment');
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'in' => 'Masuk',
            'out' => 'Keluar',
            'adjustment' => 'Penyesuaian',
            default => $this->type,
        };
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToBusiness;

class StockMovement extends Model
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, BelongsToBusiness;

    protected $fillable = [
        'business_id',
        'product_id',
        'user_id',
        'type',
        'quantity',
        'reference_type',
        'reference_id',
        'note',
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
}
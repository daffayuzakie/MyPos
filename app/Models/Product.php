<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToBusiness;

class Product extends Model
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, BelongsToBusiness;

    protected $fillable = [
        'business_id',
        'category_id',
        'unit_id',
        'sku',
        'barcode',
        'name',
        'description',
        'purchase_price',
        'selling_price',
        'stock',
        'minimum_stock',
        'image',
        'is_active',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'stock' => 'integer',
        'minimum_stock' => 'integer',
        'is_active' => 'boolean',
    ];

    protected $table = 'products';

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function reservations()
    {
        return $this->hasMany(StockReservation::class);
    }

    public function getAvailableStock(): int
    {
        $reserved = $this->reservations()
            ->where('status', 'active')
            ->sum('quantity');
        return max(0, $this->stock - $reserved);
    }
}
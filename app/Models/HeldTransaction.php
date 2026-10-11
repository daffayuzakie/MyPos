<?php

namespace App\Models;

use App\Traits\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeldTransaction extends Model
{
    use BelongsToBusiness, HasFactory;

    protected $fillable = [
        'business_id', 'user_id', 'customer_id',
        'customer_name', 'items', 'subtotal',
        'discount_type', 'discount_value', 'notes', 'held_at',
    ];

    protected $casts = [
        'items' => 'array',
        'subtotal' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'held_at' => 'datetime',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}

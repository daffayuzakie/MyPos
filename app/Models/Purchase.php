<?php

namespace App\Models;

use App\Traits\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    /** @use HasFactory<UserFactory> */
    use BelongsToBusiness, HasFactory;

    protected $fillable = [
        'business_id',
        'supplier_id',
        'user_id',
        'invoice_number',
        'total_amount',
        'status',
        'purchase_date',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'purchase_date' => 'datetime',
    ];

    protected $table = 'purchases';

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }
}

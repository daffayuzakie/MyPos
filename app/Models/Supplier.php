<?php

namespace App\Models;

use App\Traits\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    /** @use HasFactory<UserFactory> */
    use BelongsToBusiness, HasFactory;

    protected $fillable = [
        'business_id',
        'name',
        'phone',
        'email',
        'address',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $table = 'suppliers';

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
}

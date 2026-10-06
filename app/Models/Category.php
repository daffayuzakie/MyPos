<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToBusiness;

class Category extends Model
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, BelongsToBusiness;

    protected $fillable = [
        'business_id',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $table = 'categories';

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Query categories for a specific business.
     *
     * Usage: Category::whereBusiness($businessId)->get()
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $businessId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereBusiness(Builder $query, int $businessId): Builder
    {
        return BelongsToBusiness::forBusiness($query, $businessId);
    }
}
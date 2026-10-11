<?php

namespace App\Models;

use Database\Factories\BusinessFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Business extends Model
{
    /** @use HasFactory<BusinessFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'timezone',
        'email',
        'phone',
        'address',
        'logo',
        'tax_percentage',
        'payment_methods',
        'onboarded_at',
        'invoice_prefix',
        'invoice_sequence',
    ];

    protected $hidden = [];

    protected $casts = [
        'tax_percentage' => 'decimal:2',
        'payment_methods' => 'array',
        'onboarded_at' => 'datetime',
    ];

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? Storage::url($this->logo) : null;
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function primaryBranch()
    {
        return $this->hasOne(Branch::class)->where('is_primary', true);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function suppliers()
    {
        return $this->hasMany(Supplier::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
}

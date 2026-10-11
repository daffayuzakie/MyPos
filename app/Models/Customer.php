<?php

namespace App\Models;

use App\Traits\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use BelongsToBusiness, HasFactory;

    protected $fillable = [
        'business_id', 'name', 'phone', 'email', 'address',
        'points', 'level', 'total_spent', 'notes',
    ];

    protected $casts = [
        'points' => 'integer',
        'total_spent' => 'decimal:2',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function addPoints(int $points): void
    {
        $this->increment('points', $points);
        $this->updateLevel();
    }

    public function updateLevel(): void
    {
        $totalSpent = $this->total_spent;
        $level = match (true) {
            $totalSpent >= 10000000 => 'platinum',
            $totalSpent >= 5000000 => 'gold',
            $totalSpent >= 1000000 => 'silver',
            default => 'regular',
        };
        $this->update(['level' => $level]);
    }

    public function scopeSearch($query, ?string $search)
    {
        if (! $search) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        });
    }
}

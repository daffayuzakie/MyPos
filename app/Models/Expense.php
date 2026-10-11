<?php

namespace App\Models;

use App\Traits\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    /** @use HasFactory<UserFactory> */
    use BelongsToBusiness, HasFactory;

    protected $fillable = [
        'business_id',
        'user_id',
        'category',
        'amount',
        'description',
        'expense_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    protected $table = 'expenses';

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

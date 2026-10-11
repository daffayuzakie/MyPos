<?php

namespace Database\Factories;

use App\Models\Sale;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SaleFactory extends Factory
{
    protected $model = Sale::class;

    public function definition()
    {
        return [
            'business_id' => fn ($variables) => $variables['business_id'],
            'user_id' => fn ($variables) => $variables['user_id'],
            'invoice_number' => Str::random(10),
            'subtotal' => 0,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 0,
            'status' => 'pending',
            'transaction_date' => now(),
        ];
    }
}

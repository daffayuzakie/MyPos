<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'category_id' => Category::factory(),
            'unit_id' => Unit::factory(),
            'sku' => Str::upper(Str::random(5)).'-'.Str::random(3),
            'barcode' => Str::random(13),
            'name' => fake()->word(),
            'description' => fake()->optional()->sentence(),
            'purchase_price' => fake()->randomNumber(5) + 100,
            'selling_price' => fake()->randomNumber(4) + 2000,
            'stock' => 0,
            'is_active' => true,
        ];
    }
}

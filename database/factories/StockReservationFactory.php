<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockReservation;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockReservationFactory extends Factory
{
    protected $model = StockReservation::class;

    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'sale_id' => Sale::factory(),
            'product_id' => Product::factory(),
            'quantity' => fake()->numberBetween(1, 100),
            'status' => 'active',
            'expires_at' => now()->addMinutes(15),
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'status' => 'active',
            'expires_at' => now()->addMinutes(15),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'status' => 'expired',
            'expires_at' => now()->subMinutes(5),
        ]);
    }

    public function converted(): static
    {
        return $this->state(fn () => [
            'status' => 'converted',
            'expires_at' => now()->subMinutes(5),
        ]);
    }

    public function released(): static
    {
        return $this->state(fn () => [
            'status' => 'released',
            'expires_at' => now()->subMinutes(5),
        ]);
    }

    public function forBusiness(int $businessId): static
    {
        return $this->state(fn () => [
            'business_id' => $businessId,
        ]);
    }

    public function forProduct(int $productId): static
    {
        return $this->state(fn () => [
            'product_id' => $productId,
        ]);
    }

    public function forSale(int $saleId): static
    {
        return $this->state(fn () => [
            'sale_id' => $saleId,
        ]);
    }
}

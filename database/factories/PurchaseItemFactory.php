<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseItemFactory extends Factory
{
    protected $model = PurchaseItem::class;

    public function definition(): array
    {
        return [
            'purchase_id' => Purchase::factory(),
            'product_id' => Product::factory(),
            'quantity' => fake()->numberBetween(1, 100),
            'unit_price' => fake()->randomFloat(2, 1000, 100000),
            'subtotal' => 0,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (PurchaseItem $item) {
            $item->subtotal = $item->quantity * $item->unit_price;
        });
    }

    public function forPurchase(int $purchaseId): static
    {
        return $this->state(fn () => [
            'purchase_id' => $purchaseId,
        ]);
    }

    public function forProduct(int $productId): static
    {
        return $this->state(fn () => [
            'product_id' => $productId,
        ]);
    }
}

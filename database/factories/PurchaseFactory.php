<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PurchaseFactory extends Factory
{
    protected $model = Purchase::class;

    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'supplier_id' => Supplier::factory(),
            'user_id' => User::factory(),
            'invoice_number' => 'PO-'.Str::upper(Str::random(8)),
            'total_amount' => 0,
            'status' => 'draft',
            'purchase_date' => now(),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'status' => 'draft',
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => 'completed',
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => [
            'status' => 'cancelled',
        ]);
    }

    public function forBusiness(int $businessId): static
    {
        return $this->state(fn () => [
            'business_id' => $businessId,
        ]);
    }

    public function forSupplier(int $supplierId): static
    {
        return $this->state(fn () => [
            'supplier_id' => $supplierId,
        ]);
    }

    public function withTotal(float $total): static
    {
        return $this->state(fn () => [
            'total_amount' => $total,
        ]);
    }
}

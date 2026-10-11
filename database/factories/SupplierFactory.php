<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'name' => fake()->company(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->safeEmail(),
            'address' => fake()->address(),
            'notes' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => [
            'is_active' => false,
        ]);
    }

    public function forBusiness(int $businessId): static
    {
        return $this->state(fn () => [
            'business_id' => $businessId,
        ]);
    }
}

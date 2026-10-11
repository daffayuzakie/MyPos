<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

class UnitFactory extends Factory
{
    protected $model = Unit::class;

    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'name' => fake()->unique()->word(),
            'code' => strtoupper(fake()->unique()->lexify('???')),
            'is_active' => true,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Business;
use Illuminate\Database\Eloquent\Factories\Factory;

class BranchFactory extends Factory
{
    protected $model = Branch::class;

    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'name' => 'Cabang '.$this->faker->city,
            'code' => strtoupper($this->faker->bothify('??##')),
            'phone' => $this->faker->phoneNumber,
            'address' => $this->faker->address,
            'is_primary' => false,
            'is_active' => true,
        ];
    }
}

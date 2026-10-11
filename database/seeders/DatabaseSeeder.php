<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleAndUserSeeder::class,
            BusinessRegistrationSeeder::class,
        ]);

        // Create test user with business_id (must be NOT NULL)
        $business = Business::first();
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'business_id' => $business ? $business->id : 1,
        ]);
    }
}

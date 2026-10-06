<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Traits\HasRoles;

class BusinessRegistrationSeeder extends Seeder
{
    public function run(): void
    {
        // Check if business already exists
        if (Business::exists()) {
            \Illuminate\Support\Facades\Log::info('Business already exists, skipping registration.');
            return;
        }

        // Create the business
        $business = Business::create([
            'name' => 'Default Cafe',
            'slug' => 'default-cafe',
            'email' => 'admin@default-cafe.test',
            'phone' => '+628123456789',
            'address' => 'Jl. Contoh No. 123',
            'tax_percentage' => 0,
            'invoice_prefix' => 'INV',
            'invoice_sequence' => 1,
        ]);

        // Create the first user (admin) and associate with business
        $adminUser = User::create([
            'name' => 'Admin',
            'email' => 'admin@default-cafe.test',
            'password' => bcrypt('password123'), // Default password
            'business_id' => $business->id,
            'status' => 'active',
        ]);

        // Assign admin role
        $adminUser->assignRole('admin');

        \Illuminate\Support\Facades\Log::info('Business and admin user registered successfully.', [
            'business_id' => $business->id,
            'admin_user_id' => $adminUser->id,
        ]);
    }
}
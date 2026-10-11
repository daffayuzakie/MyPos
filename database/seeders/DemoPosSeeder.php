<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class DemoPosSeeder extends Seeder
{
    public function run(): void
    {
        $business = Business::first();
        if (! $business) {
            return;
        }

        // Categories
        $catMinuman = Category::firstOrCreate(
            ['business_id' => $business->id, 'name' => 'Minuman'],
            ['is_active' => true]
        );
        $catMakanan = Category::firstOrCreate(
            ['business_id' => $business->id, 'name' => 'Makanan'],
            ['is_active' => true]
        );
        $catCamilan = Category::firstOrCreate(
            ['business_id' => $business->id, 'name' => 'Camilan'],
            ['is_active' => true]
        );

        // Unit
        $unit = Unit::firstOrCreate(
            ['business_id' => $business->id, 'code' => 'PRS'],
            ['name' => 'Porsi', 'is_active' => true]
        );

        $productsData = [
            // Minuman
            ['category_id' => $catMinuman->id, 'name' => 'Kopi Susu', 'selling_price' => 22000, 'purchase_price' => 12000, 'stock' => 50, 'sku' => 'MNM-001'],
            ['category_id' => $catMinuman->id, 'name' => 'Teh Tarik', 'selling_price' => 18000, 'purchase_price' => 9000, 'stock' => 45, 'sku' => 'MNM-002'],
            ['category_id' => $catMinuman->id, 'name' => 'Es Jeruk', 'selling_price' => 15000, 'purchase_price' => 7000, 'stock' => 40, 'sku' => 'MNM-003'],
            ['category_id' => $catMinuman->id, 'name' => 'Air Mineral 600 ml', 'selling_price' => 6000, 'purchase_price' => 3000, 'stock' => 100, 'sku' => 'MNM-004'],

            // Makanan
            ['category_id' => $catMakanan->id, 'name' => 'Mie Goreng', 'selling_price' => 25000, 'purchase_price' => 14000, 'stock' => 35, 'sku' => 'MKN-001'],
            ['category_id' => $catMakanan->id, 'name' => 'Nasi Goreng', 'selling_price' => 28000, 'purchase_price' => 15000, 'stock' => 30, 'sku' => 'MKN-002'],
            ['category_id' => $catMakanan->id, 'name' => 'Roti Bakar', 'selling_price' => 18000, 'purchase_price' => 9000, 'stock' => 25, 'sku' => 'MKN-003'],
            ['category_id' => $catMakanan->id, 'name' => 'Pisang Goreng', 'selling_price' => 14000, 'purchase_price' => 7000, 'stock' => 20, 'sku' => 'MKN-004'],

            // Camilan
            ['category_id' => $catCamilan->id, 'name' => 'Kentang Goreng', 'selling_price' => 20000, 'purchase_price' => 10000, 'stock' => 30, 'sku' => 'CML-001'],
            ['category_id' => $catCamilan->id, 'name' => 'Keripik Singkong', 'selling_price' => 12000, 'purchase_price' => 6000, 'stock' => 50, 'sku' => 'CML-002'],
            ['category_id' => $catCamilan->id, 'name' => 'Coklat Batang', 'selling_price' => 15000, 'purchase_price' => 8000, 'stock' => 40, 'sku' => 'CML-003'],
            ['category_id' => $catCamilan->id, 'name' => 'Kue Brownies', 'selling_price' => 17000, 'purchase_price' => 9000, 'stock' => 25, 'sku' => 'CML-004'],
        ];

        foreach ($productsData as $data) {
            Product::updateOrCreate(
                ['business_id' => $business->id, 'name' => $data['name']],
                array_merge($data, [
                    'unit_id' => $unit->id,
                    'is_active' => true,
                ])
            );
        }

        // Demo Customers
        $customers = [
            ['name' => 'Budi Santoso', 'phone' => '081234567890', 'email' => 'budi@example.com', 'points' => 150, 'level' => 'gold', 'total_spent' => 1500000],
            ['name' => 'Siti Nurhaliza', 'phone' => '081987654321', 'email' => 'siti@example.com', 'points' => 60, 'level' => 'silver', 'total_spent' => 600000],
            ['name' => 'Andi Wijaya', 'phone' => '082123456789', 'email' => 'andi@example.com', 'points' => 20, 'level' => 'regular', 'total_spent' => 200000],
        ];

        foreach ($customers as $c) {
            Customer::updateOrCreate(
                ['business_id' => $business->id, 'phone' => $c['phone']],
                $c
            );
        }
    }
}

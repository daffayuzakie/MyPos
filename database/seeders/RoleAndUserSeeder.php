<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Business;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class RoleAndUserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Permission (hanya jika belum ada)
        $permissionNames = [
            'products view', 'products create', 'products edit', 'products delete',
            'sales view-all', 'sales view-own', 'sales create', 'sales cancel',
            'purchases view', 'purchases create', 'purchases edit',
            'users view', 'users create', 'users edit', 'users deactivate',
            'reports view', 'business settings',
        ];

        foreach ($permissionNames as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // 2. Buat Role (hanya jika belum ada)
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $kasirRole = Role::firstOrCreate(['name' => 'kasir', 'guard_name' => 'web']);

        // 3. Assign permissions to roles
        $allPermissions = Permission::where('guard_name', 'web')->get()->pluck('id');
        $adminRole->permissions()->sync($allPermissions->toArray());

        $kasirPermissions = [
            'products view',
            'sales view-all',
            'sales view-own',
            'sales create',
            'purchases view',
            'reports view',
        ];
        $kasirPermissionIds = [];
        foreach ($kasirPermissions as $name) {
            $id = Permission::where('name', $name)->where('guard_name', 'web')->value('id');
            if ($id) {
                $kasirPermissionIds[] = $id;
            }
        }
        $kasirRole->permissions()->sync($kasirPermissionIds);

        // 4. Buat Business terlebih dahulu (biuser_id NOT NULL)
        $business = Business::firstOrCreate([
            'name' => 'Default Cafe',
            'slug' => 'default-cafe',
            'email' => 'admin@default-cafe.test',
            'phone' => '+628123456789',
            'address' => 'Jl. Contoh No. 123',
            'tax_percentage' => 0,
            'invoice_prefix' => 'INV',
            'invoice_sequence' => 1,
        ]);

        // 5. Buat Akun Admin Default dengan business_id
        $admin = User::firstOrCreate(
            ['email' => 'admin@pos.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password123'),
                'business_id' => $business->id,
                'status' => 'active',
            ]
        );
        $admin->assignRole($adminRole);

        // 5. Buat Akun Kasir Default dengan business_id
        $kasir = User::firstOrCreate(
            ['email' => 'kasir@pos.com'],
            [
                'name' => 'Kasir Pertama',
                'password' => Hash::make('password123'),
                'business_id' => $business->id,
                'status' => 'active',
            ]
        );
        $kasir->assignRole($kasirRole);
    }
}
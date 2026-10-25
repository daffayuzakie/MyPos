<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Business;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndUserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Permission (hanya jika belum ada)
        $permissionNames = [
            'products view', 'products create', 'products edit', 'products delete',
            'categories view', 'categories create', 'categories edit', 'categories delete',
            'sales view-all', 'sales view-own', 'sales create', 'sales cancel',
            'users view', 'users create', 'users edit', 'users deactivate',
            'reports view', 'business settings',
            'stock view', 'stock create',
        ];

        foreach ($permissionNames as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // 2. Buat Role (hanya jika belum ada)
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $managerRole = Role::firstOrCreate(['name' => 'manajer', 'guard_name' => 'web']);
        $kasirRole = Role::firstOrCreate(['name' => 'kasir', 'guard_name' => 'web']);

        // 3. Assign permissions to roles
        $allPermissions = Permission::where('guard_name', 'web')->get()->pluck('id');
        $adminRole = Role::where('name', 'admin')->where('guard_name', 'web')->first();
        $adminRole->permissions()->sync($allPermissions->toArray());

        $managerPermissions = [
            'products view', 'products create', 'products edit', 'products delete',
            'categories view', 'categories create', 'categories edit', 'categories delete',
            'sales view-all', 'sales view-own', 'sales create', 'sales cancel',
            'users view',
            'reports view',
            'stock view', 'stock create',
        ];
        $managerPermissionIds = Permission::where('guard_name', 'web')
            ->whereIn('name', $managerPermissions)
            ->pluck('id');
        Role::where('name', 'manajer')->where('guard_name', 'web')->first()
            ->permissions()->sync($managerPermissionIds->toArray());

        $kasirPermissions = [
            'products view',
            'sales view-all',
            'sales view-own',
            'sales create',
            'reports view',
            'stock view',
        ];
        $kasirPermissionIds = [];
        foreach ($kasirPermissions as $name) {
            $id = Permission::where('name', $name)->where('guard_name', 'web')->value('id');
            if ($id) {
                $kasirPermissionIds[] = $id;
            }
        }
        $kasirRole = Role::where('name', 'kasir')->where('guard_name', 'web')->first();
        $kasirRole->permissions()->sync($kasirPermissionIds);

        // 4. Buat Business terlebih dahulu (biuser_id NOT NULL)
        $business = Business::firstOrCreate(
            ['slug' => 'default-cafe'],
            [
                'name' => 'Default Cafe',
                'email' => 'admin@default-cafe.test',
                'phone' => '+628123456789',
                'address' => 'Jl. Contoh No. 123',
                'tax_percentage' => 0,
                'invoice_prefix' => 'INV',
                'invoice_sequence' => 1,
            ]
        );

        // Cabang utama untuk demo
        $primaryBranch = Branch::firstOrCreate(
            ['business_id' => $business->id, 'is_primary' => true],
            [
                'name' => 'Cabang Utama',
                'code' => 'PUSAT',
                'phone' => '+628123456789',
                'address' => 'Jl. Contoh No. 123',
                'is_active' => true,
            ]
        );

        // Backfill cabang untuk pengguna lama yang belum punya cabang
        $business->users()->whereNull('branch_id')->update(['branch_id' => $primaryBranch->id]);

        // 5. Buat Akun Admin Default dengan business_id
        $admin = User::firstOrCreate(
            ['email' => 'admin@pos.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password123'),
                'business_id' => $business->id,
                'branch_id' => $primaryBranch->id,
                'status' => 'active',
            ]
        );
        $admin->update(['pin' => '1234']);
        $admin->assignRole('admin');

        // 5. Buat Akun Kasir Default dengan business_id
        $kasir = User::firstOrCreate(
            ['email' => 'kasir@pos.com'],
            [
                'name' => 'Kasir Pertama',
                'password' => Hash::make('password123'),
                'pin' => '1234',
                'business_id' => $business->id,
                'branch_id' => $primaryBranch->id,
                'status' => 'active',
            ]
        );
        $kasir->update(['pin' => '1234']);
        $kasir->assignRole('kasir');
    }
}

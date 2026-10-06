<?php

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

// Get existing roles
$adminRole = Role::where('name', 'admin')->where('guard_name', 'web')->first();
$kasirRole = Role::where('name', 'kasir')->where('guard_name', 'web')->first();

// Create permissions if they don't exist
$permissionNames = [
    'products view', 'products create', 'products edit', 'products delete',
    'sales view-all', 'sales view-own', 'sales create', 'sales cancel',
    'purchases view', 'purchases create', 'purchases edit',
    'users view', 'users create', 'users edit', 'users deactivate',
    'reports view', 'business settings',
];

foreach ($permissionNames as $name) {
    if (!Permission::where('name', $name)->where('guard_name', 'web')->exists()) {
        Permission::create(['name' => $name, 'guard_name' => 'web']);
    }
}

// Assign permissions to admin role
if ($adminRole) {
    $adminRole->permissions()->sync(Permission::where('guard_name', 'web')->pluck('id'));
    echo "Admin role permissions updated\n";
}

// Assign permissions to kasir role
if ($kasirRole) {
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
    echo "Kasir role permissions updated\n";
}

// Assign admin role to the first user
$user = \App\Models\User::first();
if ($user) {
    // First, remove all existing roles
    $user->removeRole($user->getRoleNames());
    // Assign admin role
    $user->assignRole('admin');
    echo "Admin role assigned to user {$user->id} (email: {$user->email})\n";
}

echo "Roles and permissions setup complete\n";
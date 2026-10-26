<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AuthorizationHardeningTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;

    private Business $otherBusiness;

    protected function setUp(): void
    {
        parent::setUp();

        $this->business = Business::factory()->create();
        $this->otherBusiness = Business::factory()->create();

        $this->seedPermissions();
    }

    public function test_manager_with_only_view_permission_cannot_create_employee(): void
    {
        $manager = $this->userWithRole('manajer', ['users view']);

        $response = $this->actingAs($manager)->post(route('users.store'), [
            'name' => 'Karyawan Baru',
            'contact' => 'karyawan@example.com',
            'role' => 'kasir',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('users', ['email' => 'karyawan@example.com']);
    }

    public function test_manager_with_only_view_permission_cannot_update_employee(): void
    {
        $manager = $this->userWithRole('manajer', ['users view']);
        $target = User::factory()->create(['business_id' => $this->business->id]);

        $response = $this->actingAs($manager)->put(route('users.update', $target), [
            'name' => 'Diubah',
            'contact' => 'diubah@example.com',
            'role' => 'kasir',
        ]);

        $response->assertForbidden();
        $this->assertNotSame('Diubah', $target->fresh()->name);
    }

    public function test_admin_can_create_employee(): void
    {
        $admin = $this->userWithRole('admin');

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Kasir Baru',
            'contact' => 'kasir-baru@example.com',
            'role' => 'kasir',
        ]);

        $response->assertRedirect(route('users.index'));

        $employee = User::where('email', 'kasir-baru@example.com')->firstOrFail();

        $this->assertSame($this->business->id, $employee->business_id);
        $this->assertTrue($employee->hasRole('kasir'));
    }

    public function test_staff_without_admin_role_cannot_assign_admin_role(): void
    {
        $operator = $this->userWithRole('kasir', [
            'users view', 'users create', 'users edit', 'users deactivate',
        ]);

        $response = $this->actingAs($operator)->post(route('users.store'), [
            'name' => 'Admin Palsu',
            'contact' => 'admin-palsu@example.com',
            'role' => 'admin',
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', ['email' => 'admin-palsu@example.com']);
    }

    public function test_admin_cannot_update_employee_from_another_business(): void
    {
        $admin = $this->userWithRole('admin');
        $foreignUser = User::factory()->create([
            'business_id' => $this->otherBusiness->id,
            'name' => 'Karyawan Lain',
        ]);

        $response = $this->actingAs($admin)->put(route('users.update', $foreignUser), [
            'name' => 'Diambil Alih',
            'contact' => 'foreign@example.com',
            'role' => 'kasir',
        ]);

        $response->assertForbidden();
        $this->assertSame('Karyawan Lain', $foreignUser->fresh()->name);
    }

    public function test_admin_cannot_deactivate_own_account(): void
    {
        $admin = $this->userWithRole('admin');

        $response = $this->actingAs($admin)->post(route('users.deactivate', $admin));

        $response->assertSessionHasErrors('error');
        $this->assertSame(User::STATUS_ACTIVE, $admin->fresh()->status);
    }

    public function test_kasir_with_view_permission_cannot_delete_category(): void
    {
        $kasir = $this->userWithRole('kasir', ['categories view']);
        $category = Category::factory()->create(['business_id' => $this->business->id]);

        $response = $this->actingAs($kasir)->delete(route('categories.destroy', $category));

        $response->assertForbidden();
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_kasir_with_view_permission_cannot_update_unit(): void
    {
        $kasir = $this->userWithRole('kasir', ['units view']);
        $unit = Unit::factory()->create(['business_id' => $this->business->id]);

        $response = $this->actingAs($kasir)->put(route('units.update', $unit), [
            'name' => 'Diubah',
            'code' => 'XX',
        ]);

        $response->assertForbidden();
        $this->assertNotSame('Diubah', $unit->fresh()->name);
    }

    public function test_kasir_without_delete_permission_cannot_delete_customer(): void
    {
        $kasir = $this->userWithRole('kasir', ['customers view', 'customers create']);
        $customer = Customer::create([
            'business_id' => $this->business->id,
            'name' => 'Pelanggan Tetap',
        ]);

        $response = $this->actingAs($kasir)->delete(route('customers.destroy', $customer));

        $response->assertForbidden();
        $this->assertDatabaseHas('customers', ['id' => $customer->id]);
    }

    public function test_kasir_can_still_create_customer_at_the_counter(): void
    {
        $kasir = $this->userWithRole('kasir', ['customers view', 'customers create']);

        $response = $this->actingAs($kasir)->post(route('customers.store'), [
            'name' => 'Pelanggan Baru',
            'phone' => '08123456789',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('customers', [
            'business_id' => $this->business->id,
            'name' => 'Pelanggan Baru',
        ]);
    }

    public function test_manager_can_view_employee_list_without_create_action(): void
    {
        $manager = $this->userWithRole('manajer', ['users view']);

        $response = $this->actingAs($manager)->get(route('users.index'));

        $response->assertOk();
        $response->assertDontSee('+ Tambah karyawan');
    }

    public function test_kasir_cannot_view_employee_list(): void
    {
        $kasir = $this->userWithRole('kasir', ['sales create']);

        $response = $this->actingAs($kasir)->get(route('users.index'));

        $response->assertForbidden();
    }

    private function seedPermissions(): void
    {
        $names = [
            'users view', 'users create', 'users edit', 'users deactivate',
            'categories view', 'categories create', 'categories edit', 'categories delete',
            'units view', 'units create', 'units edit', 'units delete',
            'customers view', 'customers create', 'customers edit', 'customers delete',
            'sales create',
        ];

        foreach ($names as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions(Permission::all());

        Role::firstOrCreate(['name' => 'manajer', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'kasir', 'guard_name' => 'web']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * @param  array<int, string>  $permissions
     */
    private function userWithRole(string $role, array $permissions = [], ?Business $business = null): User
    {
        $user = User::factory()->create([
            'business_id' => ($business ?? $this->business)->id,
            'status' => User::STATUS_ACTIVE,
        ]);

        $user->assignRole($role);

        if ($permissions !== []) {
            $user->givePermissionTo($permissions);
        }

        return $user;
    }
}

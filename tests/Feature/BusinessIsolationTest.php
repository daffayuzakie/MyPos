<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class BusinessIsolationTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    protected Business $businessA;
    protected Business $businessB;
    protected User $adminA;
    protected User $kasirA;
    protected User $adminB;
    protected User $kasirB;
    protected Product $productA;
    protected Product $productB;
    protected Category $categoryA;
    protected Category $categoryB;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles and permissions
        $permissionNames = [
            'products view', 'products create', 'products edit', 'products delete',
            'sales view-all', 'sales view-own', 'sales create', 'sales cancel',
            'purchases view', 'purchases create', 'purchases edit',
            'users view', 'users create', 'users edit', 'users deactivate',
            'reports view', 'business settings',
        ];

        foreach ($permissionNames as $name) {
            \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $kasirRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'kasir', 'guard_name' => 'web']);

        $allPermissions = \Spatie\Permission\Models\Permission::where('guard_name', 'web')->get()->pluck('id');
        $adminRole = \Spatie\Permission\Models\Role::where('name', 'admin')->where('guard_name', 'web')->first();
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
            $id = \Spatie\Permission\Models\Permission::where('name', $name)->where('guard_name', 'web')->value('id');
            if ($id) {
                $kasirPermissionIds[] = $id;
            }
        }
        $kasirRole = \Spatie\Permission\Models\Role::where('name', 'kasir')->where('guard_name', 'web')->first();
        $kasirRole->permissions()->sync($kasirPermissionIds);

        // Create two businesses
        $this->businessA = Business::create([
            'name' => 'Business A',
            'slug' => 'business-a-' . time(),
            'email' => 'a@test.com',
        ]);
        $this->businessB = Business::create([
            'name' => 'Business B',
            'slug' => 'business-b-' . time(),
            'email' => 'b@test.com',
        ]);
        
        $this->adminA = User::create([
            'name' => 'Admin A',
            'email' => 'admin-a@test.com',
            'password' => bcrypt('password123'),
            'business_id' => $this->businessA->id,
            'status' => 'active',
        ])->assignRole('admin');
        
        $this->kasirA = User::create([
            'name' => 'Kasir A',
            'email' => 'kasir-a@test.com',
            'password' => bcrypt('password123'),
            'business_id' => $this->businessA->id,
            'status' => 'active',
        ])->assignRole('kasir');
        
        $this->adminB = User::create([
            'name' => 'Admin B',
            'email' => 'admin-b@test.com',
            'password' => bcrypt('password123'),
            'business_id' => $this->businessB->id,
            'status' => 'active',
        ])->assignRole('admin');
        
        $this->kasirB = User::create([
            'name' => 'Kasir B',
            'email' => 'kasir-b@test.com',
            'password' => bcrypt('password123'),
            'business_id' => $this->businessB->id,
            'status' => 'active',
        ])->assignRole('kasir');
        
        $this->categoryA = Category::create([
            'business_id' => $this->businessA->id,
            'name' => 'Category A',
        ]);
        $this->categoryB = Category::create([
            'business_id' => $this->businessB->id,
            'name' => 'Category B',
        ]);
        
        $this->productA = Product::create([
            'business_id' => $this->businessA->id,
            'category_id' => $this->categoryA->id,
            'sku' => 'SKU-A1',
            'name' => 'Product A',
            'purchase_price' => 1000,
            'selling_price' => 2000,
            'stock' => 10,
            'minimum_stock' => 1,
            'is_active' => true,
        ]);
        $this->productB = Product::create([
            'business_id' => $this->businessB->id,
            'category_id' => $this->categoryB->id,
            'sku' => 'SKU-B1',
            'name' => 'Product B',
            'purchase_price' => 1000,
            'selling_price' => 2000,
            'stock' => 10,
            'minimum_stock' => 1,
            'is_active' => true,
        ]);
    }

    public function test_business_a_user_can_access_own_resources(): void
    {
        $this->actingAs($this->adminA);
        
        $this->assertTrue($this->adminA->can('view', $this->productA));
    }

    public function test_business_a_user_cannot_access_business_b_resources(): void
    {
        $this->actingAs($this->adminA);
        
        // Direct access to Business B's product
        $this->assertFalse($this->adminA->can('view', $this->productB));
        
        // Query scoped to Business A should not return Business B resources
        $productsForA = Product::forBusiness(Product::query(), $this->businessA->id)->get();
        $this->assertCount(1, $productsForA);
    }

    public function test_business_b_user_can_access_own_resources(): void
    {
        $this->actingAs($this->adminB);
        
        $this->assertTrue($this->adminB->can('view', $this->productB));
    }

    public function test_business_b_user_cannot_access_business_a_resources(): void
    {
        $this->actingAs($this->adminB);
        
        $this->assertFalse($this->adminB->can('view', $this->productA));
    }

    public function test_kasir_can_access_own_business_resources(): void
    {
        $this->actingAs($this->kasirA);
        
        $this->assertTrue($this->kasirA->can('view', $this->productA));
        $this->assertFalse($this->kasirA->can('view', $this->productB));
    }

    public function test_kasir_cannot_manage_users(): void
    {
        $this->actingAs($this->kasirA);
        
        $this->assertFalse($this->kasirA->can('users view'));
    }

    public function test_admin_cannot_access_other_business_users(): void
    {
        $this->actingAs($this->adminA);
        
        $userB = User::create([
            'name' => 'User B',
            'email' => 'user-b@test.com',
            'password' => bcrypt('password123'),
            'business_id' => $this->businessB->id,
            'status' => 'active',
        ]);
        
        $this->assertFalse($this->adminA->can('view', $userB));
    }

    public function test_direct_model_find_is_scoped(): void
    {
        $this->actingAs($this->adminA);
        
        // Direct find with explicit business scoping
        $product = Product::forBusiness(Product::query(), $this->businessA->id)->findOrFail($this->productA->id);
        $this->assertEquals($this->productA->id, $product->id);
        
        // Direct find on other business throws ModelNotFoundException
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        Product::forBusiness(Product::query(), $this->businessA->id)->findOrFail($this->productB->id);
    }

    public function test_policy_enforces_business_ownership(): void
    {
        $this->actingAs($this->adminA);
        
        // Can update own product
        $this->assertTrue($this->adminA->can('update', $this->productA));
        
        // Cannot update other business product
        $this->assertFalse($this->adminA->can('update', $this->productB));
    }
}
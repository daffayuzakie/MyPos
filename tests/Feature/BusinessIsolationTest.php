<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BusinessIsolationTest extends TestCase
{
    use RefreshDatabase;

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

    protected Unit $unitA;

    protected Unit $unitB;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles and permissions
        $permissionNames = [
            'products view', 'products create', 'products edit', 'products delete',
            'sales view-all', 'sales view-own', 'sales create', 'sales cancel',
            'users view', 'users create', 'users edit', 'users deactivate',
            'reports view', 'business settings',
            'categories view', 'categories create', 'categories edit', 'categories delete',
            'units view', 'units create', 'units edit', 'units delete',
        ];

        foreach ($permissionNames as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $kasirRole = Role::firstOrCreate(['name' => 'kasir', 'guard_name' => 'web']);

        $allPermissions = Permission::where('guard_name', 'web')->get()->pluck('id');
        $adminRole = Role::where('name', 'admin')->where('guard_name', 'web')->first();
        $adminRole->permissions()->sync($allPermissions->toArray());

        $kasirPermissions = [
            'products view',
            'sales view-all',
            'sales view-own',
            'sales create',
            'reports view',
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

        // Create two businesses
        $this->businessA = Business::create([
            'name' => 'Business A',
            'slug' => 'business-a-'.time(),
            'email' => 'a@test.com',
        ]);
        $this->businessB = Business::create([
            'name' => 'Business B',
            'slug' => 'business-b-'.time(),
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

        $this->unitA = Unit::create([
            'business_id' => $this->businessA->id,
            'name' => 'Kilogram',
            'code' => 'KG',
        ]);
        $this->unitB = Unit::create([
            'business_id' => $this->businessB->id,
            'name' => 'Liter',
            'code' => 'L',
        ]);

        $this->productA = Product::create([
            'business_id' => $this->businessA->id,
            'category_id' => $this->categoryA->id,
            'sku' => 'SKU-A1',
            'barcode' => '9876543210987',
            'name' => 'Product A',
            'purchase_price' => 1000,
            'selling_price' => 2000,
            'is_active' => true,
        ]);
        $this->productB = Product::create([
            'business_id' => $this->businessB->id,
            'category_id' => $this->categoryB->id,
            'sku' => 'SKU-B1',
            'name' => 'Product B',
            'purchase_price' => 1000,
            'selling_price' => 2000,
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
        $this->expectException(ModelNotFoundException::class);
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

    // Category CRUD tests - using actual application requests
    public function test_admin_can_create_category(): void
    {
        $this->actingAs($this->adminA);

        $response = $this->post('/categories', [
            'name' => 'New Category',
            'description' => 'Test description',
        ]);

        $response->assertStatus(302)
            ->assertRedirect('/categories');

        $this->assertDatabaseHas('categories', [
            'name' => 'New Category',
            'business_id' => $this->businessA->id,
        ]);
    }

    public function test_admin_can_view_own_categories(): void
    {
        $this->actingAs($this->adminA);

        $response = $this->get('/categories');

        $response->assertStatus(200);
    }

    public function test_admin_can_edit_own_category(): void
    {
        $this->actingAs($this->adminA);

        $response = $this->put("/categories/{$this->categoryA->id}", [
            'name' => 'Updated Category',
        ]);

        $response->assertStatus(302)
            ->assertRedirect('/categories');

        $this->assertDatabaseHas('categories', [
            'id' => $this->categoryA->id,
            'name' => 'Updated Category',
        ]);
    }

    public function test_admin_can_delete_own_category(): void
    {
        $this->actingAs($this->adminA);

        $response = $this->delete("/categories/{$this->categoryA->id}");

        $response->assertStatus(302)
            ->assertRedirect('/categories');

        $this->assertDatabaseMissing('categories', [
            'id' => $this->categoryA->id,
        ]);
    }

    public function test_business_a_user_cannot_access_business_b_category(): void
    {
        $this->actingAs($this->adminA);

        $response = $this->get("/categories/{$this->categoryB->id}/edit");

        // Should be 403 because user cannot edit other business's category
        $response->assertStatus(403);
    }

    public function test_duplicate_category_name_within_business_is_rejected(): void
    {
        $this->actingAs($this->adminA);

        $response = $this->post('/categories', [
            'name' => 'Category A', // Already exists in business A
        ]);

        // Validation failure - check for redirect and session errors
        $response->assertStatus(302);
        $response->assertSessionHasErrors('name');

        // Category count should remain 1 (the original)
        $this->assertEquals(1, Category::where('business_id', $this->businessA->id)->where('name', 'Category A')->count());
    }

    public function test_same_category_name_different_businesses_allowed(): void
    {
        $this->actingAs($this->adminA);

        // Business A already has 'Category A', create Business B with same name
        $this->actingAs($this->adminB);
        $response = $this->post('/categories', [
            'name' => 'Category A', // Same name, different business
        ]);

        // Should succeed since uniqueness is per-business
        $response->assertStatus(302)
            ->assertRedirect('/categories');
    }

    // Unit CRUD tests - using actual application requests
    public function test_admin_can_create_unit(): void
    {
        $this->actingAs($this->adminA);

        $response = $this->post('/units', [
            'name' => 'Piece',
            'code' => 'PCS',
        ]);

        $response->assertStatus(302)
            ->assertRedirect('/units');

        $this->assertDatabaseHas('units', [
            'name' => 'Piece',
            'code' => 'PCS',
            'business_id' => $this->businessA->id,
        ]);
    }

    public function test_admin_can_view_own_units(): void
    {
        $this->actingAs($this->adminA);

        $response = $this->get('/units');

        $response->assertStatus(200);
    }

    public function test_admin_can_edit_own_unit(): void
    {
        $this->actingAs($this->adminA);

        $response = $this->put("/units/{$this->unitA->id}", [
            'name' => 'Updated Unit',
            'code' => 'UPD',
        ]);

        $response->assertStatus(302)
            ->assertRedirect('/units');

        $this->assertDatabaseHas('units', [
            'id' => $this->unitA->id,
            'name' => 'Updated Unit',
            'code' => 'UPD',
        ]);
    }

    public function test_admin_can_delete_own_unit(): void
    {
        $this->actingAs($this->adminA);

        $response = $this->delete("/units/{$this->unitA->id}");

        $response->assertStatus(302)
            ->assertRedirect('/units');

        $this->assertDatabaseMissing('units', [
            'id' => $this->unitA->id,
        ]);
    }

    public function test_business_a_user_cannot_access_business_b_unit(): void
    {
        $this->actingAs($this->adminA);

        $response = $this->get("/units/{$this->unitB->id}/edit");

        // Should be 403 because user cannot edit other business's unit
        $response->assertStatus(403);
    }

    public function test_duplicate_unit_code_within_business_is_rejected(): void
    {
        $this->actingAs($this->adminA);

        $response = $this->post('/units', [
            'name' => 'New Unit',
            'code' => 'KG', // Already exists in business A
        ]);

        // Validation failure - check for redirect and session errors
        $response->assertStatus(302);
        $response->assertSessionHasErrors('code');

        // Unit count should remain 1 (the original)
        $this->assertEquals(1, Unit::where('business_id', $this->businessA->id)->where('code', 'KG')->count());
    }

    public function test_same_unit_code_different_businesses_allowed(): void
    {
        $this->actingAs($this->adminA);

        // Business A already has code 'KG', create Business B with same code
        $this->actingAs($this->adminB);
        $response = $this->post('/units', [
            'name' => 'New Unit',
            'code' => 'KG', // Same code, different business
        ]);

        // Should succeed since uniqueness is per-business
        $response->assertStatus(302)
            ->assertRedirect('/units');
    }

    // Product CRUD tests - using actual application requests
    public function test_admin_can_create_product(): void
    {
        $this->actingAs($this->adminA);

        $response = $this->post('/products', [
            'name' => 'New Product',
            'sku' => 'SKU-NEW',
            'barcode' => '9876543210988',
            'category_id' => $this->categoryA->id,
            'unit_id' => $this->unitA->id,
            'purchase_price' => 1000,
            'selling_price' => 2000,
        ]);

        $response->assertStatus(302)
            ->assertRedirect('/products');

        $this->assertDatabaseHas('products', [
            'name' => 'New Product',
            'sku' => 'SKU-NEW',
            'business_id' => $this->businessA->id,
        ]);
    }

    public function test_admin_can_view_own_products(): void
    {
        $this->actingAs($this->adminA);

        $response = $this->get('/products');

        $response->assertStatus(200);
    }

    public function test_admin_can_edit_own_product(): void
    {
        $this->actingAs($this->adminA);

        $response = $this->put("/products/{$this->productA->id}", [
            'name' => 'Updated Product',
            'sku' => 'SKU-A1',
            'barcode' => '9876543210987',
            'category_id' => $this->categoryA->id,
            'unit_id' => $this->unitA->id,
            'purchase_price' => 1500,
            'selling_price' => 2500,
        ]);

        $response->assertStatus(302)
            ->assertRedirect('/products');

        $this->assertDatabaseHas('products', [
            'id' => $this->productA->id,
            'name' => 'Updated Product',
            'purchase_price' => 1500,
            'selling_price' => 2500,
        ]);
    }

    public function test_admin_can_delete_own_product(): void
    {
        $this->actingAs($this->adminA);

        $response = $this->delete("/products/{$this->productA->id}");

        $response->assertStatus(302)
            ->assertRedirect('/products');

        $this->assertDatabaseMissing('products', [
            'id' => $this->productA->id,
        ]);
    }

    // Business isolation tests for Product
    public function test_business_a_user_cannot_access_business_b_product(): void
    {
        $this->actingAs($this->adminA);

        $response = $this->get("/products/{$this->productB->id}/edit");

        // Should be 403 because user cannot edit other business's product
        $response->assertStatus(403);
    }

    public function test_business_a_user_cannot_create_product_using_business_b_category(): void
    {
        $this->actingAs($this->adminA);

        $response = $this->post('/products', [
            'name' => 'New Product',
            'sku' => 'SKU-CROSS',
            'barcode' => '1111111111111',
            'category_id' => $this->categoryB->id, // Business B's category
            'unit_id' => $this->unitA->id,
            'purchase_price' => 1000,
            'selling_price' => 2000,
        ]);

        // Should be rejected with validation error
        $response->assertStatus(302);
        $response->assertSessionHasErrors('category_id');

        // Product should not be created
        $this->assertDatabaseMissing('products', [
            'sku' => 'SKU-CROSS',
            'business_id' => $this->businessA->id,
        ]);
    }

    public function test_business_a_user_cannot_create_product_using_business_b_unit(): void
    {
        $this->actingAs($this->adminA);

        $response = $this->post('/products', [
            'name' => 'New Product',
            'sku' => 'SKU-CROSS2',
            'barcode' => '2222222222222',
            'category_id' => $this->categoryA->id,
            'unit_id' => $this->unitB->id, // Business B's unit
            'purchase_price' => 1000,
            'selling_price' => 2000,
        ]);

        // Should be rejected with validation error
        $response->assertStatus(302);
        $response->assertSessionHasErrors('unit_id');

        // Product should not be created
        $this->assertDatabaseMissing('products', [
            'sku' => 'SKU-CROSS2',
            'business_id' => $this->businessA->id,
        ]);
    }

    // Cross-business update tests
    public function test_business_a_user_cannot_update_business_b_product(): void
    {
        $this->actingAs($this->adminA);

        $response = $this->put("/products/{$this->productB->id}", [
            'name' => 'Hacked Product',
            'sku' => 'SKU-B1',
            'barcode' => '9999999999999', // Different barcode to avoid unique conflict
            'category_id' => $this->categoryA->id,
            'unit_id' => $this->unitA->id,
            'purchase_price' => 1000,
            'selling_price' => 2000,
        ]);

        // Should be rejected (403 Forbidden)
        $response->assertStatus(403);
    }

    public function test_business_a_user_cannot_update_product_with_business_b_category(): void
    {
        $this->actingAs($this->adminA);

        $response = $this->put("/products/{$this->productA->id}", [
            'name' => 'Updated Product',
            'sku' => 'SKU-A1',
            'barcode' => '9876543210987',
            'category_id' => $this->categoryB->id, // Business B's category
            'unit_id' => $this->unitA->id,
            'purchase_price' => 1000,
            'selling_price' => 2000,
        ]);

        // Should be rejected with validation error
        $response->assertStatus(302);
        $response->assertSessionHasErrors('category_id');

        // Product should not be updated with other business category
        $this->assertDatabaseMissing('products', [
            'id' => $this->productA->id,
            'category_id' => $this->categoryB->id,
        ]);
    }

    public function test_business_a_user_cannot_update_product_with_business_b_unit(): void
    {
        $this->actingAs($this->adminA);

        $response = $this->put("/products/{$this->productA->id}", [
            'name' => 'Updated Product',
            'sku' => 'SKU-A1',
            'barcode' => '9876543210987',
            'category_id' => $this->categoryA->id,
            'unit_id' => $this->unitB->id, // Business B's unit
            'purchase_price' => 1000,
            'selling_price' => 2000,
        ]);

        // Should be rejected with validation error
        $response->assertStatus(302);
        $response->assertSessionHasErrors('unit_id');

        // Product should not be updated with other business unit
        $this->assertDatabaseMissing('products', [
            'id' => $this->productA->id,
            'unit_id' => $this->unitB->id,
        ]);
    }

    // Cross-business delete tests
    public function test_business_a_user_cannot_delete_business_b_product(): void
    {
        $this->actingAs($this->adminA);

        $response = $this->delete("/products/{$this->productB->id}");

        // Should be rejected (403 Forbidden)
        $response->assertStatus(403);

        // Product B should still exist
        $this->assertDatabaseHas('products', [
            'id' => $this->productB->id,
        ]);
    }

    // Uniqueness tests
    public function test_duplicate_sku_within_business_is_rejected(): void
    {
        $this->actingAs($this->adminA);

        $response = $this->post('/products', [
            'name' => 'New Product',
            'sku' => 'SKU-A1', // Already exists in business A
            'barcode' => '1111111111111',
            'category_id' => $this->categoryA->id,
            'unit_id' => $this->unitA->id,
            'purchase_price' => 1000,
            'selling_price' => 2000,
        ]);

        // Validation failure - check for redirect and session errors
        $response->assertStatus(302);
        $response->assertSessionHasErrors('sku');

        // Product should not be created with duplicate SKU
        $this->assertDatabaseMissing('products', [
            'sku' => 'SKU-A1',
            'business_id' => $this->businessA->id,
            'name' => 'New Product',
        ]);
    }

    public function test_duplicate_barcode_within_business_is_rejected(): void
    {
        $this->actingAs($this->adminA);

        $response = $this->post('/products', [
            'name' => 'New Product',
            'sku' => 'SKU-UNIQUE',
            'barcode' => '9876543210987', // Already exists in business A (productA)
            'category_id' => $this->categoryA->id,
            'unit_id' => $this->unitA->id,
            'purchase_price' => 1000,
            'selling_price' => 2000,
        ]);

        // Validation failure - check for redirect and session errors
        $response->assertStatus(302);
        $response->assertSessionHasErrors('barcode');

        // Product should not be created with duplicate barcode
        $this->assertDatabaseMissing('products', [
            'barcode' => '9876543210987',
            'business_id' => $this->businessA->id,
            'name' => 'New Product',
        ]);
    }

    public function test_same_sku_different_businesses_allowed(): void
    {
        $this->actingAs($this->adminA);

        // Business A already has 'SKU-A1', create Business B with same SKU
        $this->actingAs($this->adminB);
        $response = $this->post('/products', [
            'name' => 'Product B Variant',
            'sku' => 'SKU-A1', // Same SKU, different business
            'barcode' => '2222222222222',
            'category_id' => $this->categoryB->id,
            'unit_id' => $this->unitB->id,
            'purchase_price' => 1000,
            'selling_price' => 2000,
        ]);

        // Should succeed since uniqueness is per-business
        $response->assertStatus(302)
            ->assertRedirect('/products');

        $this->assertDatabaseHas('products', [
            'sku' => 'SKU-A1',
            'business_id' => $this->businessB->id,
        ]);
    }

    public function test_same_barcode_different_businesses_allowed(): void
    {
        $this->actingAs($this->adminA);

        // Business A already has barcode '9876543210987', create Business B with same barcode
        $this->actingAs($this->adminB);
        $response = $this->post('/products', [
            'name' => 'Product B Variant',
            'sku' => 'SKU-UNIQUE-B',
            'barcode' => '9876543210987', // Same barcode, different business
            'category_id' => $this->categoryB->id,
            'unit_id' => $this->unitB->id,
            'purchase_price' => 1000,
            'selling_price' => 2000,
        ]);

        // Should succeed since uniqueness is per-business
        $response->assertStatus(302)
            ->assertRedirect('/products');

        $this->assertDatabaseHas('products', [
            'barcode' => '9876543210987',
            'business_id' => $this->businessB->id,
        ]);
    }

    // Update with own SKU/barcode should be allowed
    public function test_update_product_with_own_sku_allowed(): void
    {
        $this->actingAs($this->adminA);

        $response = $this->put("/products/{$this->productA->id}", [
            'name' => 'Updated Product A',
            'sku' => 'SKU-A1', // Same SKU as itself
            'barcode' => '9876543210987', // Same barcode as itself
            'category_id' => $this->categoryA->id,
            'unit_id' => $this->unitA->id,
            'purchase_price' => 1500,
            'selling_price' => 2500,
        ]);

        $response->assertStatus(302)
            ->assertRedirect('/products');

        $this->assertDatabaseHas('products', [
            'id' => $this->productA->id,
            'name' => 'Updated Product A',
        ]);
    }

    // Kasir authorization tests
    public function test_kasir_cannot_create_product(): void
    {
        $this->actingAs($this->kasirA);

        $response = $this->post('/products', [
            'name' => 'New Product',
            'sku' => 'SKU-KASIR',
            'barcode' => '1111111111111',
            'category_id' => $this->categoryA->id,
            'unit_id' => $this->unitA->id,
            'purchase_price' => 1000,
            'selling_price' => 2000,
        ]);

        $response->assertStatus(403);
    }

    public function test_kasir_cannot_edit_product(): void
    {
        $this->actingAs($this->kasirA);

        $response = $this->put("/products/{$this->productA->id}", [
            'name' => 'Hacked Product',
            'sku' => 'SKU-A1',
            'barcode' => '9876543210987',
            'category_id' => $this->categoryA->id,
            'unit_id' => $this->unitA->id,
            'purchase_price' => 1000,
            'selling_price' => 2000,
        ]);

        $response->assertStatus(403);
    }

    public function test_kasir_cannot_delete_product(): void
    {
        $this->actingAs($this->kasirA);

        $response = $this->delete("/products/{$this->productA->id}");

        $response->assertStatus(403);
    }

    // Search and pagination tests
    public function test_product_search_only_within_business(): void
    {
        // Create additional products for Business A
        Product::create([
            'business_id' => $this->businessA->id,
            'category_id' => $this->categoryA->id,
            'unit_id' => $this->unitA->id,
            'sku' => 'SKU-SEARCH1',
            'name' => 'Coffee Beans',
            'purchase_price' => 500,
            'selling_price' => 1000,
            'is_active' => true,
        ]);

        Product::create([
            'business_id' => $this->businessA->id,
            'category_id' => $this->categoryA->id,
            'unit_id' => $this->unitA->id,
            'sku' => 'SKU-SEARCH2',
            'name' => 'Tea Leaves',
            'purchase_price' => 300,
            'selling_price' => 800,
            'is_active' => true,
        ]);

        $this->actingAs($this->adminA);

        // Search for "Coffee" - should only return Business A products
        $response = $this->get('/products?search=Coffee');

        $response->assertStatus(200);
        // Should only find products within Business A
        $content = $response->getContent();
        $this->assertStringContainsString('Coffee Beans', $content);
        $this->assertStringNotContainsString('Product B', $content);
    }

    public function test_product_pagination_only_within_business(): void
    {
        // Create many products for Business A
        for ($i = 1; $i <= 15; $i++) {
            Product::create([
                'business_id' => $this->businessA->id,
                'category_id' => $this->categoryA->id,
                'unit_id' => $this->unitA->id,
                'sku' => "SKU-PAG{$i}",
                'name' => "Product {$i}",
                'purchase_price' => 1000,
                'selling_price' => 2000,
                'is_active' => true,
            ]);
        }

        $this->actingAs($this->adminA);

        $response = $this->get('/products');

        $response->assertStatus(200);
        // Should paginate (default 10 per page)
        $content = $response->getContent();
        $this->assertStringContainsString('pagination', strtolower($content));
    }

    // Business Settings Tests
    public function test_admin_can_access_business_settings(): void
    {
        $this->actingAs($this->adminA);

        $response = $this->get(route('business.settings'));

        $response->assertStatus(200);
        $response->assertSee('Business Settings');
    }

    public function test_admin_can_update_business_settings(): void
    {
        $this->actingAs($this->adminA);

        $response = $this->put(route('business.settings.update'), [
            'name' => 'Updated Business Name',
            'slug' => $this->businessA->slug,
            'email' => 'updated@example.com',
            'phone' => '+628123456789',
            'address' => 'Updated Address',
            'tax_percentage' => 10,
            'invoice_prefix' => 'INV',
            'invoice_sequence' => 1,
        ]);

        $response->assertRedirect(route('business.settings'));
        $this->businessA->refresh();
        $this->assertEquals('Updated Business Name', $this->businessA->name);
        $this->assertEquals('updated@example.com', $this->businessA->email);
        $this->assertEquals(10, $this->businessA->tax_percentage);
    }

    public function test_admin_can_change_business_code(): void
    {
        $this->actingAs($this->adminA);

        $response = $this->put(route('business.settings.update'), [
            'name' => $this->businessA->name,
            'slug' => 'kode-baru-123',
            'email' => $this->businessA->email,
        ]);

        $response->assertRedirect(route('business.settings'));
        $this->assertEquals('kode-baru-123', $this->businessA->fresh()->slug);
    }

    public function test_business_code_must_be_a_valid_slug(): void
    {
        $this->actingAs($this->adminA);

        $response = $this->put(route('business.settings.update'), [
            'name' => $this->businessA->name,
            'slug' => 'Invalid Slug!',
            'email' => $this->businessA->email,
        ]);

        $response->assertSessionHasErrors('slug');
    }

    public function test_business_code_must_be_unique(): void
    {
        $this->actingAs($this->adminA);

        $response = $this->put(route('business.settings.update'), [
            'name' => $this->businessA->name,
            'slug' => $this->businessB->slug,
            'email' => $this->businessA->email,
        ]);

        $response->assertSessionHasErrors('slug');
    }

    public function test_admin_can_upload_business_logo(): void
    {
        Storage::fake('public');
        $this->actingAs($this->adminA);

        $response = $this->put(route('business.settings.update'), [
            'name' => $this->businessA->name,
            'slug' => $this->businessA->slug,
            'email' => $this->businessA->email,
            'logo' => UploadedFile::fake()->image('logo.png'),
        ]);

        $response->assertRedirect(route('business.settings'));

        $this->businessA->refresh();
        $this->assertNotNull($this->businessA->logo);
        Storage::disk('public')->assertExists($this->businessA->logo);
        $this->assertStringContainsString('/storage/', $this->businessA->logo_url);
    }

    public function test_kasir_cannot_access_business_settings(): void
    {
        $this->actingAs($this->kasirA);

        $response = $this->get(route('business.settings'));

        $response->assertStatus(403);
    }

    public function test_kasir_sidebar_hides_employee_and_settings_links(): void
    {
        $this->actingAs($this->kasirA);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Karyawan')
            ->assertDontSee('Pengaturan');
    }

    public function test_admin_sidebar_shows_employee_and_settings_links(): void
    {
        $this->actingAs($this->adminA);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Karyawan')
            ->assertSee('Pengaturan');
    }

    public function test_business_a_admin_cannot_access_business_b_settings(): void
    {
        $this->actingAs($this->adminA);

        // Try to access another business's settings (not directly possible via route,
        // but verify policy prevents cross-business access)
        $response = $this->get(route('business.settings'));

        $response->assertStatus(200);
        // Should only see own business settings
        $this->assertStringNotContainsString($this->businessB->name, $response->getContent());
    }
}

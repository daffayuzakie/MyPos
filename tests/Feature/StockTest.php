<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StockTest extends TestCase
{
    use RefreshDatabase;

    protected Business $businessA;

    protected Business $businessB;

    protected User $adminA;

    protected User $kasirA;

    protected User $adminB;

    protected User $kasirB;

    protected Category $categoryA;

    protected Category $categoryB;

    protected Unit $unitA;

    protected Unit $unitB;

    protected Product $productA;

    protected Product $productB;

    protected function setUp(): void
    {
        parent::setUp();

        /*
         * ============================================================
         * PERMISSIONS & ROLES
         * ============================================================
         */

        $permissionNames = [
            'products view',
            'products create',
            'products edit',
            'products delete',

            'sales view-all',
            'sales view-own',
            'sales create',
            'sales cancel',

            'purchases view',
            'purchases create',
            'purchases edit',

            'users view',
            'users create',
            'users edit',
            'users deactivate',

            'reports view',
            'business settings',

            'categories view',
            'categories create',
            'categories edit',
            'categories delete',

            'units view',
            'units create',
            'units edit',
            'units delete',

            'stock view',
            'stock create',
            'stock history',
        ];

        foreach ($permissionNames as $name) {
            Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]);
        }

        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $kasirRole = Role::firstOrCreate([
            'name' => 'kasir',
            'guard_name' => 'web',
        ]);

        /*
         * Admin mendapatkan semua permission.
         */
        $allPermissions = Permission::where(
            'guard_name',
            'web'
        )->get();

        $adminRole->syncPermissions($allPermissions);

        /*
         * Kasir hanya mendapatkan permission yang memang
         * dibutuhkan untuk MVP.
         */
        $kasirRole->syncPermissions([
            'products view',
            'sales view-all',
            'sales view-own',
            'sales create',
            'purchases view',
            'reports view',
            'stock view',
        ]);

        /*
         * ============================================================
         * BUSINESSES
         * ============================================================
         */

        $this->businessA = Business::create([
            'name' => 'Business A',
            'slug' => 'business-a-'.uniqid(),
            'email' => 'a@test.com',
        ]);

        $this->businessB = Business::create([
            'name' => 'Business B',
            'slug' => 'business-b-'.uniqid(),
            'email' => 'b@test.com',
        ]);

        /*
         * ============================================================
         * USERS
         * ============================================================
         */

        $this->adminA = User::create([
            'name' => 'Admin A',
            'email' => 'admin-a@test.com',
            'password' => bcrypt('password123'),
            'business_id' => $this->businessA->id,
            'status' => 'active',
        ]);

        $this->adminA->assignRole('admin');

        $this->kasirA = User::create([
            'name' => 'Kasir A',
            'email' => 'kasir-a@test.com',
            'password' => bcrypt('password123'),
            'business_id' => $this->businessA->id,
            'status' => 'active',
        ]);

        $this->kasirA->assignRole('kasir');

        $this->adminB = User::create([
            'name' => 'Admin B',
            'email' => 'admin-b@test.com',
            'password' => bcrypt('password123'),
            'business_id' => $this->businessB->id,
            'status' => 'active',
        ]);

        $this->adminB->assignRole('admin');

        $this->kasirB = User::create([
            'name' => 'Kasir B',
            'email' => 'kasir-b@test.com',
            'password' => bcrypt('password123'),
            'business_id' => $this->businessB->id,
            'status' => 'active',
        ]);

        $this->kasirB->assignRole('kasir');

        /*
         * ============================================================
         * CATEGORIES
         * ============================================================
         */

        $this->categoryA = Category::create([
            'business_id' => $this->businessA->id,
            'name' => 'Category A',
        ]);

        $this->categoryB = Category::create([
            'business_id' => $this->businessB->id,
            'name' => 'Category B',
        ]);

        /*
         * ============================================================
         * UNITS
         * ============================================================
         */

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

        /*
         * ============================================================
         * PRODUCTS
         * ============================================================
         */

        $this->productA = Product::create([
            'business_id' => $this->businessA->id,
            'category_id' => $this->categoryA->id,
            'unit_id' => $this->unitA->id,
            'sku' => 'SKU-A1',
            'barcode' => '9876543210987',
            'name' => 'Product A',
            'purchase_price' => 1000,
            'selling_price' => 2000,
            'is_active' => true,
        ]);

        $this->productA->stock = 10;
        $this->productA->save();

        $this->productB = Product::create([
            'business_id' => $this->businessB->id,
            'category_id' => $this->categoryB->id,
            'unit_id' => $this->unitB->id,
            'sku' => 'SKU-B1',
            'barcode' => '9876543210988',
            'name' => 'Product B',
            'purchase_price' => 1000,
            'selling_price' => 2000,
            'is_active' => true,
        ]);

        $this->productB->stock = 10;
        $this->productB->save();
    }

    /*
     * ================================================================
     * STOCK IN
     * ================================================================
     */

    public function test_admin_can_create_stock_in(): void
    {
        $this->actingAs($this->adminA);

        $response = $this->post('/stock', [
            'product_id' => $this->productA->id,
            'type' => 'in',
            'quantity' => 20,
            'reason' => 'Restock from supplier',
            'note' => 'Monthly restock',
        ]);

        $response->assertStatus(302)
            ->assertRedirect('/stock');

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->productA->id,
            'type' => 'in',
            'quantity' => 20,
            'reason' => 'Restock from supplier',
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $this->productA->id,
            'stock' => 30,
        ]);
    }

    /*
     * ================================================================
     * STOCK OUT
     * ================================================================
     */

    public function test_admin_can_create_stock_out(): void
    {
        $this->actingAs($this->adminA);

        $response = $this->post('/stock', [
            'product_id' => $this->productA->id,
            'type' => 'out',
            'quantity' => 5,
            'reason' => 'Customer purchase',
            'note' => 'Sale to customer',
        ]);

        $response->assertStatus(302)
            ->assertRedirect('/stock');

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->productA->id,
            'type' => 'out',
            'quantity' => 5,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $this->productA->id,
            'stock' => 5,
        ]);
    }

    /*
     * ================================================================
     * ADJUSTMENT
     * ================================================================
     */

    public function test_admin_can_create_positive_adjustment(): void
    {
        $this->actingAs($this->adminA);

        $response = $this->post('/stock', [
            'product_id' => $this->productA->id,
            'type' => 'adjustment',
            'quantity' => 15,
            'reason' => 'Inventory correction',
            'note' => 'Found missing items',
        ]);

        $response->assertStatus(302)
            ->assertRedirect('/stock');

        $this->assertDatabaseHas('products', [
            'id' => $this->productA->id,
            'stock' => 25,
        ]);
    }

    public function test_admin_can_create_negative_adjustment(): void
    {
        $this->actingAs($this->adminA);

        $response = $this->post('/stock', [
            'product_id' => $this->productA->id,
            'type' => 'adjustment',
            'quantity' => -5,
            'reason' => 'Damaged goods',
            'note' => 'Written off',
        ]);

        $response->assertStatus(302)
            ->assertRedirect('/stock');

        $this->assertDatabaseHas('products', [
            'id' => $this->productA->id,
            'stock' => 5,
        ]);
    }

    /*
     * ================================================================
     * VALIDATION
     * ================================================================
     */

    public function test_cannot_create_in_with_zero_quantity(): void
    {
        $this->actingAs($this->adminA);

        $response = $this->post('/stock', [
            'product_id' => $this->productA->id,
            'type' => 'in',
            'quantity' => 0,
            'reason' => 'Test',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('quantity');

        $this->productA->refresh();

        $this->assertEquals(10, $this->productA->stock);
    }

    public function test_cannot_create_out_with_zero_quantity(): void
    {
        $this->actingAs($this->adminA);

        $response = $this->post('/stock', [
            'product_id' => $this->productA->id,
            'type' => 'out',
            'quantity' => 0,
            'reason' => 'Test',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('quantity');

        $this->productA->refresh();

        $this->assertEquals(10, $this->productA->stock);
    }

    public function test_cannot_create_adjustment_with_zero_quantity(): void
    {
        $this->actingAs($this->adminA);

        $response = $this->post('/stock', [
            'product_id' => $this->productA->id,
            'type' => 'adjustment',
            'quantity' => 0,
            'reason' => 'Test',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('quantity');

        $this->productA->refresh();

        $this->assertEquals(10, $this->productA->stock);
    }

    public function test_cannot_create_out_more_than_available_stock(): void
    {
        $this->actingAs($this->adminA);

        $response = $this->post('/stock', [
            'product_id' => $this->productA->id,
            'type' => 'out',
            'quantity' => 100,
            'reason' => 'Test',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('quantity');

        $this->productA->refresh();

        $this->assertEquals(10, $this->productA->stock);
    }

    public function test_cannot_adjust_to_negative_stock(): void
    {
        $this->actingAs($this->adminA);

        $response = $this->post('/stock', [
            'product_id' => $this->productA->id,
            'type' => 'adjustment',
            'quantity' => -100,
            'reason' => 'Test',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('quantity');

        $this->productA->refresh();

        $this->assertEquals(10, $this->productA->stock);
    }

    public function test_reason_is_required(): void
    {
        $this->actingAs($this->adminA);

        $response = $this->post('/stock', [
            'product_id' => $this->productA->id,
            'type' => 'in',
            'quantity' => 10,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('reason');

        $this->assertDatabaseMissing('stock_movements', [
            'product_id' => $this->productA->id,
            'type' => 'in',
            'quantity' => 10,
        ]);

        $this->productA->refresh();

        $this->assertEquals(10, $this->productA->stock);
    }

    public function test_note_is_optional(): void
    {
        $this->actingAs($this->adminA);

        $response = $this->post('/stock', [
            'product_id' => $this->productA->id,
            'type' => 'in',
            'quantity' => 10,
            'reason' => 'Restock',
        ]);

        $response->assertStatus(302)
            ->assertRedirect('/stock');

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->productA->id,
            'type' => 'in',
            'quantity' => 10,
            'reason' => 'Restock',
        ]);

        $this->productA->refresh();

        $this->assertEquals(20, $this->productA->stock);
    }

    /*
     * ================================================================
     * BUSINESS ISOLATION
     * ================================================================
     */

    public function test_business_a_cannot_view_business_b_movements(): void
    {
        $this->actingAs($this->adminA);

        StockMovement::factory()->create([
            'business_id' => $this->businessB->id,
            'product_id' => $this->productB->id,
            'user_id' => $this->adminB->id,
            'type' => 'in',
            'quantity' => 10,
            'stock_before' => 0,
            'stock_after' => 10,
            'reason' => 'Business B movement',
        ]);

        $response = $this->get('/stock');

        $response->assertStatus(200);

        $response->assertDontSee('Business B movement');
    }

    public function test_business_a_cannot_create_movement_for_business_b_product(): void
    {
        $this->actingAs($this->adminA);

        $response = $this->post('/stock', [
            'product_id' => $this->productB->id,
            'type' => 'in',
            'quantity' => 10,
            'reason' => 'Cross business attempt',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('product_id');

        $this->assertDatabaseMissing('stock_movements', [
            'business_id' => $this->businessA->id,
            'product_id' => $this->productB->id,
        ]);

        $this->productB->refresh();

        $this->assertEquals(10, $this->productB->stock);
    }

    /*
     * ================================================================
     * AUTHORIZATION
     * ================================================================
     */

    public function test_admin_can_view_stock(): void
    {
        $this->actingAs($this->adminA);

        $response = $this->get('/stock');

        $response->assertStatus(200);
    }

    public function test_kasir_can_view_stock(): void
    {
        $this->actingAs($this->kasirA);

        $response = $this->get('/stock');

        $response->assertStatus(200);
    }

    public function test_kasir_can_view_stock_movements(): void
    {
        $this->actingAs($this->kasirA);

        $response = $this->get('/stock');

        $response->assertStatus(200);
    }

    public function test_kasir_cannot_create_stock_movement(): void
    {
        $this->actingAs($this->kasirA);

        $response = $this->post('/stock', [
            'product_id' => $this->productA->id,
            'type' => 'in',
            'quantity' => 10,
            'reason' => 'Unauthorized stock movement',
        ]);

        $response->assertStatus(403);

        $this->productA->refresh();

        $this->assertEquals(10, $this->productA->stock);
    }

    /*
     * ================================================================
     * AUDIT INTEGRITY
     * ================================================================
     */

    public function test_movement_stores_authenticated_user(): void
    {
        $this->actingAs($this->adminA);

        $response = $this->post('/stock', [
            'product_id' => $this->productA->id,
            'type' => 'in',
            'quantity' => 5,
            'reason' => 'Test',
        ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('stock_movements', [
            'user_id' => $this->adminA->id,
            'business_id' => $this->businessA->id,
            'product_id' => $this->productA->id,
        ]);
    }

    public function test_movement_stores_correct_stock_before_and_after_for_in(): void
    {
        $this->actingAs($this->adminA);

        $product = Product::create([
            'business_id' => $this->businessA->id,
            'category_id' => $this->categoryA->id,
            'unit_id' => $this->unitA->id,
            'sku' => 'SKU-STOCK-IN',
            'name' => 'Stock IN Product',
            'purchase_price' => 1000,
            'selling_price' => 2000,
            'is_active' => true,
        ]);

        $product->stock = 10;
        $product->save();

        $response = $this->post('/stock', [
            'product_id' => $product->id,
            'type' => 'in',
            'quantity' => 5,
            'reason' => 'Stock IN test',
        ]);

        $response->assertStatus(302);

        $movement = StockMovement::where(
            'product_id',
            $product->id
        )->latest()->first();

        $this->assertNotNull($movement);
        $this->assertEquals(10, $movement->stock_before);
        $this->assertEquals(15, $movement->stock_after);
        $this->assertEquals(5, $movement->quantity);
    }

    public function test_movement_stores_correct_stock_before_and_after_for_out(): void
    {
        $this->actingAs($this->adminA);

        $product = Product::create([
            'business_id' => $this->businessA->id,
            'category_id' => $this->categoryA->id,
            'unit_id' => $this->unitA->id,
            'sku' => 'SKU-STOCK-OUT',
            'name' => 'Stock OUT Product',
            'purchase_price' => 1000,
            'selling_price' => 2000,
            'is_active' => true,
        ]);

        $product->stock = 20;
        $product->save();

        $response = $this->post('/stock', [
            'product_id' => $product->id,
            'type' => 'out',
            'quantity' => 8,
            'reason' => 'Stock OUT test',
        ]);

        $response->assertStatus(302);

        $movement = StockMovement::where(
            'product_id',
            $product->id
        )->latest()->first();

        $this->assertNotNull($movement);
        $this->assertEquals(20, $movement->stock_before);
        $this->assertEquals(12, $movement->stock_after);
        $this->assertEquals(8, $movement->quantity);
    }

    public function test_movement_stores_correct_stock_before_and_after_for_adjustment(): void
    {
        $this->actingAs($this->adminA);

        $product = Product::create([
            'business_id' => $this->businessA->id,
            'category_id' => $this->categoryA->id,
            'unit_id' => $this->unitA->id,
            'sku' => 'SKU-STOCK-ADJUSTMENT',
            'name' => 'Adjustment Product',
            'purchase_price' => 1000,
            'selling_price' => 2000,
            'is_active' => true,
        ]);

        $product->stock = 15;
        $product->save();

        $response = $this->post('/stock', [
            'product_id' => $product->id,
            'type' => 'adjustment',
            'quantity' => -3,
            'reason' => 'Damaged goods',
        ]);

        $response->assertStatus(302);

        $movement = StockMovement::where(
            'product_id',
            $product->id
        )->latest()->first();

        $this->assertNotNull($movement);
        $this->assertEquals(15, $movement->stock_before);
        $this->assertEquals(12, $movement->stock_after);
        $this->assertEquals(-3, $movement->quantity);
    }

    /*
     * ================================================================
     * STOCK INTEGRITY
     * ================================================================
     */

    public function test_product_stock_equals_latest_movement_stock_after(): void
    {
        $this->actingAs($this->adminA);

        $product = Product::create([
            'business_id' => $this->businessA->id,
            'category_id' => $this->categoryA->id,
            'unit_id' => $this->unitA->id,
            'sku' => 'SKU-INTEGRITY',
            'name' => 'Integrity Test Product',
            'purchase_price' => 1000,
            'selling_price' => 2000,
            'is_active' => true,
        ]);

        /*
         * Stock IN
         */
        $response = $this->post('/stock', [
            'product_id' => $product->id,
            'type' => 'in',
            'quantity' => 5,
            'reason' => 'Initial stock addition',
        ]);

        $response->assertRedirect(route('stock.index'));

        $product->refresh();

        $latestMovement = StockMovement::where(
            'product_id',
            $product->id
        )->latest()->first();

        $this->assertNotNull($latestMovement);
        $this->assertEquals(5, $product->stock);
        $this->assertEquals(5, $latestMovement->stock_after);
    }

    /*
     * ================================================================
     * PRODUCT DELETE PROTECTION
     * ================================================================
     */

    public function test_product_with_movement_history_cannot_be_deleted(): void
    {
        $this->actingAs($this->adminA);

        /*
         * Product already has stock movement.
         * Product should not be deletable.
         */
        $this->post('/stock', [
            'product_id' => $this->productA->id,
            'type' => 'in',
            'quantity' => 5,
            'reason' => 'Create movement history',
        ]);

        $response = $this->delete(
            "/products/{$this->productA->id}"
        );

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertDatabaseHas('products', [
            'id' => $this->productA->id,
        ]);
    }

    public function test_product_without_movement_can_be_deleted(): void
    {
        $this->actingAs($this->adminA);

        $product = Product::create([
            'business_id' => $this->businessA->id,
            'category_id' => $this->categoryA->id,
            'unit_id' => $this->unitA->id,
            'sku' => 'SKU-NO-MOVEMENT',
            'name' => 'Product No Movement',
            'purchase_price' => 1000,
            'selling_price' => 2000,
            'is_active' => true,
        ]);

        $response = $this->delete(
            "/products/{$product->id}"
        );

        $response->assertStatus(302);

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }

    /*
     * ================================================================
     * FAILED OPERATION MUST NOT CHANGE STOCK
     * ================================================================
     *
     * Ini bukan concurrency test.
     *
     * Kita hanya memastikan operasi yang ditolak oleh validation
     * tidak menyebabkan perubahan stock.
     *
     * Concurrency protection sendiri berada di StockService melalui
     * DB transaction + lockForUpdate().
     */

    public function test_failed_stock_out_does_not_change_stock(): void
    {
        $this->actingAs($this->adminA);

        $initialStock = $this->productA->stock;

        $response = $this->post('/stock', [
            'product_id' => $this->productA->id,
            'type' => 'out',
            'quantity' => $initialStock + 100,
            'reason' => 'Should fail',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('quantity');

        $this->productA->refresh();

        $this->assertEquals(
            $initialStock,
            $this->productA->stock
        );

        $this->assertDatabaseMissing('stock_movements', [
            'product_id' => $this->productA->id,
            'reason' => 'Should fail',
        ]);
    }
}

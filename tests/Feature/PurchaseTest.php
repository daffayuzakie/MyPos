<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use App\Services\PurchaseService;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PurchaseTest extends TestCase
{
    use RefreshDatabase;

    protected Business $business;

    protected User $admin;

    protected User $kasir;

    protected Supplier $supplier;

    protected Product $product;

    protected PurchaseService $purchaseService;

    protected function setUp(): void
    {
        parent::setUp();

        $permissionNames = [
            'products view', 'products create', 'products edit', 'products delete',
            'sales view-all', 'sales view-own', 'sales create', 'sales cancel',
            'purchases view', 'purchases create', 'purchases edit',
            'stock view', 'stock create',
            'categories view', 'categories create', 'categories edit', 'categories delete',
            'units view', 'units create', 'units edit', 'units delete',
            'users view', 'users create', 'users edit', 'users deactivate',
            'reports view', 'business settings',
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
        $adminRole->syncPermissions(Permission::where('guard_name', 'web')->get());

        $kasirRole = Role::firstOrCreate([
            'name' => 'kasir',
            'guard_name' => 'web',
        ]);
        $kasirRole->givePermissionTo(['purchases view', 'products view']);

        $this->business = Business::factory()->create();
        $this->admin = User::factory()->create([
            'business_id' => $this->business->id,
            'status' => 'active',
        ]);
        $this->admin->assignRole('admin');

        $this->kasir = User::factory()->create([
            'business_id' => $this->business->id,
            'status' => 'active',
        ]);
        $this->kasir->assignRole('kasir');

        $this->supplier = Supplier::factory()->create([
            'business_id' => $this->business->id,
            'name' => 'Test Supplier',
        ]);

        $this->product = Product::factory()->create([
            'business_id' => $this->business->id,
            'stock' => 100,
        ]);

        $this->purchaseService = new PurchaseService(
            new Purchase,
            new PurchaseItem,
            new Product,
            new StockMovement,
            new Supplier,
            new StockService(
                new Product,
                new StockMovement
            )
        );
    }

    // =========================================================================
    // CREATE PURCHASE TESTS
    // =========================================================================

    public function test_admin_can_create_purchase()
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('purchases.store'), [
            'supplier_id' => $this->supplier->id,
            'invoice_number' => 'PO-001',
            'purchase_date' => now()->format('Y-m-d'),
            'notes' => 'Test purchase',
        ]);

        $response->assertRedirect(route('purchases.index'));
        $this->assertDatabaseHas('purchases', [
            'invoice_number' => 'PO-001',
            'supplier_id' => $this->supplier->id,
            'status' => 'draft',
            'business_id' => $this->business->id,
        ]);
    }

    public function test_admin_cannot_create_purchase_with_duplicate_invoice()
    {
        $this->actingAs($this->admin);

        Purchase::factory()->create([
            'business_id' => $this->business->id,
            'invoice_number' => 'PO-001',
        ]);

        $response = $this->post(route('purchases.store'), [
            'supplier_id' => $this->supplier->id,
            'invoice_number' => 'PO-001',
            'purchase_date' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('invoice_number');
    }

    public function test_admin_cannot_create_purchase_with_invalid_supplier()
    {
        $this->actingAs($this->admin);

        $otherBusiness = Business::factory()->create();
        $otherSupplier = Supplier::factory()->create([
            'business_id' => $otherBusiness->id,
        ]);

        $response = $this->post(route('purchases.store'), [
            'supplier_id' => $otherSupplier->id,
            'invoice_number' => 'PO-001',
            'purchase_date' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('supplier_id');
    }

    public function test_kasir_cannot_create_purchase()
    {
        $this->actingAs($this->kasir);

        $response = $this->post(route('purchases.store'), [
            'supplier_id' => $this->supplier->id,
            'invoice_number' => 'PO-001',
            'purchase_date' => now()->format('Y-m-d'),
        ]);

        $response->assertStatus(403);
    }

    public function test_creating_draft_purchase_does_not_change_stock()
    {
        $this->actingAs($this->admin);

        $initialStock = $this->product->stock;

        $purchase = Purchase::factory()->create([
            'business_id' => $this->business->id,
            'supplier_id' => $this->supplier->id,
            'status' => 'draft',
        ]);

        $this->product->refresh();
        $this->assertEquals($initialStock, $this->product->stock);
    }

    // =========================================================================
    // PURCHASE ITEM TESTS
    // =========================================================================

    public function test_admin_can_add_item_to_draft_purchase()
    {
        $this->actingAs($this->admin);

        $purchase = Purchase::factory()->create([
            'business_id' => $this->business->id,
            'supplier_id' => $this->supplier->id,
            'status' => 'draft',
        ]);

        $response = $this->post(route('purchases.items.store', $purchase), [
            'product_id' => $this->product->id,
            'quantity' => 10,
            'unit_price' => 50000,
        ]);

        $response->assertRedirect(route('purchases.index'));
        $this->assertDatabaseHas('purchase_items', [
            'purchase_id' => $purchase->id,
            'product_id' => $this->product->id,
            'quantity' => 10,
            'unit_price' => 50000,
            'subtotal' => 500000,
        ]);
    }

    public function test_adding_item_updates_purchase_total()
    {
        $this->actingAs($this->admin);

        $purchase = Purchase::factory()->create([
            'business_id' => $this->business->id,
            'supplier_id' => $this->supplier->id,
            'status' => 'draft',
            'total_amount' => 0,
        ]);

        $this->post(route('purchases.items.store', $purchase), [
            'product_id' => $this->product->id,
            'quantity' => 5,
            'unit_price' => 20000,
        ]);

        $purchase->refresh();
        $this->assertEquals(100000, $purchase->total_amount);
    }

    public function test_cannot_add_duplicate_product_to_purchase()
    {
        $this->actingAs($this->admin);

        $purchase = Purchase::factory()->create([
            'business_id' => $this->business->id,
            'supplier_id' => $this->supplier->id,
            'status' => 'draft',
        ]);

        PurchaseItem::factory()->create([
            'purchase_id' => $purchase->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->post(route('purchases.items.store', $purchase), [
            'product_id' => $this->product->id,
            'quantity' => 10,
            'unit_price' => 50000,
        ]);

        $response->assertSessionHasErrors('product_id');
    }

    public function test_cannot_add_item_to_completed_purchase()
    {
        $this->actingAs($this->admin);

        $purchase = Purchase::factory()->create([
            'business_id' => $this->business->id,
            'supplier_id' => $this->supplier->id,
            'status' => 'completed',
        ]);

        $response = $this->post(route('purchases.items.store', $purchase), [
            'product_id' => $this->product->id,
            'quantity' => 10,
            'unit_price' => 50000,
        ]);

        $response->assertSessionHasErrors('purchase');
    }

    public function test_admin_can_remove_item_from_draft_purchase()
    {
        $this->actingAs($this->admin);

        $purchase = Purchase::factory()->create([
            'business_id' => $this->business->id,
            'supplier_id' => $this->supplier->id,
            'status' => 'draft',
            'total_amount' => 500000,
        ]);

        $item = PurchaseItem::factory()->create([
            'purchase_id' => $purchase->id,
            'product_id' => $this->product->id,
            'quantity' => 10,
            'unit_price' => 50000,
            'subtotal' => 500000,
        ]);

        $response = $this->delete(route('purchases.items.destroy', [$purchase, $item]));

        $response->assertRedirect(route('purchases.index'));
        $this->assertDatabaseMissing('purchase_items', ['id' => $item->id]);

        $purchase->refresh();
        $this->assertEquals(0, $purchase->total_amount);
    }

    public function test_cannot_remove_item_from_completed_purchase()
    {
        $this->actingAs($this->admin);

        $purchase = Purchase::factory()->create([
            'business_id' => $this->business->id,
            'supplier_id' => $this->supplier->id,
            'status' => 'completed',
        ]);

        $item = PurchaseItem::factory()->create([
            'purchase_id' => $purchase->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->delete(route('purchases.items.destroy', [$purchase, $item]));

        $response->assertSessionHasErrors('purchase');
    }

    public function test_cannot_add_item_to_purchase_of_another_business()
    {
        $this->actingAs($this->admin);

        $otherBusiness = Business::factory()->create();
        $otherSupplier = Supplier::factory()->create([
            'business_id' => $otherBusiness->id,
        ]);
        $otherProduct = Product::factory()->create([
            'business_id' => $otherBusiness->id,
        ]);
        $otherPurchase = Purchase::factory()->create([
            'business_id' => $otherBusiness->id,
            'supplier_id' => $otherSupplier->id,
            'status' => 'draft',
        ]);

        $response = $this->post(route('purchases.items.store', $otherPurchase), [
            'product_id' => $otherProduct->id,
            'quantity' => 10,
            'unit_price' => 50000,
        ]);

        $response->assertStatus(403);
    }

    // =========================================================================
    // COMPLETE PURCHASE TESTS
    // =========================================================================

    public function test_admin_can_complete_draft_purchase()
    {
        $this->actingAs($this->admin);

        $purchase = Purchase::factory()->create([
            'business_id' => $this->business->id,
            'supplier_id' => $this->supplier->id,
            'status' => 'draft',
        ]);

        PurchaseItem::factory()->create([
            'purchase_id' => $purchase->id,
            'product_id' => $this->product->id,
            'quantity' => 10,
            'unit_price' => 50000,
            'subtotal' => 500000,
        ]);

        $purchase->update(['total_amount' => 500000]);

        $initialStock = $this->product->stock;

        $response = $this->post(route('purchases.complete', $purchase));

        $response->assertRedirect(route('purchases.index'));
        $purchase->refresh();
        $this->assertEquals('completed', $purchase->status);

        $this->product->refresh();
        $this->assertEquals($initialStock + 10, $this->product->stock);
    }

    public function test_completing_purchase_creates_stock_movements()
    {
        $this->actingAs($this->admin);

        $purchase = Purchase::factory()->create([
            'business_id' => $this->business->id,
            'supplier_id' => $this->supplier->id,
            'status' => 'draft',
        ]);

        PurchaseItem::factory()->create([
            'purchase_id' => $purchase->id,
            'product_id' => $this->product->id,
            'quantity' => 10,
            'unit_price' => 50000,
            'subtotal' => 500000,
        ]);

        $purchase->update(['total_amount' => 500000]);

        $initialMovementCount = StockMovement::count();

        $this->post(route('purchases.complete', $purchase));

        $this->assertEquals($initialMovementCount + 1, StockMovement::count());

        $movement = StockMovement::latest()->first();
        $this->assertEquals('in', $movement->type);
        $this->assertEquals('purchase', $movement->reference_type);
        $this->assertEquals($purchase->id, $movement->reference_id);
        $this->assertEquals($this->product->id, $movement->product_id);
        $this->assertEquals(10, $movement->quantity);
    }

    public function test_completing_purchase_twice_does_not_duplicate_stock()
    {
        $this->actingAs($this->admin);

        $purchase = Purchase::factory()->create([
            'business_id' => $this->business->id,
            'supplier_id' => $this->supplier->id,
            'status' => 'draft',
        ]);

        PurchaseItem::factory()->create([
            'purchase_id' => $purchase->id,
            'product_id' => $this->product->id,
            'quantity' => 10,
            'unit_price' => 50000,
            'subtotal' => 500000,
        ]);

        $purchase->update(['total_amount' => 500000]);

        $initialStock = $this->product->stock;

        $response1 = $this->post(route('purchases.complete', $purchase));
        $response1->assertRedirect();
        $response1->assertSessionHas('success');

        $this->product->refresh();
        $stockAfterFirstComplete = $this->product->stock;

        // Try to complete again - should fail with error message
        $response2 = $this->post(route('purchases.complete', $purchase));
        $response2->assertRedirect();
        $response2->assertSessionHas('error', 'Only draft purchases can be completed.');

        $this->product->refresh();
        $this->assertEquals($stockAfterFirstComplete, $this->product->stock);

        // Verify no duplicate stock movements
        $this->assertEquals(1, StockMovement::where('reference_type', 'purchase')
            ->where('reference_id', $purchase->id)
            ->count());
    }

    public function test_cannot_complete_purchase_without_items()
    {
        $this->actingAs($this->admin);

        $purchase = Purchase::factory()->create([
            'business_id' => $this->business->id,
            'supplier_id' => $this->supplier->id,
            'status' => 'draft',
        ]);

        $response = $this->post(route('purchases.complete', $purchase));

        $response->assertSessionHasErrors('completion');
        $purchase->refresh();
        $this->assertEquals('draft', $purchase->status);
    }

    public function test_completing_purchase_is_atomic_on_failure()
    {
        // This test verifies that if an error occurs during completion,
        // no stock changes are persisted - and proves a real mutation occurred before rollback
        $this->actingAs($this->admin);

        $product2 = Product::factory()->create([
            'business_id' => $this->business->id,
            'stock' => 50,
        ]);

        $purchase = Purchase::factory()->create([
            'business_id' => $this->business->id,
            'supplier_id' => $this->supplier->id,
            'status' => 'draft',
        ]);

        PurchaseItem::factory()->create([
            'purchase_id' => $purchase->id,
            'product_id' => $this->product->id,
            'quantity' => 10,
            'unit_price' => 50000,
            'subtotal' => 500000,
        ]);

        PurchaseItem::factory()->create([
            'purchase_id' => $purchase->id,
            'product_id' => $product2->id,
            'quantity' => 5,
            'unit_price' => 30000,
            'subtotal' => 150000,
        ]);

        $purchase->update(['total_amount' => 650000]);

        $initialStock1 = $this->product->stock;
        $initialStock2 = $product2->stock;
        $initialMovementCount = StockMovement::count();

        $product1 = $this->product;

        // Real StockService for the first call, mock throws on second
        $realStockService = new StockService(
            new Product,
            new StockMovement
        );

        $mockStockService = \Mockery::mock(StockService::class)
            ->makePartial();
        $callCount = 0;
        $mutationEvidence = [];

        $mockStockService->shouldReceive('performStockIn')
            ->times(2)
            ->andReturnUsing(function ($productId, $quantity, $businessId, $userId, $reason, $note, $referenceType, $referenceId) use (&$callCount, &$mutationEvidence, $realStockService, $initialStock1, $initialMovementCount, $product2, $product1) {
                $callCount++;

                if ($callCount === 1) {
                    // First call: delegate to REAL StockService to prove real mutation
                    $result = $realStockService->performStockIn(
                        $productId, $quantity, $businessId, $userId, $reason, $note, $referenceType, $referenceId
                    );

                    // CAPTURE EVIDENCE: inside the transaction, first product stock increased + movement created
                    $product1->refresh();
                    $product2->refresh();
                    $mutationEvidence['stock1_after_first'] = $product1->stock;
                    $mutationEvidence['movement_count_after_first'] = StockMovement::count();

                    // Assert mutation actually happened before failure
                    $this->assertEquals($initialStock1 + 10, $mutationEvidence['stock1_after_first'],
                        'First product stock must increase by 10 inside transaction before failure');
                    $this->assertEquals($initialMovementCount + 1, $mutationEvidence['movement_count_after_first'],
                        'Stock movement count must increase by 1 inside transaction before failure');

                    return $result;
                }

                if ($callCount === 2) {
                    // Second call: simulate failure AFTER first mutation persisted in DB
                    throw new \InvalidArgumentException('Simulated failure on second item');
                }
            });

        $purchaseServiceWithMock = new PurchaseService(
            new Purchase,
            new PurchaseItem,
            new Product,
            new StockMovement,
            new Supplier,
            $mockStockService
        );

        $this->app->instance(PurchaseService::class, $purchaseServiceWithMock);

        $response = $this->post(route('purchases.complete', $purchase));

        $this->product->refresh();
        $product2->refresh();
        $purchase->refresh();

        // Verify rollback: stock unchanged
        $this->assertEquals($initialStock1, $this->product->stock);
        $this->assertEquals($initialStock2, $product2->stock);

        // Verify no movements created
        $this->assertEquals($initialMovementCount, StockMovement::count());

        // Verify purchase status remains draft
        $this->assertEquals('draft', $purchase->status);

        // Verify exception is handled (redirect with validation error)
        $response->assertRedirect();
        $response->assertSessionHasErrors('completion');

        \Mockery::close();
    }

    public function test_completing_purchase_updates_multiple_products()
    {
        $this->actingAs($this->admin);

        $product2 = Product::factory()->create([
            'business_id' => $this->business->id,
            'stock' => 50,
        ]);

        $purchase = Purchase::factory()->create([
            'business_id' => $this->business->id,
            'supplier_id' => $this->supplier->id,
            'status' => 'draft',
        ]);

        PurchaseItem::factory()->create([
            'purchase_id' => $purchase->id,
            'product_id' => $this->product->id,
            'quantity' => 10,
            'unit_price' => 50000,
            'subtotal' => 500000,
        ]);

        PurchaseItem::factory()->create([
            'purchase_id' => $purchase->id,
            'product_id' => $product2->id,
            'quantity' => 5,
            'unit_price' => 30000,
            'subtotal' => 150000,
        ]);

        $purchase->update(['total_amount' => 650000]);

        $stock1Before = $this->product->stock;
        $stock2Before = $product2->stock;

        $this->post(route('purchases.complete', $purchase));

        $this->product->refresh();
        $product2->refresh();

        $this->assertEquals($stock1Before + 10, $this->product->stock);
        $this->assertEquals($stock2Before + 5, $product2->stock);

        $this->assertEquals(2, StockMovement::where('reference_type', 'purchase')
            ->where('reference_id', $purchase->id)->count());
    }

    // =========================================================================
    // BUSINESS ISOLATION TESTS
    // =========================================================================

    public function test_business_a_cannot_view_business_b_purchases()
    {
        $this->actingAs($this->admin);

        $otherBusiness = Business::factory()->create();
        $otherSupplier = Supplier::factory()->create([
            'business_id' => $otherBusiness->id,
        ]);
        $otherPurchase = Purchase::factory()->create([
            'business_id' => $otherBusiness->id,
            'supplier_id' => $otherSupplier->id,
        ]);

        $response = $this->get(route('purchases.show', $otherPurchase));
        $response->assertStatus(403);
    }

    public function test_business_a_cannot_complete_business_b_purchase()
    {
        $this->actingAs($this->admin);

        $otherBusiness = Business::factory()->create();
        $otherSupplier = Supplier::factory()->create([
            'business_id' => $otherBusiness->id,
        ]);
        $otherPurchase = Purchase::factory()->create([
            'business_id' => $otherBusiness->id,
            'supplier_id' => $otherSupplier->id,
            'status' => 'draft',
        ]);

        PurchaseItem::factory()->create([
            'purchase_id' => $otherPurchase->id,
            'product_id' => Product::factory()->create(['business_id' => $otherBusiness->id])->id,
            'quantity' => 10,
            'unit_price' => 50000,
            'subtotal' => 500000,
        ]);

        $otherPurchase->update(['total_amount' => 500000]);

        $response = $this->post(route('purchases.complete', $otherPurchase));
        $response->assertStatus(403);
    }

    public function test_business_a_cannot_add_items_to_business_b_purchase()
    {
        $this->actingAs($this->admin);

        $otherBusiness = Business::factory()->create();
        $otherSupplier = Supplier::factory()->create([
            'business_id' => $otherBusiness->id,
        ]);
        $otherProduct = Product::factory()->create([
            'business_id' => $otherBusiness->id,
        ]);
        $otherPurchase = Purchase::factory()->create([
            'business_id' => $otherBusiness->id,
            'supplier_id' => $otherSupplier->id,
            'status' => 'draft',
        ]);

        $response = $this->post(route('purchases.items.store', $otherPurchase), [
            'product_id' => $otherProduct->id,
            'quantity' => 10,
            'unit_price' => 50000,
        ]);

        $response->assertStatus(403);
    }

    public function test_purchase_list_only_shows_own_business()
    {
        $this->actingAs($this->admin);

        $otherBusiness = Business::factory()->create();
        $otherSupplier = Supplier::factory()->create([
            'business_id' => $otherBusiness->id,
        ]);
        Purchase::factory()->create([
            'business_id' => $otherBusiness->id,
            'supplier_id' => $otherSupplier->id,
            'invoice_number' => 'OTHER-PO-001',
        ]);

        $ownPurchase = Purchase::factory()->create([
            'business_id' => $this->business->id,
            'supplier_id' => $this->supplier->id,
            'invoice_number' => 'OWN-PO-001',
        ]);

        $response = $this->get(route('purchases.index'));
        $response->assertSee('OWN-PO-001');
        $response->assertDontSee('OTHER-PO-001');
    }

    // =========================================================================
    // VALIDATION TESTS
    // =========================================================================

    public function test_purchase_requires_supplier()
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('purchases.store'), [
            'invoice_number' => 'PO-001',
            'purchase_date' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('supplier_id');
    }

    public function test_purchase_requires_invoice_number()
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('purchases.store'), [
            'supplier_id' => $this->supplier->id,
            'purchase_date' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('invoice_number');
    }

    public function test_purchase_requires_purchase_date()
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('purchases.store'), [
            'supplier_id' => $this->supplier->id,
            'invoice_number' => 'PO-001',
        ]);

        $response->assertSessionHasErrors('purchase_date');
    }

    public function test_purchase_item_requires_positive_quantity()
    {
        $this->actingAs($this->admin);

        $purchase = Purchase::factory()->create([
            'business_id' => $this->business->id,
            'supplier_id' => $this->supplier->id,
            'status' => 'draft',
        ]);

        $response = $this->post(route('purchases.items.store', $purchase), [
            'product_id' => $this->product->id,
            'quantity' => 0,
            'unit_price' => 50000,
        ]);

        $response->assertSessionHasErrors('quantity');
    }

    public function test_purchase_item_requires_non_negative_price()
    {
        $this->actingAs($this->admin);

        $purchase = Purchase::factory()->create([
            'business_id' => $this->business->id,
            'supplier_id' => $this->supplier->id,
            'status' => 'draft',
        ]);

        $response = $this->post(route('purchases.items.store', $purchase), [
            'product_id' => $this->product->id,
            'quantity' => 10,
            'unit_price' => -100,
        ]);

        $response->assertSessionHasErrors('unit_price');
    }

    // =========================================================================
    // EDIT PURCHASE TESTS
    // =========================================================================

    public function test_admin_can_edit_draft_purchase()
    {
        $this->actingAs($this->admin);

        $purchase = Purchase::factory()->create([
            'business_id' => $this->business->id,
            'supplier_id' => $this->supplier->id,
            'status' => 'draft',
            'invoice_number' => 'PO-001',
        ]);

        $response = $this->put(route('purchases.update', $purchase), [
            'supplier_id' => $this->supplier->id,
            'invoice_number' => 'PO-002',
            'purchase_date' => now()->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('purchases.index'));
        $purchase->refresh();
        $this->assertEquals('PO-002', $purchase->invoice_number);
    }

    public function test_cannot_edit_completed_purchase()
    {
        $this->actingAs($this->admin);

        $purchase = Purchase::factory()->create([
            'business_id' => $this->business->id,
            'supplier_id' => $this->supplier->id,
            'status' => 'completed',
        ]);

        $response = $this->put(route('purchases.update', $purchase), [
            'supplier_id' => $this->supplier->id,
            'invoice_number' => 'PO-002',
            'purchase_date' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHas('error', 'Only draft purchases can be edited.');
    }
}

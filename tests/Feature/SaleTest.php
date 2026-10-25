<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Category;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\Unit;
use App\Models\User;
use App\Services\DiscountService;
use App\Services\SaleService;
use App\Services\StockReservationService;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SaleTest extends TestCase
{
    use RefreshDatabase;

    protected Business $business;

    protected User $admin;

    protected User $kasir;

    protected Category $category;

    protected Unit $unit;

    protected Product $product;

    protected Product $product2;

    protected SaleService $saleService;

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
        $kasirRole->givePermissionTo([
            'products view',
            'sales view-all',
            'sales view-own',
            'sales create',
            'reports view',
            'stock view',
        ]);

        $this->business = Business::factory()->create([
            'invoice_prefix' => 'INV',
            'invoice_sequence' => 1,
            'tax_percentage' => 10,
        ]);

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

        $this->category = Category::factory()->create([
            'business_id' => $this->business->id,
        ]);

        $this->unit = Unit::factory()->create([
            'business_id' => $this->business->id,
        ]);

        $this->product = Product::factory()->create([
            'business_id' => $this->business->id,
            'category_id' => $this->category->id,
            'unit_id' => $this->unit->id,
            'selling_price' => 50000,
            'stock' => 100,
        ]);

        $this->product2 = Product::factory()->create([
            'business_id' => $this->business->id,
            'category_id' => $this->category->id,
            'unit_id' => $this->unit->id,
            'selling_price' => 30000,
            'stock' => 50,
        ]);

        $this->saleService = new SaleService(
            new Sale,
            new SaleItem,
            new Product,
            new Payment,
            new StockService(
                new Product,
                new StockMovement
            ),
            new StockReservationService,
            new DiscountService
        );
    }

    // =========================================================================
    // SUCCESSFUL CHECKOUT TESTS
    // =========================================================================

    public function test_kasir_can_access_pos_page()
    {
        $this->actingAs($this->kasir);

        $response = $this->get(route('pos.index'));

        $response->assertStatus(200);
        $response->assertSee('POS');
    }

    public function test_admin_can_access_pos_page()
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('pos.index'));

        $response->assertStatus(200);
    }

    public function test_kasir_can_search_products()
    {
        $this->actingAs($this->kasir);

        $response = $this->get(route('pos.search-products', ['q' => $this->product->name]));

        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => $this->product->name]);
    }

    public function test_successful_cash_checkout_single_item()
    {
        $this->actingAs($this->kasir);

        $initialStock = $this->product->stock;
        $initialMovementCount = StockMovement::count();
        $quantity = 5;
        $receivedCash = 300000; // 5 * 50000 = 250000 + 10% tax = 275000

        $response = $this->postJson(route('pos.checkout'), [
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => $quantity],
            ],
            'received_cash' => $receivedCash,
            'notes' => 'Test sale',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'sale' => ['id', 'invoice_number', 'total_amount'],
                'change',
                'payment' => ['id', 'amount', 'status', 'paid_at', 'metadata'],
            ]);

        $this->assertTrue($response->json('success'));
        $this->assertEquals(25000, $response->json('change')); // 300000 - 275000

        $this->product->refresh();
        $this->assertEquals($initialStock - 5, $this->product->stock);

        $this->assertEquals($initialMovementCount + 1, StockMovement::count());
        $movement = StockMovement::latest()->first();
        $this->assertEquals('out', $movement->type);
        $this->assertEquals('sale', $movement->reference_type);
        $this->assertEquals(5, $movement->quantity);
        $this->assertEquals($initialStock, $movement->stock_before);
        $this->assertEquals($initialStock - 5, $movement->stock_after);

        $sale = Sale::latest()->first();
        $this->assertEquals('completed', $sale->status);
        $this->assertEquals(250000, $sale->subtotal);
        $this->assertEquals(25000, $sale->tax_amount);
        $this->assertEquals(275000, $sale->total_amount);

        $this->assertEquals(1, $sale->items->count());
        $item = $sale->items->first();
        $this->assertEquals(5, $item->quantity);
        $this->assertEquals(50000, $item->unit_price);
        $this->assertEquals(250000, $item->subtotal);

        $payment = $sale->payments->first();
        $this->assertEquals('cash', $payment->method);
        $this->assertEquals('cash', $payment->provider);
        $this->assertEquals('paid', $payment->status);
        $this->assertEquals(275000, $payment->amount);
        $this->assertEquals(300000, $payment->metadata['received_cash']);
        $this->assertEquals(25000, $payment->metadata['change']);
    }

    public function test_successful_cash_checkout_multiple_items()
    {
        $this->actingAs($this->kasir);

        $initialStock1 = $this->product->stock;
        $initialStock2 = $this->product2->stock;

        $response = $this->postJson(route('pos.checkout'), [
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 3],
                ['product_id' => $this->product2->id, 'quantity' => 2],
            ],
            'received_cash' => 300000,
            'notes' => 'Multi-item sale',
        ]);

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));

        // subtotal = 3*50000 + 2*30000 = 210000
        // tax 10% = 21000
        // total = 231000
        $this->assertEquals(69000, $response->json('change')); // 300000 - 231000

        $this->product->refresh();
        $this->product2->refresh();
        $this->assertEquals($initialStock1 - 3, $this->product->stock);
        $this->assertEquals($initialStock2 - 2, $this->product2->stock);

        $this->assertEquals(2, StockMovement::where('reference_type', 'sale')->count());

        $sale = Sale::latest()->first();
        $this->assertEquals(2, $sale->items->count());
        $this->assertEquals(210000, $sale->subtotal);
        $this->assertEquals(21000, $sale->tax_amount);
        $this->assertEquals(231000, $sale->total_amount);
    }

    public function test_checkout_returns_correct_change_and_payment_metadata()
    {
        $this->actingAs($this->kasir);

        // Test with received_cash = 100000, total = 55000 (50000 + 10% tax)
        $response = $this->postJson(route('pos.checkout'), [
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 1],
            ],
            'received_cash' => 100000,
        ]);

        $response->assertStatus(200);
        $paymentMetadata = $response->json('payment.metadata');
        $this->assertEquals(100000, $paymentMetadata['received_cash']); // What customer gave
        $this->assertEquals(45000, $paymentMetadata['change']); // 100000 - 55000
        $this->assertEquals(55000, $response->json('payment.amount')); // Total amount due
    }

    // =========================================================================
    // INSUFFICIENT CASH TESTS
    // =========================================================================

    public function test_insufficient_cash_rejected()
    {
        $this->actingAs($this->kasir);

        $response = $this->postJson(route('pos.checkout'), [
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 1],
            ],
            'received_cash' => 10000, // Less than 55000
        ]);

        $response->assertStatus(400);
        $this->assertArrayHasKey('error', $response->json());

        // No sale should be created
        $this->assertEquals(0, Sale::count());
        $this->assertEquals(0, SaleItem::count());
        $this->assertEquals(0, Payment::count());
        $this->assertEquals(0, StockMovement::count());

        $this->product->refresh();
        $this->assertEquals(100, $this->product->stock);
    }

    // =========================================================================
    // INSUFFICIENT STOCK TESTS
    // =========================================================================

    public function test_insufficient_stock_rejected()
    {
        $this->actingAs($this->kasir);

        $response = $this->postJson(route('pos.checkout'), [
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 200], // More than stock
            ],
            'received_cash' => 10000000,
        ]);

        $response->assertStatus(400);
        $this->assertArrayHasKey('error', $response->json());

        // No changes persisted
        $this->assertEquals(0, Sale::count());
        $this->product->refresh();
        $this->assertEquals(100, $this->product->stock);
    }

    // =========================================================================
    // RESERVED STOCK PROTECTION
    // =========================================================================

    public function test_cannot_sell_stock_reserved_for_pending_midtrans()
    {
        $this->actingAs($this->kasir);

        // Create a reservation for a pending Midtrans sale
        $reservationService = new StockReservationService;
        $pendingSale = Sale::factory()->create([
            'business_id' => $this->business->id,
            'user_id' => $this->admin->id,
            'status' => 'pending',
        ]);

        $reservationService->create(
            $this->business->id,
            $pendingSale->id,
            $this->product->id,
            80 // Reserve 80 out of 100
        );

        // Available stock should be 20 (100 - 80)
        $available = (new StockReservationService)->availableStock($this->business->id, $this->product->id);
        $this->assertEquals(20, $available);

        // Try to sell 30 - should fail because only 20 available
        $response = $this->postJson(route('pos.checkout'), [
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 30],
            ],
            'received_cash' => 1000000,
        ]);

        $response->assertStatus(400);
        $this->assertArrayHasKey('error', $response->json());

        // No sale created
        $this->assertEquals(0, Sale::where('business_id', $this->business->id)->where('status', 'completed')->count());
        $this->product->refresh();
        $this->assertEquals(100, $this->product->stock); // Stock unchanged
    }

    public function test_can_sell_when_reservation_released()
    {
        $this->actingAs($this->kasir);

        // Create and release a reservation
        $reservationService = new StockReservationService;
        $pendingSale = Sale::factory()->create([
            'business_id' => $this->business->id,
            'user_id' => $this->admin->id,
            'status' => 'pending',
        ]);

        $reservation = (new StockReservationService)->create(
            $this->business->id,
            $pendingSale->id,
            $this->product->id,
            80
        );

        $reservationService->release($reservation->id, $this->business->id);

        // Available stock should be back to 100
        $available = (new StockReservationService)->availableStock($this->business->id, $this->product->id);
        $this->assertEquals(100, $available);

        // Now checkout should succeed
        $response = $this->postJson(route('pos.checkout'), [
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 30],
            ],
            'received_cash' => 2000000,
        ]);

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));

        $this->product->refresh();
        $this->assertEquals(70, $this->product->stock); // 100 - 30
    }

    // =========================================================================
    // INVOICE UNIQUENESS
    // =========================================================================

    public function test_invoice_number_uniqueness_enforced()
    {
        $this->actingAs($this->kasir);

        // First sale
        $response1 = $this->postJson(route('pos.checkout'), [
            'items' => [['product_id' => $this->product->id, 'quantity' => 1]],
            'received_cash' => 100000,
        ]);
        $response1->assertStatus(200);
        $invoice1 = $response1->json('sale.invoice_number');

        // Second sale
        $response2 = $this->postJson(route('pos.checkout'), [
            'items' => [['product_id' => $this->product->id, 'quantity' => 1]],
            'received_cash' => 100000,
        ]);
        $response2->assertStatus(200);
        $invoice2 = $response2->json('sale.invoice_number');

        $this->assertNotEquals($invoice1, $invoice2);
        $this->assertEquals(2, Sale::count());
    }

    public function test_invoice_number_can_repeat_across_different_businesses()
    {
        // First business: first sale -> INV-1
        $this->actingAs($this->kasir);

        $response1 = $this->postJson(route('pos.checkout'), [
            'items' => [['product_id' => $this->product->id, 'quantity' => 1]],
            'received_cash' => 100000,
        ]);
        $response1->assertStatus(200);
        $this->assertEquals('INV-1', $response1->json('sale.invoice_number'));

        // Second business with the same prefix/sequence -> also INV-1 (must not collide)
        $otherBusiness = Business::factory()->create([
            'invoice_prefix' => 'INV',
            'invoice_sequence' => 1,
            'tax_percentage' => 0,
        ]);

        $otherKasir = User::factory()->create([
            'business_id' => $otherBusiness->id,
            'status' => 'active',
        ]);
        $otherKasir->assignRole('kasir');

        $otherProduct = Product::factory()->create([
            'business_id' => $otherBusiness->id,
            'selling_price' => 10000,
            'stock' => 10,
        ]);

        $this->actingAs($otherKasir);

        $response2 = $this->postJson(route('pos.checkout'), [
            'items' => [['product_id' => $otherProduct->id, 'quantity' => 1]],
            'received_cash' => 100000,
        ]);

        $response2->assertStatus(200);
        $this->assertEquals('INV-1', $response2->json('sale.invoice_number'));

        $this->assertEquals(2, Sale::count());
        $this->assertEquals('INV-1', Sale::where('business_id', $this->business->id)->first()->invoice_number);
        $this->assertEquals('INV-1', Sale::where('business_id', $otherBusiness->id)->first()->invoice_number);
    }

    // =========================================================================
    // MIDTRANS WEBHOOK RESOLUTION
    // =========================================================================

    public function test_midtrans_webhook_resolves_only_the_matching_business_order()
    {
        config(['midtrans.server_key' => 'test-server-key']);

        // Two pending sales sharing the same per-business invoice number.
        $saleA = Sale::factory()->create([
            'business_id' => $this->business->id,
            'user_id' => $this->kasir->id,
            'invoice_number' => 'INV-1',
            'status' => 'pending',
        ]);
        $paymentA = Payment::create([
            'business_id' => $this->business->id,
            'sale_id' => $saleA->id,
            'method' => 'midtrans',
            'provider' => 'midtrans',
            'amount' => 100,
            'status' => 'pending',
            'metadata' => ['snap_order_id' => $saleA->midtransOrderId()],
        ]);

        $otherBusiness = Business::factory()->create(['invoice_prefix' => 'INV', 'invoice_sequence' => 1]);
        $otherUser = User::factory()->create(['business_id' => $otherBusiness->id]);
        $saleB = Sale::factory()->create([
            'business_id' => $otherBusiness->id,
            'user_id' => $otherUser->id,
            'invoice_number' => 'INV-1',
            'status' => 'pending',
        ]);
        $paymentB = Payment::create([
            'business_id' => $otherBusiness->id,
            'sale_id' => $saleB->id,
            'method' => 'midtrans',
            'provider' => 'midtrans',
            'amount' => 100,
            'status' => 'pending',
            'metadata' => ['snap_order_id' => $saleB->midtransOrderId()],
        ]);

        $this->assertNotEquals($saleA->midtransOrderId(), $saleB->midtransOrderId());

        $orderId = $saleB->midtransOrderId();
        $payload = [
            'order_id' => $orderId,
            'status_code' => '200',
            'gross_amount' => '100.00',
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
        ];
        $payload['signature_key'] = hash('sha512', $orderId.'200'.'100.00'.'test-server-key');

        $response = $this->postJson(route('midtrans.webhook'), $payload);
        $response->assertStatus(200);

        $this->assertEquals('paid', $paymentB->fresh()->status);
        $this->assertEquals('pending', $paymentA->fresh()->status);
    }

    // =========================================================================
    // BUSINESS ISOLATION
    // =========================================================================

    public function test_business_a_cannot_sell_business_b_products()
    {
        $this->actingAs($this->kasir);

        $otherBusiness = Business::factory()->create();
        $otherProduct = Product::factory()->create([
            'business_id' => $otherBusiness->id,
            'stock' => 100,
        ]);

        $response = $this->postJson(route('pos.checkout'), [
            'items' => [
                ['product_id' => $otherProduct->id, 'quantity' => 1],
            ],
            'received_cash' => 100000,
        ]);

        $response->assertStatus(400);
        $this->assertArrayHasKey('error', $response->json());
    }

    public function test_sales_list_only_shows_own_business()
    {
        $this->actingAs($this->kasir);

        $otherBusiness = Business::factory()->create();
        $otherProduct = Product::factory()->create([
            'business_id' => $otherBusiness->id,
        ]);
        Sale::factory()->create([
            'business_id' => $otherBusiness->id,
            'user_id' => User::factory()->create(['business_id' => $otherBusiness->id])->id,
            'invoice_number' => 'OTHER-INV-001',
        ]);

        $ownSale = Sale::factory()->create([
            'business_id' => $this->business->id,
            'user_id' => $this->kasir->id,
            'invoice_number' => 'OWN-INV-001',
        ]);

        $response = $this->get(route('sales.index'));

        $response->assertStatus(200);
        $response->assertSee('OWN-INV-001');
        $response->assertDontSee('OTHER-INV-001');
    }

    public function test_business_a_cannot_view_business_b_sale()
    {
        $this->actingAs($this->kasir);

        $otherBusiness = Business::factory()->create();
        $otherUser = User::factory()->create(['business_id' => $otherBusiness->id]);
        $otherSale = Sale::factory()->create([
            'business_id' => $otherBusiness->id,
            'user_id' => $otherUser->id,
        ]);

        $response = $this->get(route('sales.show', $otherSale));

        $response->assertStatus(403);
    }

    // =========================================================================
    // ROLLBACK TEST
    // =========================================================================

    public function test_checkout_rollback_on_failure()
    {
        $this->actingAs($this->kasir);

        // Use a mock SaleService that fails after first item
        $mockStockService = \Mockery::mock(StockService::class)
            ->makePartial();
        $callCount = 0;
        $mockStockService->shouldReceive('performStockOut')
            ->times(2)
            ->andReturnUsing(function () use (&$callCount) {
                $callCount++;
                if ($callCount === 2) {
                    throw new \InvalidArgumentException('Simulated DB failure on second item');
                }

                return new StockMovement;
            });

        $mockReservationService = new StockReservationService;

        $saleServiceWithMock = new SaleService(
            new Sale,
            new SaleItem,
            new Product,
            new Payment,
            $mockStockService,
            $mockReservationService,
            new DiscountService
        );

        $this->app->instance(SaleService::class, $saleServiceWithMock);

        $this->actingAs($this->kasir);

        $product2 = Product::factory()->create([
            'business_id' => $this->business->id,
            'stock' => 50,
        ]);

        $initialStock1 = $this->product->stock;
        $initialStock2 = $product2->stock;
        $initialMovementCount = StockMovement::count();
        $initialSaleCount = Sale::count();
        $initialPaymentCount = Payment::count();

        $response = $this->postJson(route('pos.checkout'), [
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 10],
                ['product_id' => $product2->id, 'quantity' => 5],
            ],
            'received_cash' => 1000000,
        ]);

        $response->assertStatus(400);
        $this->assertArrayHasKey('error', $response->json());

        // Verify rollback
        $this->product->refresh();
        $product2->refresh();
        $this->assertEquals($initialStock1, $this->product->stock);
        $this->assertEquals($initialStock2, $product2->stock);

        $this->assertEquals($initialMovementCount, StockMovement::count());
        $this->assertEquals($initialSaleCount, Sale::count());
        $this->assertEquals($initialPaymentCount, Payment::count());

        \Mockery::close();
    }

    // =========================================================================
    // TAX CALCULATION
    // =========================================================================

    public function test_tax_calculation_uses_business_settings()
    {
        $this->business->tax_percentage = 5; // 5% tax
        $this->business->save();

        $this->actingAs($this->kasir);

        $response = $this->postJson(route('pos.checkout'), [
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 2], // 2 * 50000 = 100000
            ],
            'received_cash' => 200000,
        ]);

        $response->assertStatus(200);
        $sale = Sale::latest()->first();
        // subtotal = 100000, tax 5% = 5000, total = 105000
        $this->assertEquals(100000, $sale->subtotal);
        $this->assertEquals(5000, $sale->tax_amount);
        $this->assertEquals(105000, $sale->total_amount);
    }

    public function test_zero_tax_business()
    {
        $this->business->tax_percentage = 0;
        $this->business->save();

        $this->actingAs($this->kasir);

        $response = $this->postJson(route('pos.checkout'), [
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 1],
            ],
            'received_cash' => 100000,
        ]);

        $response->assertStatus(200);
        $sale = Sale::latest()->first();
        $this->assertEquals(50000, $sale->subtotal);
        $this->assertEquals(0, $sale->tax_amount);
        $this->assertEquals(50000, $sale->total_amount);
    }

    // =========================================================================
    // PRICE SNAPSHOT
    // =========================================================================

    public function test_sale_item_stores_price_snapshot()
    {
        $this->actingAs($this->kasir);

        $originalPrice = $this->product->selling_price;

        $response = $this->postJson(route('pos.checkout'), [
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 1],
            ],
            'received_cash' => 100000,
        ]);

        $response->assertStatus(200);

        $sale = Sale::latest()->first();
        $item = $sale->items->first();

        // SaleItem should store the price at time of sale
        $this->assertEquals($originalPrice, $item->unit_price);
        $this->assertEquals($originalPrice, $item->subtotal);

        // Change product price
        $this->product->selling_price = $originalPrice + 10000;
        $this->product->save();

        // Sale item should still have original price
        $item->refresh();
        $this->assertEquals($originalPrice, $item->unit_price);
    }
}

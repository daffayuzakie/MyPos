<?php

namespace Tests\Feature;

use App\Exceptions\InsufficientStockReservationException;
use App\Exceptions\InvalidStockReservationStateException;
use App\Models\Business;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockReservation;
use App\Models\User;
use App\Services\StockReservationService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StockReservationTest extends TestCase
{
    use RefreshDatabase;

    protected Business $business;

    protected User $admin;

    protected Product $product;

    protected StockReservationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $permissionNames = [
            'products view', 'products create', 'products edit', 'products delete',
            'sales view-all', 'sales view-own', 'sales create', 'sales cancel',
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

        $this->business = Business::factory()->create();
        $this->admin = User::factory()->create([
            'business_id' => $this->business->id,
            'status' => 'active',
        ]);
        $this->admin->assignRole('admin');

        $this->product = Product::factory()->create([
            'business_id' => $this->business->id,
            'stock' => 100,
        ]);

        $this->service = new StockReservationService;
    }

    public function test_create_reservation_with_sufficient_stock()
    {
        $this->actingAs($this->admin);

        $sale = Sale::factory()->create([
            'business_id' => $this->business->id,
            'user_id' => $this->admin->id,
        ]);

        $reservation = $this->service->create(
            $this->business->id,
            $sale->id,
            $this->product->id,
            10
        );

        $this->assertInstanceOf(StockReservation::class, $reservation);
        $this->assertEquals($this->business->id, $reservation->business_id);
        $this->assertEquals($sale->id, $reservation->sale_id);
        $this->assertEquals($this->product->id, $reservation->product_id);
        $this->assertEquals(10, $reservation->quantity);
        $this->assertEquals('active', $reservation->status);
        $this->assertNotNull($reservation->expires_at);
    }

    public function test_product_stock_is_not_reduced_when_reservation_created()
    {
        $this->actingAs($this->admin);

        $sale = Sale::factory()->create([
            'business_id' => $this->business->id,
            'user_id' => $this->admin->id,
        ]);

        $initialStock = $this->product->stock;

        $reservation = $this->service->create(
            $this->business->id,
            $sale->id,
            $this->product->id,
            10
        );

        $this->product->refresh();

        $this->assertEquals($initialStock, $this->product->stock);
    }

    public function test_available_stock_decreases_after_reservation()
    {
        $this->actingAs($this->admin);

        $sale = Sale::factory()->create([
            'business_id' => $this->business->id,
            'user_id' => $this->admin->id,
        ]);

        $this->service->create(
            $this->business->id,
            $sale->id,
            $this->product->id,
            20
        );

        $available = $this->service->availableStock(
            $this->business->id,
            $this->product->id
        );

        $this->assertEquals(80, $available);
    }

    public function test_insufficient_available_stock_is_rejected()
    {
        $this->actingAs($this->admin);

        $sale = Sale::factory()->create([
            'business_id' => $this->business->id,
            'user_id' => $this->admin->id,
        ]);

        $this->expectException(InsufficientStockReservationException::class);

        $this->service->create(
            $this->business->id,
            $sale->id,
            $this->product->id,
            999
        );
    }

    public function test_business_isolation_on_create()
    {
        $this->actingAs($this->admin);

        $otherBusiness = Business::factory()->create();
        $otherAdmin = User::factory()->create([
            'business_id' => $otherBusiness->id,
            'status' => 'active',
        ]);
        $otherAdmin->assignRole('admin');

        $otherProduct = Product::factory()->create([
            'business_id' => $otherBusiness->id,
            'stock' => 5,
        ]);

        $sale = Sale::factory()->create([
            'business_id' => $otherBusiness->id,
            'user_id' => $otherAdmin->id,
        ]);

        $this->expectException(InsufficientStockReservationException::class);

        $this->service->create(
            $otherBusiness->id,
            $sale->id,
            $otherProduct->id,
            10
        );
    }

    public function test_release_active_reservation()
    {
        $this->actingAs($this->admin);

        $sale = Sale::factory()->create([
            'business_id' => $this->business->id,
            'user_id' => $this->admin->id,
        ]);

        $reservation = $this->service->create(
            $this->business->id,
            $sale->id,
            $this->product->id,
            10
        );

        $released = $this->service->release($reservation->id, $this->business->id);

        $this->assertEquals('released', $released->status);
    }

    public function test_release_is_idempotent()
    {
        $this->actingAs($this->admin);

        $sale = Sale::factory()->create([
            'business_id' => $this->business->id,
            'user_id' => $this->admin->id,
        ]);

        $reservation = $this->service->create(
            $this->business->id,
            $sale->id,
            $this->product->id,
            10
        );

        $this->service->release($reservation->id, $this->business->id);

        $released = $this->service->release($reservation->id, $this->business->id);

        $this->assertEquals('released', $released->status);
    }

    public function test_cannot_release_converted_reservation()
    {
        $this->actingAs($this->admin);

        $sale = Sale::factory()->create([
            'business_id' => $this->business->id,
            'user_id' => $this->admin->id,
        ]);

        $reservation = $this->service->create(
            $this->business->id,
            $sale->id,
            $this->product->id,
            10
        );

        $this->service->convert($reservation->id, $this->business->id);

        $this->expectException(InvalidStockReservationStateException::class);

        $this->service->release($reservation->id, $this->business->id);
    }

    public function test_cannot_release_expired_reservation()
    {
        $this->actingAs($this->admin);

        $sale = Sale::factory()->create([
            'business_id' => $this->business->id,
            'user_id' => $this->admin->id,
        ]);

        $reservation = $this->service->create(
            $this->business->id,
            $sale->id,
            $this->product->id,
            10
        );

        $reservation->status = 'expired';
        $reservation->save();

        $this->expectException(InvalidStockReservationStateException::class);

        $this->service->release($reservation->id, $this->business->id);
    }

    public function test_convert_active_reservation()
    {
        $this->actingAs($this->admin);

        $sale = Sale::factory()->create([
            'business_id' => $this->business->id,
            'user_id' => $this->admin->id,
        ]);

        $reservation = $this->service->create(
            $this->business->id,
            $sale->id,
            $this->product->id,
            10
        );

        $converted = $this->service->convert($reservation->id, $this->business->id);

        $this->assertEquals('converted', $converted->status);
    }

    public function test_convert_is_idempotent()
    {
        $this->actingAs($this->admin);

        $sale = Sale::factory()->create([
            'business_id' => $this->business->id,
            'user_id' => $this->admin->id,
        ]);

        $reservation = $this->service->create(
            $this->business->id,
            $sale->id,
            $this->product->id,
            10
        );

        $this->service->convert($reservation->id, $this->business->id);

        $converted = $this->service->convert($reservation->id, $this->business->id);

        $this->assertEquals('converted', $converted->status);
    }

    public function test_cannot_convert_released_reservation()
    {
        $this->actingAs($this->admin);

        $sale = Sale::factory()->create([
            'business_id' => $this->business->id,
            'user_id' => $this->admin->id,
        ]);

        $reservation = $this->service->create(
            $this->business->id,
            $sale->id,
            $this->product->id,
            10
        );

        $this->service->release($reservation->id, $this->business->id);

        $this->expectException(InvalidStockReservationStateException::class);

        $this->service->convert($reservation->id, $this->business->id);
    }

    public function test_cannot_convert_expired_reservation()
    {
        $this->actingAs($this->admin);

        $sale = Sale::factory()->create([
            'business_id' => $this->business->id,
            'user_id' => $this->admin->id,
        ]);

        $reservation = $this->service->create(
            $this->business->id,
            $sale->id,
            $this->product->id,
            10
        );

        $reservation->status = 'expired';
        $reservation->save();

        $this->expectException(InvalidStockReservationStateException::class);

        $this->service->convert($reservation->id, $this->business->id);
    }

    public function test_expire_active_reservation()
    {
        $this->actingAs($this->admin);

        $sale = Sale::factory()->create([
            'business_id' => $this->business->id,
            'user_id' => $this->admin->id,
        ]);

        $reservation = $this->service->create(
            $this->business->id,
            $sale->id,
            $this->product->id,
            10
        );

        $expired = $this->service->expire($reservation->id, $this->business->id);

        $this->assertEquals('expired', $expired->status);
    }

    public function test_expire_is_idempotent()
    {
        $this->actingAs($this->admin);

        $sale = Sale::factory()->create([
            'business_id' => $this->business->id,
            'user_id' => $this->admin->id,
        ]);

        $reservation = $this->service->create(
            $this->business->id,
            $sale->id,
            $this->product->id,
            10
        );

        $this->service->expire($reservation->id, $this->business->id);

        $expired = $this->service->expire($reservation->id, $this->business->id);

        $this->assertEquals('expired', $expired->status);
    }

    public function test_cannot_expire_converted_reservation()
    {
        $this->actingAs($this->admin);

        $sale = Sale::factory()->create([
            'business_id' => $this->business->id,
            'user_id' => $this->admin->id,
        ]);

        $reservation = $this->service->create(
            $this->business->id,
            $sale->id,
            $this->product->id,
            10
        );

        $this->service->convert($reservation->id, $this->business->id);

        $this->expectException(InvalidStockReservationStateException::class);

        $this->service->expire($reservation->id, $this->business->id);
    }

    public function test_available_stock_when_no_reservations()
    {
        $this->actingAs($this->admin);

        $available = $this->service->availableStock(
            $this->business->id,
            $this->product->id
        );

        $this->assertEquals(100, $available);
    }

    // =========================================================================
    // CROSS-BUSINESS ISOLATION TESTS
    // =========================================================================

    public function test_business_a_cannot_create_reservation_for_business_b_product()
    {
        $this->actingAs($this->admin);

        $otherBusiness = Business::factory()->create();
        $otherProduct = Product::factory()->create([
            'business_id' => $otherBusiness->id,
            'stock' => 100,
        ]);

        $sale = Sale::factory()->create([
            'business_id' => $this->business->id,
            'user_id' => $this->admin->id,
        ]);

        $this->expectException(ModelNotFoundException::class);

        // Business A's sale trying to reserve Business B's product
        $this->service->create(
            $this->business->id,
            $sale->id,
            $otherProduct->id,
            10
        );
    }

    public function test_business_a_cannot_create_reservation_using_business_b_sale()
    {
        $this->actingAs($this->admin);

        $otherBusiness = Business::factory()->create();
        $otherAdmin = User::factory()->create([
            'business_id' => $otherBusiness->id,
            'status' => 'active',
        ]);
        $otherAdmin->assignRole('admin');

        $otherSale = Sale::factory()->create([
            'business_id' => $otherBusiness->id,
            'user_id' => $otherAdmin->id,
        ]);

        $this->expectException(ModelNotFoundException::class);

        // Business A trying to use Business B's sale ID
        $this->service->create(
            $this->business->id,
            $otherSale->id,
            $this->product->id,
            10
        );
    }

    public function test_business_a_cannot_release_business_b_reservation()
    {
        $this->actingAs($this->admin);

        $otherBusiness = Business::factory()->create();
        $otherAdmin = User::factory()->create([
            'business_id' => $otherBusiness->id,
            'status' => 'active',
        ]);
        $otherAdmin->assignRole('admin');

        $otherProduct = Product::factory()->create([
            'business_id' => $otherBusiness->id,
            'stock' => 100,
        ]);

        $otherSale = Sale::factory()->create([
            'business_id' => $otherBusiness->id,
            'user_id' => $otherAdmin->id,
        ]);

        $otherReservation = $this->service->create(
            $otherBusiness->id,
            $otherSale->id,
            $otherProduct->id,
            10
        );

        $this->expectException(ModelNotFoundException::class);

        // Business A trying to release Business B's reservation
        $this->service->release($otherReservation->id, $this->business->id);
    }

    public function test_business_a_cannot_convert_business_b_reservation()
    {
        $this->actingAs($this->admin);

        $otherBusiness = Business::factory()->create();
        $otherAdmin = User::factory()->create([
            'business_id' => $otherBusiness->id,
            'status' => 'active',
        ]);
        $otherAdmin->assignRole('admin');

        $otherProduct = Product::factory()->create([
            'business_id' => $otherBusiness->id,
            'stock' => 100,
        ]);

        $otherSale = Sale::factory()->create([
            'business_id' => $otherBusiness->id,
            'user_id' => $otherAdmin->id,
        ]);

        $otherReservation = $this->service->create(
            $otherBusiness->id,
            $otherSale->id,
            $otherProduct->id,
            10
        );

        $this->expectException(ModelNotFoundException::class);

        // Business A trying to convert Business B's reservation
        $this->service->convert($otherReservation->id, $this->business->id);
    }

    public function test_business_a_cannot_expire_business_b_reservation()
    {
        $this->actingAs($this->admin);

        $otherBusiness = Business::factory()->create();
        $otherAdmin = User::factory()->create([
            'business_id' => $otherBusiness->id,
            'status' => 'active',
        ]);
        $otherAdmin->assignRole('admin');

        $otherProduct = Product::factory()->create([
            'business_id' => $otherBusiness->id,
            'stock' => 100,
        ]);

        $otherSale = Sale::factory()->create([
            'business_id' => $otherBusiness->id,
            'user_id' => $otherAdmin->id,
        ]);

        $otherReservation = $this->service->create(
            $otherBusiness->id,
            $otherSale->id,
            $otherProduct->id,
            10
        );

        $this->expectException(ModelNotFoundException::class);

        // Business A trying to expire Business B's reservation
        $this->service->expire($otherReservation->id, $this->business->id);
    }

    public function test_available_stock_is_business_scoped()
    {
        $this->actingAs($this->admin);

        $otherBusiness = Business::factory()->create();
        $otherAdmin = User::factory()->create([
            'business_id' => $otherBusiness->id,
            'status' => 'active',
        ]);
        $otherAdmin->assignRole('admin');

        $otherProduct = Product::factory()->create([
            'business_id' => $otherBusiness->id,
            'stock' => 50,
        ]);

        $otherSale = Sale::factory()->create([
            'business_id' => $otherBusiness->id,
            'user_id' => $otherAdmin->id,
        ]);

        // Create reservation in other business
        $this->service->create(
            $otherBusiness->id,
            $otherSale->id,
            $otherProduct->id,
            30
        );

        // Business A's available stock should not be affected
        $available = $this->service->availableStock($this->business->id, $this->product->id);
        $this->assertEquals(100, $available);

        // Other business's available stock should be reduced
        $otherAvailable = $this->service->availableStock($otherBusiness->id, $otherProduct->id);
        $this->assertEquals(20, $otherAvailable);
    }

    // =========================================================================
    // AVAILABLE STOCK EDGE CASE TESTS
    // =========================================================================

    public function test_available_stock_excludes_expired_reservations()
    {
        $this->actingAs($this->admin);

        $sale = Sale::factory()->create([
            'business_id' => $this->business->id,
            'user_id' => $this->admin->id,
        ]);

        $reservation = $this->service->create(
            $this->business->id,
            $sale->id,
            $this->product->id,
            30
        );

        // Manually expire the reservation by setting status and past expires_at
        $reservation->status = 'expired';
        $reservation->expires_at = now()->subMinutes(10);
        $reservation->save();

        // Available stock should be full (expired reservation not counted)
        $available = $this->service->availableStock($this->business->id, $this->product->id);
        $this->assertEquals(100, $available);
    }

    public function test_available_stock_excludes_released_reservations()
    {
        $this->actingAs($this->admin);

        $sale = Sale::factory()->create([
            'business_id' => $this->business->id,
            'user_id' => $this->admin->id,
        ]);

        $reservation = $this->service->create(
            $this->business->id,
            $sale->id,
            $this->product->id,
            30
        );

        $this->service->release($reservation->id, $this->business->id);

        // Available stock should be full (released reservation not counted)
        $available = $this->service->availableStock($this->business->id, $this->product->id);
        $this->assertEquals(100, $available);
    }

    public function test_available_stock_excludes_converted_reservations()
    {
        $this->actingAs($this->admin);

        $sale = Sale::factory()->create([
            'business_id' => $this->business->id,
            'user_id' => $this->admin->id,
        ]);

        $reservation = $this->service->create(
            $this->business->id,
            $sale->id,
            $this->product->id,
            30
        );

        $this->service->convert($reservation->id, $this->business->id);

        // Available stock should be full (converted reservation not counted in available)
        $available = $this->service->availableStock($this->business->id, $this->product->id);
        $this->assertEquals(100, $available);
    }

    public function test_available_stock_with_multiple_active_reservations()
    {
        $this->actingAs($this->admin);

        $sale1 = Sale::factory()->create([
            'business_id' => $this->business->id,
            'user_id' => $this->admin->id,
        ]);

        $sale2 = Sale::factory()->create([
            'business_id' => $this->business->id,
            'user_id' => $this->admin->id,
        ]);

        $this->service->create($this->business->id, $sale1->id, $this->product->id, 20);
        $this->service->create($this->business->id, $sale2->id, $this->product->id, 30);

        $available = $this->service->availableStock($this->business->id, $this->product->id);
        $this->assertEquals(50, $available); // 100 - 20 - 30 = 50
    }

    public function test_available_stock_with_expired_reservation_by_timestamp()
    {
        $this->actingAs($this->admin);

        $sale = Sale::factory()->create([
            'business_id' => $this->business->id,
            'user_id' => $this->admin->id,
        ]);

        $reservation = $this->service->create(
            $this->business->id,
            $sale->id,
            $this->product->id,
            30
        );

        // Keep status as 'active' but set expires_at in the past
        $reservation->expires_at = now()->subMinutes(10);
        $reservation->save();

        // Available stock should be full (expired by timestamp not counted)
        $available = $this->service->availableStock($this->business->id, $this->product->id);
        $this->assertEquals(100, $available);
    }

    public function test_available_stock_reservations_equal_to_stock()
    {
        $this->actingAs($this->admin);

        $sale = Sale::factory()->create([
            'business_id' => $this->business->id,
            'user_id' => $this->admin->id,
        ]);

        $this->service->create($this->business->id, $sale->id, $this->product->id, 100);

        $available = $this->service->availableStock($this->business->id, $this->product->id);
        $this->assertEquals(0, $available);
    }

    public function test_available_stock_never_negative_with_inconsistent_data()
    {
        $this->actingAs($this->admin);

        // Create a reservation directly with quantity exceeding stock
        // Create a sale first, then create reservation
        $sale = Sale::create([
            'business_id' => $this->business->id,
            'user_id' => $this->admin->id,
            'invoice_number' => 'TEST-'.Str::random(8),
            'subtotal' => 0,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 0,
            'status' => 'pending',
            'transaction_date' => now(),
        ]);

        StockReservation::create([
            'business_id' => $this->business->id,
            'sale_id' => $sale->id,
            'product_id' => $this->product->id,
            'quantity' => 150,
            'status' => 'active',
            'expires_at' => now()->addMinutes(15),
        ]);

        $available = $this->service->availableStock($this->business->id, $this->product->id);
        $this->assertEquals(0, $available); // Should never return negative
    }

    // =========================================================================
    // STATE TRANSITION TESTS (additional comprehensive tests)
    // =========================================================================

    public function test_expire_active_reservation_with_valid_timestamp()
    {
        $this->actingAs($this->admin);

        $sale = Sale::factory()->create([
            'business_id' => $this->business->id,
            'user_id' => $this->admin->id,
        ]);

        $reservation = $this->service->create(
            $this->business->id,
            $sale->id,
            $this->product->id,
            10
        );

        // Expire via service (should set status to expired)
        $expired = $this->service->expire($reservation->id, $this->business->id);

        $this->assertEquals('expired', $expired->status);
        $this->assertEquals('expired', $expired->fresh()->status);
    }

    public function test_reservation_cannot_be_released_then_converted()
    {
        $this->actingAs($this->admin);

        $sale = Sale::factory()->create([
            'business_id' => $this->business->id,
            'user_id' => $this->admin->id,
        ]);

        $reservation = $this->service->create(
            $this->business->id,
            $sale->id,
            $this->product->id,
            10
        );

        $this->service->release($reservation->id, $this->business->id);

        $this->expectException(InvalidStockReservationStateException::class);

        $this->service->convert($reservation->id, $this->business->id);
    }

    public function test_reservation_cannot_be_converted_then_released()
    {
        $this->actingAs($this->admin);

        $sale = Sale::factory()->create([
            'business_id' => $this->business->id,
            'user_id' => $this->admin->id,
        ]);

        $reservation = $this->service->create(
            $this->business->id,
            $sale->id,
            $this->product->id,
            10
        );

        $this->service->convert($reservation->id, $this->business->id);

        $this->expectException(InvalidStockReservationStateException::class);

        $this->service->release($reservation->id, $this->business->id);
    }

    public function test_reservation_cannot_be_expired_then_converted()
    {
        $this->actingAs($this->admin);

        $sale = Sale::factory()->create([
            'business_id' => $this->business->id,
            'user_id' => $this->admin->id,
        ]);

        $reservation = $this->service->create(
            $this->business->id,
            $sale->id,
            $this->product->id,
            10
        );

        $this->service->expire($reservation->id, $this->business->id);

        $this->expectException(InvalidStockReservationStateException::class);

        $this->service->convert($reservation->id, $this->business->id);
    }

    public function test_reservation_cannot_be_expired_then_released()
    {
        $this->actingAs($this->admin);

        $sale = Sale::factory()->create([
            'business_id' => $this->business->id,
            'user_id' => $this->admin->id,
        ]);

        $reservation = $this->service->create(
            $this->business->id,
            $sale->id,
            $this->product->id,
            10
        );

        $this->service->expire($reservation->id, $this->business->id);

        $this->expectException(InvalidStockReservationStateException::class);

        $this->service->release($reservation->id, $this->business->id);
    }

    public function test_is_expired_returns_true_for_status_expired_regardless_of_timestamp()
    {
        $this->actingAs($this->admin);

        $sale = Sale::factory()->create([
            'business_id' => $this->business->id,
            'user_id' => $this->admin->id,
        ]);

        $reservation = $this->service->create(
            $this->business->id,
            $sale->id,
            $this->product->id,
            10
        );

        // Set status to expired but expires_at in future
        $reservation->status = 'expired';
        $reservation->expires_at = now()->addMinutes(15);
        $reservation->save();

        $this->assertTrue($reservation->fresh()->isExpired());
    }

    public function test_is_expired_returns_false_for_converted_even_with_past_timestamp()
    {
        $this->actingAs($this->admin);

        $sale = Sale::factory()->create([
            'business_id' => $this->business->id,
            'user_id' => $this->admin->id,
        ]);

        $reservation = $this->service->create(
            $this->business->id,
            $sale->id,
            $this->product->id,
            10
        );

        $this->service->convert($reservation->id, $this->business->id);

        // Even with past timestamp, converted should not be expired
        $reservation->expires_at = now()->subMinutes(10);
        $reservation->save();

        $this->assertFalse($reservation->fresh()->isExpired());
    }

    // =========================================================================
    // INVARIANT TESTS: Reservation operations do not mutate products.stock
    // =========================================================================

    public function test_create_reservation_does_not_create_stock_movement()
    {
        $this->actingAs($this->admin);

        $sale = Sale::factory()->create([
            'business_id' => $this->business->id,
            'user_id' => $this->admin->id,
        ]);

        $initialMovementCount = $this->product->stockMovements()->count();

        $this->service->create($this->business->id, $sale->id, $this->product->id, 10);

        $this->assertEquals($initialMovementCount, $this->product->stockMovements()->count());
    }

    public function test_release_reservation_does_not_create_stock_movement()
    {
        $this->actingAs($this->admin);

        $sale = Sale::factory()->create([
            'business_id' => $this->business->id,
            'user_id' => $this->admin->id,
        ]);

        $reservation = $this->service->create($this->business->id, $sale->id, $this->product->id, 10);

        $initialMovementCount = $this->product->stockMovements()->count();

        $this->service->release($reservation->id, $this->business->id);

        $this->assertEquals($initialMovementCount, $this->product->stockMovements()->count());
    }

    public function test_expire_reservation_does_not_create_stock_movement()
    {
        $this->actingAs($this->admin);

        $sale = Sale::factory()->create([
            'business_id' => $this->business->id,
            'user_id' => $this->admin->id,
        ]);

        $reservation = $this->service->create($this->business->id, $sale->id, $this->product->id, 10);

        $initialMovementCount = $this->product->stockMovements()->count();

        $this->service->expire($reservation->id, $this->business->id);

        $this->assertEquals($initialMovementCount, $this->product->stockMovements()->count());
    }

    public function test_convert_reservation_does_not_create_stock_movement()
    {
        $this->actingAs($this->admin);

        $sale = Sale::factory()->create([
            'business_id' => $this->business->id,
            'user_id' => $this->admin->id,
        ]);

        $reservation = $this->service->create($this->business->id, $sale->id, $this->product->id, 10);

        $initialMovementCount = $this->product->stockMovements()->count();

        $this->service->convert($reservation->id, $this->business->id);

        $this->assertEquals($initialMovementCount, $this->product->stockMovements()->count());
    }

    public function test_create_reservation_does_not_modify_products_stock()
    {
        $this->actingAs($this->admin);

        $sale = Sale::factory()->create([
            'business_id' => $this->business->id,
            'user_id' => $this->admin->id,
        ]);

        $initialStock = $this->product->stock;

        $this->service->create($this->business->id, $sale->id, $this->product->id, 25);

        $this->product->refresh();
        $this->assertEquals($initialStock, $this->product->stock);
    }

    public function test_release_reservation_does_not_modify_products_stock()
    {
        $this->actingAs($this->admin);

        $sale = Sale::factory()->create([
            'business_id' => $this->business->id,
            'user_id' => $this->admin->id,
        ]);

        $reservation = $this->service->create($this->business->id, $sale->id, $this->product->id, 25);

        $this->product->refresh();
        $stockAfterCreate = $this->product->stock;

        $this->service->release($reservation->id, $this->business->id);

        $this->product->refresh();
        $this->assertEquals($stockAfterCreate, $this->product->stock);
    }

    public function test_expire_reservation_does_not_modify_products_stock()
    {
        $this->actingAs($this->admin);

        $sale = Sale::factory()->create([
            'business_id' => $this->business->id,
            'user_id' => $this->admin->id,
        ]);

        $reservation = $this->service->create($this->business->id, $sale->id, $this->product->id, 25);

        $this->product->refresh();
        $stockAfterCreate = $this->product->stock;

        $this->service->expire($reservation->id, $this->business->id);

        $this->product->refresh();
        $this->assertEquals($stockAfterCreate, $this->product->stock);
    }

    public function test_convert_reservation_does_not_modify_products_stock()
    {
        $this->actingAs($this->admin);

        $sale = Sale::factory()->create([
            'business_id' => $this->business->id,
            'user_id' => $this->admin->id,
        ]);

        $reservation = $this->service->create($this->business->id, $sale->id, $this->product->id, 25);

        $this->product->refresh();
        $stockAfterCreate = $this->product->stock;

        $this->service->convert($reservation->id, $this->business->id);

        $this->product->refresh();
        $this->assertEquals($stockAfterCreate, $this->product->stock);
    }

    // =========================================================================
    // RELEASE BY SALE TESTS (pending sale cancellation)
    // =========================================================================

    public function test_release_by_sale_releases_all_active_reservations_for_sale()
    {
        $this->actingAs($this->admin);

        $sale = Sale::factory()->create([
            'business_id' => $this->business->id,
            'user_id' => $this->admin->id,
        ]);

        // Create multiple reservations for the same sale
        $reservation1 = $this->service->create($this->business->id, $sale->id, $this->product->id, 10);
        $reservation2 = $this->service->create($this->business->id, $sale->id, $this->product->id, 20);

        $releasedCount = $this->service->releaseBySale($sale->id, $this->business->id);

        $this->assertEquals(2, $releasedCount);
        $this->assertEquals('released', $reservation1->fresh()->status);
        $this->assertEquals('released', $reservation2->fresh()->status);
    }

    public function test_release_by_sale_is_idempotent()
    {
        $this->actingAs($this->admin);

        $sale = Sale::factory()->create([
            'business_id' => $this->business->id,
            'user_id' => $this->admin->id,
        ]);

        $reservation = $this->service->create($this->business->id, $sale->id, $this->product->id, 10);

        $count1 = $this->service->releaseBySale($sale->id, $this->business->id);
        $count2 = $this->service->releaseBySale($sale->id, $this->business->id);

        $this->assertEquals(1, $count1);
        $this->assertEquals(0, $count2); // Second call finds no active reservations
    }

    public function test_release_by_sale_respects_business_isolation()
    {
        $this->actingAs($this->admin);

        $otherBusiness = Business::factory()->create();
        $otherAdmin = User::factory()->create([
            'business_id' => $otherBusiness->id,
            'status' => 'active',
        ]);
        $otherAdmin->assignRole('admin');

        $otherProduct = Product::factory()->create([
            'business_id' => $otherBusiness->id,
            'stock' => 100,
        ]);

        $otherSale = Sale::factory()->create([
            'business_id' => $otherBusiness->id,
            'user_id' => $otherAdmin->id,
        ]);

        $otherReservation = $this->service->create($otherBusiness->id, $otherSale->id, $otherProduct->id, 10);

        // Business A trying to release by Business B's sale ID
        $releasedCount = $this->service->releaseBySale($otherSale->id, $this->business->id);

        $this->assertEquals(0, $releasedCount);
        $this->assertEquals('active', $otherReservation->fresh()->status);
    }

    public function test_release_by_sale_only_releases_active_reservations()
    {
        $this->actingAs($this->admin);

        $sale = Sale::factory()->create([
            'business_id' => $this->business->id,
            'user_id' => $this->admin->id,
        ]);

        $activeReservation = $this->service->create($this->business->id, $sale->id, $this->product->id, 10);
        $releasedReservation = $this->service->create($this->business->id, $sale->id, $this->product->id, 10);
        $this->service->release($releasedReservation->id, $this->business->id);

        $convertedReservation = $this->service->create($this->business->id, $sale->id, $this->product->id, 10);
        $this->service->convert($convertedReservation->id, $this->business->id);

        $releasedCount = $this->service->releaseBySale($sale->id, $this->business->id);

        $this->assertEquals(1, $releasedCount);
        $this->assertEquals('released', $activeReservation->fresh()->status);
        $this->assertEquals('released', $releasedReservation->fresh()->status); // Already released
        $this->assertEquals('converted', $convertedReservation->fresh()->status); // Not touched
    }

    // =========================================================================
    // EXPIRATION COMMAND TESTS
    // =========================================================================

    public function test_expire_command_expires_due_reservations()
    {
        $this->actingAs($this->admin);

        $sale = Sale::factory()->create([
            'business_id' => $this->business->id,
            'user_id' => $this->admin->id,
        ]);

        $reservation = $this->service->create(
            $this->business->id,
            $sale->id,
            $this->product->id,
            10
        );

        // Set expires_at to past
        $reservation->expires_at = now()->subMinutes(5);
        $reservation->save();

        $this->artisan('stock:reservations:expire', ['--limit' => 10]);

        $this->assertEquals('expired', $reservation->fresh()->status);
    }

    public function test_expire_command_is_idempotent()
    {
        $this->actingAs($this->admin);

        $sale = Sale::factory()->create([
            'business_id' => $this->business->id,
            'user_id' => $this->admin->id,
        ]);

        $reservation = $this->service->create(
            $this->business->id,
            $sale->id,
            $this->product->id,
            10
        );

        $reservation->expires_at = now()->subMinutes(5);
        $reservation->save();

        $this->artisan('stock:reservations:expire', ['--limit' => 10]);
        $this->artisan('stock:reservations:expire', ['--limit' => 10]);

        $this->assertEquals('expired', $reservation->fresh()->status);
    }

    public function test_expire_command_does_not_affect_non_expired_reservations()
    {
        $this->actingAs($this->admin);

        $sale = Sale::factory()->create([
            'business_id' => $this->business->id,
            'user_id' => $this->admin->id,
        ]);

        $reservation = $this->service->create(
            $this->business->id,
            $sale->id,
            $this->product->id,
            10
        );

        // expires_at is in future (default 15 minutes)
        $this->artisan('stock:reservations:expire', ['--limit' => 10]);

        $this->assertEquals('active', $reservation->fresh()->status);
    }

    public function test_expire_command_respects_business_isolation()
    {
        $this->actingAs($this->admin);

        $otherBusiness = Business::factory()->create();
        $otherAdmin = User::factory()->create([
            'business_id' => $otherBusiness->id,
            'status' => 'active',
        ]);
        $otherAdmin->assignRole('admin');

        $otherProduct = Product::factory()->create([
            'business_id' => $otherBusiness->id,
            'stock' => 100,
        ]);

        $otherSale = Sale::factory()->create([
            'business_id' => $otherBusiness->id,
            'user_id' => $otherAdmin->id,
        ]);

        $otherReservation = $this->service->create($otherBusiness->id, $otherSale->id, $otherProduct->id, 10);
        $otherReservation->expires_at = now()->subMinutes(5);
        $otherReservation->save();

        // Run command - should expire all due reservations regardless of business
        $this->artisan('stock:reservations:expire', ['--limit' => 10]);

        $this->assertEquals('expired', $otherReservation->fresh()->status);
    }

    public function test_expire_command_dry_run_shows_what_would_be_expired()
    {
        $this->actingAs($this->admin);

        $sale = Sale::factory()->create([
            'business_id' => $this->business->id,
            'user_id' => $this->admin->id,
        ]);

        $reservation = $this->service->create(
            $this->business->id,
            $sale->id,
            $this->product->id,
            10
        );

        $reservation->expires_at = now()->subMinutes(5);
        $reservation->save();

        // Capture output of dry-run
        $output = $this->artisan('stock:reservations:expire', ['--dry-run' => true, '--limit' => 10]);

        // Should show the reservation but not change it
        $this->assertEquals('active', $reservation->fresh()->status);
    }
}

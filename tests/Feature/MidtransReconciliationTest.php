<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use App\Services\MidtransService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MidtransReconciliationTest extends TestCase
{
    use RefreshDatabase;

    protected Business $business;

    protected User $user;

    protected Product $product;

    protected Sale $sale;

    protected Payment $payment;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        Permission::firstOrCreate(['name' => 'sales create', 'guard_name' => 'web']);
        $role = Role::firstOrCreate(['name' => 'kasir', 'guard_name' => 'web']);
        $role->syncPermissions(['sales create']);

        $this->business = Business::factory()->create();

        $this->user = User::factory()->create([
            'business_id' => $this->business->id,
            'status' => 'active',
        ]);
        $this->user->assignRole('kasir');

        $this->product = Product::factory()->create([
            'business_id' => $this->business->id,
            'stock' => 10,
        ]);

        $this->sale = Sale::factory()->create([
            'business_id' => $this->business->id,
            'user_id' => $this->user->id,
            'invoice_number' => 'INV-1',
            'subtotal' => 100,
            'total_amount' => 100,
            'status' => 'pending',
        ]);

        SaleItem::create([
            'sale_id' => $this->sale->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'unit_price' => 100,
            'subtotal' => 100,
        ]);

        $this->payment = Payment::create([
            'business_id' => $this->business->id,
            'sale_id' => $this->sale->id,
            'method' => 'midtrans',
            'provider' => 'midtrans',
            'amount' => 100,
            'status' => 'pending',
            'metadata' => ['snap_order_id' => $this->sale->midtransOrderId()],
        ]);

        config([
            'midtrans.server_key' => 'test-server-key',
            'midtrans.api_base_url' => 'https://api.sandbox.midtrans.com',
        ]);
    }

    public function test_polling_reconciles_settlement_to_paid(): void
    {
        Http::fake([
            '*/v2/*/status' => Http::response([
                'order_id' => $this->sale->midtransOrderId(),
                'transaction_status' => 'settlement',
                'fraud_status' => 'accept',
                'transaction_id' => 'tx-1',
                'gross_amount' => '100.00',
            ]),
        ]);

        $this->actingAs($this->user)
            ->getJson(route('pos.sale-status', $this->sale->id))
            ->assertOk()
            ->assertJson(['status' => 'completed', 'payment_status' => 'paid']);

        $this->assertSame('paid', $this->payment->fresh()->status);
        $this->assertSame('completed', $this->sale->fresh()->status);
        $this->assertSame(9, $this->product->fresh()->stock);
    }

    public function test_polling_reconciles_expiry_to_failed(): void
    {
        Http::fake([
            '*/v2/*/status' => Http::response([
                'order_id' => $this->sale->midtransOrderId(),
                'transaction_status' => 'expire',
                'gross_amount' => '100.00',
            ]),
        ]);

        $this->actingAs($this->user)
            ->getJson(route('pos.sale-status', $this->sale->id))
            ->assertOk()
            ->assertJson(['status' => 'cancelled', 'payment_status' => 'expired']);

        $this->assertSame('expired', $this->payment->fresh()->status);
        $this->assertSame('cancelled', $this->sale->fresh()->status);
        $this->assertSame(10, $this->product->fresh()->stock);
    }

    public function test_polling_throttles_midtrans_status_requests(): void
    {
        Http::fake([
            '*/v2/*/status' => Http::response([
                'order_id' => $this->sale->midtransOrderId(),
                'transaction_status' => 'pending',
                'gross_amount' => '100.00',
            ]),
        ]);

        $this->actingAs($this->user)->getJson(route('pos.sale-status', $this->sale->id))->assertOk();
        $this->actingAs($this->user)->getJson(route('pos.sale-status', $this->sale->id))->assertOk();

        Http::assertSentCount(1);
    }

    public function test_status_amount_mismatch_does_not_settle_payment(): void
    {
        Http::fake([
            '*/v2/*/status' => Http::response([
                'order_id' => $this->sale->midtransOrderId(),
                'transaction_status' => 'settlement',
                'fraud_status' => 'accept',
                'gross_amount' => '999.00',
            ]),
        ]);

        $this->actingAs($this->user)->getJson(route('pos.sale-status', $this->sale->id))->assertOk();

        $this->assertSame('pending', $this->payment->fresh()->status);
        $this->assertSame('pending', $this->sale->fresh()->status);
    }

    public function test_scheduled_command_reconciles_stale_pending_payments(): void
    {
        $this->payment->created_at = now()->subMinutes(5);
        $this->payment->save();

        Http::fake([
            '*/v2/*/status' => Http::response([
                'order_id' => $this->sale->midtransOrderId(),
                'transaction_status' => 'settlement',
                'fraud_status' => 'accept',
                'gross_amount' => '100.00',
            ]),
        ]);

        $this->artisan('payments:reconcile-midtrans')->assertExitCode(0);

        $this->assertSame('paid', $this->payment->fresh()->status);
    }

    public function test_reconcile_command_dry_run_does_not_change_payment(): void
    {
        $this->payment->created_at = now()->subMinutes(5);
        $this->payment->save();

        Http::fake();

        $this->artisan('payments:reconcile-midtrans', ['--dry-run' => true])->assertExitCode(0);

        $this->assertSame('pending', $this->payment->fresh()->status);
        Http::assertNothingSent();
    }

    public function test_webhook_records_valid_event_and_settles_payment(): void
    {
        $orderId = $this->sale->midtransOrderId();
        $payload = [
            'order_id' => $orderId,
            'status_code' => '200',
            'gross_amount' => '100.00',
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
        ];
        $payload['signature_key'] = hash('sha512', $orderId.'200'.'100.00'.'test-server-key');

        $this->postJson(route('midtrans.webhook'), $payload)->assertStatus(200);

        $this->assertSame('paid', $this->payment->fresh()->status);
        $this->assertDatabaseHas('midtrans_webhook_events', [
            'order_id' => $orderId,
            'payment_id' => $this->payment->id,
            'signature_valid' => true,
            'response_status' => 200,
        ]);
    }

    public function test_webhook_records_invalid_signature(): void
    {
        $payload = [
            'order_id' => 'anything',
            'status_code' => '200',
            'gross_amount' => '100.00',
            'transaction_status' => 'settlement',
            'signature_key' => 'not-a-valid-signature',
        ];

        $this->postJson(route('midtrans.webhook'), $payload)->assertStatus(403);

        $this->assertSame('pending', $this->payment->fresh()->status);
        $this->assertDatabaseHas('midtrans_webhook_events', [
            'order_id' => 'anything',
            'signature_valid' => false,
            'response_status' => 403,
        ]);
    }

    public function test_webhook_unknown_order_is_ignored_with_200(): void
    {
        $orderId = 'unknown-order-123';
        $payload = [
            'order_id' => $orderId,
            'status_code' => '200',
            'gross_amount' => '100.00',
            'transaction_status' => 'settlement',
        ];
        $payload['signature_key'] = hash('sha512', $orderId.'200'.'100.00'.'test-server-key');

        $this->postJson(route('midtrans.webhook'), $payload)->assertStatus(200);

        $this->assertDatabaseHas('midtrans_webhook_events', [
            'order_id' => $orderId,
            'payment_id' => null,
            'signature_valid' => true,
            'response_status' => 200,
        ]);
    }

    public function test_snap_transaction_appends_notification_url_header(): void
    {
        config(['midtrans.notification_url' => 'https://example.test/midtrans/webhook']);

        Http::fake([
            '*/snap/v1/transactions' => Http::response([
                'token' => 'snap-token',
                'redirect_url' => 'https://example.test/pay',
            ], 201),
        ]);

        $result = app(MidtransService::class)->createSnapTransaction($this->sale);

        $this->assertSame('snap-token', $result['token']);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'snap/v1/transactions')
                && $request->hasHeader('X-Append-Notification', 'https://example.test/midtrans/webhook');
        });
    }
}

<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_report_metrics_are_calculated_from_completed_sales(): void
    {
        $business = Business::factory()->create(['tax_percentage' => 10]);
        $user = User::factory()->create(['business_id' => $business->id]);

        Permission::firstOrCreate(['name' => 'reports view', 'guard_name' => 'web']);
        $user->givePermissionTo('reports view');

        $product = Product::factory()->create([
            'business_id' => $business->id,
            'purchase_price' => 6000,
            'selling_price' => 50000,
        ]);

        $bestDayDate = Carbon::now()->startOfMonth();

        $bestSale = Sale::create([
            'business_id' => $business->id,
            'user_id' => $user->id,
            'invoice_number' => 'INV-REPORT-1',
            'subtotal' => 100000,
            'discount_amount' => 10000,
            'tax_amount' => 9000,
            'total_amount' => 99000,
            'status' => 'completed',
            'payment_method' => 'cash',
            'transaction_date' => $bestDayDate->toDateString(),
        ]);

        SaleItem::create([
            'sale_id' => $bestSale->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 50000,
            'discount_amount' => 0,
            'subtotal' => 100000,
        ]);

        $secondSale = Sale::create([
            'business_id' => $business->id,
            'user_id' => $user->id,
            'invoice_number' => 'INV-REPORT-2',
            'subtotal' => 50000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 50000,
            'status' => 'completed',
            'payment_method' => 'qris',
            'transaction_date' => $bestDayDate->copy()->addDay()->toDateString(),
        ]);

        SaleItem::create([
            'sale_id' => $secondSale->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 50000,
            'discount_amount' => 0,
            'subtotal' => 50000,
        ]);

        // Transaksi yang belum selesai tidak boleh masuk laporan.
        Sale::create([
            'business_id' => $business->id,
            'user_id' => $user->id,
            'invoice_number' => 'INV-REPORT-PENDING',
            'subtotal' => 999000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 999000,
            'status' => 'pending',
            'payment_method' => 'midtrans',
            'transaction_date' => $bestDayDate->toDateString(),
        ]);

        $response = $this->actingAs($user)->get(route('reports.sales', ['period' => 'month']));

        $response->assertOk();
        $response->assertViewHas('transactionCount', fn ($value) => (int) $value === 2);
        $response->assertViewHas('totalSales', fn ($value) => (float) $value === 149000.0);
        $response->assertViewHas('totalDiscount', fn ($value) => (float) $value === 10000.0);
        $response->assertViewHas('totalTax', fn ($value) => (float) $value === 9000.0);
        $response->assertViewHas('totalItemsSold', fn ($value) => (int) $value === 3);
        $response->assertViewHas('grossProfit', fn ($value) => (float) $value === 122000.0);
        $response->assertViewHas('profitMargin', fn ($value) => (float) $value === 87.1);
        $response->assertViewHas(
            'bestDayFormatted',
            fn ($value) => $value === $bestDayDate->copy()->locale('id')->isoFormat('ddd, D MMM')
        );
        $response->assertSee('Pajak 10%');
    }
}

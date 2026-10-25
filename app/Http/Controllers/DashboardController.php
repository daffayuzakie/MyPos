<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    private const PERIODS = ['today', '7days', 'month'];

    public function index(Request $request)
    {
        $businessId = auth()->user()->business_id;
        $today = Carbon::today();

        $period = $request->string('period')->toString();
        if (! in_array($period, self::PERIODS, true)) {
            $period = '7days';
        }

        // Penjualan & Transaksi Hari Ini (single aggregate query)
        $todaySummary = Sale::where('business_id', $businessId)
            ->where('transaction_date', $today->toDateString())
            ->where('status', 'completed')
            ->selectRaw('COALESCE(SUM(total_amount), 0) as sales, COUNT(*) as transactions')
            ->first();

        $todaySales = (float) $todaySummary->sales;
        $todayTransactions = (int) $todaySummary->transactions;

        // Rata-rata per Transaksi
        $averageTransaction = $todayTransactions > 0 ? round($todaySales / $todayTransactions) : 0;

        // Peringatan Stok Rendah (<= 5)
        $lowStockQuery = Product::where('business_id', $businessId)
            ->where('is_active', true)
            ->where('stock', '<=', 5);

        $lowStockCount = (clone $lowStockQuery)->count();
        $lowStockProducts = $lowStockQuery
            ->orderBy('stock', 'asc')
            ->limit(10)
            ->get(['id', 'name', 'sku', 'stock']);

        // Grafik Penjualan sesuai periode terpilih
        [$chartLabels, $chartData] = $this->buildSalesChart($businessId, $period, $today);

        // 5 Produk Terlaris
        $topProducts = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.business_id', $businessId)
            ->where('sales.status', 'completed')
            ->select(
                'products.name',
                DB::raw('SUM(sale_items.quantity) as sold_qty'),
                DB::raw('SUM(sale_items.subtotal) as revenue')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('sold_qty')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'todaySales',
            'todayTransactions',
            'averageTransaction',
            'lowStockCount',
            'chartLabels',
            'chartData',
            'topProducts',
            'lowStockProducts',
            'period'
        ));
    }

    /**
     * Build the sales chart labels/data for the given period.
     *
     * @return array{0: array<int, string>, 1: array<int, float>}
     */
    private function buildSalesChart(int $businessId, string $period, Carbon $today): array
    {
        if ($period === 'today') {
            return $this->buildHourlyChart($businessId, $today);
        }

        $isMonth = $period === 'month';
        $start = $isMonth ? $today->copy()->startOfMonth() : $today->copy()->subDays(6);
        $end = $today->copy();

        $dailyTotals = Sale::where('business_id', $businessId)
            ->where('status', 'completed')
            ->whereBetween('transaction_date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('DATE(transaction_date) as date, SUM(total_amount) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $dayNames = [
            'Sunday' => 'Min', 'Monday' => 'Sen', 'Tuesday' => 'Sel',
            'Wednesday' => 'Rab', 'Thursday' => 'Kam', 'Friday' => 'Jum', 'Saturday' => 'Sab',
        ];

        $chartLabels = [];
        $chartData = [];

        $cursor = $start->copy();
        while ($cursor->lessThanOrEqualTo($end)) {
            if ($isMonth) {
                $chartLabels[] = $cursor->format('d/m');
            } else {
                $dayName = $dayNames[$cursor->format('l')] ?? $cursor->format('D');
                $chartLabels[] = $dayName.' ('.$cursor->format('d/m').')';
            }

            $chartData[] = (float) ($dailyTotals[$cursor->toDateString()] ?? 0);
            $cursor->addDay();
        }

        return [$chartLabels, $chartData];
    }

    /**
     * Hourly breakdown for the current day.
     *
     * @return array{0: array<int, string>, 1: array<int, float>}
     */
    private function buildHourlyChart(int $businessId, Carbon $today): array
    {
        $hourlyTotals = Sale::where('business_id', $businessId)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$today->copy()->startOfDay(), $today->copy()->endOfDay()])
            ->get(['created_at', 'total_amount']);

        $hourly = array_fill(0, 24, 0.0);

        foreach ($hourlyTotals as $sale) {
            $hour = (int) $sale->created_at->format('G');
            $hourly[$hour] += (float) $sale->total_amount;
        }

        $chartLabels = [];
        $chartData = [];

        foreach ($hourly as $hour => $total) {
            $chartLabels[] = str_pad((string) $hour, 2, '0', STR_PAD_LEFT).':00';
            $chartData[] = $total;
        }

        return [$chartLabels, $chartData];
    }
}

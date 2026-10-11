<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $businessId = auth()->user()->business_id;
        $today = Carbon::today();

        // Penjualan Hari Ini
        $todaySales = (float) Sale::where('business_id', $businessId)
            ->whereDate('transaction_date', $today)
            ->where('status', 'completed')
            ->sum('total_amount');

        // Transaksi Hari Ini
        $todayTransactions = Sale::where('business_id', $businessId)
            ->whereDate('transaction_date', $today)
            ->where('status', 'completed')
            ->count();

        // Rata-rata per Transaksi
        $averageTransaction = $todayTransactions > 0 ? round($todaySales / $todayTransactions) : 0;

        // Peringatan Stok Rendah (<= 5)
        $lowStockProducts = Product::where('business_id', $businessId)
            ->where('is_active', true)
            ->where('stock', '<=', 5)
            ->orderBy('stock', 'asc')
            ->get();
        $lowStockCount = $lowStockProducts->count();

        // Grafik 7 Hari Terakhir
        $chartLabels = [];
        $chartData = [];
        $dayNames = [
            'Sunday' => 'Min', 'Monday' => 'Sen', 'Tuesday' => 'Sel',
            'Wednesday' => 'Rab', 'Thursday' => 'Kam', 'Friday' => 'Jum', 'Saturday' => 'Sab',
        ];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dayName = $dayNames[$date->format('l')] ?? $date->format('D');
            $chartLabels[] = $dayName.' ('.$date->format('d/m').')';

            $dailyTotal = (float) Sale::where('business_id', $businessId)
                ->where('status', 'completed')
                ->whereDate('transaction_date', $date)
                ->sum('total_amount');

            $chartData[] = $dailyTotal;
        }

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
            'lowStockProducts'
        ));
    }
}

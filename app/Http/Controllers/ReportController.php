<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    private function buildSalesQuery(Request $request)
    {
        $businessId = auth()->user()->business_id;

        $period = $request->get('period', 'today');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $search = $request->get('search');

        $query = Sale::where('business_id', $businessId)->where('status', 'completed');

        if ($search) {
            $query->where('invoice_number', 'like', "%{$search}%");
        }

        if ($period === 'today') {
            $query->whereDate('transaction_date', Carbon::today());
        } elseif ($period === '7days') {
            $query->where('transaction_date', '>=', Carbon::now()->subDays(7));
        } elseif ($period === 'month') {
            $query->whereMonth('transaction_date', Carbon::now()->month)
                ->whereYear('transaction_date', Carbon::now()->year);
        } elseif ($period === 'custom' && $dateFrom && $dateTo) {
            $query->whereBetween('transaction_date', [$dateFrom, $dateTo]);
        }

        return $query;
    }

    public function sales(Request $request)
    {
        $period = $request->get('period', 'today');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $search = $request->get('search');

        $sales = $this->buildSalesQuery($request)->get();

        $totalSales = (float) $sales->sum('total_amount');
        $transactionCount = $sales->count();
        $averagePerTransaction = $transactionCount > 0 ? round($totalSales / $transactionCount) : 0;

        // Kalkulasi Laba Kotor Sebenarnya: Pendapatan - Modal (Cost of Goods Sold)
        $saleIds = $sales->pluck('id');
        $totalCost = (float) DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereIn('sale_items.sale_id', $saleIds)
            ->sum(DB::raw('sale_items.quantity * products.purchase_price'));

        $grossProfit = max(0, $totalSales - $totalCost);

        // Data Penjualan Harian untuk Chart
        $dailySales = Sale::whereIn('id', $saleIds)
            ->selectRaw('DATE(transaction_date) as date, SUM(total_amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $chartLabels = $dailySales->pluck('date')->map(fn ($d) => Carbon::parse($d)->format('d M'))->toArray();
        $chartData = $dailySales->pluck('total')->map(fn ($t) => (float) $t)->toArray();

        // Rincian Metode Pembayaran
        $paymentMethods = Sale::whereIn('id', $saleIds)
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('payment_method')
            ->get();

        // Produk Terlaris dengan Margin
        $topProducts = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereIn('sales.id', $saleIds)
            ->select(
                'products.name',
                DB::raw('SUM(sale_items.quantity) as sold_qty'),
                DB::raw('SUM(sale_items.subtotal) as revenue'),
                DB::raw('SUM(sale_items.quantity * products.purchase_price) as cost')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('sold_qty')
            ->limit(10)
            ->get()
            ->map(function ($p) {
                $p->margin = $p->revenue > 0 ? round((($p->revenue - $p->cost) / $p->revenue) * 100, 1) : 0;

                return $p;
            });

        return view('reports.sales', compact(
            'totalSales', 'transactionCount', 'averagePerTransaction', 'grossProfit',
            'dailySales', 'chartLabels', 'chartData', 'paymentMethods', 'topProducts', 'period', 'dateFrom', 'dateTo', 'search'
        ));
    }

    public function exportExcel(Request $request)
    {
        $sales = $this->buildSalesQuery($request)->with('user')->get();

        $csv = "No. Nota,Waktu,Kasir,Metode,Total\n";
        foreach ($sales as $sale) {
            $kasir = $sale->user->name ?? '-';
            $waktu = $sale->transaction_date ?? $sale->created_at;
            $csv .= "\"{$sale->invoice_number}\",\"{$waktu}\",\"{$kasir}\",\"{$sale->payment_method}\",{$sale->total_amount}\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="laporan_penjualan_'.date('Ymd_His').'.csv"');
    }

    public function exportPdf(Request $request)
    {
        $business = auth()->user()->business;
        $sales = $this->buildSalesQuery($request)->with('user')->get();
        $totalSales = $sales->sum('total_amount');

        $html = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Laporan Penjualan</title>';
        $html .= '<style>body{font-family:sans-serif;padding:30px;color:#333} table{width:100%;border-collapse:collapse;margin-top:20px} th,td{border:1px solid #ddd;padding:8px;text-align:left;font-size:12px} th{background:#f4f4f4} h2{margin-bottom:5px}</style>';
        $html .= '</head><body>';
        $html .= '<h2>Laporan Penjualan - '.($business->name ?? 'POS').'</h2>';
        $html .= '<p style="font-size:12px;color:#666">Dicetak pada: '.date('d M Y H:i').' | Total Transaksi: '.$sales->count().'</p>';
        $html .= '<table><thead><tr><th>No. Nota</th><th>Waktu</th><th>Kasir</th><th>Metode</th><th style="text-align:right">Total</th></tr></thead><tbody>';
        foreach ($sales as $sale) {
            $html .= '<tr><td>'.$sale->invoice_number.'</td><td>'.($sale->transaction_date ?? $sale->created_at).'</td><td>'.($sale->user->name ?? '-').'</td><td>'.strtoupper($sale->payment_method).'</td><td style="text-align:right">Rp '.number_format($sale->total_amount, 0, ',', '.').'</td></tr>';
        }
        $html .= '</tbody><tfoot><tr><th colspan="4" style="text-align:right">TOTAL KESELURUHAN:</th><th style="text-align:right">Rp '.number_format($totalSales, 0, ',', '.').'</th></tr></tfoot></table>';
        $html .= '<script>window.onload = function() { window.print(); }</script></body></html>';

        return response($html)->header('Content-Type', 'text/html');
    }
}

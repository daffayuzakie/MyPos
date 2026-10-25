<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Apply the shared period/search filters to a sales query.
     *
     * @param  Builder|\Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $prefix  Column prefix for the sales table (e.g. "sales.")
     */
    private function applySalesFilters($query, Request $request, string $prefix = ''): void
    {
        $period = $request->get('period', 'month');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $search = $request->get('search');

        $query->where($prefix.'business_id', auth()->user()->business_id)
            ->where($prefix.'status', 'completed');

        if ($search) {
            $query->where($prefix.'invoice_number', 'like', "%{$search}%");
        }

        if ($period === 'today') {
            $query->whereDate($prefix.'transaction_date', Carbon::today()->toDateString());
        } elseif ($period === '7days') {
            $query->whereDate($prefix.'transaction_date', '>=', Carbon::now()->subDays(6)->toDateString())
                ->whereDate($prefix.'transaction_date', '<=', Carbon::today()->toDateString());
        } elseif ($period === 'month') {
            $query->whereBetween($prefix.'transaction_date', [
                Carbon::now()->startOfMonth()->toDateString(),
                Carbon::now()->endOfMonth()->toDateString(),
            ]);
        } elseif ($period === 'custom' && $dateFrom && $dateTo) {
            $query->whereBetween($prefix.'transaction_date', [$dateFrom, $dateTo]);
        }
    }

    private function buildSalesQuery(Request $request)
    {
        $query = Sale::query();
        $this->applySalesFilters($query, $request);

        return $query;
    }

    public function sales(Request $request)
    {
        $period = $request->get('period', 'month');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $search = $request->get('search');

        $businessId = auth()->user()->business_id;

        // 1. Ringkasan penjualan real dari database
        $summary = $this->buildSalesQuery($request)
            ->selectRaw('COALESCE(SUM(total_amount), 0) as sales, COUNT(*) as transactions, COALESCE(SUM(discount_amount), 0) as discount, COALESCE(SUM(tax_amount), 0) as tax')
            ->first();

        $totalSales = (float) $summary->sales;
        $transactionCount = (int) $summary->transactions;
        $averagePerTransaction = $transactionCount > 0 ? round($totalSales / $transactionCount) : 0;
        $totalDiscount = (float) $summary->discount;
        $totalTax = (float) $summary->tax;
        $taxPercentage = (float) (auth()->user()->business->tax_percentage ?? 0);

        // Omzet bersih setelah pajak: dasar perhitungan laba kotor.
        $netSales = max(0, $totalSales - $totalTax);

        // 2. Total item terjual real
        $itemsSoldQuery = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id');
        $this->applySalesFilters($itemsSoldQuery, $request, 'sales.');
        $totalItemsSold = (int) $itemsSoldQuery->sum('sale_items.quantity');

        // 3. Kalkulasi modal (COGS) dan laba kotor real
        $costQuery = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id');
        $this->applySalesFilters($costQuery, $request, 'sales.');

        $totalCost = (float) $costQuery
            ->sum(DB::raw('sale_items.quantity * products.purchase_price'));

        $grossProfit = max(0, $netSales - $totalCost);
        $profitMargin = $netSales > 0 ? round(($grossProfit / $netSales) * 100, 1) : 0;

        // 4. Perhitungan perbandingan periode sebelumnya untuk indikator pertumbuhan
        $prevSales = 0;
        $prevTrans = 0;
        $prevCost = 0;
        $prevQuery = Sale::where('business_id', $businessId)->where('status', 'completed');
        $prevCostQuery = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.business_id', $businessId)
            ->where('sales.status', 'completed');

        if ($period === 'today') {
            $yesterday = Carbon::yesterday()->toDateString();
            $prevQuery->whereDate('transaction_date', $yesterday);
            $prevCostQuery->whereDate('sales.transaction_date', $yesterday);
        } elseif ($period === '7days') {
            $prevStart = Carbon::now()->subDays(13)->toDateString();
            $prevEnd = Carbon::now()->subDays(7)->toDateString();
            $prevQuery->whereBetween('transaction_date', [$prevStart, $prevEnd]);
            $prevCostQuery->whereBetween('sales.transaction_date', [$prevStart, $prevEnd]);
        } elseif ($period === 'month') {
            $prevStart = Carbon::now()->subMonth()->startOfMonth()->toDateString();
            $prevEnd = Carbon::now()->subMonth()->endOfMonth()->toDateString();
            $prevQuery->whereBetween('transaction_date', [$prevStart, $prevEnd]);
            $prevCostQuery->whereBetween('sales.transaction_date', [$prevStart, $prevEnd]);
        }

        if (in_array($period, ['today', '7days', 'month'])) {
            $prevSummary = $prevQuery->selectRaw('COALESCE(SUM(total_amount), 0) as sales, COALESCE(SUM(tax_amount), 0) as tax, COUNT(*) as transactions')->first();
            $prevSales = (float) ($prevSummary->sales ?? 0);
            $prevTax = (float) ($prevSummary->tax ?? 0);
            $prevTrans = (int) ($prevSummary->transactions ?? 0);
            $prevCost = (float) $prevCostQuery->sum(DB::raw('sale_items.quantity * products.purchase_price'));
        }

        $prevNetSales = max(0, $prevSales - ($prevTax ?? 0));
        $prevProfit = max(0, $prevNetSales - $prevCost);
        $prevAvg = $prevTrans > 0 ? round($prevSales / $prevTrans) : 0;

        $salesGrowth = $prevSales > 0 ? round((($totalSales - $prevSales) / $prevSales) * 100, 1) : ($totalSales > 0 ? 100 : 0);
        $transactionGrowth = $transactionCount - $prevTrans;
        $avgGrowth = $prevAvg > 0 ? round((($averagePerTransaction - $prevAvg) / $prevAvg) * 100, 1) : 0;
        $profitGrowth = $prevProfit > 0 ? round((($grossProfit - $prevProfit) / $prevProfit) * 100, 1) : ($grossProfit > 0 ? 100 : 0);

        // 5. Data penjualan harian real untuk grafik
        $dailyQuery = DB::table('sales')
            ->selectRaw('DATE(transaction_date) as date, SUM(total_amount) as total');
        $this->applySalesFilters($dailyQuery, $request);
        $dailySales = $dailyQuery->groupBy('date')->orderBy('date')->get()->keyBy('date');

        $chartLabels = $dailySales->pluck('date')->map(fn ($d) => Carbon::parse($d)->format('d M'))->toArray();
        $chartData = $dailySales->pluck('total')->map(fn ($t) => (float) $t)->toArray();

        // Hari Terbaik (Peak day) real
        $bestDay = $dailySales
            ->filter(fn ($day) => (float) $day->total > 0)
            ->sortByDesc(fn ($day) => (float) $day->total)
            ->first();

        $bestDayFormatted = $bestDay
            ? Carbon::parse($bestDay->date)->locale('id')->isoFormat('ddd, D MMM')
            : '-';

        // 6. Pembuatan Bar Chart Kapsul Real sesuai Periode
        $chartBars = [];
        $maxSalesVal = $dailySales->max('total') ?: 1;

        if ($period === 'month') {
            $daysInMonth = Carbon::now()->daysInMonth;
            $currentDay = Carbon::now()->day;
            $endDay = max($currentDay, 20); // minimal 20 batang agar proporsi grafik tetap elegan

            for ($d = 1; $d <= min($daysInMonth, $endDay); $d++) {
                $dateKey = Carbon::now()->format('Y-m-').str_pad($d, 2, '0', STR_PAD_LEFT);
                $dayTotal = (float) ($dailySales->get($dateKey)->total ?? 0);
                $isPeak = ($dayTotal > 0 && $dayTotal == $maxSalesVal);

                $height = $dayTotal > 0 ? max(28, round(($dayTotal / $maxSalesVal) * 160)) : 0;
                $formattedVal = $dayTotal >= 1000000
                    ? round($dayTotal / 1000000, 1).'jt'
                    : ($dayTotal >= 1000 ? round($dayTotal / 1000).'rb' : number_format($dayTotal, 0, ',', '.'));

                $chartBars[] = [
                    'day' => $d,
                    'date' => $dateKey,
                    'val' => $dayTotal,
                    'formatted_val' => $formattedVal,
                    'active' => $dayTotal > 0,
                    'peak' => $isPeak,
                    'height' => $height,
                    'show_label' => ($d === 1 || $d % 5 === 0),
                ];
            }
        } elseif ($period === '7days') {
            for ($i = 6; $i >= 0; $i--) {
                $carbonDate = Carbon::now()->subDays($i);
                $dateKey = $carbonDate->toDateString();
                $dayTotal = (float) ($dailySales->get($dateKey)->total ?? 0);
                $isPeak = ($dayTotal > 0 && $dayTotal == $maxSalesVal);

                $height = $dayTotal > 0 ? max(28, round(($dayTotal / $maxSalesVal) * 160)) : 0;
                $formattedVal = $dayTotal >= 1000000
                    ? round($dayTotal / 1000000, 1).'jt'
                    : ($dayTotal >= 1000 ? round($dayTotal / 1000).'rb' : number_format($dayTotal, 0, ',', '.'));

                $chartBars[] = [
                    'day' => $carbonDate->locale('id')->isoFormat('ddd'),
                    'date' => $dateKey,
                    'val' => $dayTotal,
                    'formatted_val' => $formattedVal,
                    'active' => $dayTotal > 0,
                    'peak' => $isPeak,
                    'height' => $height,
                    'show_label' => true,
                ];
            }
        } elseif ($period === 'custom' && $dateFrom && $dateTo) {
            $rangeStart = Carbon::parse($dateFrom)->startOfDay();
            $rangeEnd = Carbon::parse($dateTo)->startOfDay();

            if ($rangeStart->gt($rangeEnd)) {
                [$rangeStart, $rangeEnd] = [$rangeEnd, $rangeStart];
            }

            // Batasi grafik agar tetap terbaca ketika rentang tanggal sangat panjang.
            if ($rangeStart->diffInDays($rangeEnd) > 60) {
                $rangeStart = $rangeEnd->copy()->subDays(60);
            }

            for ($date = $rangeStart->copy(); $date->lte($rangeEnd); $date->addDay()) {
                $dateKey = $date->toDateString();
                $dayTotal = (float) ($dailySales->get($dateKey)->total ?? 0);
                $isPeak = ($dayTotal > 0 && $dayTotal == $maxSalesVal);

                $height = $dayTotal > 0 ? max(28, round(($dayTotal / $maxSalesVal) * 160)) : 0;
                $formattedVal = $dayTotal >= 1000000
                    ? round($dayTotal / 1000000, 1).'jt'
                    : ($dayTotal >= 1000 ? round($dayTotal / 1000).'rb' : number_format($dayTotal, 0, ',', '.'));

                $chartBars[] = [
                    'day' => $date->locale('id')->isoFormat('D MMM'),
                    'date' => $dateKey,
                    'val' => $dayTotal,
                    'formatted_val' => $formattedVal,
                    'active' => $dayTotal > 0,
                    'peak' => $isPeak,
                    'height' => $height,
                    'show_label' => true,
                ];
            }
        } else {
            // Hari ini (Today)
            $todayKey = Carbon::today()->toDateString();
            $todayTotal = (float) ($dailySales->get($todayKey)->total ?? 0);
            $formattedVal = $todayTotal >= 1000 ? round($todayTotal / 1000).'rb' : number_format($todayTotal, 0, ',', '.');
            $chartBars[] = [
                'day' => Carbon::today()->locale('id')->isoFormat('D MMM'),
                'date' => $todayKey,
                'val' => $todayTotal,
                'formatted_val' => $formattedVal,
                'active' => $todayTotal > 0,
                'peak' => $todayTotal > 0,
                'height' => $todayTotal > 0 ? 140 : 0,
                'show_label' => true,
            ];
        }

        // 7. Format Teks Pill Tanggal
        if ($period === 'today') {
            $dateRangeText = Carbon::today()->locale('id')->isoFormat('D MMM YYYY');
        } elseif ($period === '7days') {
            $dateRangeText = Carbon::now()->subDays(6)->locale('id')->isoFormat('D').'-'.Carbon::now()->locale('id')->isoFormat('D MMM YYYY');
        } elseif ($period === 'month') {
            $dateRangeText = '1-'.Carbon::now()->locale('id')->isoFormat('D MMM YYYY');
        } elseif ($period === 'custom' && $dateFrom && $dateTo) {
            $dateRangeText = Carbon::parse($dateFrom)->locale('id')->isoFormat('D MMM').' - '.Carbon::parse($dateTo)->locale('id')->isoFormat('D MMM YYYY');
        } else {
            $dateRangeText = '1-'.Carbon::now()->locale('id')->isoFormat('D MMM YYYY');
        }

        // 8. Rincian Metode Pembayaran Real
        $paymentQuery = DB::table('sales')
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as total'));
        $this->applySalesFilters($paymentQuery, $request);
        $paymentMethods = $paymentQuery->groupBy('payment_method')->orderByDesc('total')->get()->map(function ($pm) use ($totalSales) {
            $rawMethod = strtolower($pm->payment_method ?? 'tunai');
            $pm->method_name = match ($rawMethod) {
                'cash' => 'Tunai',
                'qris' => 'QRIS',
                'midtrans' => 'Midtrans',
                'card' => 'Kartu Debit/Kredit',
                'ewallet' => 'E-Wallet',
                default => ucfirst($rawMethod)
            };
            $pm->percentage = $totalSales > 0 ? round(($pm->total / $totalSales) * 100, 1) : 0;

            return $pm;
        });

        // 9. Rincian Per Kategori Real
        $categoryQuery = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                DB::raw("COALESCE(categories.name, 'Tanpa Kategori') as category_name"),
                DB::raw('SUM(sale_items.subtotal) as total')
            );
        $this->applySalesFilters($categoryQuery, $request, 'sales.');
        $categoryBreakdown = $categoryQuery
            ->groupBy('category_name')
            ->orderByDesc('total')
            ->get()
            ->map(function ($cat) use ($totalSales) {
                $cat->percentage = $totalSales > 0 ? round(($cat->total / $totalSales) * 100, 1) : 0;

                return $cat;
            });

        // 10. Rincian Per Kasir Real
        $cashierQuery = DB::table('sales')
            ->join('users', 'sales.user_id', '=', 'users.id')
            ->select(
                'users.name as cashier_name',
                DB::raw('COUNT(sales.id) as transaction_count'),
                DB::raw('SUM(sales.total_amount) as total')
            );
        $this->applySalesFilters($cashierQuery, $request, 'sales.');
        $cashierBreakdown = $cashierQuery
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total')
            ->get()
            ->map(function ($c) use ($totalSales) {
                $c->percentage = $totalSales > 0 ? round(($c->total / $totalSales) * 100, 1) : 0;

                return $c;
            });

        // 11. Produk Terlaris Real dari sale_items
        $topQuery = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                'products.name',
                DB::raw("COALESCE(categories.name, 'Umum') as category_name"),
                DB::raw('SUM(sale_items.quantity) as sold_qty'),
                DB::raw('SUM(sale_items.subtotal) as revenue'),
                DB::raw('SUM(sale_items.quantity * products.purchase_price) as cost')
            );
        $this->applySalesFilters($topQuery, $request, 'sales.');

        $topProducts = $topQuery
            ->groupBy('products.id', 'products.name', 'categories.name')
            ->orderByDesc('sold_qty')
            ->limit(10)
            ->get()
            ->map(function ($p) {
                $p->profit = max(0, $p->revenue - $p->cost);
                $p->margin = $p->revenue > 0 ? round(($p->profit / $p->revenue) * 100, 1) : 0;

                return $p;
            });

        // 12. Transaksi Terbaru Real dari database
        $recentSales = $this->buildSalesQuery($request)
            ->with('user:id,name')
            ->latest('transaction_date')
            ->latest('id')
            ->limit(10)
            ->get()
            ->map(function ($s) {
                $rawMethod = strtolower($s->payment_method ?? 'cash');
                $methodName = match ($rawMethod) {
                    'cash' => 'Tunai',
                    'qris' => 'QRIS',
                    'midtrans' => 'Midtrans',
                    'card' => 'Kartu',
                    'ewallet' => 'E-Wallet',
                    default => ucfirst($rawMethod)
                };

                return (object) [
                    'invoice_number' => $s->invoice_number,
                    'date_formatted' => Carbon::parse($s->transaction_date ?? $s->created_at)->locale('id')->isoFormat('D MMM, HH.mm'),
                    'cashier_name' => $s->user->name ?? 'Kasir',
                    'payment_method' => $methodName,
                    'total_amount' => $s->total_amount,
                    'status' => $s->status === 'completed' ? 'Lunas' : ucfirst($s->status),
                ];
            });

        return view('reports.sales', compact(
            'totalSales', 'transactionCount', 'averagePerTransaction', 'grossProfit', 'profitMargin',
            'salesGrowth', 'transactionGrowth', 'avgGrowth', 'profitGrowth',
            'totalItemsSold', 'totalDiscount', 'totalTax', 'taxPercentage', 'bestDayFormatted', 'dateRangeText',
            'chartBars', 'dailySales', 'chartLabels', 'chartData', 'paymentMethods', 'categoryBreakdown', 'cashierBreakdown',
            'topProducts', 'recentSales', 'period', 'dateFrom', 'dateTo', 'search'
        ));
    }

    public function exportExcel(Request $request)
    {
        $sales = $this->buildSalesQuery($request)
            ->select(['id', 'user_id', 'invoice_number', 'transaction_date', 'created_at', 'payment_method', 'total_amount'])
            ->with('user:id,name')
            ->get();

        $csv = "No. Nota,Waktu,Kasir,Metode,Total\n";
        foreach ($sales as $sale) {
            $kasir = $sale->user->name ?? '-';
            $waktu = $sale->transaction_date ?? $sale->created_at;
            $rawMethod = strtolower($sale->payment_method ?? 'cash');
            $methodName = match ($rawMethod) {
                'cash' => 'Tunai',
                'qris' => 'QRIS',
                'midtrans' => 'Midtrans',
                default => ucfirst($rawMethod)
            };
            $csv .= "\"{$sale->invoice_number}\",\"{$waktu}\",\"{$kasir}\",\"{$methodName}\",{$sale->total_amount}\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="laporan_penjualan_'.date('Ymd_His').'.csv"');
    }

    public function exportPdf(Request $request)
    {
        $business = auth()->user()->business;
        $sales = $this->buildSalesQuery($request)
            ->select(['id', 'user_id', 'invoice_number', 'transaction_date', 'created_at', 'payment_method', 'total_amount'])
            ->with('user:id,name')
            ->get();
        $totalSales = $sales->sum('total_amount');

        $html = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Laporan Penjualan</title>';
        $html .= '<style>body{font-family:sans-serif;padding:30px;color:#333} table{width:100%;border-collapse:collapse;margin-top:20px} th,td{border:1px solid #ddd;padding:8px;text-align:left;font-size:12px} th{background:#f4f4f4} h2{margin-bottom:5px}</style>';
        $html .= '</head><body>';
        $html .= '<h2>Laporan Penjualan - '.($business->name ?? 'POS').'</h2>';
        $html .= '<p style="font-size:12px;color:#666">Dicetak pada: '.date('d M Y H:i').' | Total Transaksi: '.$sales->count().'</p>';
        $html .= '<table><thead><tr><th>No. Nota</th><th>Waktu</th><th>Kasir</th><th>Metode</th><th style="text-align:right">Total</th></tr></thead><tbody>';
        foreach ($sales as $sale) {
            $rawMethod = strtolower($sale->payment_method ?? 'cash');
            $methodName = match ($rawMethod) {
                'cash' => 'Tunai',
                'qris' => 'QRIS',
                'midtrans' => 'Midtrans',
                default => ucfirst($rawMethod)
            };
            $html .= '<tr><td>'.$sale->invoice_number.'</td><td>'.($sale->transaction_date ?? $sale->created_at).'</td><td>'.($sale->user->name ?? '-').'</td><td>'.strtoupper($methodName).'</td><td style="text-align:right">Rp '.number_format($sale->total_amount, 0, ',', '.').'</td></tr>';
        }
        $html .= '</tbody><tfoot><tr><th colspan="4" style="text-align:right">TOTAL KESELURUHAN:</th><th style="text-align:right">Rp '.number_format($totalSales, 0, ',', '.').'</th></tr></tfoot></table>';
        $html .= '<script>window.onload = function() { window.print(); }</script></body></html>';

        return response($html)->header('Content-Type', 'text/html');
    }
}

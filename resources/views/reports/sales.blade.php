@extends('layouts.pos')

@section('topbar-search')
    <x-topbar-search :action="route('reports.sales')" placeholder="Cari No. Nota..." />
@endsection

@section('content')
<div class="p-6">
    <div class="flex justify-between items-start mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Laporan Penjualan</h1>
            <p class="text-gray-500 text-sm">Ringkasan performa bisnis Anda</p>
        </div>
        <div class="flex gap-2">
            <select class="border border-gray-300 rounded-lg text-sm px-3 py-2 bg-white">
                <option>Semua Cabang</option>
            </select>
            <a href="{{ route('reports.sales.export-excel', request()->query()) }}" class="pos-btn-secondary flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Ekspor Excel
            </a>
            <a href="{{ route('reports.sales.export-pdf', request()->query()) }}" class="pos-btn-primary flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Ekspor PDF
            </a>
        </div>
    </div>

    <!-- Tabs -->
    <div class="border-b border-gray-200 mb-6">
        <nav class="-mb-px flex space-x-8">
            <a href="?period=today" class="{{ ($period ?? 'today') == 'today' ? 'border-teal-700 text-teal-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                Hari ini
            </a>
            <a href="?period=7days" class="{{ ($period ?? 'today') == '7days' ? 'border-teal-700 text-teal-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                7 hari terakhir
            </a>
            <a href="?period=month" class="{{ ($period ?? 'today') == 'month' ? 'border-teal-700 text-teal-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                Bulan ini
            </a>
        </nav>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="pos-card p-5">
            <div class="text-sm font-medium text-gray-500 mb-1">Total Penjualan</div>
            <div class="text-2xl font-bold text-gray-900">Rp {{ number_format($totalSales ?? 0, 0, ',', '.') }}</div>
        </div>
        <div class="pos-card p-5">
            <div class="text-sm font-medium text-gray-500 mb-1">Jumlah Transaksi</div>
            <div class="text-2xl font-bold text-gray-900">{{ $transactionCount ?? 0 }}</div>
        </div>
        <div class="pos-card p-5">
            <div class="text-sm font-medium text-gray-500 mb-1">Rata-rata per Transaksi</div>
            <div class="text-2xl font-bold text-gray-900">Rp {{ number_format($averagePerTransaction ?? 0, 0, ',', '.') }}</div>
        </div>
        <div class="pos-card p-5">
            <div class="text-sm font-medium text-gray-500 mb-1">Laba Kotor</div>
            <div class="text-2xl font-bold text-teal-700">Rp {{ number_format($grossProfit ?? 0, 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Chart -->
        <div class="lg:col-span-2 pos-card">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Penjualan per Hari</h3>
            <canvas id="salesReportChart" height="100"></canvas>
        </div>

        <!-- Payment Methods -->
        <div class="pos-card">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Metode Pembayaran</h3>
            <div class="space-y-4">
                @forelse($paymentMethods ?? [] as $pm)
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="font-medium text-gray-700">{{ ucfirst($pm->payment_method) }} ({{ $pm->count }}x)</span>
                        <span class="font-bold text-gray-900">Rp {{ number_format($pm->total, 0, ',', '.') }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-teal-600 h-2 rounded-full" style="width: {{ $totalSales > 0 ? ($pm->total / $totalSales * 100) : 0 }}%"></div>
                    </div>
                </div>
                @empty
                <div class="text-sm text-gray-400 py-4 text-center">Belum ada transaksi.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Top Products Table -->
    <div class="pos-card overflow-hidden p-0">
        <div class="p-4 border-b border-gray-200">
            <h3 class="text-lg font-bold text-gray-900">Produk Terlaris</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th scope="col" class="px-6 py-3">Produk</th>
                        <th scope="col" class="px-6 py-3">Terjual</th>
                        <th scope="col" class="px-6 py-3">Pendapatan</th>
                        <th scope="col" class="px-6 py-3">Margin</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topProducts ?? [] as $product)
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $product->name }}</td>
                        <td class="px-6 py-4">{{ $product->sold_qty }}</td>
                        <td class="px-6 py-4">Rp {{ number_format($product->revenue, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-teal-700 font-bold">{{ $product->margin }}%</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center py-6 text-gray-400">Tidak ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('salesReportChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($chartLabels ?? []) !!},
                    datasets: [{
                        label: 'Penjualan',
                        data: {!! json_encode($chartData ?? []) !!},
                        backgroundColor: '#0f766e',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                                }
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
@endsection

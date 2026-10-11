@extends('layouts.pos')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
            <p class="text-gray-500 text-sm">Selamat datang kembali, {{ auth()->user()->name }}</p>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-teal-700 rounded-xl p-6 text-white shadow-sm">
            <div class="text-teal-100 text-sm font-medium mb-1">Penjualan Hari Ini</div>
            <div class="text-3xl font-bold">Rp {{ number_format($todaySales ?? 0, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
            <div class="text-gray-500 text-sm font-medium mb-1">Transaksi Hari Ini</div>
            <div class="text-3xl font-bold text-gray-900">{{ $todayTransactions ?? 0 }}</div>
        </div>
        <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
            <div class="text-gray-500 text-sm font-medium mb-1">Rata-rata Transaksi</div>
            <div class="text-3xl font-bold text-gray-900">Rp {{ number_format($averageTransaction ?? 0, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
            <div class="text-gray-500 text-sm font-medium mb-1">Produk Stok Rendah</div>
            <div class="text-3xl font-bold {{ ($lowStockCount ?? 0) > 0 ? 'text-red-600' : 'text-gray-900' }}">{{ $lowStockCount ?? 0 }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Chart -->
        <div class="lg:col-span-2 pos-card">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Penjualan 7 Hari Terakhir</h3>
            <canvas id="salesChart" height="100"></canvas>
        </div>

        <!-- Right Column -->
        <div class="space-y-6">
            <!-- Top Products -->
            <div class="pos-card">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Produk Terlaris</h3>
                <div class="space-y-4">
                    @forelse($topProducts ?? [] as $product)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center font-bold">
                                {{ substr($product->name, 0, 1) }}
                            </div>
                            <div>
                                <div class="font-medium text-sm text-gray-900">{{ $product->name }}</div>
                                <div class="text-xs text-gray-500">{{ $product->sold_qty }} terjual</div>
                            </div>
                        </div>
                        <div class="font-medium text-sm">Rp {{ number_format($product->revenue, 0, ',', '.') }}</div>
                    </div>
                    @empty
                    <div class="text-center text-sm text-gray-500 py-4">Belum ada data penjualan</div>
                    @endforelse
                </div>
            </div>

            <!-- Low Stock -->
            <div class="pos-card">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex justify-between items-center">
                    <span>Peringatan Stok</span>
                    <span class="text-xs bg-red-100 text-red-700 px-2 py-1 rounded-full font-medium">{{ $lowStockCount ?? 0 }}</span>
                </h3>
                <div class="space-y-3">
                    @forelse($lowStockProducts ?? [] as $product)
                    <div class="flex items-center justify-between bg-red-50 p-3 rounded-lg border border-red-100">
                        <div>
                            <div class="font-medium text-sm text-red-900">{{ $product->name }}</div>
                            <div class="text-xs text-red-700">SKU: {{ $product->sku }}</div>
                        </div>
                        <div class="text-right">
                            <div class="font-bold text-sm text-red-700">{{ $product->stock }}</div>
                            <div class="text-xs text-red-500">Sisa</div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-sm text-gray-500 py-4">Semua stok aman</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('salesChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($chartLabels ?? ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min']) !!},
                    datasets: [{
                        label: 'Penjualan',
                        data: {!! json_encode($chartData ?? [0,0,0,0,0,0,0]) !!},
                        borderColor: '#0f766e',
                        backgroundColor: 'rgba(15, 118, 110, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false }
                    },
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

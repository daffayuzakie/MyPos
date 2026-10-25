@extends('layouts.pos')

@section('content')
<div class="space-y-5 md:space-y-6" x-data="{ showCustomModal: false, init() { this.$watch('showCustomModal', val => { if(window.Alpine && Alpine.store('modal')) Alpine.store('modal').open = Boolean(val); }); } }">

    <!-- Filter Buttons & Export Actions (Matching Laporan.png & Laporan Â· Mobile.png) -->
    <div class="flex flex-col gap-3">
        <!-- Filter Controls & Date Range -->
        <div class="flex flex-wrap items-center justify-between gap-2.5">
            <div class="flex flex-wrap items-center gap-2">
                <a href="?period=today" 
                   class="{{ ($period ?? 'month') === 'today' ? 'bg-[#4D869C] text-white font-bold shadow-xs' : 'bg-[#CDE8E5] text-[#1D3B47] hover:bg-[#b8ded9] font-bold' }} px-5 py-2 rounded-full text-xs sm:text-sm transition-all">
                    Hari ini
                </a>
                <a href="?period=7days" 
                   class="{{ ($period ?? 'month') === '7days' ? 'bg-[#4D869C] text-white font-bold shadow-xs' : 'bg-[#CDE8E5] text-[#1D3B47] hover:bg-[#b8ded9] font-bold' }} px-5 py-2 rounded-full text-xs sm:text-sm transition-all">
                    7 hari
                </a>
                <a href="?period=month" 
                   class="{{ ($period ?? 'month') === 'month' ? 'bg-[#4D869C] text-white font-bold shadow-xs' : 'bg-[#CDE8E5] text-[#1D3B47] hover:bg-[#b8ded9] font-bold' }} px-5 py-2 rounded-full text-xs sm:text-sm transition-all">
                    Bulan ini
                </a>
                <button type="button" @click="showCustomModal = true" 
                        class="{{ ($period ?? 'month') === 'custom' ? 'bg-[#4D869C] text-white font-bold shadow-xs' : 'bg-[#CDE8E5] text-[#1D3B47] hover:bg-[#b8ded9] font-bold' }} px-5 py-2 rounded-full text-xs sm:text-sm transition-all">
                    Kustom
                </button>
            </div>

            <!-- Date Range, Branch, & Exports on Desktop / Mobile wrap -->
            <div class="flex flex-wrap items-center gap-2">
                <span class="bg-[#CDE8E5] text-[#1D3B47] font-semibold px-4 py-2 rounded-full text-xs sm:text-sm">
                    {{ $dateRangeText }}
                </span>
                <span class="bg-[#CDE8E5] text-[#1D3B47] font-semibold px-4 py-2 rounded-full text-xs sm:text-sm">
                    Semua cabang
                </span>
                <a href="{{ route('reports.sales.export-excel', request()->query()) }}" 
                   class="bg-[#CDE8E5] hover:bg-[#b8ded9] text-[#1D3B47] font-semibold px-5 py-2 rounded-full text-xs sm:text-sm transition-all">
                    Excel
                </a>
                <a href="{{ route('reports.sales.export-pdf', request()->query()) }}" 
                   class="bg-[#4D869C] hover:bg-[#3D6B7D] text-white font-bold px-5 py-2 rounded-full text-xs sm:text-sm transition-all shadow-xs">
                    PDF
                </a>
            </div>
        </div>
    </div>

    <!-- 4 Primary Metric Cards (2x2 on Mobile, 1x4 on Desktop) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 md:gap-5">
        
        <!-- Card 1: Total Penjualan (Soft Mint #CDE8E5 Matching Dashboard Card 1) -->
        <div class="bg-[#CDE8E5] rounded-3xl p-5 md:p-6 shadow-xs flex flex-col justify-between min-h-[135px]">
            <div class="text-[#1D3B47]/70 text-xs sm:text-sm font-semibold">Total penjualan</div>
            <div class="text-[#1D3B47] font-black text-xl sm:text-2xl lg:text-3xl tracking-tight mt-1">
                Rp {{ number_format($totalSales, 0, ',', '.') }}
            </div>
            <div class="mt-2">
                <span class="{{ $salesGrowth >= 0 ? 'bg-[#4D869C] text-white' : 'bg-[#d9383a] text-white' }} text-[11px] font-black px-2.5 py-0.5 rounded-full inline-block">
                    {{ $salesGrowth >= 0 ? '+' : '' }}{{ $salesGrowth }}%
                </span>
            </div>
        </div>

        <!-- Card 2: Transaksi -->
        <div class="bg-white border border-[#D6E5ED] rounded-3xl p-5 md:p-6 shadow-xs flex flex-col justify-between min-h-[135px]">
            <div class="text-[#75929E] text-xs sm:text-sm font-medium">Transaksi</div>
            <div class="text-[#1D3B47] font-black text-2xl sm:text-3xl lg:text-4xl tracking-tight mt-1">
                {{ $transactionCount }}
            </div>
            <div class="mt-2">
                <span class="{{ $transactionGrowth >= 0 ? 'bg-[#CDE8E5] text-[#1D3B47]' : 'bg-[#d9383a] text-white' }} text-[11px] font-black px-2.5 py-0.5 rounded-full inline-block">
                    {{ $transactionGrowth >= 0 ? '+' : '' }}{{ $transactionGrowth }}
                </span>
            </div>
        </div>

        <!-- Card 3: Rata-rata transaksi -->
        <div class="bg-white border border-[#D6E5ED] rounded-3xl p-5 md:p-6 shadow-xs flex flex-col justify-between min-h-[135px]">
            <div class="text-[#75929E] text-xs sm:text-sm font-medium">Rata-rata transaksi</div>
            <div class="text-[#1D3B47] font-black text-xl sm:text-2xl lg:text-3xl tracking-tight mt-1">
                Rp {{ number_format($averagePerTransaction, 0, ',', '.') }}
            </div>
            <div class="mt-2">
                <span class="{{ $avgGrowth >= 0 ? 'bg-[#CDE8E5] text-[#1D3B47]' : 'bg-[#d9383a] text-white' }} text-[11px] font-black px-2.5 py-0.5 rounded-full inline-block">
                    {{ $avgGrowth >= 0 ? '+' : '' }}{{ $avgGrowth }}%
                </span>
            </div>
        </div>

        <!-- Card 4: Laba kotor -->
        <div class="bg-white border border-[#D6E5ED] rounded-3xl p-5 md:p-6 shadow-xs flex flex-col justify-between min-h-[135px]">
            <div class="text-[#75929E] text-xs sm:text-sm font-medium">Laba kotor</div>
            <div class="text-[#1D3B47] font-black text-xl sm:text-2xl lg:text-3xl tracking-tight mt-1">
                Rp {{ number_format($grossProfit, 0, ',', '.') }}
            </div>
            <div class="mt-2 flex flex-wrap items-center gap-1.5">
                <span class="{{ $profitGrowth >= 0 ? 'bg-[#CDE8E5] text-[#1D3B47]' : 'bg-[#d9383a] text-white' }} text-[11px] font-black px-2.5 py-0.5 rounded-full inline-block">
                    {{ $profitGrowth >= 0 ? '+' : '' }}{{ $profitGrowth }}%
                </span>
                <span class="bg-[#CDE8E5] text-[#1D3B47] text-[11px] font-black px-2.5 py-0.5 rounded-full inline-block">
                    Margin {{ $profitMargin }}%
                </span>
            </div>
        </div>

    </div>

    <!-- Secondary Metrics Summary Card (Row 2: Item terjual, Diskon, Pajak, Hari terbaik) -->
    <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-xs">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-0 md:divide-x divide-gray-300/80">
            <!-- Item terjual -->
            <div class="md:px-4 first:pl-0">
                <div class="text-xs text-[#75929E] font-medium">Item terjual</div>
                <div class="text-xl sm:text-2xl font-black text-[#1D3B47] mt-1">
                    {{ $totalItemsSold }}
                </div>
            </div>
            <!-- Diskon -->
            <div class="md:px-4">
                <div class="text-xs text-[#75929E] font-medium">Diskon</div>
                <div class="text-xl sm:text-2xl font-black text-[#1D3B47] mt-1">
                    Rp {{ number_format($totalDiscount, 0, ',', '.') }}
                </div>
            </div>
            <!-- Pajak (mengikuti pengaturan usaha) -->
            <div class="md:px-4">
                <div class="text-xs text-[#75929E] font-medium">
                    Pajak{{ $taxPercentage > 0 ? ' '.rtrim(rtrim(number_format($taxPercentage, 2, ',', '.'), '0'), ',').'%' : '' }}
                </div>
                <div class="text-xl sm:text-2xl font-black text-[#1D3B47] mt-1">
                    Rp {{ number_format($totalTax, 0, ',', '.') }}
                </div>
            </div>
            <!-- Hari terbaik -->
            <div class="md:px-4 last:pr-0">
                <div class="text-xs text-[#75929E] font-medium">Hari terbaik</div>
                <div class="text-xl sm:text-2xl font-black text-[#1D3B47] mt-1 truncate">
                    {{ $bestDayFormatted }}
                </div>
            </div>
        </div>
    </div>

    <!-- Middle Section 1: Penjualan Harian & Metode Pembayaran -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 md:gap-6 items-stretch">
        
        <!-- Penjualan Harian (Capsule Bar Chart Real Data) -->
        <div class="lg:col-span-8 bg-white rounded-3xl p-6 shadow-xs flex flex-col justify-between min-h-[360px]">
            <h3 class="font-bold text-sm text-[#1D3B47] tracking-tight mb-4">Penjualan harian</h3>

            <!-- Custom Capsule Bar Chart -->
            <div class="w-full overflow-x-auto hide-scrollbar pt-6">
                <div class="h-56 sm:h-64 flex items-end justify-between gap-1.5 sm:gap-3 px-2 sm:px-4 pb-2 w-full min-w-[500px]">
                    @forelse($chartBars as $b)
                        <div class="flex flex-col items-center justify-end h-full flex-1 relative group">
                            @if($b['peak'])
                                <span class="text-[10px] font-bold text-[#1D3B47] mb-1 leading-none">{{ $b['formatted_val'] }}</span>
                                <div class="w-3 sm:w-3.5 rounded-full bg-[#4D869C] transition-all duration-300"
                                     style="height: {{ $b['height'] }}px;"
                                     title="{{ $b['date'] }}: Rp {{ number_format($b['val'], 0, ',', '.') }}"></div>
                            @elseif($b['active'])
                                <div class="w-3 sm:w-3.5 rounded-full bg-[#1D3B47] transition-all duration-300"
                                     style="height: {{ $b['height'] }}px;"
                                     title="{{ $b['date'] }}: Rp {{ number_format($b['val'], 0, ',', '.') }}"></div>
                            @else
                                <div class="w-2 sm:w-3 h-1 bg-gray-300 rounded-full my-1" title="{{ $b['date'] }}: Rp 0"></div>
                            @endif

                            <span class="text-[10px] sm:text-xs font-semibold text-gray-400 mt-3 h-4 flex items-center justify-center">
                                {{ $b['show_label'] ? $b['day'] : '' }}
                            </span>
                        </div>
                    @empty
                        <div class="w-full text-center py-12 text-gray-400 text-xs font-medium">
                            Tidak ada data penjualan pada rentang tanggal ini.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Metode Pembayaran Breakdown Card (Real Data) -->
        <div class="lg:col-span-4 bg-white rounded-3xl p-6 shadow-xs flex flex-col justify-between min-h-[360px]">
            <div>
                <h3 class="font-bold text-sm text-[#1D3B47] tracking-tight mb-5">Metode pembayaran</h3>

                <div class="space-y-5">
                    @forelse($paymentMethods as $pm)
                    <div>
                        <div class="flex items-center justify-between text-xs font-bold text-[#1D3B47] mb-1.5">
                            <span>{{ $pm->method_name ?? ucfirst($pm->payment_method) }} <span class="text-[#75929E] font-normal ml-0.5">{{ $pm->count }}x</span></span>
                            <span>Rp {{ number_format($pm->total, 0, ',', '.') }} <span class="text-[#75929E] font-normal ml-1">{{ $pm->percentage }}%</span></span>
                        </div>
                        <div class="w-full bg-gray-300 rounded-full h-2.5 overflow-hidden">
                            <div class="bg-[#1D3B47] h-2.5 rounded-full transition-all duration-500" 
                                 style="width: {{ $pm->percentage }}%"></div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-12 text-gray-400 text-xs font-medium">
                        Belum ada metode pembayaran tercatat pada periode ini
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

    <!-- Middle Section 2: Per Kategori & Per Kasir (Side by Side 2 Columns - Real Data) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 md:gap-6">
        
        <!-- Card 1: Per Kategori -->
        <div class="bg-white rounded-3xl p-6 shadow-xs flex flex-col justify-between">
            <div>
                <h3 class="font-bold text-sm text-[#1D3B47] tracking-tight mb-5">Per kategori</h3>

                <div class="space-y-4">
                    @forelse($categoryBreakdown as $cat)
                    <div>
                        <div class="flex items-center justify-between text-xs font-bold text-[#1D3B47] mb-1.5">
                            <span>{{ $cat->category_name }}</span>
                            <span>Rp {{ number_format($cat->total, 0, ',', '.') }} <span class="text-[#75929E] font-normal ml-1">{{ $cat->percentage }}%</span></span>
                        </div>
                        <div class="w-full bg-gray-300 rounded-full h-2.5 overflow-hidden">
                            <div class="bg-[#1D3B47] h-2.5 rounded-full transition-all duration-500" 
                                 style="width: {{ $cat->percentage }}%"></div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8 text-gray-400 text-xs font-medium">
                        Belum ada penjualan kategori pada tanggal ini
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Card 2: Per Kasir -->
        <div class="bg-white rounded-3xl p-6 shadow-xs flex flex-col justify-between">
            <div>
                <h3 class="font-bold text-sm text-[#1D3B47] tracking-tight mb-5">Per kasir</h3>

                <div class="space-y-4">
                    @forelse($cashierBreakdown as $c)
                    <div>
                        <div class="flex items-center justify-between text-xs font-bold text-[#1D3B47] mb-1.5">
                            <span>{{ $c->cashier_name }} <span class="text-[#75929E] font-normal text-[11px] ml-1">{{ $c->transaction_count }} transaksi</span></span>
                            <span>Rp {{ number_format($c->total, 0, ',', '.') }} <span class="text-[#75929E] font-normal ml-1">{{ $c->percentage }}%</span></span>
                        </div>
                        <div class="w-full bg-gray-300 rounded-full h-2.5 overflow-hidden">
                            <div class="bg-[#1D3B47] h-2.5 rounded-full transition-all duration-500" 
                                 style="width: {{ $c->percentage }}%"></div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8 text-gray-400 text-xs font-medium">
                        Belum ada data transaksi kasir pada tanggal ini
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

    <!-- Bottom Section 1: Produk Terlaris Table Card (Real Data) -->
    <div class="bg-white rounded-3xl p-6 shadow-xs overflow-hidden">
        <h3 class="font-bold text-sm text-[#1D3B47] tracking-tight mb-4">Produk terlaris</h3>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead>
                    <tr class="text-[11px] font-bold text-[#75929E] uppercase tracking-wider pb-3 border-b border-gray-300/60">
                        <th class="pb-3">PRODUK</th>
                        <th class="pb-3 hidden md:table-cell">KATEGORI</th>
                        <th class="pb-3 text-center sm:text-left">TERJUAL</th>
                        <th class="pb-3">PENDAPATAN</th>
                        <th class="pb-3 hidden md:table-cell">LABA</th>
                        <th class="pb-3 text-center hidden md:table-cell">MARGIN</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-300/50">
                    @forelse($topProducts as $index => $product)
                    <tr class="hover:bg-[#EEF7FF] transition-colors">
                        <!-- Rank & Product Name -->
                        <td class="py-3.5 font-bold text-[#1D3B47]">
                            {{ $index + 1 }}. {{ $product->name }}
                        </td>
                        <!-- Kategori (Hidden on mobile) -->
                        <td class="py-3.5 hidden md:table-cell">
                            <span class="bg-gray-200 text-[#1D3B47] px-3 py-1 rounded-full text-xs font-semibold">
                                {{ $product->category_name ?? 'Umum' }}
                            </span>
                        </td>
                        <!-- Terjual -->
                        <td class="py-3.5 text-center sm:text-left font-medium text-[#1D3B47]">
                            {{ $product->sold_qty }}
                        </td>
                        <!-- Pendapatan -->
                        <td class="py-3.5 font-bold text-[#1D3B47]">
                            Rp {{ number_format($product->revenue, 0, ',', '.') }}
                        </td>
                        <!-- Laba (Hidden on mobile) -->
                        <td class="py-3.5 font-medium text-[#1D3B47] hidden md:table-cell">
                            Rp {{ number_format($product->profit, 0, ',', '.') }}
                        </td>
                        <!-- Margin (Hidden on mobile) -->
                        <td class="py-3.5 text-center hidden md:table-cell">
                            <span class="bg-[#CDE8E5] text-[#1D3B47] font-black text-xs px-3 py-1 rounded-full inline-block">
                                {{ number_format($product->margin, 1, ',', '.') }}%
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-xs text-[#75929E] font-medium">
                            Tidak ada produk terjual pada tanggal ini
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bottom Section 2: Transaksi Terbaru Table Card (Real Data) -->
    <div class="bg-white rounded-3xl p-6 shadow-xs overflow-hidden">
        <h3 class="font-bold text-sm text-[#1D3B47] tracking-tight mb-4">Transaksi terbaru</h3>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead>
                    <tr class="text-[11px] font-bold text-[#75929E] uppercase tracking-wider pb-3 border-b border-gray-300/60">
                        <th class="pb-3">NOTA</th>
                        <th class="pb-3 hidden md:table-cell">TANGGAL</th>
                        <th class="pb-3 hidden md:table-cell">KASIR</th>
                        <th class="pb-3 hidden md:table-cell">METODE</th>
                        <th class="pb-3">TOTAL</th>
                        <th class="pb-3 text-center sm:text-right">STATUS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-300/50">
                    @forelse($recentSales as $sale)
                    <tr class="hover:bg-[#EEF7FF] transition-colors">
                        <!-- No. Nota -->
                        <td class="py-3.5 font-bold text-[#1D3B47] font-mono text-xs">
                            #{{ $sale->invoice_number }}
                        </td>
                        <!-- Tanggal (Hidden on mobile) -->
                        <td class="py-3.5 text-xs text-[#75929E] font-medium hidden md:table-cell">
                            {{ $sale->date_formatted }}
                        </td>
                        <!-- Kasir (Hidden on mobile) -->
                        <td class="py-3.5 text-xs text-[#1D3B47] font-medium hidden md:table-cell">
                            {{ $sale->cashier_name }}
                        </td>
                        <!-- Metode (Hidden on mobile) -->
                        <td class="py-3.5 text-xs text-[#1D3B47] font-medium hidden md:table-cell">
                            {{ $sale->payment_method }}
                        </td>
                        <!-- Total -->
                        <td class="py-3.5 font-bold text-[#1D3B47]">
                            Rp {{ number_format($sale->total_amount, 0, ',', '.') }}
                        </td>
                        <!-- Status Badge Lunas -->
                        <td class="py-3.5 text-center sm:text-right">
                            <span class="bg-[#CDE8E5] text-[#1D3B47] font-black text-xs px-3.5 py-1 rounded-full inline-block">
                                {{ $sale->status ?? 'Lunas' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-xs text-[#75929E] font-medium">
                            Tidak ada transaksi penjualan pada tanggal ini
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL FILTER TANGGAL KUSTOM                -->
    <!-- Matching Popup Design                      -->
    <!-- ========================================== -->
    <div x-show="showCustomModal" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center bg-black/60 backdrop-blur-xs p-0 sm:p-4">
        <div @click.away="showCustomModal = false"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-t-[32px] sm:rounded-[32px] p-6 sm:p-8 max-w-sm sm:max-w-md w-full relative shadow-2xl overflow-hidden flex flex-col justify-between">
            
            <!-- Mobile Top Handle Bar -->
            <div class="w-12 h-1.5 bg-gray-300 rounded-full mx-auto mb-3 sm:hidden shrink-0"></div>

            <div class="flex items-center justify-between mb-5 shrink-0">
                <h3 class="text-lg sm:text-xl font-black text-[#1D3B47]">Pilih rentang tanggal</h3>
                <button type="button" @click="showCustomModal = false" 
                        class="w-9 h-9 bg-[#4D869C] hover:bg-[#3D6B7D] text-white rounded-full flex items-center justify-center shrink-0 transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="GET" action="{{ route('reports.sales') }}" id="customDateFilterForm" class="space-y-4">
                <input type="hidden" name="period" value="custom">

                <div>
                    <label class="block text-xs sm:text-sm font-bold text-[#1D3B47] mb-1.5">Dari tanggal</label>
                    <input type="date" name="date_from" value="{{ $dateFrom ?? date('Y-m-01') }}" required
                           class="w-full bg-white border border-[#D6E5ED] rounded-2xl px-4 py-3 text-xs sm:text-sm font-semibold text-[#1D3B47] focus:outline-none focus:ring-2 focus:ring-[#4D869C]">
                </div>

                <div>
                    <label class="block text-xs sm:text-sm font-bold text-[#1D3B47] mb-1.5">Sampai tanggal</label>
                    <input type="date" name="date_to" value="{{ $dateTo ?? date('Y-m-d') }}" required
                           class="w-full bg-white border border-[#D6E5ED] rounded-2xl px-4 py-3 text-xs sm:text-sm font-semibold text-[#1D3B47] focus:outline-none focus:ring-2 focus:ring-[#4D869C]">
                </div>
            </form>

            <!-- Footer Actions -->
            <div class="flex items-center justify-between pt-5 mt-4 border-t border-[#D6E5ED] shrink-0">
                <button type="button" @click="showCustomModal = false" class="text-[#75929E] font-bold hover:text-black text-xs sm:text-sm transition-colors cursor-pointer">
                    Batal
                </button>
                <button type="submit" form="customDateFilterForm" 
                        class="bg-[#4D869C] hover:bg-[#3D6B7D] text-white font-bold px-7 sm:px-8 py-3 rounded-full text-xs sm:text-sm shadow-sm transition-all cursor-pointer">
                    Terapkan
                </button>
            </div>
        </div>
    </div>

</div>
@endsection






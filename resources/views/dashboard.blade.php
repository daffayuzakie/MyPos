@extends('layouts.pos')

@section('content')
@php
    $maxVal = 1;
    if (!empty($chartData)) {
        $maxVal = max(max($chartData), 1);
    }

    if (!function_exists('formatCompactIdr')) {
        function formatCompactIdr($val) {
            if ($val >= 1000000) {
                $num = round($val / 1000000, 1);
                return ((float)$num == (int)$num ? (int)$num : $num) . 'jt';
            } elseif ($val >= 1000) {
                return round($val / 1000) . 'rb';
            } elseif ($val > 0) {
                return number_format($val, 0, ',', '.');
            }
            return '';
        }
    }

    $avatarColors = [
        'bg-[#518e9a] text-white',
        'bg-[#B6D9D5] text-[#1D3B47]',
        'bg-[#406a77] text-white',
        'bg-[#4D869C] text-white',
        'bg-[#a3cbcf] text-[#1D3B47]',
    ];
@endphp

<div class="space-y-5 md:space-y-6">

    <!-- 4 Summary Metric Cards (2x2 on Mobile, 1x4 on Desktop) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 md:gap-5">
        
        <!-- Card 1: Penjualan Hari Ini (Light Soft Mint #CDE8E5) -->
        <div class="bg-[#CDE8E5] rounded-3xl p-5 md:p-6 shadow-xs flex flex-col justify-between min-h-[125px]">
            <div class="text-[#1D3B47]/70 text-xs sm:text-sm font-semibold">Penjualan hari ini</div>
            <div class="text-[#1D3B47] font-black text-xl sm:text-2xl lg:text-3xl xl:text-4xl tracking-tight mt-2">
                Rp {{ number_format($todaySales ?? 0, 0, ',', '.') }}
            </div>
        </div>

        <!-- Card 2: Transaksi -->
        <div class="bg-white border border-[#D6E5ED] rounded-3xl p-5 md:p-6 shadow-xs flex flex-col justify-between min-h-[125px]">
            <div class="text-[#1D3B47]/70 text-xs sm:text-sm font-semibold">Transaksi</div>
            <div class="text-[#1D3B47] font-black text-2xl sm:text-3xl lg:text-4xl tracking-tight mt-2">
                {{ $todayTransactions ?? 0 }}
            </div>
        </div>

        <!-- Card 3: Rata-rata -->
        <div class="bg-white border border-[#D6E5ED] rounded-3xl p-5 md:p-6 shadow-xs flex flex-col justify-between min-h-[125px]">
            <div class="text-[#1D3B47]/70 text-xs sm:text-sm font-semibold">Rata-rata</div>
            <div class="text-[#1D3B47] font-black text-xl sm:text-2xl lg:text-3xl xl:text-4xl tracking-tight mt-2">
                Rp {{ number_format($averageTransaction ?? 0, 0, ',', '.') }}
            </div>
        </div>

        <!-- Card 4: Stok Rendah (Red accent #d9383a) -->
        <div class="bg-white border border-[#D6E5ED] rounded-3xl p-5 md:p-6 shadow-xs flex flex-col justify-between min-h-[125px]">
            <div class="text-[#1D3B47]/70 text-xs sm:text-sm font-semibold">Stok rendah</div>
            <div class="text-[#d9383a] font-black text-2xl sm:text-3xl lg:text-4xl tracking-tight mt-2">
                {{ $lowStockCount ?? 0 }}
            </div>
        </div>

    </div>

    <!-- Main Content Section: Left Chart & Right Stats -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 md:gap-6 items-start">
        
        <!-- Left Container: Bar Chart Card (7 hari terakhir) -->
        <div class="lg:col-span-7 xl:col-span-8 bg-white border border-[#D6E5ED] rounded-3xl p-5 sm:p-6 md:p-8 shadow-xs flex flex-col justify-between min-h-[440px]">
            
            <!-- Card Header: Title & Period Filters -->
            <div class="flex items-start justify-between gap-2 mb-6">
                <div>
                    <h3 class="font-black text-lg md:text-xl text-[#1D3B47] leading-tight tracking-tight">
                        @if($period === 'today')
                            Penjualan Hari Ini
                        @elseif($period === 'month')
                            Penjualan Bulan Ini
                        @else
                            <span class="sm:hidden">7 hari<br>terakhir</span>
                            <span class="hidden sm:inline">7 hari terakhir</span>
                            <span class="sr-only">Penjualan 7 Hari Terakhir</span>
                        @endif
                    </h3>
                </div>
                <div class="flex flex-wrap items-center justify-end gap-1.5 sm:gap-2 text-xs md:text-sm">
                    <a href="{{ route('dashboard', ['period' => 'today']) }}" 
                       class="{{ $period === 'today' ? 'bg-[#4D869C] text-white font-bold shadow-xs' : 'bg-[#CDE8E5] text-[#1D3B47] hover:bg-[#b8ded9] font-bold' }} px-4 py-2 rounded-full transition-colors text-xs">
                        Hari ini
                    </a>
                    <a href="{{ route('dashboard', ['period' => '7days']) }}" 
                       class="{{ $period === '7days' ? 'bg-[#4D869C] text-white font-bold shadow-xs' : 'bg-[#CDE8E5] text-[#1D3B47] hover:bg-[#b8ded9] font-bold' }} px-4 py-2 rounded-full transition-colors text-xs">
                        7 hari
                    </a>
                    <a href="{{ route('dashboard', ['period' => 'month']) }}" 
                       class="{{ $period === 'month' ? 'bg-[#4D869C] text-white font-bold shadow-xs' : 'bg-[#CDE8E5] text-[#1D3B47] hover:bg-[#b8ded9] font-bold' }} px-4 py-2 rounded-full transition-colors text-xs">
                        Bulan ini
                    </a>
                </div>
            </div>

            <!-- Custom Modern Bar Chart Area (Matching Mockup with Rounded Capsules & Flat Pills) -->
            <div class="w-full overflow-x-auto hide-scrollbar pt-6">
                <div class="h-64 sm:h-72 flex items-end justify-between gap-2 sm:gap-4 px-1 sm:px-4 pb-2 {{ count($chartLabels) > 10 ? 'min-w-[680px]' : 'w-full' }}">
                    @foreach($chartLabels as $index => $label)
                        @php
                            $val = (float)($chartData[$index] ?? 0);
                            $shortLabel = explode(' ', $label)[0];
                            // Height scales from min 48px to max 195px
                            $heightPx = $val > 0 ? max(48, round(($val / $maxVal) * 195)) : 0;
                            // Last day or current period day is active primary teal
                            $isToday = ($loop->last && $period === '7days') || ($period === 'today' && $loop->last);
                        @endphp
                        <div class="flex flex-col items-center justify-end h-full flex-1 min-w-[32px]">
                            @if($val > 0)
                                <span class="text-[10px] sm:text-xs font-bold text-[#1D3B47] mb-2 truncate text-center tracking-tight">
                                    {{ formatCompactIdr($val) }}
                                </span>
                                <div class="w-7 sm:w-10 md:w-12 rounded-2xl transition-all duration-300 {{ $isToday ? 'bg-[#4D869C]' : 'bg-[#B6D9D5]' }}"
                                     style="height: {{ $heightPx }}px;"
                                     title="{{ $label }}: Rp {{ number_format($val, 0, ',', '.') }}">
                                </div>
                            @else
                                <div class="h-2 w-6 sm:w-8 bg-[#D6E5ED] rounded-full mx-auto my-1.5" title="{{ $label }}: Rp 0"></div>
                            @endif
                            <span class="text-xs {{ $isToday ? 'font-bold text-[#1D3B47]' : 'font-semibold text-[#75929E]' }} mt-3 truncate text-center">
                                {{ $shortLabel }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

        <!-- Right Container: Terlaris & Peringatan Stok -->
        <div class="lg:col-span-5 xl:col-span-4 flex flex-col gap-5 md:gap-6">
            
            <!-- Terlaris Card -->
            <div class="bg-white border border-[#D6E5ED] rounded-3xl p-5 sm:p-6 shadow-xs">
                <h3 class="font-black text-lg text-[#1D3B47] tracking-tight mb-5">Terlaris</h3>
                
                <div class="space-y-4">
                    @forelse($topProducts ?? [] as $product)
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3.5 min-w-0">
                            @if($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-11 h-11 rounded-2xl object-cover shrink-0 shadow-xs border border-[#D6E5ED]">
                            @else
                            <div class="w-11 h-11 rounded-2xl flex items-center justify-center font-black text-base shrink-0 shadow-xs {{ $avatarColors[$loop->index % count($avatarColors)] }}">
                                {{ strtoupper(substr($product->name, 0, 1)) }}
                            </div>
                            @endif
                            <div class="min-w-0">
                                <h4 class="font-bold text-sm sm:text-base text-[#1D3B47] truncate leading-tight">{{ $product->name }}</h4>
                                <p class="text-xs text-[#75929E] font-medium mt-0.5">{{ $product->sold_qty }} terjual</p>
                            </div>
                        </div>
                        <div class="font-bold text-sm sm:text-base text-[#1D3B47] whitespace-nowrap text-right pl-2">
                            Rp {{ number_format($product->revenue, 0, ',', '.') }}
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-6 text-sm text-[#75929E] font-medium">
                        Belum ada data penjualan
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Peringatan Stok Card -->
            <div class="bg-white border border-[#D6E5ED] rounded-3xl p-5 sm:p-6 shadow-xs">
                <h3 class="font-black text-lg text-[#1D3B47] tracking-tight mb-4">Peringatan stok</h3>
                
                <div class="space-y-3">
                    @forelse($lowStockProducts ?? [] as $product)
                    <div class="bg-white border-l-4 border-[#b91c1c] border-y border-r border-[#D6E5ED] rounded-2xl p-4 flex items-center justify-between text-[#1D3B47] shadow-xs">
                        <div class="flex items-center gap-3 min-w-0 pr-3">
                            @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-10 h-10 rounded-xl object-cover shrink-0 shadow-xs border border-[#D6E5ED]">
                            @else
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center font-black text-sm shrink-0 shadow-xs bg-[#CDE8E5] text-[#1D3B47]">
                                    {{ strtoupper(substr($product->name, 0, 1)) }}
                                </div>
                            @endif
                            <div class="min-w-0">
                                <h4 class="font-bold text-sm sm:text-base text-[#1D3B47] leading-tight truncate">{{ $product->name }}</h4>
                                <p class="text-xs text-[#75929E] font-semibold uppercase tracking-wider mt-0.5 truncate">{{ $product->sku }}</p>
                            </div>
                        </div>
                        <div class="text-[#d9383a] font-black text-2xl sm:text-3xl tracking-tight shrink-0">
                            {{ $product->stock }}
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-6 text-sm text-[#75929E] font-medium">
                        Semua stok aman
                    </div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

</div>
@endsection



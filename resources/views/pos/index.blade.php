<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasir - {{ auth()->user()->business->name ?? 'POS' }}</title>
    <link rel="icon" type="image/png" href="{{ Storage::url('logo mypos.png') }}">
    <link rel="apple-touch-icon" href="{{ Storage::url('logo mypos.png') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @if(!empty($midtransConfigured))
    <script src="{{ $midtransSnapUrl }}" data-client-key="{{ $midtransClientKey }}"></script>
    @endif

    <style>
        body {
            font-family: 'Figtree', sans-serif;
            background-color: #EEF7FF;
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        @media print {
            body * { visibility: hidden; }
            #receipt-print-area, #receipt-print-area * { visibility: visible; }
            #receipt-print-area { position: absolute; left: 0; top: 0; width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="min-h-screen text-[#1D3B47] antialiased selection:bg-[#4D869C] selection:text-white overflow-x-hidden bg-[#EEF7FF]" x-data="cashierPOS()" x-init="init()">

    <!-- OFFLINE BANNER -->
    <div class="fixed top-0 left-0 right-0 z-50 bg-[#d9383a] text-white text-center py-1.5 text-xs font-bold shadow-md transition-all" x-show="!$store.app.isOnline" style="display: none;">
        Mode Offline: Koneksi internet terputus. Data transaksi akan disimpan di antrean lokal.
    </div>

    <!-- MOBILE BACKDROP OVERLAY FOR SIDEBAR -->
    <div x-show="sidebarOpen" 
         @click="sidebarOpen = false" 
         class="fixed inset-0 z-40 bg-black/40 backdrop-blur-xs lg:hidden transition-opacity" 
         x-cloak 
         style="display: none;"></div>

    <!-- COLLAPSIBLE LEFT SIDEBAR NAVBAR (LIGHT MINT CLEAN THEME) -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
           class="fixed top-0 left-0 h-screen w-64 bg-[#CDE8E5] border-r border-[#B6D9D5] z-50 flex flex-col transition-transform duration-300 ease-in-out shadow-xl">
        
        <!-- Sidebar Brand & Close Button -->
        <div class="flex items-center justify-between p-5 border-b border-[#B6D9D5] shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-[#4D869C] flex items-center justify-center text-white font-black text-xl shadow-sm">
                    P
                </div>
                <span class="text-[#1D3B47] font-extrabold text-lg tracking-tight">MyPos</span>
            </div>
            <!-- Toggle / Close Button -->
            <button @click="sidebarOpen = false" class="text-[#1D3B47]/70 hover:text-[#1D3B47] p-1.5 rounded-xl hover:bg-[#B6D9D5] transition-colors" title="Tutup Menu">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 px-4 py-4 space-y-1.5 overflow-y-auto">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3.5 px-4 py-3 text-sm font-semibold text-[#1D3B47] hover:text-[#192d35] hover:bg-[#B6D9D5] rounded-2xl transition-all duration-200">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('pos.index') }}" class="flex items-center gap-3.5 px-4 py-3 text-sm font-bold bg-[#4D869C] text-white rounded-2xl transition-all duration-200 shadow-sm">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span>Kasir</span>
            </a>
            <a href="{{ route('reports.sales') }}" class="flex items-center gap-3.5 px-4 py-3 text-sm font-semibold text-[#1D3B47] hover:text-[#192d35] hover:bg-[#B6D9D5] rounded-2xl transition-all duration-200">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                <span>Laporan</span>
            </a>
            <a href="{{ route('products.index') }}" class="flex items-center gap-3.5 px-4 py-3 text-sm font-semibold text-[#1D3B47] hover:text-[#192d35] hover:bg-[#B6D9D5] rounded-2xl transition-all duration-200">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                <span>Produk & Stok</span>
            </a>
            <a href="{{ route('customers.index') }}" class="flex items-center gap-3.5 px-4 py-3 text-sm font-semibold text-[#1D3B47] hover:text-[#192d35] hover:bg-[#B6D9D5] rounded-2xl transition-all duration-200">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span>Pelanggan</span>
            </a>
            @can('users view')
            <a href="{{ route('users.index') }}" class="flex items-center gap-3.5 px-4 py-3 text-sm font-semibold text-[#1D3B47] hover:text-[#192d35] hover:bg-[#B6D9D5] rounded-2xl transition-all duration-200">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                <span>Karyawan</span>
            </a>
            @endcan
            <a href="{{ route('business.settings') }}" class="flex items-center gap-3.5 px-4 py-3 text-sm font-semibold text-[#1D3B47] hover:text-[#192d35] hover:bg-[#B6D9D5] rounded-2xl transition-all duration-200">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                <span>Pengaturan</span>
            </a>
        </nav>

        <!-- User Profile Dropdown in Sidebar Bottom -->
        <div class="p-4 border-t border-[#B6D9D5] shrink-0">
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" @click.away="open = false" class="w-full flex items-center gap-3 p-2 hover:bg-[#B6D9D5] rounded-2xl transition-colors text-left">
                    <div class="w-8 h-8 rounded-full bg-[#4D869C] text-white flex items-center justify-center font-bold text-xs shrink-0">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-xs font-bold text-[#1D3B47] truncate">{{ auth()->user()->name }}</div>
                        <div class="text-[10px] text-[#75929E] truncate">{{ auth()->user()->roles->first()->name ?? 'Kasir' }}</div>
                    </div>
                    <svg class="w-3.5 h-3.5 text-[#1D3B47]/70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div x-show="open" x-cloak class="absolute bottom-full left-0 w-full mb-2 bg-white rounded-2xl shadow-xl border border-[#D6E5ED] overflow-hidden py-1 z-50">
                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-xs font-semibold text-[#1D3B47] hover:bg-[#EEF7FF]">Profil Pengguna</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full text-left px-4 py-2 text-xs font-semibold text-[#d9383a] hover:bg-[#fdeeee]">Keluar (Logout)</button>
                    </form>
                </div>
            </div>
        </div>
    </aside>

    <!-- MAIN CASHIER CONTAINER -->
    <div :class="sidebarOpen ? 'lg:pl-64' : 'lg:pl-0'" class="transition-all duration-300 ease-in-out min-h-screen lg:h-screen flex flex-col overflow-x-hidden pb-20 lg:pb-0 bg-[#EEF7FF]">
        
        <div class="h-full flex flex-col p-3 sm:p-4 md:p-5 gap-3 sm:gap-4 overflow-hidden">
            
            <!-- TOP HEADER BAR (Seamless with background) -->
            <header class="flex items-center justify-between gap-3 shrink-0 px-1 py-1">
                
                <!-- Left: Hamburger Toggle + Business Info -->
                <div class="flex items-center gap-2.5 sm:gap-3.5 min-w-0">
                    <!-- Hamburger Toggle Button -->
                    <button @click="sidebarOpen = !sidebarOpen" 
                            class="p-2.5 rounded-2xl text-[#1D3B47] hover:text-[#192d35] bg-[#CDE8E5] hover:bg-[#d5e5ec] transition-colors shrink-0" 
                            :title="sidebarOpen ? 'Tutup Menu Navigasi' : 'Buka Menu Navigasi'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>

                    <!-- Brand & Cashier Info -->
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-full bg-[#518e9a] text-white font-black text-sm flex items-center justify-center shrink-0 shadow-sm">
                            {{ strtoupper(substr(auth()->user()->business->name ?? 'WB', 0, 2)) }}
                        </div>
                        <div class="truncate">
                            <h1 class="text-xs sm:text-sm font-black text-[#1D3B47] leading-tight tracking-tight uppercase truncate">
                                {{ auth()->user()->business->name ?? 'WARUNG BERKAH' }}
                            </h1>
                            <p class="text-[11px] text-[#75929E] font-medium truncate">
                                Kasir: {{ auth()->user()->name ?? 'Kasir' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Center: Search Pill Bar -->
                <div class="flex-1 max-w-md hidden sm:block px-2">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#75929E]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" x-model="searchQuery" 
                               @input.debounce.300ms="searchProducts()"
                               @keydown.enter.prevent="handleBarcodeSubmit()"
                               class="w-full pl-10 pr-4 py-2 bg-[#CDE8E5] border-0 rounded-full text-xs text-[#1D3B47] placeholder-[#75929E] focus:outline-none focus:ring-2 focus:ring-[#4D869C] transition-all" 
                               placeholder="Cari produk atau scan barcode...">
                    </div>
                </div>

                <!-- Right: Online Status -->
                <div class="flex items-center gap-2 shrink-0 justify-end">
                    <div class="flex items-center gap-1.5 px-3 py-1.5 bg-[#CDE8E5] rounded-full text-xs font-semibold text-[#1D3B47]">
                        <span class="w-2 h-2 rounded-full" :class="$store.app?.isOnline !== false ? 'bg-[#4D869C]' : 'bg-[#d9383a]'"></span>
                        <span x-text="$store.app?.isOnline !== false ? 'Online' : 'Offline'">Online</span>
                    </div>
                </div>
            </header>

            <!-- Mobile Search Bar (Visible only on small screens) -->
            <div class="sm:hidden px-1">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#75929E]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" x-model="searchQuery" 
                           @input.debounce.300ms="searchProducts()"
                           @keydown.enter.prevent="handleBarcodeSubmit()"
                           class="w-full pl-10 pr-4 py-2 bg-[#CDE8E5] border-0 rounded-full text-xs text-[#1D3B47] placeholder-[#75929E] focus:outline-none focus:ring-2 focus:ring-[#4D869C]" 
                           placeholder="Cari produk...">
                </div>
            </div>

            <!-- MAIN CONTENT WORKSPACE -->
            <div class="flex-1 flex flex-col lg:flex-row gap-4 overflow-hidden">
                
                <!-- LEFT AREA: Kategori + Grid Produk -->
                <div class="flex-1 flex flex-col gap-3 overflow-hidden min-w-0">
                    
                    <!-- Kategori Filter Pills -->
                    <div class="flex items-center gap-2 shrink-0 overflow-x-auto py-1 no-scrollbar">
                        <button @click="activeCategory = 'all'; searchProducts()" 
                                :class="activeCategory === 'all' ? 'bg-[#4D869C] text-white shadow-xs' : 'bg-[#CDE8E5] text-[#1D3B47] hover:bg-[#CDE8E5]'" 
                                class="px-5 py-2 rounded-full text-xs font-bold transition-all shrink-0">
                            Semua
                        </button>
                        <template x-for="category in categories" :key="category.id">
                            <button @click="activeCategory = category.id; searchProducts()" 
                                    :class="activeCategory == category.id ? 'bg-[#4D869C] text-white shadow-xs' : 'bg-[#CDE8E5] text-[#1D3B47] hover:bg-[#CDE8E5]'" 
                                    class="px-5 py-2 rounded-full text-xs font-bold transition-all shrink-0" 
                                    x-text="category.name">
                            </button>
                        </template>
                    </div>

                    <!-- Product Grid (Responsive: 2 cols on mobile, 4 cols on desktop) -->
                    <div class="flex-1 overflow-y-auto pr-1">
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3.5">
                            <template x-for="product in filteredProducts" :key="product.id">
                                <div @click="addToCart(product)" 
                                     class="bg-[#EEF7FF] border border-[#D6E5ED] rounded-3xl p-3 sm:p-3.5 cursor-pointer hover:shadow-md transition-all duration-200 flex flex-col justify-between group select-none relative">
                                    
                                    <!-- Card Kotak Warna Signature dengan Inisial Huruf Besar / Foto Produk -->
                                    <div class="w-full aspect-square rounded-2xl flex items-center justify-center relative transition-transform duration-200 group-hover:scale-[1.02] overflow-hidden"
                                         :class="product.image ? 'bg-white' : getProductBlockStyle(product)">
                                        
                                        <!-- Badge Kuantitas di Keranjang -->
                                        <div x-show="getCartQty(product.id) > 0" 
                                             class="absolute -top-1.5 -right-1.5 w-6 h-6 bg-[#4D869C] text-white border-2 border-white rounded-full flex items-center justify-center text-xs font-black shadow-md z-10" 
                                             x-text="getCartQty(product.id)">
                                        </div>
                                        
                                        <!-- Foto Produk jika ada -->
                                        <template x-if="product.image">
                                             <img :src="'/storage/' + product.image" :alt="product.name" class="w-full h-full object-cover rounded-2xl">
                                        </template>

                                        <!-- Inisial Huruf Besar jika tidak ada foto -->
                                        <template x-if="!product.image">
                                            <span class="text-3xl sm:text-4xl font-black tracking-tight select-none" 
                                                  x-text="product.name.charAt(0).toUpperCase()">
                                            </span>
                                        </template>
                                    </div>

                                    <!-- Info Produk di Bawah Kotak Warna -->
                                    <div class="mt-2.5">
                                        <h3 class="font-bold text-[#1D3B47] text-xs sm:text-sm leading-tight truncate" x-text="product.name"></h3>
                                        <div class="flex justify-between items-baseline mt-1 gap-1">
                                            <p class="text-xs sm:text-sm font-black text-[#1D3B47]" x-text="formatPrice(product.selling_price)"></p>
                                            <span class="text-[11px] font-semibold" 
                                                  :class="product.stock <= 5 ? 'text-[#d9383a] font-bold' : 'text-[#75929E]'" 
                                                  x-text="'Stok: ' + product.stock"></span>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <!-- Empty State -->
                            <div x-show="filteredProducts.length === 0" class="col-span-full py-16 text-center text-[#75929E]">
                                <svg class="w-12 h-12 mx-auto text-[#75929E]/50 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                <p class="text-xs font-semibold">Tidak ada produk ditemukan</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT AREA: Panel Pesanan / Cart (Matching Soft Ice-Blue Clean Card) -->
                <div class="hidden lg:flex lg:w-[380px] xl:w-[400px] bg-[#EEF7FF] border border-[#D6E5ED] rounded-3xl p-5 sm:p-6 text-[#1D3B47] shadow-xl flex-col shrink-0 justify-between lg:max-h-full">
                    
                    <!-- Header Pesanan & List Items -->
                    <div class="flex flex-col overflow-hidden flex-1">
                        <!-- Title & Nota Number -->
                        <div class="flex justify-between items-baseline mb-3 shrink-0">
                            <h2 class="text-base sm:text-lg font-black text-[#1D3B47]">Pesanan</h2>
                            <span class="text-xs text-[#75929E] font-mono" x-text="'#' + invoiceNumber"></span>
                        </div>

                        <!-- Customer Indicator Pill -->
                        <div class="mb-3 shrink-0 py-2.5 px-3.5 bg-[#CDE8E5] rounded-2xl flex justify-between items-center text-xs">
                            <span class="text-[#75929E] font-medium">Pelanggan:</span>
                            <div class="flex items-center gap-1.5">
                                <button @click="showCustomerModal = true" class="font-bold text-[#4D869C] hover:underline" x-text="selectedCustomer ? selectedCustomer.name : 'Umum / Pilih'"></button>
                                <button x-show="selectedCustomer" @click="selectedCustomer = null" class="text-[#d9383a] font-bold hover:text-black">&times;</button>
                            </div>
                        </div>

                        <!-- Cart Item Rows -->
                        <div class="flex-1 overflow-y-auto space-y-3 pr-1">
                            <template x-for="(item, index) in cart" :key="item.id">
                                <div class="flex items-center justify-between gap-2 py-1 border-b border-[#D6E5ED] pb-2">
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-xs font-bold text-[#1D3B47] leading-tight truncate" x-text="item.name"></h4>
                                        <p class="text-[11px] text-[#75929E] mt-0.5" x-text="formatPrice(item.selling_price)"></p>
                                    </div>

                                    <!-- Selector Quantity: [ - ]  1  [ + ] -->
                                    <div class="flex items-center bg-[#CDE8E5] border border-[#cbdfe7] rounded-xl p-0.5 shrink-0">
                                        <button @click="decreaseQty(index)" class="w-6 h-6 flex items-center justify-center text-[#1D3B47] hover:bg-[#d5e5ec] rounded-lg font-bold text-xs transition-colors">-</button>
                                        <span class="w-6 text-center text-xs font-black text-[#4D869C]" x-text="item.qty"></span>
                                        <button @click="increaseQty(index)" class="w-6 h-6 flex items-center justify-center text-[#1D3B47] hover:bg-[#d5e5ec] rounded-lg font-bold text-xs transition-colors">+</button>
                                    </div>

                                    <!-- Total Item -->
                                    <div class="text-xs font-black text-[#1D3B47] w-16 text-right shrink-0" x-text="formatPrice(item.selling_price * item.qty)"></div>
                                </div>
                            </template>

                            <!-- Empty Cart State -->
                            <div x-show="cart.length === 0" class="h-28 flex flex-col items-center justify-center text-[#75929E]">
                                <p class="text-xs font-medium">Belum ada item di keranjang</p>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Summary, Metode Pembayaran, & Action Buttons -->
                    <div class="shrink-0 pt-3 border-t border-[#D6E5ED] space-y-3">
                        
                        <!-- Subtotal, Diskon, PPN -->
                        <div class="space-y-1.5 text-xs">
                            <div class="flex justify-between text-[#75929E] font-medium">
                                <span>Subtotal</span>
                                <span class="text-[#1D3B47] font-semibold" x-text="formatPrice(subtotal)"></span>
                            </div>
                            
                            <!-- Diskon (Clickable) -->
                            <div class="flex justify-between text-[#75929E] font-medium cursor-pointer hover:text-[#4D869C] transition-colors" 
                                 @click="showDiscountModal = true">
                                <span class="flex items-center gap-1">
                                    <span>Diskon</span>
                                    <svg class="w-3 h-3 text-[#4D869C]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </span>
                                <span :class="discountAmount > 0 ? 'text-[#d9383a] font-bold' : 'text-[#1D3B47]'" 
                                      x-text="discountAmount > 0 ? '- ' + formatPrice(discountAmount) : 'Rp 0'"></span>
                            </div>

                            <div class="flex justify-between text-[#75929E] font-medium">
                                <span x-text="'PPN ' + taxPercentage + '%'"></span>
                                <span class="text-[#1D3B47] font-semibold" x-text="formatPrice(taxAmount)"></span>
                            </div>
                        </div>

                        <!-- Total in Huge Blue-Teal Font -->
                        <div class="flex justify-between items-baseline pt-2 border-t border-[#D6E5ED]">
                            <span class="text-xs sm:text-sm font-bold text-[#75929E]">Total</span>
                            <span class="text-2xl sm:text-3xl font-black text-[#4D869C] tracking-tight" x-text="formatPrice(totalAmount)"></span>
                        </div>

                        <!-- METODE PEMBAYARAN -->
                        <div>
                            <span class="block text-[10px] font-bold text-[#75929E] uppercase tracking-wider mb-2">METODE PEMBAYARAN</span>
                            <div class="grid grid-cols-2 gap-2">
                                <!-- Tunai -->
                                <button @click="paymentMethod = 'cash'" 
                                        :class="paymentMethod === 'cash' ? 'bg-[#4D869C] text-white font-black shadow-xs' : 'bg-[#CDE8E5] text-[#1D3B47] hover:bg-[#d5e5ec] font-bold'" 
                                        class="rounded-2xl py-2.5 text-xs text-center transition-all duration-200">
                                    Tunai
                                </button>
                                <!-- Non Tunai (Midtrans) -->
                                <button @click="paymentMethod = 'midtrans'" 
                                        :class="paymentMethod === 'midtrans' ? 'bg-[#4D869C] text-white font-black shadow-xs' : 'bg-[#CDE8E5] text-[#1D3B47] hover:bg-[#d5e5ec] font-bold'" 
                                        class="rounded-2xl py-2.5 text-xs text-center transition-all duration-200">
                                    Non Tunai
                                </button>
                            </div>
                        </div>

                        <!-- TOMBOL ACTION: Batal | Tahan | Bayar -->
                        <div class="flex items-center gap-2 pt-1">
                            <button @click="clearCart()" 
                                    :disabled="cart.length === 0"
                                    class="px-4 py-3 bg-[#CDE8E5] hover:bg-[#d5e5ec] text-[#1D3B47] rounded-full text-xs font-bold disabled:opacity-40 disabled:cursor-not-allowed transition-all duration-200">
                                Batal
                            </button>
                            
                            <button @click="cart.length > 0 ? holdCurrentTransaction() : showHeldModal = true" 
                                    class="px-4 py-3 bg-[#CDE8E5] hover:bg-[#d5e5ec] text-[#1D3B47] rounded-full text-xs font-bold flex items-center gap-1.5 transition-all duration-200 relative">
                                <span>Tahan</span>
                                <span x-show="heldCount > 0" class="px-1.5 py-0.2 bg-[#4D869C] text-white rounded-full text-[10px] font-black" x-text="heldCount"></span>
                            </button>
                            
                            <button @click="startPayment()" 
                                    :disabled="cart.length === 0 || isProcessing" 
                                    class="flex-1 py-3.5 bg-[#4D869C] hover:bg-[#3D6B7D] text-white rounded-full text-sm font-black shadow-md disabled:opacity-40 disabled:cursor-not-allowed transition-all duration-200 flex items-center justify-center gap-2">
                                <span x-show="isProcessing">Memproses...</span>
                                <span x-show="!isProcessing">Bayar &rarr;</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MOBILE BOTTOM NAVIGATION (Fixed at bottom on < lg screens) -->
    <div x-show="!$store.modal.open" x-cloak class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-[#CDE8E5] border-t border-[#B6D9D5] px-3 py-2 flex items-center justify-around text-[#1D3B47] shadow-lg transition-all">
        <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-1 text-[10px] font-medium hover:text-black">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            <span>Dashboard</span>
        </a>
        <a href="{{ route('pos.index') }}" class="flex flex-col items-center gap-1 text-[10px] font-black text-[#4D869C]">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            <span>Kasir</span>
        </a>
        <a href="{{ route('products.index') }}" class="flex flex-col items-center gap-1 text-[10px] font-medium hover:text-black">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
            <span>Produk</span>
        </a>
        <a href="{{ route('reports.sales') }}" class="flex flex-col items-center gap-1 text-[10px] font-medium hover:text-black">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            <span>Laporan</span>
        </a>
        <button @click="sidebarOpen = true" class="flex flex-col items-center gap-1 text-[10px] font-medium hover:text-black">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            <span>Lainnya</span>
        </button>
    </div>

        <!-- FLOATING CART BUTTON (Mobile only, shown when items in cart and modal/drawer closed) -->
    <div x-show="!showMobileCart && !$store.modal.open && cart.length > 0" 
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         class="lg:hidden fixed bottom-18 left-4 right-4 z-40">
        <button @click="showMobileCart = true" 
                class="w-full bg-[#4D869C] hover:bg-[#3D6B7D] text-white py-3.5 px-5 rounded-2xl shadow-xl flex items-center justify-between font-bold text-sm transition-all active:scale-[0.98]">
            <div class="flex items-center gap-2.5">
                <span class="w-7 h-7 rounded-full bg-white text-[#4D869C] flex items-center justify-center text-xs font-black" x-text="cart.reduce((sum, item) => sum + item.qty, 0)"></span>
                <span>Lihat Keranjang</span>
            </div>
            <span class="text-base font-black" x-text="formatPrice(totalAmount)"></span>
        </button>
    </div>

    <!-- FULLSCREEN / SHEET KERANJANG MOBILE (Matching Keranjang Mobile.png) -->
    <div x-show="showMobileCart" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="lg:hidden fixed inset-0 z-50 bg-[#EEF7FF] flex flex-col justify-between overflow-hidden">
        
        <!-- Header Keranjang Mobile -->
        <div class="flex items-center justify-between p-4 bg-white border-b border-[#D6E5ED] shrink-0">
            <div class="flex items-center gap-3">
                <button type="button" @click="showMobileCart = false" 
                        class="w-9 h-9 rounded-full bg-[#D4E5EA] hover:bg-[#D4E5EA] text-[#1D3B47] flex items-center justify-center transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <div>
                    <h2 class="text-base font-black text-[#1D3B47]">Keranjang Pesanan</h2>
                    <span class="text-[11px] text-[#75929E] font-mono" x-text="'#' + invoiceNumber"></span>
                </div>
            </div>
            <button @click="clearCart(); if(cart.length === 0) showMobileCart = false;" 
                    :disabled="cart.length === 0"
                    class="text-xs font-bold text-[#d9383a] hover:underline disabled:opacity-40">
                Kosongkan
            </button>
        </div>

        <!-- Customer Indicator -->
        <div class="px-4 py-2.5 bg-[#CDE8E5] mx-4 mt-3 rounded-2xl flex justify-between items-center text-xs shrink-0">
            <span class="text-[#75929E] font-medium">Pelanggan:</span>
            <div class="flex items-center gap-1.5">
                <button @click="showCustomerModal = true" class="font-bold text-[#4D869C] hover:underline" x-text="selectedCustomer ? selectedCustomer.name : 'Umum / Pilih'"></button>
                <button x-show="selectedCustomer" @click="selectedCustomer = null" class="text-[#d9383a] font-bold">&times;</button>
            </div>
        </div>

        <!-- Cart Items List (Scrollable) -->
        <div class="flex-1 overflow-y-auto px-4 py-3 space-y-3">
            <template x-for="(item, index) in cart" :key="item.id">
                <div class="bg-white rounded-2xl p-3 border border-[#D6E5ED] flex items-center justify-between gap-3 shadow-xs">
                    <div class="flex-1 min-w-0">
                        <h4 class="text-xs font-bold text-[#1D3B47] truncate" x-text="item.name"></h4>
                        <p class="text-[11px] text-[#75929E] mt-0.5" x-text="formatPrice(item.selling_price)"></p>
                    </div>

                    <!-- Quantity: [ - ] 1 [ + ] -->
                    <div class="flex items-center bg-[#CDE8E5] border border-[#cbdfe7] rounded-xl p-0.5 shrink-0">
                        <button @click="decreaseQty(index)" class="w-6 h-6 flex items-center justify-center text-[#1D3B47] hover:bg-[#d5e5ec] rounded-lg font-bold text-xs">-</button>
                        <span class="w-6 text-center text-xs font-black text-[#4D869C]" x-text="item.qty"></span>
                        <button @click="increaseQty(index)" class="w-6 h-6 flex items-center justify-center text-[#1D3B47] hover:bg-[#d5e5ec] rounded-lg font-bold text-xs">+</button>
                    </div>

                    <!-- Subtotal Item -->
                    <div class="text-xs font-black text-[#1D3B47] w-18 text-right shrink-0" x-text="formatPrice(item.selling_price * item.qty)"></div>
                </div>
            </template>

            <div x-show="cart.length === 0" class="py-16 text-center text-[#75929E]">
                <p class="text-xs font-semibold">Keranjang kosong</p>
                <button @click="showMobileCart = false" class="mt-3 px-5 py-2 bg-[#4D869C] text-white rounded-full text-xs font-bold">Pilih Produk</button>
            </div>
        </div>

        <!-- Bottom Sheet Summary & Checkout -->
        <div class="bg-white border-t border-[#D6E5ED] p-4 space-y-3 shrink-0 shadow-lg">
            <!-- Subtotal, Diskon, PPN -->
            <div class="space-y-1 text-xs">
                <div class="flex justify-between text-[#75929E]">
                    <span>Subtotal</span>
                    <span class="text-[#1D3B47] font-semibold" x-text="formatPrice(subtotal)"></span>
                </div>
                <div class="flex justify-between text-[#75929E] cursor-pointer" @click="showDiscountModal = true">
                    <span class="flex items-center gap-1 text-[#4D869C]">
                        <span>Diskon</span>
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    </span>
                    <span :class="discountAmount > 0 ? 'text-[#d9383a] font-bold' : 'text-[#1D3B47]'" 
                          x-text="discountAmount > 0 ? '- ' + formatPrice(discountAmount) : 'Rp 0'"></span>
                </div>
                <div class="flex justify-between text-[#75929E]">
                    <span x-text="'PPN ' + taxPercentage + '%'"></span>
                    <span class="text-[#1D3B47] font-semibold" x-text="formatPrice(taxAmount)"></span>
                </div>
                <div class="flex justify-between items-baseline pt-1.5 border-t border-[#D6E5ED]">
                    <span class="font-bold text-[#75929E]">Total</span>
                    <span class="text-xl font-black text-[#4D869C]" x-text="formatPrice(totalAmount)"></span>
                </div>
            </div>

            <!-- Metode Pembayaran -->
            <div class="grid grid-cols-2 gap-2 pt-1">
                <button @click="paymentMethod = 'cash'" 
                        :class="paymentMethod === 'cash' ? 'bg-[#4D869C] text-white font-black' : 'bg-[#CDE8E5] text-[#1D3B47] font-bold'" 
                        class="rounded-xl py-2 text-xs transition-all">
                    Tunai
                </button>
                <button @click="paymentMethod = 'midtrans'" 
                        :class="paymentMethod === 'midtrans' ? 'bg-[#4D869C] text-white font-black' : 'bg-[#CDE8E5] text-[#1D3B47] font-bold'" 
                        class="rounded-xl py-2 text-xs transition-all">
                    Non Tunai
                </button>
            </div>

            <!-- Action Buttons: Tahan | Bayar -->
            <div class="flex items-center gap-2 pt-1">
                <button @click="cart.length > 0 ? holdCurrentTransaction() : showHeldModal = true" 
                        class="px-4 py-3 bg-[#CDE8E5] hover:bg-[#d5e5ec] text-[#1D3B47] rounded-full text-xs font-bold transition-all">
                    Tahan
                </button>
                <button @click="startPayment()" 
                        :disabled="cart.length === 0 || isProcessing" 
                        class="flex-1 py-3 bg-[#4D869C] hover:bg-[#3D6B7D] text-white rounded-full text-sm font-black shadow-md disabled:opacity-40 transition-all flex items-center justify-center gap-2">
                    <span x-show="isProcessing">Memproses...</span>
                    <span x-show="!isProcessing">Bayar &rarr;</span>
                </button>
            </div>
        </div>
    </div>

    <!-- =================================================== -->
    <!-- MODAL 1: Dialog Pembayaran Tunai (Pecahan & Kembalian) -->
    <!-- =================================================== -->
    <div x-show="showCashModal" x-cloak 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center bg-black/40 backdrop-blur-xs p-0 sm:p-4">
        <div @click.away="showCashModal = false" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-t-[32px] sm:rounded-[32px] p-6 sm:p-8 max-w-md w-full relative shadow-2xl overflow-hidden flex flex-col justify-between max-h-[92vh] border border-[#D6E5ED]">
            
            <!-- Mobile Top Handle Bar -->
            <div class="w-12 h-1.5 bg-gray-300 rounded-full mx-auto mb-3 sm:hidden shrink-0"></div>

            <div class="flex items-center justify-between mb-4 shrink-0">
                <h3 class="text-lg sm:text-xl font-black text-[#1D3B47]">Pembayaran tunai</h3>
                <button type="button" @click="showCashModal = false" 
                        class="w-9 h-9 bg-[#D4E5EA] hover:bg-[#D4E5EA] text-[#1D3B47] rounded-full flex items-center justify-center shrink-0 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="space-y-4 overflow-y-auto flex-1 pr-1">
                <!-- Total yang Harus Dibayar -->
                <div class="text-center py-4 bg-[#EEF7FF] rounded-2xl border border-[#D6E5ED]">
                    <span class="text-xs text-[#75929E] font-bold uppercase tracking-wider">Total Tagihan</span>
                    <div class="text-2xl sm:text-3xl font-black text-[#1D3B47] mt-1" x-text="formatPrice(totalAmount)"></div>
                </div>

                <!-- Input Uang Diterima -->
                <div>
                    <label class="block text-xs sm:text-sm font-bold text-[#1D3B47] mb-1.5">Uang diterima (Rp)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center font-bold text-[#75929E] text-sm">Rp</span>
                        <input type="number" x-model.number="receivedCash" 
                               class="w-full pl-11 pr-4 py-3 bg-[#f0f6f8] border border-[#D6E5ED] rounded-2xl text-base font-black text-[#1D3B47] focus:outline-none focus:ring-2 focus:ring-[#4D869C]" 
                               placeholder="0">
                    </div>
                </div>

                <!-- Tombol Pecahan Uang Cepat -->
                <div class="space-y-1.5">
                    <span class="text-[11px] font-bold text-[#75929E]">Pecahan cepat:</span>
                    <div class="grid grid-cols-3 gap-2">
                        <button type="button" @click="receivedCash = totalAmount" class="py-2.5 bg-[#EEF7FF] hover:bg-[#CDE8E5] text-[#1D3B47] border border-[#D6E5ED] rounded-2xl text-xs font-bold transition-all shadow-xs">Uang Pas</button>
                        <button type="button" @click="receivedCash = 50000" class="py-2.5 bg-[#EEF7FF] hover:bg-[#CDE8E5] text-[#1D3B47] border border-[#D6E5ED] rounded-2xl text-xs font-bold transition-all shadow-xs">50.000</button>
                        <button type="button" @click="receivedCash = 100000" class="py-2.5 bg-[#EEF7FF] hover:bg-[#CDE8E5] text-[#1D3B47] border border-[#D6E5ED] rounded-2xl text-xs font-bold transition-all shadow-xs">100.000</button>
                        <button type="button" @click="receivedCash = 150000" class="py-2.5 bg-[#EEF7FF] hover:bg-[#CDE8E5] text-[#1D3B47] border border-[#D6E5ED] rounded-2xl text-xs font-bold transition-all shadow-xs">150.000</button>
                        <button type="button" @click="receivedCash = 200000" class="py-2.5 bg-[#EEF7FF] hover:bg-[#CDE8E5] text-[#1D3B47] border border-[#D6E5ED] rounded-2xl text-xs font-bold transition-all shadow-xs">200.000</button>
                        <button type="button" @click="receivedCash = 500000" class="py-2.5 bg-[#EEF7FF] hover:bg-[#CDE8E5] text-[#1D3B47] border border-[#D6E5ED] rounded-2xl text-xs font-bold transition-all shadow-xs">500.000</button>
                    </div>
                </div>

                <!-- Kembalian Card -->
                <div class="p-4 bg-[#EEF7FF] rounded-2xl border border-[#D6E5ED] flex justify-between items-center">
                    <span class="text-xs sm:text-sm font-bold text-[#75929E]">Kembalian:</span>
                    <span class="text-lg sm:text-xl font-black" :class="receivedCash >= totalAmount ? 'text-[#1D3B47]' : 'text-[#d9383a]'" 
                          x-text="receivedCash >= totalAmount ? formatPrice(receivedCash - totalAmount) : 'Kurang ' + formatPrice(totalAmount - receivedCash)">
                    </span>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="flex items-center justify-between pt-5 mt-4 border-t border-[#D6E5ED] shrink-0">
                <button type="button" @click="showCashModal = false" class="text-[#75929E] font-bold hover:text-black text-xs sm:text-sm transition-colors cursor-pointer">
                    Batal
                </button>
                <button type="button" @click="submitCheckout()" 
                        :disabled="receivedCash < totalAmount || isProcessing" 
                        class="bg-[#4D869C] hover:bg-[#3D6B7D] text-white font-bold px-7 sm:px-8 py-3 rounded-full text-xs sm:text-sm shadow-sm disabled:opacity-40 disabled:cursor-not-allowed transition-all cursor-pointer">
                    <span x-show="isProcessing">Memproses...</span>
                    <span x-show="!isProcessing">Selesaikan Transaksi</span>
                </button>
            </div>
        </div>
    </div>

    <!-- =================================================== -->
    <!-- MODAL 2: PEMBAYARAN BERHASIL                       -->
    <!-- =================================================== -->
    <div x-show="showReceiptModal" x-cloak 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center bg-black/40 backdrop-blur-xs p-0 sm:p-4">
        <div @click.away="closeReceiptModal()" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-t-[32px] sm:rounded-[32px] p-6 sm:p-8 max-w-sm sm:max-w-md w-full relative shadow-2xl overflow-hidden flex flex-col justify-between max-h-[92vh] border border-[#D6E5ED]">
            
            <!-- Mobile Top Handle Bar -->
            <div class="w-12 h-1.5 bg-gray-300 rounded-full mx-auto mb-3 sm:hidden shrink-0"></div>

            <!-- Close Button Header -->
            <div class="flex justify-end mb-1 shrink-0">
                <button type="button" @click="closeReceiptModal()" 
                        class="w-9 h-9 bg-[#D4E5EA] hover:bg-[#D4E5EA] text-[#1D3B47] rounded-full flex items-center justify-center shrink-0 transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Content Body -->
            <div class="flex-1 overflow-y-auto pr-1">
                <!-- Checkmark Icon Circle -->
                <div class="w-20 h-20 rounded-full bg-[#CDE8E5] border-4 border-[#4D869C] flex items-center justify-center mx-auto mb-3 shadow-xs shrink-0">
                    <svg class="w-10 h-10 text-[#4D869C]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="M5 13l4 4L19 7"/></svg>
                </div>

                <!-- Title & Invoice Number -->
                <h2 class="text-xl sm:text-2xl font-black text-[#1D3B47] text-center tracking-tight">Pembayaran berhasil</h2>
                <div class="text-xs sm:text-sm font-semibold text-[#75929E] font-mono text-center mt-1" 
                     x-text="'#' + (receiptData?.sale?.invoice_number || invoiceNumber)"></div>

                <!-- Large Total Amount -->
                <div class="text-3xl sm:text-4xl font-black text-[#1D3B47] text-center my-5 tracking-tight" 
                     x-text="formatPrice(receiptData?.sale?.total_amount || totalAmount)"></div>

                <!-- Payment Breakdown Rows -->
                <div class="space-y-3 py-2 border-t border-[#D6E5ED]">
                    <div class="flex items-center justify-between text-xs sm:text-sm">
                        <span class="text-[#75929E] font-medium">Metode</span>
                        <span class="font-bold text-[#1D3B47] capitalize" x-text="receiptData?.sale?.payment_method || paymentMethod"></span>
                    </div>
                    <div class="flex items-center justify-between text-xs sm:text-sm">
                        <span class="text-[#75929E] font-medium">Diterima</span>
                        <span class="font-bold text-[#1D3B47]" 
                              x-text="formatPrice(paymentMethod === 'cash' ? (receivedCash || receiptData?.sale?.total_amount || totalAmount) : (receiptData?.sale?.total_amount || totalAmount))">
                        </span>
                    </div>
                    <div class="border-b border-[#D6E5ED] my-2"></div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm sm:text-base font-bold text-[#75929E]">Kembalian</span>
                        <span class="text-2xl sm:text-3xl font-black text-[#1D3B47]" x-text="formatPrice(receiptData?.change || 0)"></span>
                    </div>
                </div>
            </div>

            <!-- Footer Action Buttons: Transaksi baru (kiri) | Cetak struk (kanan) -->
            <div class="flex items-center justify-between pt-5 mt-4 border-t border-[#D6E5ED] shrink-0">
                <button type="button" @click="closeReceiptModal()" 
                        class="text-[#4D869C] hover:text-[#3D6B7D] font-bold text-xs sm:text-sm underline decoration-[#4D869C] decoration-2 transition-all cursor-pointer">
                    Transaksi baru
                </button>

                <div class="flex items-center gap-2">
                    <button type="button" @click="sendReceiptWA()" title="Kirim WhatsApp" 
                            class="w-10 h-10 bg-emerald-600 hover:bg-emerald-700 text-white rounded-full flex items-center justify-center transition-colors shrink-0 shadow-xs cursor-pointer">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                    </button>
                    <button type="button" @click="window.print()" 
                            class="bg-[#4D869C] hover:bg-[#3D6B7D] text-white font-bold px-7 sm:px-8 py-3 rounded-full text-xs sm:text-sm shadow-sm transition-all flex items-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        <span>Cetak struk</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Area Struk Thermal Khusus Cetak (Hanya tampil saat window.print()) -->
    <div id="receipt-print-area" class="hidden print:block font-mono text-xs text-gray-900 p-4 w-[80mm] mx-auto space-y-2">
        <div class="text-center pb-2 border-b border-dashed border-gray-400">
            <h2 class="font-bold text-sm uppercase">{{ auth()->user()->business->name ?? 'POS' }}</h2>
            <p class="text-[10px]">{{ auth()->user()->business->address ?? 'Alamat Toko' }}</p>
            <p class="text-[10px]">{{ auth()->user()->business->phone ?? '' }}</p>
        </div>
        <div class="flex justify-between text-[11px] text-gray-600">
            <span x-text="'No: ' + (receiptData?.sale?.invoice_number || invoiceNumber)"></span>
            <span x-text="new Date().toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'})"></span>
        </div>
        <div class="text-[11px] text-gray-600">
            Kasir: {{ auth()->user()->name }}
            <template x-if="receiptData?.sale?.customer?.name">
                <span x-text="' | Pelanggan: ' + receiptData.sale.customer.name"></span>
            </template>
        </div>
        <div class="border-t border-b border-dashed border-gray-400 py-2 space-y-1">
            <template x-for="item in lastSaleItems" :key="item.id">
                <div class="flex justify-between text-[11px]">
                    <span class="flex-1 truncate pr-2" x-text="item.name + ' x' + item.qty"></span>
                    <span class="font-semibold" x-text="formatPrice(item.selling_price * item.qty)"></span>
                </div>
            </template>
        </div>
        <div class="space-y-1 text-[11px]">
            <div class="flex justify-between">
                <span>Subtotal</span><span x-text="formatPrice(receiptData?.sale?.subtotal || subtotal)"></span>
            </div>
            <template x-if="(receiptData?.sale?.discount_amount || discountAmount) > 0">
                <div class="flex justify-between text-[#d9383a]">
                    <span>Diskon</span><span x-text="'- ' + formatPrice(receiptData?.sale?.discount_amount || discountAmount)"></span>
                </div>
            </template>
            <div class="flex justify-between">
                <span x-text="'PPN ' + taxPercentage + '%'"></span>
                <span x-text="formatPrice(receiptData?.sale?.tax_amount || taxAmount)"></span>
            </div>
            <div class="flex justify-between font-bold text-sm pt-1 border-t border-gray-300">
                <span>TOTAL</span><span x-text="formatPrice(receiptData?.sale?.total_amount || totalAmount)"></span>
            </div>
            <div class="flex justify-between pt-1">
                <span x-text="'Bayar (' + (receiptData?.sale?.payment_method || paymentMethod).toUpperCase() + ')'"></span>
                <span x-text="formatPrice(receiptData?.received_cash || receivedCash || receiptData?.sale?.total_amount || totalAmount)"></span>
            </div>
            <div class="flex justify-between">
                <span>Kembalian</span><span x-text="formatPrice(receiptData?.change || 0)"></span>
            </div>
        </div>
        <div class="text-center pt-3 text-[10px] text-gray-500 border-t border-dashed border-gray-400">
            Terima kasih atas kunjungan Anda!<br>
            Barang yang dibeli tidak dapat ditukar.
        </div>
    </div>

    <!-- =================================================== -->
    <!-- MODAL MIDTRANS: Menunggu Pembayaran                -->
    <!-- =================================================== -->
    <div x-show="showMidtransModal" x-cloak class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center bg-black/40 backdrop-blur-xs p-0 sm:p-4">
        <div @click.away="midtransPaymentStatus !== 'pending' ? showMidtransModal = false : null" 
             class="bg-white rounded-t-[32px] sm:rounded-[32px] p-6 sm:p-8 max-w-sm sm:max-w-md w-full relative shadow-2xl overflow-hidden text-center space-y-4">
            
            <!-- Mobile Top Handle Bar -->
            <div class="w-12 h-1.5 bg-gray-300 rounded-full mx-auto mb-3 sm:hidden shrink-0"></div>

            <div class="flex justify-between items-center pb-2 border-b border-[#D6E5ED]">
                <h3 class="text-base sm:text-lg font-black text-[#1D3B47]">Pembayaran non tunai</h3>
                <button @click="showMidtransModal = false" class="w-9 h-9 bg-[#D4E5EA] hover:bg-[#D4E5EA] text-[#1D3B47] rounded-full flex items-center justify-center transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="text-center py-3 bg-[#EEF7FF] rounded-2xl border border-[#D6E5ED]">
                <span class="text-xs text-[#75929E] font-bold uppercase tracking-wider">Total Tagihan</span>
                <div class="text-xl sm:text-2xl font-black text-[#1D3B47] mt-1" x-text="formatPrice(pendingMidtransTotal || totalAmount)"></div>
                <div class="text-[11px] text-[#75929E] font-mono mt-1" x-text="pendingMidtransInvoice ? '#' + pendingMidtransInvoice : '#' + invoiceNumber"></div>
            </div>

            <div class="py-2">
                <template x-if="midtransPaymentStatus === 'pending'">
                    <div class="text-xs sm:text-sm text-gray-700 font-semibold animate-pulse">Menunggu penyelesaian pembayaran di Midtrans...</div>
                </template>
                <template x-if="midtransPaymentStatus === 'completed'">
                    <div class="text-xs sm:text-sm font-bold text-emerald-700">Pembayaran berhasil! Menyiapkan nota...</div>
                </template>
                <template x-if="midtransPaymentStatus === 'failed'">
                    <div class="text-xs sm:text-sm font-bold text-[#d9383a]">Pembayaran gagal atau kedaluwarsa.</div>
                </template>
            </div>

            <div class="pt-2 border-t border-[#D6E5ED]">
                <button @click="showMidtransModal = false" class="w-full py-3 bg-[#4D869C] hover:bg-[#3D6B7D] text-white rounded-full text-xs sm:text-sm font-bold transition-all">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- =================================================== -->
    <!-- MODAL 3: Atur Diskon                               -->
    <!-- =================================================== -->
    <div x-show="showDiscountModal" x-cloak 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center bg-black/40 backdrop-blur-xs p-0 sm:p-4">
        <div @click.away="showDiscountModal = false" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-t-[32px] sm:rounded-[32px] p-6 sm:p-8 max-w-sm w-full relative shadow-2xl overflow-hidden flex flex-col justify-between border border-[#D6E5ED]">
            
            <!-- Mobile Top Handle Bar -->
            <div class="w-12 h-1.5 bg-gray-300 rounded-full mx-auto mb-3 sm:hidden shrink-0"></div>

            <div class="flex items-center justify-between mb-5 shrink-0">
                <h3 class="text-lg sm:text-xl font-black text-[#1D3B47]">Atur diskon</h3>
                <button type="button" @click="showDiscountModal = false" 
                        class="w-9 h-9 bg-[#D4E5EA] hover:bg-[#D4E5EA] text-[#1D3B47] rounded-full flex items-center justify-center shrink-0 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" @click="tempDiscount.type = 'percentage'" 
                            :class="tempDiscount.type === 'percentage' ? 'bg-[#4D869C] text-white font-bold shadow-xs' : 'bg-[#CDE8E5] text-[#1D3B47] font-semibold'"
                            class="py-2.5 text-xs rounded-2xl transition-all">
                        Persen (%)
                    </button>
                    <button type="button" @click="tempDiscount.type = 'fixed'" 
                            :class="tempDiscount.type === 'fixed' ? 'bg-[#4D869C] text-white font-bold shadow-xs' : 'bg-[#CDE8E5] text-[#1D3B47] font-semibold'"
                            class="py-2.5 text-xs rounded-2xl transition-all">
                        Nominal (Rp)
                    </button>
                </div>

                <div>
                    <label class="block text-xs sm:text-sm font-bold text-[#1D3B47] mb-1.5" x-text="tempDiscount.type === 'percentage' ? 'Besaran diskon (%)' : 'Besaran diskon (Rp)'"></label>
                    <input type="number" x-model.number="tempDiscount.value" 
                           class="w-full bg-[#EEF7FF] border border-[#D6E5ED] rounded-2xl px-4 py-3 text-xs sm:text-sm font-semibold text-[#1D3B47] focus:outline-none focus:ring-2 focus:ring-[#4D869C]" 
                           placeholder="0">
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="flex items-center justify-between pt-5 mt-4 border-t border-gray-300 shrink-0">
                <button type="button" @click="resetDiscount()" class="text-[#d9383a] font-bold text-xs sm:text-sm underline decoration-[#d9383a] decoration-2 hover:opacity-80 transition-all cursor-pointer">
                    Hapus
                </button>
                <div class="flex items-center gap-3">
                    <button type="button" @click="showDiscountModal = false" class="text-[#75929E] font-bold hover:text-[#1D3B47] text-xs sm:text-sm transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="button" @click="applyDiscount()" class="bg-[#4D869C] hover:bg-[#3D6B7D] text-white font-bold px-7 sm:px-8 py-3 rounded-full text-xs sm:text-sm shadow-sm transition-all cursor-pointer">
                        Terapkan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- =================================================== -->
    <!-- MODAL 4: Daftar Pesanan Ditahan (Hold)              -->
    <!-- =================================================== -->
    <div x-show="showHeldModal" x-cloak 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center bg-black/40 backdrop-blur-xs p-0 sm:p-4">
        <div @click.away="showHeldModal = false" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-t-[32px] sm:rounded-[32px] p-6 sm:p-8 max-w-md w-full relative shadow-2xl overflow-hidden flex flex-col max-h-[85vh] border border-[#D6E5ED]">
            
            <!-- Mobile Top Handle Bar -->
            <div class="w-12 h-1.5 bg-gray-300 rounded-full mx-auto mb-3 sm:hidden shrink-0"></div>

            <div class="flex items-center justify-between pb-3 mb-3 border-b border-[#D6E5ED] shrink-0">
                <h3 class="text-lg sm:text-xl font-black text-[#1D3B47]" x-text="'Pesanan ditahan (' + heldTransactions.length + ')'"></h3>
                <button type="button" @click="showHeldModal = false" 
                        class="w-9 h-9 bg-[#D4E5EA] hover:bg-[#D4E5EA] text-[#1D3B47] rounded-full flex items-center justify-center shrink-0 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="overflow-y-auto flex-1 space-y-2.5 pr-1">
                <template x-for="held in heldTransactions" :key="held.id">
                    <div class="p-3.5 rounded-2xl border border-[#D6E5ED] bg-[#EEF7FF] flex justify-between items-center text-xs">
                        <div>
                            <div class="font-bold text-[#1D3B47] text-sm" x-text="held.customer_name || 'Pesanan #Tahan'"></div>
                            <div class="text-[11px] text-[#75929E] font-medium mt-0.5" x-text="(Array.isArray(held.items) ? held.items.length : JSON.parse(held.items || '[]').length) + ' Item â€¢ ' + formatPrice(held.subtotal)"></div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="resumeHeldTransaction(held.id)" class="px-4 py-2 bg-[#4D869C] hover:bg-[#3D6B7D] text-white rounded-full font-bold text-xs transition-all shadow-xs">
                                Buka
                            </button>
                            <button type="button" @click="deleteHeldTransaction(held.id)" class="p-2 text-[#d9383a] hover:bg-[#fdeeee] rounded-full transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                    </div>
                </template>
                <div x-show="heldTransactions.length === 0" class="py-10 text-center text-[#75929E] text-xs font-semibold">
                    Tidak ada pesanan yang ditahan saat ini.
                </div>
            </div>
        </div>
    </div>

    <!-- =================================================== -->
    <!-- MODAL 5: Pilih Pelanggan                           -->
    <!-- =================================================== -->
    <div x-show="showCustomerModal" x-cloak 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center bg-black/40 backdrop-blur-xs p-0 sm:p-4">
        <div @click.away="showCustomerModal = false" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-t-[32px] sm:rounded-[32px] p-6 sm:p-8 max-w-sm sm:max-w-md w-full relative shadow-2xl overflow-hidden flex flex-col max-h-[85vh] border border-[#D6E5ED]">
            
            <!-- Mobile Top Handle Bar -->
            <div class="w-12 h-1.5 bg-gray-300 rounded-full mx-auto mb-3 sm:hidden shrink-0"></div>

            <div class="flex items-center justify-between pb-3 mb-3 border-b border-[#D6E5ED] shrink-0">
                <h3 class="text-lg sm:text-xl font-black text-[#1D3B47]">Pilih pelanggan</h3>
                <button type="button" @click="showCustomerModal = false" 
                        class="w-9 h-9 bg-[#D4E5EA] hover:bg-[#D4E5EA] text-[#1D3B47] rounded-full flex items-center justify-center shrink-0 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="mb-3 shrink-0">
                <input type="text" x-model="customerSearch" @input.debounce.300ms="searchCustomers()" 
                       class="w-full bg-[#EEF7FF] border border-[#D6E5ED] rounded-2xl px-4 py-3 text-xs sm:text-sm font-semibold text-[#1D3B47] placeholder-[#75929E] focus:outline-none focus:ring-2 focus:ring-[#4D869C]" 
                       placeholder="Cari nama atau telepon...">
            </div>

            <div class="overflow-y-auto flex-1 space-y-2 pr-1">
                <button type="button" @click="selectCustomer(null)" class="w-full text-left p-3 hover:bg-[#EEF7FF] rounded-2xl text-xs flex justify-between items-center transition-colors border border-transparent hover:border-gray-300">
                    <span class="font-bold text-[#1D3B47]">Pelanggan Umum (Tanpa Member)</span>
                </button>
                <template x-for="cust in customersList" :key="cust.id">
                    <button type="button" @click="selectCustomer(cust)" class="w-full text-left p-3 hover:bg-[#EEF7FF] rounded-2xl text-xs flex justify-between items-center transition-colors border border-transparent hover:border-gray-300">
                        <div>
                            <div class="font-bold text-[#1D3B47] text-sm" x-text="cust.name"></div>
                            <div class="text-[11px] text-[#75929E] font-medium" x-text="cust.phone || cust.email || '-'"></div>
                        </div>
                        <span class="px-3 py-1 rounded-full text-[10px] font-black bg-[#4D869C] text-white uppercase tracking-wider" x-text="cust.level || 'Member'"></span>
                    </button>
                </template>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div x-show="showToast" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-2"
         class="fixed top-4 right-4 z-[60] max-w-sm">
        <div :class="toastType === 'success' ? 'bg-[#4D869C] text-white border border-[#4D869C]/40' : 'bg-[#d9383a] text-white'"
             class="flex items-center gap-2.5 rounded-2xl shadow-xl px-4 py-3">
            <svg x-show="toastType === 'success'" class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
            <svg x-show="toastType !== 'success'" class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"></path></svg>
            <span class="text-xs font-bold leading-snug" x-text="toastMessage"></span>
        </div>
    </div>

    <!-- Alpine Data Script -->
    <script>
        function cashierPOS() {
            return {
                // Sidebar state (Open on large screens by default, collapsible with 1-click)
                sidebarOpen: window.innerWidth >= 1280,

                products: @json($products ?? []),
                categories: @json($categories ?? []),
                cart: [],
                searchQuery: '',
                activeCategory: 'all',
                paymentMethod: 'cash',
                receivedCash: 0,
                isProcessing: false,

                // Modals
                showMobileCart: false,
                showCashModal: false,
                showReceiptModal: false,
                showDiscountModal: false,
                showHeldModal: false,
                showCustomerModal: false,

                // Data & State
                pendingMidtransSaleId: null,
                pendingMidtransInvoice: null,
                pendingMidtransTotal: 0,
                midtransPaymentStatus: 'pending',
                showMidtransModal: false,
                receiptData: null,

                // Toast notification
                showToast: false,
                toastMessage: '',
                toastType: 'success',
                toastTimer: null,
                lastSaleItems: [],
                heldTransactions: [],
                heldCount: {{ $heldTransactionsCount ?? 0 }},
                selectedCustomer: null,
                customerSearch: '',
                customersList: [],
                discount: { type: 'none', value: 0 },
                tempDiscount: { type: 'percentage', value: 0 },
                taxPercentage: {{ auth()->user()->business->tax_percentage ?? 11 }},
                invoiceNumber: '{{ auth()->user()->business->invoice_prefix ?? "INV" }}-{{ str_pad(auth()->user()->business->invoice_sequence ?? 1, 4, "0", STR_PAD_LEFT) }}',

                init() {
                    this.loadHeldTransactions();
                    this.searchCustomers();

                    // Watch modals and sync with global store to hide mobile bottom bar
                    const modalKeys = ['showMobileCart', 'showCashModal', 'showReceiptModal', 'showDiscountModal', 'showHeldModal', 'showCustomerModal', 'showMidtransModal'];
                    modalKeys.forEach(k => {
                        this.$watch(k, () => {
                            const anyOpen = modalKeys.some(key => Boolean(this[key]));
                            if (window.Alpine && Alpine.store('modal')) {
                                Alpine.store('modal').open = anyOpen;
                            }
                        });
                    });
                },

                getProductBlockStyle(product) {
                    const styles = [
                        'bg-[#4D869C] text-white',
                        'bg-[#CDE8E5] text-[#1D3B47]',
                        'bg-[#7AB2B2] text-white',
                        'bg-[#EEF7FF] text-[#1D3B47]'
                    ];
                    return styles[(product.id || 0) % styles.length];
                },

                get filteredProducts() {
                    return this.products.filter(p => {
                        const q = this.searchQuery.toLowerCase();
                        const matchQ = p.name.toLowerCase().includes(q) || (p.sku && p.sku.toLowerCase().includes(q)) || (p.barcode && p.barcode.includes(q));
                        const matchCat = this.activeCategory === 'all' || p.category_id == this.activeCategory;
                        return matchQ && matchCat;
                    });
                },

                get subtotal() {
                    return this.cart.reduce((sum, item) => sum + (item.selling_price * item.qty), 0);
                },

                get discountAmount() {
                    if (this.discount.type === 'percentage') {
                        return Math.round(this.subtotal * (this.discount.value / 100));
                    } else if (this.discount.type === 'fixed') {
                        return Math.min(this.discount.value, this.subtotal);
                    }
                    return 0;
                },

                get taxableAmount() {
                    return Math.max(0, this.subtotal - this.discountAmount);
                },

                get taxAmount() {
                    return Math.round(this.taxableAmount * (this.taxPercentage / 100));
                },

                get totalAmount() {
                    return this.taxableAmount + this.taxAmount;
                },

                getCartQty(productId) {
                    const item = this.cart.find(i => i.id === productId);
                    return item ? item.qty : 0;
                },

                addToCart(product) {
                    if (product.stock <= 0) {
                        alert('Stok produk ini sedang habis!');
                        return;
                    }

                    const existing = this.cart.find(i => i.id === product.id);
                    if (existing) {
                        if (existing.qty + 1 > product.stock) {
                            alert('Stok tidak mencukupi! Maksimum stok: ' + product.stock);
                            return;
                        }
                        existing.qty++;
                    } else {
                        this.cart.push({
                            id: product.id,
                            name: product.name,
                            selling_price: parseFloat(product.selling_price),
                            qty: 1,
                            stock: product.stock
                        });
                    }
                },

                increaseQty(index) {
                    const item = this.cart[index];
                    const product = this.products.find(p => p.id === item.id);
                    if (product && item.qty + 1 > product.stock) {
                        alert('Stok tidak mencukupi! Maksimum stok: ' + product.stock);
                        return;
                    }
                    item.qty++;
                },

                decreaseQty(index) {
                    if (this.cart[index].qty > 1) {
                        this.cart[index].qty--;
                    } else {
                        this.cart.splice(index, 1);
                    }
                },

                clearCart() {
                    if (confirm('Yakin ingin membatalkan semua pesanan di keranjang?')) {
                        this.cart = [];
                        this.discount = { type: 'none', value: 0 };
                        this.selectedCustomer = null;
                    }
                },

                applyDiscount() {
                    if (this.tempDiscount.value > 0) {
                        this.discount = { ...this.tempDiscount };
                    } else {
                        this.discount = { type: 'none', value: 0 };
                    }
                    this.showDiscountModal = false;
                },

                resetDiscount() {
                    this.discount = { type: 'none', value: 0 };
                    this.tempDiscount = { type: 'percentage', value: 0 };
                    this.showDiscountModal = false;
                },

                async holdCurrentTransaction() {
                    if (this.cart.length === 0) return;
                    try {
                        const payload = {
                            items: this.cart,
                            customer_id: this.selectedCustomer?.id || null,
                            customer_name: this.selectedCustomer?.name || 'Pesanan ' + new Date().toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'}),
                            subtotal: this.subtotal,
                            discount_type: this.discount.type !== 'none' ? this.discount.type : null,
                            discount_value: this.discount.value || null,
                            notes: ''
                        };

                        const res = await fetch('{{ route("pos.hold") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        });

                        if (res.ok) {
                            this.cart = [];
                            this.discount = { type: 'none', value: 0 };
                            this.selectedCustomer = null;
                            await this.loadHeldTransactions();
                            alert('Pesanan berhasil ditahan.');
                        }
                    } catch (e) {
                        alert('Gagal menahan pesanan: ' + e.message);
                    }
                },

                async loadHeldTransactions() {
                    try {
                        const res = await fetch('{{ route("pos.held") }}');
                        this.heldTransactions = await res.json();
                        this.heldCount = this.heldTransactions.length;
                    } catch (e) {
                        console.error(e);
                    }
                },

                async resumeHeldTransaction(id) {
                    try {
                        const res = await fetch('/pos/resume/' + id, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            }
                        });
                        const data = await res.json();
                        this.cart = typeof data.items === 'string' ? JSON.parse(data.items) : data.items;
                        if (data.discount_type) {
                            this.discount = { type: data.discount_type, value: data.discount_value };
                        }
                        this.showHeldModal = false;
                        await this.loadHeldTransactions();
                    } catch (e) {
                        alert('Gagal membuka pesanan: ' + e.message);
                    }
                },

                async deleteHeldTransaction(id) {
                    if (!confirm('Hapus transaksi tertahan ini?')) return;
                    try {
                        await fetch('/pos/held/' + id, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            }
                        });
                        await this.loadHeldTransactions();
                    } catch (e) {
                        console.error(e);
                    }
                },

                async searchProducts() {
                    try {
                        const params = new URLSearchParams();
                        if (this.searchQuery) params.set('q', this.searchQuery);
                        if (this.activeCategory && this.activeCategory !== 'all') params.set('category_id', this.activeCategory);

                        const res = await fetch('{{ route("pos.search-products") }}?' + params.toString(), {
                            headers: { 'Accept': 'application/json' }
                        });
                        if (res.ok) {
                            this.products = await res.json();
                        }
                    } catch (e) {
                        console.error(e);
                    }
                },

                async handleBarcodeSubmit() {
                    const term = (this.searchQuery || '').trim();
                    if (!term) return;
                    try {
                        const res = await fetch('{{ route("pos.search-products") }}?q=' + encodeURIComponent(term), {
                            headers: { 'Accept': 'application/json' }
                        });
                        if (!res.ok) return;
                        const items = await res.json();
                        const lower = term.toLowerCase();
                        const exact = items.find(p =>
                            (p.barcode && p.barcode.toLowerCase() === lower) ||
                            (p.sku && p.sku.toLowerCase() === lower)
                        );
                        const product = exact || (items.length === 1 ? items[0] : null);
                        if (product) {
                            this.addToCart(product);
                            this.searchQuery = '';
                            this.searchProducts();
                        }
                    } catch (e) {
                        console.error(e);
                    }
                },

                async searchCustomers() {
                    try {
                        const res = await fetch('/pos/search-customers?q=' + encodeURIComponent(this.customerSearch));
                        this.customersList = await res.json();
                    } catch (e) {
                        console.error(e);
                    }
                },

                selectCustomer(cust) {
                    this.selectedCustomer = cust;
                    this.showCustomerModal = false;
                },

                startPayment() {
                    if (this.cart.length === 0) return;
                    if (this.paymentMethod === 'cash') {
                        this.receivedCash = this.totalAmount;
                        this.showCashModal = true;
                    } else if (this.paymentMethod === 'midtrans') {
                        this.receivedCash = this.totalAmount;
                        this.submitCheckout();
                    } else {
                        this.receivedCash = this.totalAmount;
                        this.submitCheckout();
                    }
                },

                async submitCheckout() {
                    this.isProcessing = true;
                    try {
                        const payload = {
                            items: this.cart.map(i => ({ product_id: i.id, quantity: i.qty })),
                            payment_method: this.paymentMethod,
                            received_cash: this.paymentMethod === 'cash' ? this.receivedCash : this.totalAmount,
                            discount_type: this.discount.type !== 'none' ? this.discount.type : null,
                            discount_value: this.discount.value || null,
                            customer_id: this.selectedCustomer?.id || null
                        };

                        const res = await fetch('{{ route("pos.checkout") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        });

                        const data = await res.json();
                        if (!res.ok) {
                            throw new Error(data.error || 'Terjadi kesalahan saat transaksi.');
                        }

                        if (data.pending && data.payment_method === 'midtrans') {
                            this.showCashModal = false;
                            this.handleMidtransPayment(data);
                            return;
                        }

                        this.lastSaleItems = [...this.cart];
                        this.receiptData = data;
                        this.showCashModal = false;
                        this.showReceiptModal = true;

                        // Kurangi stok lokal di browser
                        this.cart.forEach(cartItem => {
                            const p = this.products.find(prod => prod.id === cartItem.id);
                            if (p) p.stock = Math.max(0, p.stock - cartItem.qty);
                        });

                        // Reset keranjang
                        this.cart = [];
                        this.discount = { type: 'none', value: 0 };
                        this.selectedCustomer = null;

                        // Update sequence nota berikutnya
                        if (data.sale?.invoice_number) {
                            const parts = data.sale.invoice_number.split('-');
                            if (parts.length >= 2) {
                                const nextSeq = parseInt(parts[1]) + 1;
                                this.invoiceNumber = parts[0] + '-' + String(nextSeq).padStart(4, '0');
                            }
                        }

                    } catch (err) {
                        alert('Gagal menyelesaikan pembayaran: ' + err.message);
                    } finally {
                        this.isProcessing = false;
                    }
                },

                closeReceiptModal() {
                    this.showReceiptModal = false;
                    this.receiptData = null;
                },

                sendReceiptWA() {
                    const sale = this.receiptData?.sale;
                    if (!sale) return;
                    
                    let text = `*NOTA PENJUALAN - {{ auth()->user()->business->name ?? 'POS' }}*\n`;
                    text += `No: ${sale.invoice_number}\n`;
                    text += `Waktu: ${new Date().toLocaleString('id-ID')}\n`;
                    if (sale.customer?.name) {
                        text += `Pelanggan: ${sale.customer.name}\n`;
                    }
                    text += `--------------------------------\n`;
                    this.lastSaleItems.forEach(i => {
                        text += `${i.name} (${i.qty}x) = ${this.formatPrice(i.selling_price * i.qty)}\n`;
                    });
                    text += `--------------------------------\n`;
                    text += `*TOTAL: ${this.formatPrice(sale.total_amount)}*\n`;
                    text += `Metode: ${sale.payment_method.toUpperCase()}\n`;
                    text += `Terima kasih!`;

                    const phone = sale.customer?.phone ? sale.customer.phone.replace(/[^0-9]/g, '') : '';
                    const url = phone 
                        ? `https://api.whatsapp.com/send?phone=${phone}&text=${encodeURIComponent(text)}`
                        : `https://api.whatsapp.com/send?text=${encodeURIComponent(text)}`;

                    window.open(url, '_blank');
                },

                async handleMidtransPayment(data) {
                    if (!data.snap_token && !data.redirect_url) {
                        alert('Gagal memulai pembayaran Midtrans.');
                        this.isProcessing = false;
                        return;
                    }

                    const token = data.snap_token;
                    this.pendingMidtransSaleId = data.sale?.id;
                    this.pendingMidtransInvoice = data.sale?.invoice_number;
                    this.pendingMidtransTotal = data.sale?.total_amount;
                    this.midtransPaymentStatus = 'pending';
                    this.showMidtransModal = true;

                    try {
                        if (window.snap && typeof window.snap.pay === 'function') {
                            const self = this;
                            window.snap.pay(token, {
                                onSuccess: function (result) {
                                    self.pollMidtransStatus();
                                },
                                onPending: function (result) {
                                    self.pollMidtransStatus();
                                },
                                onError: function (result) {
                                    self.midtransPaymentStatus = 'failed';
                                    alert('Pembayaran gagal atau dibatalkan.');
                                },
                                onClose: function () {
                                    self.pollMidtransStatus();
                                }
                            });
                        } else if (data.redirect_url) {
                            window.open(data.redirect_url, '_blank');
                            this.pollMidtransStatus();
                        }
                    } finally {
                        this.isProcessing = false;
                        this.cart = [];
                        this.discount = { type: 'none', value: 0 };
                        this.selectedCustomer = null;
                    }
                },

                async pollMidtransStatus() {
                    if (!this.pendingMidtransSaleId) {
                        this.showMidtransModal = false;
                        return;
                    }

                    try {
                        const res = await fetch('{{ route("pos.sale-status", ":id") }}'.replace(':id', this.pendingMidtransSaleId));
                        if (!res.ok) {
                            setTimeout(() => this.pollMidtransStatus(), 2000);
                            return;
                        }
                        const status = await res.json();
                        if (status.status === 'completed') {
                            this.midtransPaymentStatus = 'completed';
                            this.showNotification('Pembayaran berhasil â€” ' + (this.pendingMidtransInvoice || 'transaksi non-tunai'), 'success');
                            // Fetch receipt data
                            const receiptRes = await fetch('/pos/receipt/' + this.pendingMidtransSaleId);
                            if (receiptRes.ok) {
                                const sale = await receiptRes.json();
                                this.receiptData = { sale: sale };
                                this.lastSaleItems = sale.items ? sale.items.map(i => ({
                                    id: i.product_id,
                                    name: i.product?.name || 'Produk',
                                    selling_price: parseFloat(i.unit_price),
                                    qty: i.quantity
                                })) : [];
                            }
                            setTimeout(() => {
                                this.showMidtransModal = false;
                                this.showReceiptModal = true;
                            }, 1500);
                        } else if (status.payment_status === 'failed' || status.payment_status === 'expired' || status.payment_status === 'cancelled' || status.status === 'cancelled') {
                            this.midtransPaymentStatus = 'failed';
                            setTimeout(() => {
                                this.showMidtransModal = false;
                            }, 2000);
                        } else {
                            setTimeout(() => this.pollMidtransStatus(), 2000);
                        }
                    } catch (e) {
                        setTimeout(() => this.pollMidtransStatus(), 2000);
                    }
                },

                showNotification(message, type = 'success', duration = 4000) {
                    this.toastMessage = message;
                    this.toastType = type;
                    this.showToast = true;

                    if (this.toastTimer) clearTimeout(this.toastTimer);
                    this.toastTimer = setTimeout(() => {
                        this.showToast = false;
                    }, duration);
                },

                formatPrice(val) {
                    return new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    }).format(val || 0);
                }
            };
        }
    </script>
</body>
</html>




<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasir - {{ auth()->user()->business->name ?? 'POS' }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

    @if(!empty($midtransConfigured))
    <script src="{{ $midtransSnapUrl }}" data-client-key="{{ $midtransClientKey }}"></script>
    @endif

    <style>
        body {
            font-family: 'Figtree', sans-serif;
            background-color: #f1f3f5;
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
        @media print {
            body * { visibility: hidden; }
            #receipt-print-area, #receipt-print-area * { visibility: visible; }
            #receipt-print-area { position: absolute; left: 0; top: 0; width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="min-h-screen text-gray-900 antialiased selection:bg-teal-700 selection:text-white overflow-x-hidden" x-data="cashierPOS()" x-init="init()">

    <!-- OFFLINE BANNER -->
    <div class="fixed top-0 left-0 right-0 z-50 bg-rose-600 text-white text-center py-1.5 text-xs font-semibold shadow-md transition-all" x-show="!$store.app.isOnline" style="display: none;">
        Mode Offline: Koneksi internet terputus. Data transaksi akan disimpan di antrean lokal.
    </div>

    <!-- MOBILE BACKDROP OVERLAY FOR SIDEBAR -->
    <div x-show="sidebarOpen" 
         @click="sidebarOpen = false" 
         class="fixed inset-0 z-40 bg-black/40 backdrop-blur-xs lg:hidden transition-opacity" 
         x-cloak 
         style="display: none;"></div>

    <!-- COLLAPSIBLE LEFT SIDEBAR NAVBAR -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
           class="fixed top-0 left-0 h-screen w-56 bg-white border-r border-gray-200 z-50 flex flex-col transition-transform duration-300 ease-in-out shadow-lg lg:shadow-none">
        
        <!-- Sidebar Brand & Close Button -->
        <div class="flex items-center justify-between p-4 border-b border-gray-100 shrink-0">
            <div class="flex items-center gap-2.5 overflow-hidden">
                <img src="{{ str_replace(' ', '%20', Storage::url('logo mypos.png')) }}" alt="MyPos" class="h-8 w-auto object-contain shrink-0">
                <div class="font-bold text-gray-900 truncate text-sm leading-tight">
                    MyPos
                </div>
            </div>
            <!-- Toggle / Close Button -->
            <button @click="sidebarOpen = false" class="text-gray-400 hover:text-gray-700 p-1.5 rounded-lg hover:bg-gray-100 transition-colors" title="Tutup Menu">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- Navigation Links (7 Items Matching Reference Mockup) -->
        <nav class="flex-1 px-3 py-3 space-y-1 overflow-y-auto">
            <a href="{{ route('dashboard') }}" class="pos-sidebar-link">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('pos.index') }}" class="pos-sidebar-link active">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span>Kasir</span>
            </a>
            <a href="{{ route('reports.sales') }}" class="pos-sidebar-link">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                <span>Laporan</span>
            </a>
            <a href="{{ route('products.index') }}" class="pos-sidebar-link">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                <span>Produk & Stok</span>
            </a>
            <a href="{{ route('customers.index') }}" class="pos-sidebar-link">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span>Pelanggan</span>
            </a>
            <a href="{{ route('purchases.index') }}" class="pos-sidebar-link">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                <span>Supplier</span>
            </a>
            <a href="{{ route('users.index') }}" class="pos-sidebar-link">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                <span>Karyawan</span>
            </a>
            <a href="{{ route('business.settings') }}" class="pos-sidebar-link">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                <span>Pengaturan</span>
            </a>
        </nav>

        <!-- User Profile Dropdown in Sidebar Bottom -->
        <div class="p-3 border-t border-gray-200 shrink-0">
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" @click.away="open = false" class="w-full flex items-center gap-2.5 p-2 hover:bg-gray-50 rounded-xl transition-colors">
                    <div class="w-7 h-7 rounded-full bg-gray-200 flex items-center justify-center text-gray-700 font-bold text-xs shrink-0">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <div class="flex-1 text-left min-w-0">
                        <div class="text-xs font-bold text-gray-900 truncate">{{ auth()->user()->name }}</div>
                        <div class="text-[10px] text-gray-500 truncate">{{ auth()->user()->roles->first()->name ?? 'Kasir' }}</div>
                    </div>
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div x-show="open" x-cloak class="absolute bottom-full left-0 w-full mb-2 bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden py-1 z-50">
                    <a href="{{ route('profile.edit') }}" class="block px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">Profil Pengguna</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full text-left px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50">Keluar (Logout)</button>
                    </form>
                </div>
            </div>
        </div>
    </aside>

    <!-- MAIN CASHIER CONTAINER (Dynamically shifts with transition when sidebar opens/closes on desktop) -->
    <div :class="sidebarOpen ? 'lg:pl-56' : 'lg:pl-0'" class="transition-all duration-300 ease-in-out h-screen flex flex-col overflow-hidden">
        
        <div class="h-screen flex flex-col p-3 sm:p-4 md:p-5 gap-3 sm:gap-4 overflow-hidden">
            
            <!-- TOP HEADER BAR -->
            <header class="flex items-center justify-between gap-3 shrink-0 bg-white px-4 py-2.5 rounded-2xl shadow-xs border border-gray-200/80">
                
                <!-- Left: Hamburger Toggle + Business Info -->
                <div class="flex items-center gap-2.5 sm:gap-3.5 min-w-0">
                    <!-- Hamburger Toggle Button -->
                    <button @click="sidebarOpen = !sidebarOpen" 
                            class="p-2 rounded-xl text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition-colors shrink-0" 
                            :title="sidebarOpen ? 'Tutup Menu Navigasi' : 'Buka Menu Navigasi'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>

                    <!-- Brand & Cashier Info -->
                    <div class="flex items-center gap-2.5">
                        @if (auth()->user()->business?->logo)
                            <img src="{{ auth()->user()->business->logo_url }}" alt="{{ auth()->user()->business->name }}" class="h-9 w-auto max-w-[9rem] object-contain shrink-0">
                        @else
                            <div class="w-9 h-9 bg-[#0d6e53] text-white font-extrabold text-base flex items-center justify-center rounded-xl shadow-xs shrink-0">
                                {{ substr(auth()->user()->business->name ?? 'P', 0, 1) }}
                            </div>
                        @endif
                        <div class="hidden sm:block truncate">
                            <h1 class="text-xs sm:text-sm font-extrabold text-gray-900 leading-tight tracking-tight uppercase truncate">
                                {{ auth()->user()->business->name ?? 'POS' }}
                            </h1>
                            <p class="text-[11px] text-gray-500 font-medium truncate">
                                Kasir: {{ auth()->user()->name ?? 'Kasir' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Center: Search Bar -->
                <div class="flex-1 max-w-xl px-2">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" x-model="searchQuery" 
                               class="w-full pl-9 pr-4 py-2 bg-gray-50 hover:bg-gray-100/60 focus:bg-white border border-gray-200 rounded-xl text-xs text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-teal-700 focus:border-teal-700 shadow-2xs transition-all" 
                               placeholder="Cari produk atau scan barcode...">
                    </div>
                </div>

                <!-- Right: Online Status -->
                <div class="flex items-center gap-2 sm:gap-3 shrink-0 justify-end">
                    <div class="hidden md:flex items-center gap-1.5 px-2.5 py-1 bg-gray-50 rounded-lg border border-gray-100">
                        <span class="w-2 h-2 rounded-full" :class="$store.app?.isOnline !== false ? 'bg-[#10b981]' : 'bg-rose-500'"></span>
                        <span class="text-[11px] font-semibold text-gray-600" x-text="$store.app?.isOnline !== false ? 'Online' : 'Offline'">Online</span>
                    </div>
                </div>
            </header>

            <!-- MAIN CONTENT WORKSPACE -->
            <div class="flex-1 flex flex-col lg:flex-row gap-3 sm:gap-4 overflow-hidden">
                
                <!-- LEFT AREA: Kategori + Grid Produk -->
                <div class="flex-1 flex flex-col gap-3 overflow-hidden min-w-0">
                    
                    <!-- Kategori Filter Pills -->
                    <div class="flex items-center gap-2 shrink-0 overflow-x-auto py-1">
                        <button @click="activeCategory = 'all'" 
                                :class="activeCategory === 'all' ? 'bg-[#18181b] text-white shadow-xs' : 'bg-white text-gray-800 border border-gray-200 hover:bg-gray-50'" 
                                class="px-4 py-1.5 rounded-full text-xs font-bold transition-all shrink-0">
                            Semua
                        </button>
                        <template x-for="category in categories" :key="category.id">
                            <button @click="activeCategory = category.id" 
                                    :class="activeCategory == category.id ? 'bg-[#18181b] text-white shadow-xs' : 'bg-white text-gray-800 border border-gray-200 hover:bg-gray-50'" 
                                    class="px-4 py-1.5 rounded-full text-xs font-bold transition-all shrink-0" 
                                    x-text="category.name">
                            </button>
                        </template>
                    </div>

                    <!-- Product Grid (Responsive: 2-5 Kolom Pastel Sesuai Desain) -->
                    <div class="flex-1 overflow-y-auto pr-1">
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-3">
                            <template x-for="product in filteredProducts" :key="product.id">
                                <div @click="addToCart(product)" 
                                     class="bg-white rounded-2xl p-3 border border-gray-100 shadow-2xs cursor-pointer hover:shadow-md hover:border-gray-300 transition-all flex flex-col justify-between group select-none">
                                    
                                    <!-- Card Kotak Warna Pastel dengan Inisial Huruf Besar -->
                                    <div class="w-full aspect-[4/3] rounded-xl flex items-center justify-center relative transition-transform group-hover:scale-[1.02]"
                                         :class="getProductColor(product)">
                                        
                                        <!-- Badge Kuantitas di Keranjang -->
                                        <div x-show="getCartQty(product.id) > 0" 
                                             class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-[#18181b] text-white rounded-full flex items-center justify-center text-[10px] font-bold shadow-md z-10" 
                                             x-text="getCartQty(product.id)">
                                        </div>
                                        
                                        <!-- Inisial Huruf Besar di Tengah -->
                                        <span class="text-3xl font-black tracking-tight select-none" 
                                              :class="getProductTextColor(product)"
                                              x-text="product.name.charAt(0).toUpperCase()">
                                        </span>
                                    </div>

                                    <!-- Info Produk di Bawah Kotak Warna -->
                                    <div class="mt-2.5">
                                        <h3 class="font-bold text-gray-900 text-xs leading-snug truncate" x-text="product.name"></h3>
                                        <div class="flex justify-between items-baseline mt-1">
                                            <p class="text-xs text-gray-600 font-semibold" x-text="formatPrice(product.selling_price)"></p>
                                            <span class="text-[10px] font-medium" :class="product.stock <= 5 ? 'text-amber-600 font-bold' : 'text-gray-400'" x-text="'Stok: ' + product.stock"></span>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <!-- Empty State -->
                            <div x-show="filteredProducts.length === 0" class="col-span-full py-16 text-center text-gray-400">
                                <svg class="w-12 h-12 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                <p class="text-xs font-semibold">Tidak ada produk ditemukan</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT AREA: Panel Pesanan / Cart -->
                <div class="w-full lg:w-[360px] xl:w-[380px] bg-white rounded-2xl p-4 sm:p-5 shadow-xs border border-gray-200/80 flex flex-col shrink-0 justify-between max-h-[45vh] lg:max-h-full">
                    
                    <!-- Header Pesanan & List Items -->
                    <div class="flex flex-col overflow-hidden flex-1">
                        <!-- Title & Nota Number -->
                        <div class="flex justify-between items-baseline mb-2.5 shrink-0">
                            <h2 class="text-sm sm:text-base font-extrabold text-gray-900">Pesanan</h2>
                            <span class="text-xs text-gray-400 font-medium" x-text="'Nota #' + invoiceNumber"></span>
                        </div>

                        <!-- Customer Indicator -->
                        <div class="mb-2.5 shrink-0 py-1.5 px-3 bg-gray-50 rounded-xl border border-gray-100 flex justify-between items-center text-xs">
                            <span class="text-gray-500">Pelanggan:</span>
                            <div class="flex items-center gap-1.5">
                                <button @click="showCustomerModal = true" class="font-bold text-teal-700 hover:underline" x-text="selectedCustomer ? selectedCustomer.name : 'Umum / Pilih'"></button>
                                <button x-show="selectedCustomer" @click="selectedCustomer = null" class="text-rose-500 font-bold hover:text-rose-700">&times;</button>
                            </div>
                        </div>

                        <!-- Cart Item Rows -->
                        <div class="flex-1 overflow-y-auto space-y-3 pr-1">
                            <template x-for="(item, index) in cart" :key="item.id">
                                <div class="flex items-center justify-between gap-2 py-0.5">
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-xs font-bold text-gray-900 leading-tight truncate" x-text="item.name"></h4>
                                        <p class="text-[11px] text-gray-500 mt-0.5" x-text="formatPrice(item.selling_price)"></p>
                                    </div>

                                    <!-- Selector Quantity: [ - ]  1  [ + ] -->
                                    <div class="flex items-center border border-gray-200 rounded-lg bg-white shrink-0">
                                        <button @click="decreaseQty(index)" class="w-6 h-6 flex items-center justify-center text-gray-600 hover:text-black font-semibold text-xs">-</button>
                                        <span class="w-6 text-center text-xs font-bold text-gray-900" x-text="item.qty"></span>
                                        <button @click="increaseQty(index)" class="w-6 h-6 flex items-center justify-center text-gray-600 hover:text-black font-semibold text-xs">+</button>
                                    </div>

                                    <!-- Total Item -->
                                    <div class="text-xs font-bold text-gray-900 w-16 text-right shrink-0" x-text="formatPrice(item.selling_price * item.qty)"></div>
                                </div>
                            </template>

                            <!-- Empty Cart State -->
                            <div x-show="cart.length === 0" class="h-28 flex flex-col items-center justify-center text-gray-400">
                                <p class="text-xs font-medium">Belum ada item di keranjang</p>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Summary, Metode Pembayaran, & Action Buttons -->
                    <div class="shrink-0 pt-3 border-t border-gray-100 space-y-3">
                        
                        <!-- Subtotal, Diskon, PPN -->
                        <div class="space-y-1.5 text-xs">
                            <div class="flex justify-between text-gray-600 font-medium">
                                <span>Subtotal</span>
                                <span class="text-gray-900 font-semibold" x-text="formatPrice(subtotal)"></span>
                            </div>
                            
                            <!-- Diskon (Clickable) -->
                            <div class="flex justify-between text-gray-600 font-medium cursor-pointer hover:text-teal-700" 
                                 @click="showDiscountModal = true">
                                <span class="flex items-center gap-1">
                                    <span>Diskon</span>
                                    <svg class="w-3 h-3 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </span>
                                <span :class="discountAmount > 0 ? 'text-rose-600 font-bold' : 'text-gray-900'" 
                                      x-text="discountAmount > 0 ? '- ' + formatPrice(discountAmount) : 'Rp 0'"></span>
                            </div>

                            <div class="flex justify-between text-gray-600 font-medium">
                                <span x-text="'PPN ' + taxPercentage + '%'"></span>
                                <span class="text-gray-900 font-semibold" x-text="formatPrice(taxAmount)"></span>
                            </div>
                        </div>

                        <!-- Total -->
                        <div class="flex justify-between items-baseline pt-2 border-t border-gray-100">
                            <span class="text-xs sm:text-sm font-bold text-gray-900">Total</span>
                            <span class="text-xl sm:text-2xl font-black text-gray-950" x-text="formatPrice(totalAmount)"></span>
                        </div>

                        <!-- METODE PEMBAYARAN -->
                        <div>
                            <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">METODE PEMBAYARAN</span>
                            <div class="grid grid-cols-2 gap-1.5">
                                <!-- Tunai -->
                                <button @click="paymentMethod = 'cash'" 
                                        :class="paymentMethod === 'cash' ? 'border-[#0d6e53] bg-[#e6f4ea] text-[#0d6e53] font-bold ring-1 ring-[#0d6e53]' : 'border-gray-200 bg-white text-gray-700 font-medium hover:bg-gray-50'" 
                                        class="border rounded-xl py-2 text-xs text-center transition-all">
                                    Tunai
                                </button>
                                <!-- Non Tunai (Midtrans) -->
                                <button @click="paymentMethod = 'midtrans'" 
                                        :class="paymentMethod === 'midtrans' ? 'border-[#0d6e53] bg-[#e6f4ea] text-[#0d6e53] font-bold ring-1 ring-[#0d6e53]' : 'border-gray-200 bg-white text-gray-700 font-medium hover:bg-gray-50'" 
                                        class="border rounded-xl py-2 text-xs text-center transition-all">
                                    Non Tunai
                                </button>
                            </div>
                        </div>

                        <!-- TOMBOL ACTION: Batal | Tahan | Bayar -->
                        <div class="flex items-center gap-2 pt-1">
                            <button @click="clearCart()" 
                                    :disabled="cart.length === 0"
                                    class="px-3 py-2.5 border border-gray-300 text-gray-700 rounded-xl text-xs font-bold hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                                Batal
                            </button>
                            
                            <button @click="cart.length > 0 ? holdCurrentTransaction() : showHeldModal = true" 
                                    class="px-3 py-2.5 border border-gray-300 text-gray-700 rounded-xl text-xs font-bold hover:bg-gray-50 flex items-center gap-1 transition-colors relative">
                                <span>Tahan</span>
                                <span x-show="heldCount > 0" class="px-1.5 py-0.2 bg-teal-700 text-white rounded-full text-[10px]" x-text="heldCount"></span>
                            </button>
                            
                            <button @click="startPayment()" 
                                    :disabled="cart.length === 0 || isProcessing" 
                                    class="flex-1 py-2.5 bg-[#0d6e53] hover:bg-[#0a5742] text-white rounded-xl text-xs font-extrabold shadow-sm disabled:opacity-40 disabled:cursor-not-allowed transition-all flex items-center justify-center gap-2">
                                <span x-show="isProcessing">Memproses...</span>
                                <span x-show="!isProcessing">Bayar &rarr;</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 1: Dialog Pembayaran Tunai (Kembalian & Uang Cepat) -->
    <div x-show="showCashModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4">
        <div @click.away="showCashModal = false" class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden p-6 space-y-5">
            <div class="flex justify-between items-center border-b pb-3">
                <h3 class="text-base font-extrabold text-gray-900">Pembayaran Tunai</h3>
                <button @click="showCashModal = false" class="text-gray-400 hover:text-gray-600 text-2xl font-bold leading-none">&times;</button>
            </div>

            <!-- Total Tagihan -->
            <div class="text-center py-2 bg-gray-50 rounded-2xl border border-gray-100">
                <span class="text-xs text-gray-500 font-semibold">Total Tagihan</span>
                <div class="text-2xl font-black text-gray-900 mt-0.5" x-text="formatPrice(totalAmount)"></div>
            </div>

            <!-- Input Uang Diterima -->
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5">Uang Diterima (Rp)</label>
                <input type="number" x-model.number="receivedCash" 
                       class="w-full px-4 py-3 border-2 border-gray-200 rounded-2xl text-lg font-black text-gray-900 focus:outline-none focus:border-[#0d6e53] text-center" 
                       placeholder="0" autofocus>
            </div>

            <!-- Tombol Nominal Cepat -->
            <div class="grid grid-cols-3 gap-2">
                <button @click="receivedCash = totalAmount" class="py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-xl text-xs font-bold transition-colors">Uang Pas</button>
                <button @click="receivedCash = 10000" class="py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-xl text-xs font-bold transition-colors">10.000</button>
                <button @click="receivedCash = 20000" class="py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-xl text-xs font-bold transition-colors">20.000</button>
                <button @click="receivedCash = 50000" class="py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-xl text-xs font-bold transition-colors">50.000</button>
                <button @click="receivedCash = 100000" class="py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-xl text-xs font-bold transition-colors">100.000</button>
                <button @click="receivedCash = 200000" class="py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-xl text-xs font-bold transition-colors">200.000</button>
            </div>

            <!-- Kembalian -->
            <div class="flex justify-between items-center px-4 py-3 bg-[#e6f4ea] rounded-2xl border border-[#0d6e53]/20">
                <span class="text-xs font-bold text-[#0d6e53]">Kembalian:</span>
                <span class="text-lg font-black text-[#0d6e53]" x-text="formatPrice(Math.max(0, receivedCash - totalAmount))"></span>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-2 pt-2">
                <button @click="showCashModal = false" class="w-1/3 py-3 border border-gray-300 rounded-2xl text-xs font-bold text-gray-700 hover:bg-gray-50 transition-colors">Batal</button>
                <button @click="submitCheckout()" 
                        :disabled="receivedCash < totalAmount || isProcessing" 
                        class="flex-1 py-3 bg-[#0d6e53] hover:bg-[#0a5742] text-white rounded-2xl text-xs font-black shadow-md disabled:opacity-40 disabled:cursor-not-allowed transition-all">
                    <span x-show="isProcessing">Memproses...</span>
                    <span x-show="!isProcessing">Selesaikan Transaksi</span>
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL 2: Struk Transaksi Selesai -->
    <div x-show="showReceiptModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4">
        <div @click.away="closeReceiptModal()" class="bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden flex flex-col max-h-[90vh]">
            <div class="p-4 bg-[#0d6e53] text-white flex justify-between items-center shrink-0">
                <div>
                    <h3 class="font-bold text-sm">Transaksi Berhasil!</h3>
                    <p class="text-[11px] text-teal-100">Nota siap dicetak atau dikirim</p>
                </div>
                <button @click="closeReceiptModal()" class="text-white hover:text-teal-200 text-xl font-bold">&times;</button>
            </div>

            <!-- Area Pratinjau Struk Cetak -->
            <div id="receipt-print-area" class="p-5 overflow-y-auto flex-1 font-mono text-xs text-gray-800 space-y-3 bg-white">
                <div class="text-center pb-3 border-b border-dashed border-gray-300">
                    <h2 class="font-bold text-sm text-gray-900 uppercase">{{ auth()->user()->business->name ?? 'POS' }}</h2>
                    <p class="text-[10px] text-gray-500">{{ auth()->user()->business->address ?? 'Alamat Toko' }}</p>
                    <p class="text-[10px] text-gray-500">{{ auth()->user()->business->phone ?? '' }}</p>
                </div>

                <div class="flex justify-between text-[11px] text-gray-500">
                    <span x-text="'No: ' + (receiptData?.sale?.invoice_number || invoiceNumber)"></span>
                    <span x-text="new Date().toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'})"></span>
                </div>
                <div class="text-[11px] text-gray-500">
                    Kasir: {{ auth()->user()->name }}
                    <template x-if="receiptData?.sale?.customer?.name">
                        <span x-text="' | Pelanggan: ' + receiptData.sale.customer.name"></span>
                    </template>
                </div>

                <!-- Items Table -->
                <div class="border-t border-b border-dashed border-gray-300 py-2 space-y-1">
                    <template x-for="item in lastSaleItems" :key="item.id">
                        <div class="flex justify-between text-[11px]">
                            <span class="flex-1 truncate pr-2" x-text="item.name + ' x' + item.qty"></span>
                            <span class="font-semibold" x-text="formatPrice(item.selling_price * item.qty)"></span>
                        </div>
                    </template>
                </div>

                <!-- Rincian Total Struk -->
                <div class="space-y-1 text-[11px]">
                    <div class="flex justify-between text-gray-600">
                        <span>Subtotal</span><span x-text="formatPrice(receiptData?.sale?.subtotal || subtotal)"></span>
                    </div>
                    <template x-if="(receiptData?.sale?.discount_amount || discountAmount) > 0">
                        <div class="flex justify-between text-rose-600">
                            <span>Diskon</span><span x-text="'- ' + formatPrice(receiptData?.sale?.discount_amount || discountAmount)"></span>
                        </div>
                    </template>
                    <div class="flex justify-between text-gray-600">
                        <span x-text="'PPN ' + taxPercentage + '%'"></span>
                        <span x-text="formatPrice(receiptData?.sale?.tax_amount || taxAmount)"></span>
                    </div>
                    <div class="flex justify-between font-bold text-sm text-gray-900 pt-1 border-t border-gray-200">
                        <span>TOTAL</span><span x-text="formatPrice(receiptData?.sale?.total_amount || totalAmount)"></span>
                    </div>
                    <div class="flex justify-between text-gray-600 pt-1">
                        <span x-text="'Bayar (' + (paymentMethod).toUpperCase() + ')'"></span>
                        <span x-text="formatPrice(paymentMethod === 'cash' ? receivedCash : (receiptData?.sale?.total_amount || totalAmount))"></span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>Kembalian</span><span x-text="formatPrice(receiptData?.change || 0)"></span>
                    </div>
                </div>

                <div class="text-center pt-3 text-[10px] text-gray-400 border-t border-dashed border-gray-300 mt-2">
                    Terima kasih atas kunjungan Anda!<br>
                    Barang yang dibeli tidak dapat ditukar.
                </div>
            </div>

            <!-- Tombol Cetak / WA / Selesai -->
            <div class="p-3 bg-gray-50 flex gap-2 no-print border-t border-gray-200 shrink-0">
                <button @click="window.print()" class="flex-1 py-2 bg-gray-800 hover:bg-gray-900 text-white rounded-xl text-xs font-bold transition-colors">Cetak</button>
                <button @click="sendReceiptWA()" class="flex-1 py-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl text-xs font-bold transition-colors">WhatsApp</button>
                <button @click="closeReceiptModal()" class="px-4 py-2 bg-[#0d6e53] hover:bg-[#0a5742] text-white rounded-xl text-xs font-bold transition-colors">Selesai</button>
            </div>
        </div>
    </div>

    <!-- MODAL MIDTRANS: Menunggu Pembayaran -->
    <div x-show="showMidtransModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4">
        <div @click.away="midtransPaymentStatus !== 'pending' ? showMidtransModal = false : null" class="bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden p-6 space-y-5 text-center">
            <div class="flex justify-between items-center border-b pb-3">
                <h3 class="text-base font-extrabold text-gray-900">Pembayaran Non Tunai</h3>
                <button @click="showMidtransModal = false" class="text-gray-400 hover:text-gray-600 text-2xl font-bold leading-none">&times;</button>
            </div>

            <div class="text-center py-2 bg-gray-50 rounded-2xl border border-gray-100">
                <span class="text-xs text-gray-500 font-semibold">Total Tagihan</span>
                <div class="text-xl font-black text-gray-900 mt-0.5" x-text="formatPrice(pendingMidtransTotal || totalAmount)"></div>
                <div class="text-[10px] text-gray-500 mt-1" x-text="pendingMidtransInvoice ? pendingMidtransInvoice : invoiceNumber"></div>
            </div>

            <div class="space-y-2">
                <template x-if="midtransPaymentStatus === 'pending'">
                    <div class="text-sm text-gray-600">Menunggu penyelesaian pembayaran di Midtrans...</div>
                </template>
                <template x-if="midtransPaymentStatus === 'completed'">
                    <div class="text-sm font-semibold text-emerald-700">Pembayaran berhasil! Menampilkan struk...</div>
                </template>
                <template x-if="midtransPaymentStatus === 'failed'">
                    <div class="text-sm font-semibold text-rose-700">Pembayaran gagal atau kadaluarsa.</div>
                </template>
            </div>

            <div class="flex gap-2 pt-2">
                <button @click="showMidtransModal = false" class="flex-1 py-3 border border-gray-300 rounded-2xl text-xs font-bold text-gray-700 hover:bg-gray-50 transition-colors">Tutup</button>
            </div>
        </div>
    </div>
    <div x-show="showDiscountModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4">
        <div @click.away="showDiscountModal = false" class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden p-5 space-y-4">
            <div class="flex justify-between items-center border-b pb-2">
                <h3 class="font-bold text-sm text-gray-900">Atur Diskon</h3>
                <button @click="showDiscountModal = false" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
            </div>

            <div class="flex gap-2">
                <button @click="tempDiscount.type = 'percentage'" 
                        :class="tempDiscount.type === 'percentage' ? 'bg-[#0d6e53] text-white font-bold' : 'bg-gray-100 text-gray-700 font-medium'"
                        class="flex-1 py-2 text-xs rounded-xl transition-colors">Persen (%)</button>
                <button @click="tempDiscount.type = 'fixed'" 
                        :class="tempDiscount.type === 'fixed' ? 'bg-[#0d6e53] text-white font-bold' : 'bg-gray-100 text-gray-700 font-medium'"
                        class="flex-1 py-2 text-xs rounded-xl transition-colors">Nominal (Rp)</button>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1" x-text="tempDiscount.type === 'percentage' ? 'Besaran Diskon (%)' : 'Besaran Diskon (Rp)'"></label>
                <input type="number" x-model.number="tempDiscount.value" class="w-full px-3 py-2 border border-gray-300 rounded-xl text-xs font-bold focus:ring-teal-700 focus:border-teal-700" placeholder="0">
            </div>

            <div class="flex gap-2 pt-2">
                <button @click="resetDiscount()" class="w-1/3 py-2 border rounded-xl text-xs font-semibold text-gray-700 hover:bg-gray-50">Hapus</button>
                <button @click="applyDiscount()" class="flex-1 py-2 bg-[#0d6e53] text-white rounded-xl text-xs font-bold">Terapkan</button>
            </div>
        </div>
    </div>

    <!-- MODAL 4: Daftar Pesanan Ditahan (Hold) -->
    <div x-show="showHeldModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4">
        <div @click.away="showHeldModal = false" class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden flex flex-col max-h-[80vh] p-5">
            <div class="flex justify-between items-center border-b pb-3 mb-3 shrink-0">
                <h3 class="font-bold text-sm text-gray-900" x-text="'Pesanan Ditahan (' + heldTransactions.length + ')'"></h3>
                <button @click="showHeldModal = false" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
            </div>

            <div class="overflow-y-auto flex-1 space-y-2">
                <template x-for="held in heldTransactions" :key="held.id">
                    <div class="p-3 rounded-xl border border-gray-200 bg-gray-50 flex justify-between items-center text-xs">
                        <div>
                            <div class="font-bold text-gray-900" x-text="held.customer_name || 'Pesanan #Tahan'"></div>
                            <div class="text-[11px] text-gray-500 mt-0.5" x-text="(Array.isArray(held.items) ? held.items.length : JSON.parse(held.items || '[]').length) + ' Item • ' + formatPrice(held.subtotal)"></div>
                        </div>
                        <div class="flex gap-1.5">
                            <button @click="resumeHeldTransaction(held.id)" class="px-3 py-1.5 bg-[#0d6e53] hover:bg-[#095741] text-white rounded-lg font-bold text-xs transition-colors">Buka</button>
                            <button @click="deleteHeldTransaction(held.id)" class="p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                    </div>
                </template>
                <div x-show="heldTransactions.length === 0" class="py-8 text-center text-gray-400 text-xs font-medium">
                    Tidak ada pesanan yang ditahan.
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 5: Pilih Pelanggan -->
    <div x-show="showCustomerModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4">
        <div @click.away="showCustomerModal = false" class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden p-5 space-y-4">
            <div class="flex justify-between items-center border-b pb-2 shrink-0">
                <h3 class="font-bold text-sm text-gray-900">Pilih Pelanggan</h3>
                <button @click="showCustomerModal = false" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
            </div>

            <div>
                <input type="text" x-model="customerSearch" @input.debounce.300ms="searchCustomers()" class="w-full px-3 py-2 border border-gray-300 rounded-xl text-xs placeholder-gray-400 focus:ring-teal-700 focus:border-teal-700" placeholder="Cari nama atau telepon...">
            </div>

            <div class="max-h-60 overflow-y-auto space-y-1">
                <button @click="selectCustomer(null)" class="w-full text-left p-2.5 hover:bg-gray-50 rounded-xl text-xs flex justify-between items-center border border-transparent hover:border-gray-200">
                    <span class="font-bold text-gray-700">Pelanggan Umum (Tanpa Member)</span>
                </button>
                <template x-for="cust in customersList" :key="cust.id">
                    <button @click="selectCustomer(cust)" class="w-full text-left p-2.5 hover:bg-teal-50 rounded-xl text-xs flex justify-between items-center border border-gray-100 hover:border-teal-200">
                        <div>
                            <div class="font-bold text-gray-900" x-text="cust.name"></div>
                            <div class="text-[11px] text-gray-500" x-text="cust.phone || cust.email || '-'"></div>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-100 text-teal-800 uppercase" x-text="cust.level || 'Member'"></span>
                    </button>
                </template>
            </div>
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
                },

                getProductColor(product) {
                    const catName = (product.category?.name || '').toLowerCase();
                    if (catName.includes('minum')) {
                        return 'bg-[#d8ece5]'; // Soft Mint Pastel
                    } else if (catName.includes('makan')) {
                        return 'bg-[#fae7ce]'; // Soft Peach/Caramel Pastel
                    } else if (catName.includes('camil')) {
                        return 'bg-[#e7e4f7]'; // Soft Lavender Pastel
                    }
                    const colors = ['bg-[#d8ece5]', 'bg-[#fae7ce]', 'bg-[#e7e4f7]'];
                    return colors[product.id % colors.length];
                },

                getProductTextColor(product) {
                    const catName = (product.category?.name || '').toLowerCase();
                    if (catName.includes('minum')) {
                        return 'text-[#0d6e53]';
                    } else if (catName.includes('makan')) {
                        return 'text-[#8f4714]';
                    } else if (catName.includes('camil')) {
                        return 'text-[#4c2d82]';
                    }
                    return 'text-[#0d6e53]';
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

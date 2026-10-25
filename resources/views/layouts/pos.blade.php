<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Laravel POS') }}</title>
    <link rel="icon" type="image/png" href="{{ Storage::url('logo mypos.png') }}">
    <link rel="apple-touch-icon" href="{{ Storage::url('logo mypos.png') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-[#EEF7FF] text-[#1D3B47]" x-data="{ sidebarOpen: window.innerWidth >= 1024, mobileMenuOpen: false }">
    
    <!-- Offline Indicator -->
    <div class="fixed top-0 left-0 right-0 z-50 bg-[#d9383a] text-white text-center py-1.5 text-xs font-semibold shadow-md transition-all" x-show="!$store.app.isOnline" style="display: none;">
        Mode Offline: Koneksi internet terputus. Data transaksi akan disimpan di antrean lokal.
    </div>

    <!-- Mobile Overlay Backdrop -->
    <div x-show="sidebarOpen" 
         @click="sidebarOpen = false" 
         class="fixed inset-0 z-40 bg-black/40 backdrop-blur-xs lg:hidden transition-opacity" 
         x-cloak 
         style="display: none;"></div>

    <!-- Sidebar Navbar (Light Mint Clean Theme) -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
           class="fixed top-0 left-0 h-screen w-60 bg-[#CDE8E5] border-r border-[#B6D9D5] z-40 flex flex-col justify-between transition-transform duration-300 ease-in-out shadow-xl lg:shadow-none">
        
        <!-- Sidebar Top Area -->
        <div class="p-4 flex flex-col flex-1 overflow-y-auto">
            <!-- Brand -->
            <div class="flex items-center justify-between pb-6 pt-2 px-2">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-[#4D869C] text-white font-black text-xl flex items-center justify-center shrink-0 shadow-sm">
                        P
                    </div>
                    <div class="font-black text-[#1D3B47] text-xl tracking-tight">
                        MyPos
                    </div>
                </div>
                <button @click="sidebarOpen = false" class="lg:hidden text-[#1D3B47]/70 hover:text-[#1D3B47] p-1.5 rounded-xl hover:bg-[#B6D9D5] transition-colors" title="Tutup Menu">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="space-y-1.5 flex-1">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-2xl text-sm font-bold transition-all duration-200 ease-in-out {{ request()->routeIs('dashboard') ? 'bg-[#4D869C] text-white shadow-xs' : 'text-[#1D3B47] hover:text-[#192d35] hover:bg-[#B6D9D5] font-semibold' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('pos.index') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-2xl text-sm font-bold transition-all duration-200 ease-in-out {{ request()->routeIs('pos.*') ? 'bg-[#4D869C] text-white shadow-xs' : 'text-[#1D3B47] hover:text-[#192d35] hover:bg-[#B6D9D5] font-semibold' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span>Kasir</span>
                </a>

                <a href="{{ route('reports.sales') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-2xl text-sm font-bold transition-all duration-200 ease-in-out {{ request()->routeIs('reports.*') ? 'bg-[#4D869C] text-white shadow-xs' : 'text-[#1D3B47] hover:text-[#192d35] hover:bg-[#B6D9D5] font-semibold' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    <span>Laporan</span>
                </a>

                <a href="{{ route('products.index') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-2xl text-sm font-bold transition-all duration-200 ease-in-out {{ request()->routeIs('products.*') ? 'bg-[#4D869C] text-white shadow-xs' : 'text-[#1D3B47] hover:text-[#192d35] hover:bg-[#B6D9D5] font-semibold' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    <span>Produk & Stok</span>
                </a>

                <a href="{{ route('customers.index') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-2xl text-sm font-bold transition-all duration-200 ease-in-out {{ request()->routeIs('customers.*') ? 'bg-[#4D869C] text-white shadow-xs' : 'text-[#1D3B47] hover:text-[#192d35] hover:bg-[#B6D9D5] font-semibold' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span>Pelanggan</span>
                </a>

                @can('users view')
                <a href="{{ route('users.index') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-2xl text-sm font-bold transition-all duration-200 ease-in-out {{ request()->routeIs('users.*') ? 'bg-[#4D869C] text-white shadow-xs' : 'text-[#1D3B47] hover:text-[#192d35] hover:bg-[#B6D9D5] font-semibold' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    <span>Karyawan</span>
                </a>
                @endcan

                @can('business settings')
                <a href="{{ route('business.settings') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-2xl text-sm font-bold transition-all duration-200 ease-in-out {{ request()->routeIs('business.*') ? 'bg-[#4D869C] text-white shadow-xs' : 'text-[#1D3B47] hover:text-[#192d35] hover:bg-[#B6D9D5] font-semibold' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <span>Pengaturan</span>
                </a>
                @endcan
            </nav>
        </div>

        <!-- Sidebar Bottom User -->
        <div class="p-4 border-t border-[#B6D9D5]" x-data="{ open: false }">
            <div class="relative">
                <button @click="open = !open" @click.away="open = false" class="w-full flex items-center justify-between p-2 rounded-2xl hover:bg-[#B6D9D5] transition-colors text-left">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-8 h-8 rounded-full bg-[#4D869C] text-white flex items-center justify-center text-xs font-bold shrink-0">
                            {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                        </div>
                        <span class="text-sm font-bold text-[#1D3B47] truncate">{{ auth()->user()->name ?? 'Admin' }}</span>
                    </div>
                    <svg class="w-4 h-4 text-[#1D3B47]/70 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div x-show="open" x-cloak class="absolute bottom-full left-0 w-full mb-2 bg-white border border-[#D6E5ED] rounded-2xl shadow-xl overflow-hidden py-1 z-50">
                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-xs font-semibold text-[#1D3B47] hover:bg-[#EEF7FF]">Profil Pengguna</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full text-left px-4 py-2 text-xs font-semibold text-[#d9383a] hover:bg-[#fdeeee]">Keluar (Logout)</button>
                    </form>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div :class="sidebarOpen ? 'lg:pl-60' : 'lg:pl-0'" class="transition-all duration-300 ease-in-out min-h-screen flex flex-col bg-[#EEF7FF]">
        
        <!-- Seamless Header matching Light Mint Mockups -->
        <header class="px-4 sm:px-6 lg:px-8 py-5 flex items-center justify-between gap-4 shrink-0">
            <!-- Left: Mobile Toggle + Business Avatar & Name -->
            <div class="flex items-center gap-3 min-w-0">
                <button @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-xl text-[#1D3B47] hover:text-black hover:bg-[#dce9ee] lg:hidden transition-colors shrink-0" title="Toggle Menu">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-full bg-[#518e9a] text-white font-black flex items-center justify-center text-xs tracking-wider shrink-0 shadow-xs">
                        {{ strtoupper(substr(auth()->user()->business->name ?? 'WB', 0, 2)) }}
                    </div>
                    <span class="font-black text-sm sm:text-base text-[#1D3B47] uppercase tracking-wide truncate">
                        {{ auth()->user()->business->name ?? 'WARKOP BUDI' }}
                    </span>
                </div>
            </div>

            <!-- Center: Search Pill (desktop) -->
            <div class="hidden sm:flex flex-1 max-w-xl mx-4">
                @hasSection('topbar-search')
                    @yield('topbar-search')
                @else
                    <div class="w-full relative">
                        <input type="text" placeholder="Cari" class="w-full bg-[#CDE8E5] border-0 rounded-full px-5 py-2.5 text-sm text-[#1D3B47] placeholder-[#75929E] focus:outline-none focus:ring-2 focus:ring-[#4D869C] transition-all">
                    </div>
                @endif
            </div>

            <!-- Right: Online Badge -->
            <div class="flex items-center gap-3 shrink-0">
                <div class="flex items-center gap-2 px-4 py-2 bg-[#CDE8E5] rounded-full">
                    <span class="w-2.5 h-2.5 rounded-full" :class="$store.app?.isOnline !== false ? 'bg-[#4D869C]' : 'bg-[#d9383a]'"></span>
                    <span class="text-xs font-bold text-[#1D3B47]" x-text="$store.app?.isOnline !== false ? 'Online' : 'Offline'">Online</span>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 px-4 sm:px-6 lg:px-8 pb-28 sm:pb-8">
            @yield('content')
        </main>

        <!-- Mobile Bottom Navigation Bar (Matching Light Clean Mobile Theme) -->
        <nav x-show="!$store.modal.open" x-cloak class="sm:hidden fixed bottom-0 left-0 right-0 z-40 bg-[#CDE8E5] border-t border-[#B6D9D5] rounded-t-3xl px-4 py-2.5 flex items-center justify-around shadow-lg transition-all">
            <!-- Dashboard -->
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'bg-[#4D869C] text-white font-bold px-4 py-2 rounded-2xl flex flex-col items-center justify-center text-[10px]' : 'text-[#1D3B47] hover:text-black flex flex-col items-center text-[10px] font-medium gap-1' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span>Dashboard</span>
            </a>
            <!-- Kasir -->
            <a href="{{ route('pos.index') }}" class="{{ request()->routeIs('pos.*') ? 'bg-[#4D869C] text-white font-bold px-4 py-2 rounded-2xl flex flex-col items-center justify-center text-[10px]' : 'text-[#1D3B47] hover:text-black flex flex-col items-center text-[10px] font-medium gap-1' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span>Kasir</span>
            </a>
            <!-- Produk -->
            <a href="{{ route('products.index') }}" class="{{ request()->routeIs('products.*') ? 'bg-[#4D869C] text-white font-bold px-4 py-2 rounded-2xl flex flex-col items-center justify-center text-[10px]' : 'text-[#1D3B47] hover:text-black flex flex-col items-center text-[10px] font-medium gap-1' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                <span>Produk</span>
            </a>
            <!-- Laporan -->
            <a href="{{ route('reports.sales') }}" class="{{ request()->routeIs('reports.*') ? 'bg-[#4D869C] text-white font-bold px-4 py-2 rounded-2xl flex flex-col items-center justify-center text-[10px]' : 'text-[#1D3B47] hover:text-black flex flex-col items-center text-[10px] font-medium gap-1' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                <span>Laporan</span>
            </a>
            <!-- Lainnya (toggles modal/drawer) -->
            <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-[#1D3B47] hover:text-black flex flex-col items-center text-[10px] font-medium gap-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z"></path></svg>
                <span>Lainnya</span>
            </button>
        </nav>

        <!-- Mobile Menu Drawer (Lainnya) -->
        <div x-show="mobileMenuOpen" 
             @click="mobileMenuOpen = false" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs sm:hidden"
             x-cloak 
             style="display: none;">
            <div @click.stop 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-4"
                 class="fixed bottom-0 left-0 right-0 bg-[#CDE8E5] rounded-t-3xl p-6 text-[#1D3B47] z-50 space-y-4 shadow-2xl border-t border-[#B6D9D5]">
                <div class="flex items-center justify-between pb-3 border-b border-[#B6D9D5]">
                    <h3 class="font-bold text-base text-[#1D3B47]">Menu Lainnya</h3>
                    <button @click="mobileMenuOpen = false" class="text-[#1D3B47] hover:text-black p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="grid grid-cols-2 gap-3 pt-2">
                    <a href="{{ route('customers.index') }}" class="flex items-center gap-3 p-3 rounded-2xl bg-[#CDE8E5] text-[#1D3B47] hover:bg-[#d5e5ec] transition-colors">
                        <svg class="w-5 h-5 text-[#4D869C]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        <span class="text-xs font-bold">Pelanggan</span>
                    </a>
                    @can('users view')
                    <a href="{{ route('users.index') }}" class="flex items-center gap-3 p-3 rounded-2xl bg-[#CDE8E5] text-[#1D3B47] hover:bg-[#d5e5ec] transition-colors">
                        <svg class="w-5 h-5 text-[#4D869C]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        <span class="text-xs font-bold">Karyawan</span>
                    </a>
                    @endcan
                    @can('business settings')
                    <a href="{{ route('business.settings') }}" class="flex items-center gap-3 p-3 rounded-2xl bg-[#CDE8E5] text-[#1D3B47] hover:bg-[#d5e5ec] transition-colors">
                        <svg class="w-5 h-5 text-[#4D869C]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        <span class="text-xs font-bold">Pengaturan</span>
                    </a>
                    @endcan
                </div>
                <div class="pt-3 border-t border-[#B6D9D5] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-[#4D869C] text-white flex items-center justify-center text-xs font-bold">
                            {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                        </div>
                        <span class="text-xs font-bold text-[#1D3B47]">{{ auth()->user()->name ?? 'Admin' }}</span>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-xs font-bold text-[#d9383a] hover:bg-[#fdeeee] py-1.5 px-3 rounded-xl bg-white/60 transition-colors">
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>

    @stack('scripts')
</body>
</html>



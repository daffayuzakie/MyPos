<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Laravel POS') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-[#f4f6f8] text-gray-900" x-data="{ sidebarOpen: window.innerWidth >= 1024 }">
    
    <!-- Offline Indicator -->
    <div class="fixed top-0 left-0 right-0 z-50 bg-rose-600 text-white text-center py-1.5 text-xs font-semibold shadow-md transition-all" x-show="!$store.app.isOnline" style="display: none;">
        Mode Offline: Koneksi internet terputus. Data transaksi akan disimpan di antrean lokal.
    </div>

    <!-- Mobile Overlay Backdrop -->
    <div x-show="sidebarOpen" 
         @click="sidebarOpen = false" 
         class="fixed inset-0 z-30 bg-black/40 backdrop-blur-xs lg:hidden transition-opacity" 
         x-cloak 
         style="display: none;"></div>

    <!-- Sidebar Navbar (Matching Reference Images 1-5) -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
           class="fixed top-0 left-0 h-screen w-56 bg-white border-r border-gray-200/80 z-40 flex flex-col transition-transform duration-300 ease-in-out shadow-xs">
        
        <!-- Sidebar Header: Brand Initial & Name -->
        <div class="flex items-center justify-between p-5 border-b border-gray-100">
            <div class="flex items-center gap-3 overflow-hidden">
                <img src="{{ str_replace(' ', '%20', Storage::url('logo mypos.png')) }}" alt="MyPos" class="h-9 w-auto object-contain shrink-0">
                <div class="font-bold text-gray-900 truncate leading-tight text-sm tracking-tight">
                    MyPos
                </div>
            </div>
            <!-- Close Sidebar Button (for mobile) -->
            <button @click="sidebarOpen = false" class="lg:hidden text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- 7 Primary Navigation Links Matching Reference Mockups -->
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            <a href="{{ route('dashboard') }}" class="pos-sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('pos.index') }}" class="pos-sidebar-link {{ request()->routeIs('pos.*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span>Kasir</span>
            </a>
            <a href="{{ route('reports.sales') }}" class="pos-sidebar-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                <span>Laporan</span>
            </a>
            <a href="{{ route('products.index') }}" class="pos-sidebar-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                <span>Produk & Stok</span>
            </a>
            <a href="{{ route('customers.index') }}" class="pos-sidebar-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span>Pelanggan</span>
            </a>
            <a href="{{ route('purchases.index') }}" class="pos-sidebar-link {{ request()->routeIs('purchases.*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                <span>Supplier</span>
            </a>
            <a href="{{ route('users.index') }}" class="pos-sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                <span>Karyawan</span>
            </a>
            <a href="{{ route('business.settings') }}" class="pos-sidebar-link {{ request()->routeIs('business.*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                <span>Pengaturan</span>
            </a>
        </nav>

        <!-- User Profile Dropdown in Sidebar Bottom -->
        <div class="p-3 border-t border-gray-200/80">
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
                    <a href="{{ route('business.settings') }}" class="block px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50">Pengaturan Profil</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full text-left px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50">Keluar (Logout)</button>
                    </form>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div :class="sidebarOpen ? 'lg:pl-56' : 'lg:pl-0'" class="transition-all duration-300 ease-in-out min-h-screen flex flex-col">
        <!-- Top Bar (model melayang/notch seperti layar Kasir) -->
        <div class="sticky top-0 z-20 px-3 sm:px-4 pt-3 sm:pt-4">
        <header class="bg-white border border-gray-200/80 rounded-2xl shadow-xs px-4 py-2.5 flex items-center gap-3 justify-between shrink-0">
            <!-- Left: Toggle + Brand + Business Info -->
            <div class="flex items-center gap-2.5 sm:gap-3.5 min-w-0">
                <button @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-xl text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition-colors shrink-0" title="Toggle Menu">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
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
                            {{ auth()->user()->name ?? 'Kasir' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Center: Pencarian per halaman -->
            <div class="flex-1 max-w-xl px-2">
                @yield('topbar-search')
            </div>

            <!-- Right: Online Status -->
            <div class="flex items-center gap-2 sm:gap-3 shrink-0 justify-end">
                <div class="hidden md:flex items-center gap-1.5 px-2.5 py-1 bg-gray-50 rounded-lg border border-gray-100">
                    <span class="w-2 h-2 rounded-full" :class="$store.app?.isOnline !== false ? 'bg-[#10b981]' : 'bg-rose-500'"></span>
                    <span class="text-[11px] font-semibold text-gray-600" x-text="$store.app?.isOnline !== false ? 'Online' : 'Offline'">Online</span>
                </div>
            </div>
        </header>
        </div>

        <main class="flex-1">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>

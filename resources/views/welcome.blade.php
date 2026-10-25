<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="MyPos — Aplikasi kasir dan manajemen toko untuk mencatat penjualan, mengelola stok, menerima pembayaran, dan membaca laporan.">

    <title>{{ config('app.name', 'MyPos') }} — Aplikasi Kasir & Manajemen Toko</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <meta name="theme-color" content="#4D869C">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-[#1D3B47] antialiased bg-white selection:bg-[#4D869C] selection:text-white" x-data="{ openFaq: null }">

    <!-- Navbar -->
    <header class="sticky top-0 z-50 bg-white/95 backdrop-blur border-b border-[#D6E5ED]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Brand -->
                <a href="{{ url('/') }}" class="flex items-center gap-3">
                    <img src="{{ asset('brand/mypos-wordmark.png') }}" alt="MyPos" width="900" height="331" class="h-9 w-auto">
                </a>

                <!-- Nav links -->
                <nav class="hidden md:flex items-center gap-8 text-sm font-bold text-[#1D3B47]/80">
                    <a href="#fitur" class="hover:text-[#4D869C] transition-colors">Fitur</a>
                    <a href="#peran" class="hover:text-[#4D869C] transition-colors">Peran</a>
                    <a href="#cara-mulai" class="hover:text-[#4D869C] transition-colors">Cara mulai</a>
                    <a href="#faq" class="hover:text-[#4D869C] transition-colors">FAQ</a>
                </nav>

                <!-- Actions -->
                <div class="flex items-center gap-2.5 sm:gap-3">
                    <a href="{{ route('login') }}" class="bg-[#CDE8E5] hover:bg-[#b8ded9] text-[#1D3B47] font-bold px-5 py-2.5 rounded-full text-xs sm:text-sm transition-colors">
                        Masuk
                    </a>
                    <a href="{{ route('business.register') }}" class="bg-[#4D869C] hover:bg-[#3d7083] text-white font-bold px-5 py-2.5 rounded-full text-xs sm:text-sm shadow-xs transition-colors">
                        Daftar Gratis
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="relative overflow-hidden pt-12 pb-20 lg:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                
                <!-- Left Hero Text -->
                <div class="lg:col-span-6 space-y-6">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#CDE8E5] text-[#1D3B47] text-xs font-bold">
                        <span class="w-2 h-2 rounded-full bg-[#4D869C]"></span>
                        <span>Aplikasi kasir dan manajemen toko</span>
                    </div>

                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-[#1D3B47] tracking-tight leading-[1.12]">
                        Kasir, stok, dan laporan <span class="relative inline-block text-[#4D869C] underline decoration-[#CDE8E5] decoration-wavy decoration-2">dalam satu</span> aplikasi.
                    </h1>

                    <p class="text-base sm:text-lg text-[#557785] leading-relaxed max-w-xl font-medium">
                        MyPos membantu pemilik usaha dan kasir mencatat penjualan, mengelola stok, menerima pembayaran, dan membaca laporan.
                    </p>

                    <div class="flex flex-wrap items-center gap-3 pt-2">
                        <a href="{{ route('business.register') }}" class="bg-[#4D869C] hover:bg-[#3d7083] text-white font-bold px-7 py-3 rounded-full text-sm shadow-xs transition-colors">
                            Daftar Gratis
                        </a>
                        <a href="{{ route('login') }}" class="bg-[#CDE8E5] hover:bg-[#b8ded9] text-[#1D3B47] font-bold px-7 py-3 rounded-full text-sm transition-colors">
                            Masuk
                        </a>
                    </div>

                    <!-- Bullet checks row -->
                    <div class="grid grid-cols-2 gap-3 pt-4 text-xs font-bold text-[#1D3B47]">
                        <div class="flex items-center gap-2">
                            <span class="w-5 h-5 rounded-full bg-[#4D869C] text-white flex items-center justify-center shrink-0">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            <span>Tunai dan Midtrans</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-5 h-5 rounded-full bg-[#4D869C] text-white flex items-center justify-center shrink-0">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            <span>Scan barcode</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-5 h-5 rounded-full bg-[#4D869C] text-white flex items-center justify-center shrink-0">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            <span>Struk dan WhatsApp</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-5 h-5 rounded-full bg-[#4D869C] text-white flex items-center justify-center shrink-0">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            <span>Poin loyalitas</span>
                        </div>
                    </div>
                </div>

                <!-- Right Hero Visual: App POS Mockup matching Landing.png -->
                <div class="lg:col-span-6">
                    <div class="bg-[#EEF7FF] border border-[#D6E5ED] rounded-3xl p-4 sm:p-6 shadow-xl relative overflow-hidden">
                        <div class="grid grid-cols-12 gap-3.5">
                            <!-- Left Products Column in Mockup -->
                            <div class="col-span-7 space-y-3">
                                <div class="flex items-center gap-1.5">
                                    <span class="bg-[#4D869C] text-white font-bold px-3 py-1 rounded-full text-[10px]">Semua</span>
                                    <span class="bg-[#CDE8E5] text-[#1D3B47] font-semibold px-3 py-1 rounded-full text-[10px]">Camilan</span>
                                    <span class="bg-[#CDE8E5] text-[#1D3B47] font-semibold px-3 py-1 rounded-full text-[10px]">Makanan</span>
                                </div>
                                <div class="grid grid-cols-3 gap-2">
                                    <div class="bg-white rounded-2xl p-2 border border-[#D6E5ED] flex flex-col justify-between">
                                        <div class="aspect-square rounded-xl bg-[#518e9a] text-white font-black text-sm flex items-center justify-center">P</div>
                                        <div class="mt-1.5"><div class="text-[9px] font-bold truncate">Pisang Goreng</div><div class="text-[9px] font-black">Rp 14.000</div></div>
                                    </div>
                                    <div class="bg-white rounded-2xl p-2 border border-[#D6E5ED] flex flex-col justify-between">
                                        <div class="aspect-square rounded-xl bg-[#9ec5c0] text-[#1D3B47] font-black text-sm flex items-center justify-center">K</div>
                                        <div class="mt-1.5"><div class="text-[9px] font-bold truncate">Kopi Susu</div><div class="text-[9px] font-black">Rp 22.000</div></div>
                                    </div>
                                    <div class="bg-white rounded-2xl p-2 border border-[#D6E5ED] flex flex-col justify-between">
                                        <div class="aspect-square rounded-xl bg-[#CDE8E5] text-[#1D3B47] font-black text-sm flex items-center justify-center">K</div>
                                        <div class="mt-1.5"><div class="text-[9px] font-bold truncate">Keripik Singkong</div><div class="text-[9px] font-black">Rp 12.000</div></div>
                                    </div>
                                    <div class="bg-white rounded-2xl p-2 border border-[#D6E5ED] flex flex-col justify-between">
                                        <div class="aspect-square rounded-xl bg-[#406a77] text-white font-black text-sm flex items-center justify-center">N</div>
                                        <div class="mt-1.5"><div class="text-[9px] font-bold truncate">Nasi Goreng</div><div class="text-[9px] font-black">Rp 28.000</div></div>
                                    </div>
                                    <div class="bg-white rounded-2xl p-2 border border-[#D6E5ED] flex flex-col justify-between">
                                        <div class="aspect-square rounded-xl bg-[#71a5a7] text-white font-black text-sm flex items-center justify-center">E</div>
                                        <div class="mt-1.5"><div class="text-[9px] font-bold truncate">Es Jeruk</div><div class="text-[9px] font-black">Rp 15.000</div></div>
                                    </div>
                                    <div class="bg-white rounded-2xl p-2 border border-[#D6E5ED] flex flex-col justify-between">
                                        <div class="aspect-square rounded-xl bg-[#CDE8E5] text-[#1D3B47] font-black text-sm flex items-center justify-center">C</div>
                                        <div class="mt-1.5"><div class="text-[9px] font-bold truncate">Coklat Batang</div><div class="text-[9px] font-black">Rp 15.000</div></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Cart Column in Mockup -->
                            <div class="col-span-5 bg-[#CDE8E5] rounded-2xl p-3 flex flex-col justify-between">
                                <div>
                                    <div class="flex justify-between items-center text-[10px] font-bold">
                                        <span>Pesanan</span>
                                        <span class="text-[#75929E]">#INV-0015</span>
                                    </div>
                                    <div class="mt-2 space-y-1.5 text-[9px]">
                                        <div class="flex justify-between"><span>Pisang Goreng 2x</span><span class="font-bold">Rp 28.000</span></div>
                                        <div class="flex justify-between"><span>Nasi Goreng 1x</span><span class="font-bold">Rp 28.000</span></div>
                                        <div class="flex justify-between"><span>Kopi Susu 1x</span><span class="font-bold">Rp 22.000</span></div>
                                    </div>
                                </div>
                                <div class="pt-2 border-t border-[#B6D9D5] space-y-2">
                                    <div class="flex justify-between items-baseline">
                                        <span class="text-[10px] font-bold">Total</span>
                                        <span class="text-xs font-black">Rp 107.520</span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-1 text-[9px] text-center font-bold">
                                        <span class="bg-[#4D869C] text-white py-1 rounded-md">Tunai</span>
                                        <span class="bg-white/60 text-[#1D3B47] py-1 rounded-md">Midtrans</span>
                                    </div>
                                    <div class="bg-[#4D869C] text-white font-bold text-[10px] text-center py-1.5 rounded-full">
                                        Bayar
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Business Types Tags Section -->
    <section class="py-6 border-y border-[#D6E5ED] bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center justify-center gap-3">
                @foreach(['Warkop', 'Toko retail', 'Kafe', 'Kedai makanan', 'Minimarket', 'Toko kelontong'] as $type)
                    <span class="bg-[#EEF7FF] border border-[#D6E5ED] text-[#1D3B47] font-bold text-xs sm:text-sm px-5 py-2.5 rounded-full shadow-xs">
                        {{ $type }}
                    </span>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Feature 1: Kasir cepat dari keranjang sampai struk -->
    <section id="fitur" class="py-20 lg:py-28 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                <div class="lg:col-span-6 space-y-5">
                    <h2 class="text-3xl sm:text-4xl font-black text-[#1D3B47] tracking-tight">
                        Kasir cepat dari keranjang sampai struk
                    </h2>
                    <p class="text-base text-[#557785] font-medium leading-relaxed">
                        Satu layar untuk memilih produk, mengatur diskon, dan menerima pembayaran.
                    </p>
                    <ul class="space-y-3.5 pt-2 text-sm font-semibold text-[#1D3B47]">
                        @foreach([
                            'Cari produk atau scan barcode',
                            'Atur jumlah, diskon, dan pajak',
                            'Pilih pelanggan untuk mengumpulkan poin',
                            'Bayar tunai atau online lewat Midtrans',
                            'Cetak struk atau kirim lewat WhatsApp'
                        ] as $item)
                        <li class="flex items-center gap-3">
                            <span class="w-5 h-5 rounded-full bg-[#4D869C] text-white flex items-center justify-center shrink-0">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            <span>{{ $item }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Right: Struk preview mockup -->
                <div class="lg:col-span-6">
                    <div class="bg-[#CDE8E5] rounded-3xl p-6 sm:p-8 flex flex-col items-center justify-center">
                        <div class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-md font-mono text-xs text-[#1D3B47] space-y-3 border border-[#D6E5ED]">
                            <div class="text-center font-bold">
                                <p class="text-sm">WARKOP BUDI</p>
                                <p class="text-[10px] text-gray-500">#INV-0015</p>
                            </div>
                            <div class="border-t border-dashed border-gray-300 pt-2 space-y-1">
                                <div class="flex justify-between"><span>2x Pisang Goreng</span><span>28.000</span></div>
                                <div class="flex justify-between"><span>1x Nasi Goreng</span><span>28.000</span></div>
                                <div class="flex justify-between"><span>1x Kopi Susu</span><span>22.000</span></div>
                                <div class="flex justify-between"><span>3x Air Mineral</span><span>18.000</span></div>
                            </div>
                            <div class="border-t border-dashed border-gray-300 pt-2 space-y-1">
                                <div class="flex justify-between"><span>Subtotal</span><span>96.000</span></div>
                                <div class="flex justify-between"><span>PPN 12%</span><span>11.520</span></div>
                                <div class="flex justify-between font-bold text-sm"><span>Total</span><span>107.520</span></div>
                                <div class="flex justify-between"><span>Tunai</span><span>150.000</span></div>
                                <div class="flex justify-between"><span>Kembali</span><span>42.480</span></div>
                            </div>
                            <div class="text-center text-[10px] text-gray-500 pt-2">
                                Terima kasih
                            </div>
                        </div>
                        <div class="flex items-center gap-3 mt-5">
                            <span class="bg-white text-[#1D3B47] font-bold px-4 py-2 rounded-full text-xs shadow-xs">Cetak struk</span>
                            <span class="bg-[#4D869C] text-white font-bold px-4 py-2 rounded-full text-xs shadow-xs">Kirim WhatsApp</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Feature 2: Pembayaran online yang tercatat rapi -->
    <section class="py-20 lg:py-28 bg-[#f8fafc]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                <!-- Left: Flow steps mockup -->
                <div class="lg:col-span-6 order-2 lg:order-1">
                    <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-[#D6E5ED] space-y-4">
                        <div class="flex items-center justify-between p-3.5 rounded-2xl bg-[#EEF7FF] border border-[#D6E5ED]">
                            <div class="flex items-center gap-3">
                                <span class="w-8 h-8 rounded-full bg-[#4D869C] text-white flex items-center justify-center font-bold text-xs">1</span>
                                <div>
                                    <p class="text-xs font-bold text-[#1D3B47]">Pending</p>
                                    <p class="text-[11px] text-[#75929E]">Stok ditahan sementara</p>
                                </div>
                            </div>
                            <span class="bg-[#ffe8e8] text-[#d9383a] text-[10px] font-bold px-2.5 py-1 rounded-full">Menunggu</span>
                        </div>

                        <div class="flex items-center justify-between p-3.5 rounded-2xl bg-[#EEF7FF] border border-[#D6E5ED]">
                            <div class="flex items-center gap-3">
                                <span class="w-8 h-8 rounded-full bg-[#4D869C] text-white flex items-center justify-center font-bold text-xs">2</span>
                                <div>
                                    <p class="text-xs font-bold text-[#1D3B47]">Webhook Midtrans</p>
                                    <p class="text-[11px] text-[#75929E]">Status pembayaran diperbarui</p>
                                </div>
                            </div>
                            <span class="bg-[#CDE8E5] text-[#1D3B47] text-[10px] font-bold px-2.5 py-1 rounded-full">Berhasil</span>
                        </div>

                        <div class="flex items-center justify-between p-3.5 rounded-2xl bg-[#CDE8E5]/50 border border-[#B6D9D5]">
                            <div class="flex items-center gap-3">
                                <span class="w-8 h-8 rounded-full bg-[#4D869C] text-white flex items-center justify-center font-bold text-xs">3</span>
                                <div>
                                    <p class="text-xs font-bold text-[#1D3B47]">Selesai</p>
                                    <p class="text-[11px] text-[#75929E]">Stok dikurangi, struk siap</p>
                                </div>
                            </div>
                            <span class="bg-[#4D869C] text-white text-[10px] font-bold px-2.5 py-1 rounded-full">Completed</span>
                        </div>
                    </div>
                </div>

                <!-- Right text -->
                <div class="lg:col-span-6 space-y-5 order-1 lg:order-2">
                    <h2 class="text-3xl sm:text-4xl font-black text-[#1D3B47] tracking-tight">
                        Pembayaran online yang tercatat rapi
                    </h2>
                    <p class="text-base text-[#557785] font-medium leading-relaxed">
                        Transaksi Midtrans mengikuti status pembayaran, dan stok ikut terjaga.
                    </p>
                    <ul class="space-y-3.5 pt-2 text-sm font-semibold text-[#1D3B47]">
                        @foreach([
                            'Transaksi berstatus pending sampai dibayar',
                            'Stok ditahan sementara selama menunggu',
                            'Webhook memperbarui status otomatis',
                            'Stok dikurangi setelah pembayaran berhasil'
                        ] as $item)
                        <li class="flex items-center gap-3">
                            <span class="w-5 h-5 rounded-full bg-[#4D869C] text-white flex items-center justify-center shrink-0">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            <span>{{ $item }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Feature 3: Produk dan stok yang terpantau -->
    <section class="py-20 lg:py-28 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                <div class="lg:col-span-6 space-y-5">
                    <h2 class="text-3xl sm:text-4xl font-black text-[#1D3B47] tracking-tight">
                        Produk dan stok yang terpantau
                    </h2>
                    <p class="text-base text-[#557785] font-medium leading-relaxed">
                        Kelola katalog dan lihat setiap pergerakan stok.
                    </p>
                    <ul class="space-y-3.5 pt-2 text-sm font-semibold text-[#1D3B47]">
                        @foreach([
                            'Produk, kategori, satuan, dan gambar',
                            'Stok awal dan penyesuaian stok',
                            'Riwayat pergerakan stok',
                            'Peringatan stok rendah di dashboard'
                        ] as $item)
                        <li class="flex items-center gap-3">
                            <span class="w-5 h-5 rounded-full bg-[#4D869C] text-white flex items-center justify-center shrink-0">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            <span>{{ $item }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Right: Product & Stock Table Mockup -->
                <div class="lg:col-span-6">
                    <div class="bg-[#EEF7FF] rounded-3xl p-6 sm:p-8 shadow-sm border border-[#D6E5ED] space-y-4">
                        <div class="space-y-2 text-xs font-semibold text-[#1D3B47]">
                            <div class="flex justify-between items-center pb-2 text-[11px] font-bold text-[#75929E] uppercase">
                                <span>PRODUK</span>
                                <span>STOK</span>
                                <span>HARGA</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-[#D6E5ED]">
                                <span>Kentang Goreng</span>
                                <span class="bg-[#CDE8E5] px-2.5 py-0.5 rounded-full font-bold">27</span>
                                <span class="font-bold">Rp 20.000</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-[#D6E5ED]">
                                <span>Keripik Singkong</span>
                                <span class="bg-[#CDE8E5] px-2.5 py-0.5 rounded-full font-bold">49</span>
                                <span class="font-bold">Rp 12.000</span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span>Pisang Goreng</span>
                                <span class="border border-[#d9383a] text-[#d9383a] px-2.5 py-0.5 rounded-full font-bold">4</span>
                                <span class="font-bold">Rp 14.000</span>
                            </div>
                        </div>

                        <!-- Low Stock warning chip -->
                        <div class="p-3 rounded-2xl bg-white border-l-4 border-[#b91c1c] flex items-center justify-between text-xs shadow-xs">
                            <div>
                                <p class="font-bold text-[#1D3B47]">Pisang Goreng</p>
                                <p class="text-[10px] text-[#75929E]">Stok rendah</p>
                            </div>
                            <span class="text-base font-black text-[#d9383a]">4</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Feature 4: Pelanggan setia dengan poin -->
    <section class="py-20 lg:py-28 bg-[#f8fafc]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                <!-- Left Customer Table Mockup -->
                <div class="lg:col-span-6 order-2 lg:order-1">
                    <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-[#D6E5ED] space-y-4">
                        <div class="space-y-3 text-xs text-[#1D3B47]">
                            <div class="flex justify-between pb-2 text-[11px] font-bold text-[#75929E] uppercase">
                                <span>NAMA</span>
                                <span>POIN</span>
                                <span>TOTAL BELANJA</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-[#D6E5ED]">
                                <span class="font-bold">Budi Santoso</span>
                                <span class="font-bold">155</span>
                                <span class="font-bold">Rp 1.556.000</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-[#D6E5ED]">
                                <span class="font-bold">Siti Nurhaliza</span>
                                <span class="font-bold">60</span>
                                <span class="font-bold">Rp 600.000</span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="font-bold">Andi Wijaya</span>
                                <span class="font-bold">20</span>
                                <span class="font-bold">Rp 200.000</span>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-[#D6E5ED]">
                            <p class="text-[11px] font-bold text-[#75929E] mb-2">Level loyalitas</p>
                            <div class="flex flex-wrap gap-1.5 text-[11px] font-bold">
                                <span class="bg-[#EEF7FF] border border-[#D6E5ED] px-3 py-1 rounded-full">Reguler</span>
                                <span class="bg-[#EEF7FF] border border-[#D6E5ED] px-3 py-1 rounded-full">Silver</span>
                                <span class="bg-[#EEF7FF] border border-[#D6E5ED] px-3 py-1 rounded-full">Gold</span>
                                <span class="bg-[#EEF7FF] border border-[#D6E5ED] px-3 py-1 rounded-full">Platinum</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right text -->
                <div class="lg:col-span-6 space-y-5 order-1 lg:order-2">
                    <h2 class="text-3xl sm:text-4xl font-black text-[#1D3B47] tracking-tight">
                        Pelanggan setia dengan poin
                    </h2>
                    <p class="text-base text-[#557785] font-medium leading-relaxed">
                        Simpan data pelanggan dan beri penghargaan di setiap belanja.
                    </p>
                    <ul class="space-y-3.5 pt-2 text-sm font-semibold text-[#1D3B47]">
                        @foreach([
                            'Data pelanggan dan total belanja',
                            'Poin loyalitas dari setiap transaksi',
                            'Level Reguler, Silver, Gold, dan Platinum'
                        ] as $item)
                        <li class="flex items-center gap-3">
                            <span class="w-5 h-5 rounded-full bg-[#4D869C] text-white flex items-center justify-center shrink-0">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            <span>{{ $item }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Feature 5: Laporan penjualan yang jelas -->
    <section class="py-20 lg:py-28 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                <div class="lg:col-span-6 space-y-5">
                    <h2 class="text-3xl sm:text-4xl font-black text-[#1D3B47] tracking-tight">
                        Laporan penjualan yang jelas
                    </h2>
                    <p class="text-base text-[#557785] font-medium leading-relaxed">
                        Baca performa usaha dari omzet sampai laba kotor.
                    </p>
                    <ul class="space-y-3.5 pt-2 text-sm font-semibold text-[#1D3B47]">
                        @foreach([
                            'Omzet, jumlah transaksi, dan laba kotor',
                            'Produk terlaris dan penjualan per kategori',
                            'Penjualan per kasir dan metode pembayaran',
                            'Ekspor CSV dan cetak laporan'
                        ] as $item)
                        <li class="flex items-center gap-3">
                            <span class="w-5 h-5 rounded-full bg-[#4D869C] text-white flex items-center justify-center shrink-0">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            <span>{{ $item }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Right: Report Mockup -->
                <div class="lg:col-span-6">
                    <div class="bg-[#EEF7FF] rounded-3xl p-6 sm:p-8 shadow-sm border border-[#D6E5ED] space-y-5">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-white rounded-2xl p-4 border border-[#D6E5ED]">
                                <p class="text-xs text-[#75929E] font-semibold">Omzet</p>
                                <p class="text-xl font-black text-[#1D3B47] mt-1">Rp 1.060.520</p>
                            </div>
                            <div class="bg-white rounded-2xl p-4 border border-[#D6E5ED]">
                                <p class="text-xs text-[#75929E] font-semibold">Laba kotor</p>
                                <p class="text-xl font-black text-[#1D3B47] mt-1">Rp 547.520</p>
                            </div>
                        </div>

                        <!-- Progress bars -->
                        <div class="bg-white rounded-2xl p-4 border border-[#D6E5ED] space-y-3 text-xs">
                            <p class="font-bold text-[#1D3B47]">Metode pembayaran</p>
                            <div>
                                <div class="flex justify-between font-semibold mb-1"><span>Tunai 8x</span><span>Rp 512.000 (48,3%)</span></div>
                                <div class="w-full bg-[#EEF7FF] h-2 rounded-full overflow-hidden"><div class="bg-[#4D869C] h-full w-[48%] rounded-full"></div></div>
                            </div>
                            <div>
                                <div class="flex justify-between font-semibold mb-1"><span>Midtrans 6x</span><span>Rp 548.520 (51,7%)</span></div>
                                <div class="w-full bg-[#EEF7FF] h-2 rounded-full overflow-hidden"><div class="bg-[#518e9a] h-full w-[52%] rounded-full"></div></div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 text-xs font-bold text-[#1D3B47]">
                            <span class="bg-[#CDE8E5] px-3 py-1.5 rounded-full">Produk terlaris</span>
                            <span class="bg-[#EEF7FF] px-3 py-1.5 rounded-full">Per kategori</span>
                            <span class="bg-[#EEF7FF] px-3 py-1.5 rounded-full">Per kasir</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Fitur lengkap untuk kebutuhan harian (8 Cards Grid) -->
    <section class="py-20 lg:py-28 bg-[#f8fafc]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl sm:text-4xl font-black text-[#1D3B47] tracking-tight">
                Fitur lengkap untuk kebutuhan harian
            </h2>

            <div class="mt-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 text-left">
                @php
                    $features = [
                        ['icon' => 'cart', 'title' => 'Kasir dan barcode', 'desc' => 'Cari produk, scan barcode, atur diskon dan pajak.'],
                        ['icon' => 'box', 'title' => 'Produk dan stok', 'desc' => 'Kategori, satuan, gambar, dan penyesuaian stok.'],
                        ['icon' => 'users', 'title' => 'Pelanggan dan poin', 'desc' => 'Poin loyalitas, level, dan total belanja.'],
                        ['icon' => 'receipt', 'title' => 'Transaksi dan refund', 'desc' => 'Riwayat, detail transaksi, retur, dan stok kembali.'],
                        ['icon' => 'chart', 'title' => 'Laporan', 'desc' => 'Omzet, laba kotor, produk terlaris, dan per kasir.'],
                        ['icon' => 'user-check', 'title' => 'Karyawan dan akses', 'desc' => 'Peran admin, manajer, kasir dengan izin per fitur.'],
                        ['icon' => 'credit-card', 'title' => 'Pembayaran Midtrans', 'desc' => 'Pembayaran online dengan konfirmasi otomatis.'],
                        ['icon' => 'home', 'title' => 'Dashboard', 'desc' => 'Penjualan hari ini, grafik, dan stok rendah.'],
                    ];
                @endphp

                @foreach($features as $feat)
                <div class="bg-[#EEF7FF] border border-[#D6E5ED] rounded-3xl p-6 shadow-xs flex flex-col justify-between">
                    <div class="w-10 h-10 rounded-2xl bg-[#CDE8E5] text-[#1D3B47] flex items-center justify-center font-bold mb-4">
                        <svg class="w-5 h-5 text-[#4D869C]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-sm sm:text-base text-[#1D3B47]">{{ $feat['title'] }}</h3>
                        <p class="text-xs text-[#557785] mt-1.5 leading-relaxed">{{ $feat['desc'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Satu usaha, tiga peran -->
    <section id="peran" class="py-20 lg:py-28 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl sm:text-4xl font-black text-[#1D3B47] tracking-tight">
                Satu usaha, tiga peran
            </h2>

            <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6 text-left">
                <!-- Admin Card -->
                <div class="bg-[#EEF7FF] border border-[#D6E5ED] rounded-3xl p-6 sm:p-8 shadow-xs space-y-4">
                    <span class="bg-[#4D869C] text-white font-bold text-xs px-3.5 py-1.5 rounded-full inline-block">Admin</span>
                    <div>
                        <h3 class="font-black text-lg text-[#1D3B47]">Pemilik usaha</h3>
                        <p class="text-xs text-[#557785] mt-1">Mengelola seluruh usaha.</p>
                    </div>
                    <ul class="space-y-2.5 pt-2 text-xs font-semibold text-[#1D3B47]">
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-[#4D869C]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> Produk, stok, dan laporan</li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-[#4D869C]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> Tambah dan nonaktifkan karyawan</li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-[#4D869C]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> Atur profil usaha dan pajak</li>
                    </ul>
                </div>

                <!-- Manager Card -->
                <div class="bg-[#EEF7FF] border border-[#D6E5ED] rounded-3xl p-6 sm:p-8 shadow-xs space-y-4">
                    <span class="bg-[#518e9a] text-white font-bold text-xs px-3.5 py-1.5 rounded-full inline-block">Manager</span>
                    <div>
                        <h3 class="font-black text-lg text-[#1D3B47]">Kepala toko</h3>
                        <p class="text-xs text-[#557785] mt-1">Izin diatur per fitur.</p>
                    </div>
                    <ul class="space-y-2.5 pt-2 text-xs font-semibold text-[#1D3B47]">
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-[#4D869C]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> Hak akses sesuai kebutuhan</li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-[#4D869C]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> Pantau penjualan dan stok</li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-[#4D869C]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> Tanpa akses penuh admin</li>
                    </ul>
                </div>

                <!-- Kasir Card -->
                <div class="bg-[#CDE8E5]/50 border border-[#B6D9D5] rounded-3xl p-6 sm:p-8 shadow-xs space-y-4">
                    <span class="bg-[#CDE8E5] text-[#1D3B47] font-bold text-xs px-3.5 py-1.5 rounded-full inline-block">Kasir</span>
                    <div>
                        <h3 class="font-black text-lg text-[#1D3B47]">Karyawan kasir</h3>
                        <p class="text-xs text-[#557785] mt-1">Login cepat dengan PIN.</p>
                    </div>
                    <ul class="space-y-2.5 pt-2 text-xs font-semibold text-[#1D3B47]">
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-[#4D869C]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> Kode usaha dan PIN</li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-[#4D869C]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> Fokus di layar kasir</li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-[#4D869C]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> Pilih pelanggan dan bayar</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Aman dan terkontrol -->
    <section class="py-16 bg-[#f8fafc]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl font-black text-[#1D3B47] tracking-tight">Aman dan terkontrol</h2>
            <div class="mt-10 grid grid-cols-1 md:grid-cols-3 gap-6 text-left">
                <div class="bg-white rounded-3xl p-6 border border-[#D6E5ED] shadow-xs">
                    <h3 class="font-bold text-[#1D3B47] text-sm">Data usaha terpisah</h3>
                    <p class="text-xs text-[#557785] mt-2 leading-relaxed">MyPos multi-usaha: data setiap usaha dipisahkan, dan akses diatur lewat kebijakan.</p>
                </div>
                <div class="bg-white rounded-3xl p-6 border border-[#D6E5ED] shadow-xs">
                    <h3 class="font-bold text-[#1D3B47] text-sm">Izin per fitur</h3>
                    <p class="text-xs text-[#557785] mt-2 leading-relaxed">Atur siapa yang boleh membuka kasir, laporan, produk, dan karyawan.</p>
                </div>
                <div class="bg-white rounded-3xl p-6 border border-[#D6E5ED] shadow-xs">
                    <h3 class="font-bold text-[#1D3B47] text-sm">Pembayaran terverifikasi</h3>
                    <p class="text-xs text-[#557785] mt-2 leading-relaxed">Status Midtrans diperbarui lewat webhook, dengan rekonsiliasi status pembayaran.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Mulai dalam empat langkah -->
    <section id="cara-mulai" class="py-20 lg:py-28 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl sm:text-4xl font-black text-[#1D3B47] tracking-tight">
                Mulai dalam empat langkah
            </h2>

            <div class="mt-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 text-left">
                <div class="bg-[#EEF7FF] rounded-3xl p-6 border border-[#D6E5ED] shadow-xs flex flex-col justify-between min-h-[160px]">
                    <span class="text-3xl font-black text-[#4D869C]">1</span>
                    <div>
                        <h3 class="font-black text-sm text-[#1D3B47]">Daftar usaha</h3>
                        <p class="text-xs text-[#557785] mt-1">Buat akun admin dan kode usaha.</p>
                    </div>
                </div>

                <div class="bg-[#EEF7FF] rounded-3xl p-6 border border-[#D6E5ED] shadow-xs flex flex-col justify-between min-h-[160px]">
                    <span class="text-3xl font-black text-[#4D869C]">2</span>
                    <div>
                        <h3 class="font-black text-sm text-[#1D3B47]">Profil dan cabang</h3>
                        <p class="text-xs text-[#557785] mt-1">Isi profil usaha, logo, dan cabang.</p>
                    </div>
                </div>

                <div class="bg-[#EEF7FF] rounded-3xl p-6 border border-[#D6E5ED] shadow-xs flex flex-col justify-between min-h-[160px]">
                    <span class="text-3xl font-black text-[#4D869C]">3</span>
                    <div>
                        <h3 class="font-black text-sm text-[#1D3B47]">Pajak dan pembayaran</h3>
                        <p class="text-xs text-[#557785] mt-1">Atur pajak, prefix nota, dan metode bayar.</p>
                    </div>
                </div>

                <div class="bg-[#EEF7FF] rounded-3xl p-6 border border-[#D6E5ED] shadow-xs flex flex-col justify-between min-h-[160px]">
                    <span class="text-3xl font-black text-[#4D869C]">4</span>
                    <div>
                        <h3 class="font-black text-sm text-[#1D3B47]">Produk pertama</h3>
                        <p class="text-xs text-[#557785] mt-1">Tambahkan produk lalu mulai jualan.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Sedang dikembangkan -->
    <section class="py-12 bg-[#f8fafc]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h3 class="text-sm font-bold text-[#75929E] uppercase tracking-wider mb-4">Sedang dikembangkan</h3>
            <div class="flex flex-wrap items-center justify-center gap-2.5">
                @foreach(['Pembelian dan supplier', 'Pengeluaran', 'Mode offline', 'Operasional per cabang'] as $item)
                    <span class="bg-white border border-[#D6E5ED] text-[#1D3B47] text-xs font-semibold px-4 py-2 rounded-full shadow-xs">
                        {{ $item }}
                    </span>
                @endforeach
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="py-20 lg:py-28 bg-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl sm:text-4xl font-black text-[#1D3B47] text-center tracking-tight mb-12">
                Pertanyaan yang sering diajukan
            </h2>

            <div class="space-y-3">
                @php
                    $faqs = [
                        ['q' => 'Bagaimana kasir masuk ke aplikasi?', 'a' => 'Kasir login dengan kode usaha dan PIN 4 sampai 6 angka dari admin, atau dengan email dan password.'],
                        ['q' => 'Metode pembayaran apa yang tersedia?', 'a' => 'Tunai dan pembayaran online lewat Midtrans. Status Midtrans diperbarui otomatis lewat webhook.'],
                        ['q' => 'Apakah data usaha saya terpisah dari usaha lain?', 'a' => 'Ya, MyPos multi-usaha dan data setiap usaha dipisahkan.'],
                        ['q' => 'Bagaimana jika pelanggan meminta refund?', 'a' => 'Retur atau refund dicatat dari riwayat transaksi dan stok dikembalikan.'],
                        ['q' => 'Apakah laporan bisa diekspor?', 'a' => 'Bisa. Laporan dapat diekspor ke CSV dan dicetak.'],
                        ['q' => 'Apakah bisa dipakai saat internet mati?', 'a' => 'Mode offline masih dalam pengembangan.'],
                    ];
                @endphp

                @foreach($faqs as $i => $faq)
                <div class="bg-[#EEF7FF] border border-[#D6E5ED] rounded-2xl p-5 cursor-pointer" @click="openFaq = (openFaq === {{ $i }} ? null : {{ $i }})">
                    <div class="flex items-center justify-between font-bold text-sm text-[#1D3B47]">
                        <span>{{ $faq['q'] }}</span>
                        <svg class="w-4 h-4 transition-transform duration-200" :class="openFaq === {{ $i }} ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <div x-show="openFaq === {{ $i }}" x-cloak class="mt-3 text-xs text-[#557785] leading-relaxed pt-2 border-t border-[#D6E5ED]">
                        {{ $faq['a'] }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Bottom CTA Banner -->
    <section class="py-12 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-[#CDE8E5] rounded-3xl p-8 sm:p-12 flex flex-col sm:flex-row items-center justify-between gap-6 shadow-sm">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-black text-[#1D3B47] tracking-tight">
                        Siap merapikan usaha Anda?
                    </h2>
                </div>
                <a href="{{ route('business.register') }}" class="bg-[#4D869C] hover:bg-[#3d7083] text-white font-bold px-8 py-3.5 rounded-full text-sm shadow-xs transition-colors shrink-0">
                    Daftar usaha
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="border-t border-[#D6E5ED] py-14 bg-white text-xs text-[#75929E]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mb-12">
                <div class="col-span-2 md:col-span-1 space-y-3">
                    <a href="{{ url('/') }}" class="flex items-center gap-2.5">
                        <img src="{{ asset('brand/mypos-wordmark.png') }}" alt="MyPos" width="900" height="331" class="h-7 w-auto">
                    </a>
                    <p class="text-xs text-[#557785]">Aplikasi kasir dan manajemen toko.</p>
                </div>

                <div>
                    <h4 class="font-bold text-[#1D3B47] uppercase tracking-wider mb-3">Fitur</h4>
                    <ul class="space-y-2 font-medium">
                        <li><a href="{{ route('pos.index') }}" class="hover:text-[#4D869C]">Kasir</a></li>
                        <li><a href="{{ route('products.index') }}" class="hover:text-[#4D869C]">Produk dan stok</a></li>
                        <li><a href="{{ route('reports.sales') }}" class="hover:text-[#4D869C]">Laporan</a></li>
                        <li><a href="{{ route('customers.index') }}" class="hover:text-[#4D869C]">Pelanggan</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold text-[#1D3B47] uppercase tracking-wider mb-3">Akun</h4>
                    <ul class="space-y-2 font-medium">
                        <li><a href="{{ route('login') }}" class="hover:text-[#4D869C]">Masuk</a></li>
                        <li><a href="{{ route('business.register') }}" class="hover:text-[#4D869C]">Daftar usaha</a></li>
                        <li><a href="{{ route('pin.login') }}" class="hover:text-[#4D869C]">Login kasir</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold text-[#1D3B47] uppercase tracking-wider mb-3">Bantuan</h4>
                    <ul class="space-y-2 font-medium">
                        <li><a href="#faq" class="hover:text-[#4D869C]">FAQ</a></li>
                        <li><a href="#" class="hover:text-[#4D869C]">Kontak</a></li>
                        <li><a href="#" class="hover:text-[#4D869C]">Syarat dan privasi</a></li>
                    </ul>
                </div>
            </div>

            <div class="pt-6 border-t border-[#D6E5ED] text-center sm:text-left">
                &copy; {{ date('Y') }} MyPos
            </div>
        </div>
    </footer>

</body>
</html>

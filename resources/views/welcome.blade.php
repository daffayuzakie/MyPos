<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Kasir POS — aplikasi kasir dan manajemen toko untuk mengelola penjualan, stok, produk, dan laporan dalam satu tempat.">

        <title>{{ config('app.name', 'Kasir POS') }} — Kasir & Manajemen Toko</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-white">
        <!-- Navbar -->
        <header class="sticky top-0 z-50 bg-white/90 backdrop-blur border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <a href="{{ url('/') }}" class="flex items-center gap-2">
                        <img src="{{ str_replace(' ', '%20', Storage::url('logo mypos.png')) }}" alt="MyPos" class="h-9 w-auto object-contain">
                        <span class="text-lg font-extrabold tracking-tight">MyPos</span>
                    </a>

                    <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-gray-600">
                        <a href="#fitur" class="hover:text-teal-700 transition-colors">Fitur</a>
                        <a href="#cara-kerja" class="hover:text-teal-700 transition-colors">Cara Kerja</a>
                        <a href="#harga" class="hover:text-teal-700 transition-colors">Harga</a>
                    </nav>

                    <div class="flex items-center gap-2 sm:gap-3">
                        <a href="{{ route('login') }}" class="hidden sm:inline-flex pos-btn-secondary text-sm">Masuk</a>
                        <a href="{{ route('business.register') }}" class="pos-btn-primary text-sm">Daftar Gratis</a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Hero -->
        <section class="relative overflow-hidden bg-gradient-to-b from-teal-50 to-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                    <div>
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-100 text-teal-800 text-xs font-bold uppercase tracking-wide">
                            #1 Aplikasi Kasir Online
                        </span>
                        <h1 class="mt-5 text-4xl sm:text-5xl font-extrabold leading-tight tracking-tight text-gray-900">
                            Kelola Toko &amp; Kasir dalam <span class="text-teal-700">Satu Aplikasi</span>
                        </h1>
                        <p class="mt-5 text-lg text-gray-600 leading-relaxed">
                            Catat penjualan, pantau stok, kelola produk, dan lihat laporan harian secara real-time.
                            Cocok untuk toko retail, kafe, minimarket, hingga bisnis multi-cabang.
                        </p>

                        <div class="mt-8 flex flex-col sm:flex-row flex-wrap gap-3">
                            <a href="{{ route('business.register') }}" class="pos-btn-primary text-center text-base px-6 py-3">
                                Daftar Gratis Sekarang
                            </a>
                            <a href="{{ route('login') }}" class="pos-btn-secondary text-center text-base px-6 py-3">
                                Sudah punya akun? Masuk
                            </a>
                        </div>

                        <div class="mt-8 flex items-center gap-6 text-sm text-gray-500">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Gratis tanpa kartu kredit
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Siap pakai 5 menit
                            </div>
                        </div>
                    </div>

                    <!-- Hero visual -->
                    <div class="relative">
                        <div class="absolute -inset-4 bg-teal-200/40 blur-3xl rounded-full"></div>
                        <div class="relative pos-card">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Penjualan Hari Ini</p>
                                    <p class="text-3xl font-extrabold text-gray-900">Rp 4.750.000</p>
                                </div>
                                <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold">+12%</span>
                            </div>
                            <div class="mt-6 space-y-3">
                                @foreach ([['Kopi Susu', 'x2', 'Rp 30.000'], ['Nasi Goreng', 'x1', 'Rp 25.000'], ['Es Teh Manis', 'x3', 'Rp 21.000']] as $row)
                                    <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 border border-gray-100">
                                        <div class="flex items-center gap-3">
                                            <span class="w-9 h-9 rounded-lg bg-teal-700/10 text-teal-700 flex items-center justify-center font-bold text-xs">POS</span>
                                            <span class="text-sm font-semibold text-gray-800">{{ $row[0] }}</span>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm font-bold text-gray-900">{{ $row[2] }}</p>
                                            <p class="text-[11px] text-gray-400">{{ $row[1] }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-5 flex items-center justify-between rounded-xl bg-teal-700 text-white px-4 py-3">
                                <span class="text-sm font-semibold">Total</span>
                                <span class="text-lg font-extrabold">Rp 76.000</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Fitur -->
        <section id="fitur" class="py-16 lg:py-24 bg-white scroll-mt-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-2xl mx-auto text-center">
                    <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight">Semua yang toko Anda butuhkan</h2>
                    <p class="mt-4 text-gray-600">Dari transaksi cepat sampai laporan keuangan, semuanya dalam satu sistem yang mudah digunakan.</p>
                </div>

                <div class="mt-14 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @php
                        $features = [
                            ['Kasir Cepat', 'Proses checkout cepat dengan pencarian produk, diskon, dan berbagai metode pembayaran.', 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z'],
                            ['Manajemen Stok', 'Pantau persediaan otomatis, notifikasi stok menipis, dan riwayat pergerakan barang.', 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
                            ['Kelola Produk', 'Atur produk, kategori, satuan, harga beli, dan harga jual dengan rapi.', 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z'],
                            ['Laporan Real-time', 'Lihat omzet, laba, dan performa penjualan harian tanpa menghitung manual.', 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                            ['Multi Cabang', 'Kelola beberapa outlet dalam satu akun dengan kontrol akses per cabang.', 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                            ['Hak Akses & Peran', 'Atur peran Owner, Manajer, dan Kasir dengan izin yang sesuai tugasnya.', 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
                        ];
                    @endphp

                    @foreach ($features as $feature)
                        <div class="pos-card hover:shadow-md transition-shadow">
                            <div class="w-11 h-11 rounded-xl bg-teal-50 text-teal-700 flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $feature[2] }}"/>
                                </svg>
                            </div>
                            <h3 class="mt-4 text-lg font-bold">{{ $feature[0] }}</h3>
                            <p class="mt-2 text-sm text-gray-600 leading-relaxed">{{ $feature[1] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Cara Kerja -->
        <section id="cara-kerja" class="py-16 lg:py-24 bg-gray-50 scroll-mt-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-2xl mx-auto text-center">
                    <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight">Mulai hanya dalam 3 langkah</h2>
                    <p class="mt-4 text-gray-600">Tidak perlu instalasi rumit. Daftar, atur usaha, langsung jualan.</p>
                </div>

                <div class="mt-14 grid grid-cols-1 md:grid-cols-3 gap-6">
                    @php
                        $steps = [
                            ['1', 'Daftar Akun', 'Buat akun pemilik usaha dengan email dan kata sandi. Gratis.'],
                            ['2', 'Atur Usaha', 'Lengkapi profil usaha, tambahkan cabang pertama, dan atur produk.'],
                            ['3', 'Mulai Jualan', 'Buka kasir, proses transaksi, dan pantau laporan secara real-time.'],
                        ];
                    @endphp

                    @foreach ($steps as $step)
                        <div class="relative pos-card">
                            <span class="absolute -top-4 left-6 flex items-center justify-center w-9 h-9 rounded-xl bg-teal-700 text-white font-extrabold">{{ $step[0] }}</span>
                            <h3 class="mt-4 text-lg font-bold">{{ $step[1] }}</h3>
                            <p class="mt-2 text-sm text-gray-600 leading-relaxed">{{ $step[2] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Harga -->
        <section id="harga" class="py-16 lg:py-24 bg-white scroll-mt-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-2xl mx-auto text-center">
                    <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight">Harga sederhana &amp; transparan</h2>
                    <p class="mt-4 text-gray-600">Mulai gratis, upgrade saat bisnis Anda berkembang.</p>
                </div>

                <div class="mt-14 grid grid-cols-1 md:grid-cols-3 gap-6 max-w-5xl mx-auto">
                    <div class="pos-card flex flex-col">
                        <h3 class="text-lg font-bold">Gratis</h3>
                        <p class="mt-2 text-sm text-gray-600">Untuk usaha yang baru memulai.</p>
                        <p class="mt-6 text-4xl font-extrabold">Rp 0<span class="text-base font-medium text-gray-400">/bln</span></p>
                        <ul class="mt-6 space-y-3 text-sm text-gray-600 flex-1">
                            <li class="flex gap-2"><span class="text-teal-600">&#10003;</span> 1 cabang</li>
                            <li class="flex gap-2"><span class="text-teal-600">&#10003;</span> Kasir &amp; transaksi</li>
                            <li class="flex gap-2"><span class="text-teal-600">&#10003;</span> Manajemen produk &amp; stok</li>
                        </ul>
                        <a href="{{ route('business.register') }}" class="mt-8 pos-btn-secondary text-center">Daftar Gratis</a>
                    </div>

                    <div class="pos-card flex flex-col ring-2 ring-teal-700 relative">
                        <span class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 rounded-full bg-teal-700 text-white text-[11px] font-bold uppercase tracking-wide">Populer</span>
                        <h3 class="text-lg font-bold">Bisnis</h3>
                        <p class="mt-2 text-sm text-gray-600">Untuk toko yang sedang tumbuh.</p>
                        <p class="mt-6 text-4xl font-extrabold">Rp 99rb<span class="text-base font-medium text-gray-400">/bln</span></p>
                        <ul class="mt-6 space-y-3 text-sm text-gray-600 flex-1">
                            <li class="flex gap-2"><span class="text-teal-600">&#10003;</span> Hingga 3 cabang</li>
                            <li class="flex gap-2"><span class="text-teal-600">&#10003;</span> Laporan lengkap</li>
                            <li class="flex gap-2"><span class="text-teal-600">&#10003;</span> Manajemen karyawan &amp; peran</li>
                            <li class="flex gap-2"><span class="text-teal-600">&#10003;</span> Dukungan prioritas</li>
                        </ul>
                        <a href="{{ route('business.register') }}" class="mt-8 pos-btn-primary text-center">Mulai Sekarang</a>
                    </div>

                    <div class="pos-card flex flex-col">
                        <h3 class="text-lg font-bold">Enterprise</h3>
                        <p class="mt-2 text-sm text-gray-600">Untuk jaringan multi-cabang.</p>
                        <p class="mt-6 text-4xl font-extrabold">Custom</p>
                        <ul class="mt-6 space-y-3 text-sm text-gray-600 flex-1">
                            <li class="flex gap-2"><span class="text-teal-600">&#10003;</span> Cabang tanpa batas</li>
                            <li class="flex gap-2"><span class="text-teal-600">&#10003;</span> Integrasi &amp; API</li>
                            <li class="flex gap-2"><span class="text-teal-600">&#10003;</span> Pendampingan khusus</li>
                        </ul>
                        <a href="{{ route('business.register') }}" class="mt-8 pos-btn-secondary text-center">Hubungi Kami</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="py-16 lg:py-20 bg-teal-700">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">Siap memodernkan toko Anda?</h2>
                <p class="mt-4 text-teal-50">Bergabung dan rasakan kemudahan mengelola penjualan setiap hari.</p>
                <div class="mt-8 flex flex-col sm:flex-row justify-center flex-wrap gap-3">
                    <a href="{{ route('business.register') }}" class="inline-flex justify-center px-6 py-3 rounded-lg bg-white text-teal-700 font-bold hover:bg-teal-50 transition-colors">
                        Daftar Gratis
                    </a>
                    <a href="{{ route('login') }}" class="inline-flex justify-center px-6 py-3 rounded-lg border border-white/70 text-white font-bold hover:bg-teal-800 transition-colors">
                        Masuk
                    </a>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-gray-900 text-gray-400">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="flex flex-col md:flex-row justify-between gap-8">
                    <div class="max-w-sm">
                        <div class="flex items-center gap-2">
                            <img src="{{ str_replace(' ', '%20', Storage::url('logo mypos.png')) }}" alt="MyPos" class="h-9 w-auto object-contain bg-white rounded-xl p-1">
                            <span class="text-lg font-extrabold text-white">MyPos</span>
                        </div>
                        <p class="mt-4 text-sm">Aplikasi kasir dan manajemen toko untuk bisnis yang ingin tumbuh lebih cepat.</p>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-8 text-sm">
                        <div>
                            <h4 class="text-white font-bold mb-3">Produk</h4>
                            <ul class="space-y-2">
                                <li><a href="#fitur" class="hover:text-white transition-colors">Fitur</a></li>
                                <li><a href="#harga" class="hover:text-white transition-colors">Harga</a></li>
                                <li><a href="#cara-kerja" class="hover:text-white transition-colors">Cara Kerja</a></li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="text-white font-bold mb-3">Akun</h4>
                            <ul class="space-y-2">
                                <li><a href="{{ route('login') }}" class="hover:text-white transition-colors">Masuk</a></li>
                                <li><a href="{{ route('business.register') }}" class="hover:text-white transition-colors">Daftar</a></li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="text-white font-bold mb-3">Bantuan</h4>
                            <ul class="space-y-2">
                                <li><a href="mailto:support@kasirpos.test" class="hover:text-white transition-colors">Hubungi Kami</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="mt-10 pt-6 border-t border-white/10 text-xs flex flex-col sm:flex-row justify-between gap-2">
                    <p>&copy; {{ date('Y') }} MyPos. Seluruh hak cipta dilindungi.</p>
                    <p>Dibuat dengan Laravel</p>
                </div>
            </div>
        </footer>
    </body>
</html>

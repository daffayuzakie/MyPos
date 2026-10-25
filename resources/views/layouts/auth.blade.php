<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', config('app.name', 'Kasir POS'))</title>

        <link rel="icon" type="image/png" href="{{ Storage::url('logo mypos.png') }}">
        <link rel="apple-touch-icon" href="{{ Storage::url('logo mypos.png') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-white">
        <div class="min-h-screen lg:grid lg:grid-cols-2">
            <!-- Brand panel -->
            <aside class="hidden lg:flex flex-col justify-between bg-gradient-to-br from-teal-700 via-teal-800 to-teal-900 text-white p-10 xl:p-14">
                <a href="{{ url('/') }}" class="flex items-center gap-2 w-fit">
                    <img src="{{ str_replace(' ', '%20', Storage::url('logo mypos.png')) }}" alt="MyPos" class="h-9 w-auto object-contain bg-white rounded-xl p-1">
                    <span class="text-lg font-extrabold">MyPos</span>
                </a>

                <div>
                    <h2 class="text-3xl xl:text-4xl font-extrabold leading-tight">
                        Kelola toko &amp; kasir dalam satu aplikasi.
                    </h2>
                    <p class="mt-4 text-teal-100 leading-relaxed">
                        Catat penjualan, pantau stok, dan lihat laporan real-time â€” kapan saja, di mana saja.
                    </p>

                    <ul class="mt-8 space-y-4 text-sm">
                        @foreach (['Kasir cepat dengan berbagai metode pembayaran', 'Manajemen produk & stok otomatis', 'Laporan penjualan real-time', 'Kelola banyak cabang & karyawan'] as $point)
                            <li class="flex items-start gap-3">
                                <span class="mt-0.5 flex items-center justify-center w-5 h-5 rounded-full bg-teal-600 text-white shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </span>
                                <span class="text-teal-50">{{ $point }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <p class="text-xs text-teal-200">&copy; {{ date('Y') }} MyPos. Seluruh hak cipta dilindungi.</p>
            </aside>

            <!-- Form side -->
            <div class="flex flex-col min-h-screen">
                <header class="flex items-center justify-between gap-4 px-6 py-5 sm:px-8">
                    <a href="{{ url('/') }}" class="lg:hidden flex items-center gap-2">
                        <img src="{{ str_replace(' ', '%20', Storage::url('logo mypos.png')) }}" alt="MyPos" class="h-8 w-auto object-contain">
                        <span class="font-extrabold">MyPos</span>
                    </a>
                    <div class="ml-auto text-sm">
                        @yield('auth-alt')
                    </div>
                </header>

                <main class="flex-1 flex items-center justify-center px-6 sm:px-8 pb-12">
                    <div class="w-full max-w-md">
                        @yield('content')
                    </div>
                </main>
            </div>
        </div>
    </body>
</html>



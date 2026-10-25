<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', config('app.name', 'MyPos'))</title>

        <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
        <meta name="theme-color" content="#4D869C">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-[#1D3B47] antialiased bg-white">
        <div class="min-h-screen lg:grid lg:grid-cols-2">
            <!-- Brand side panel (Light Mint matching Design) -->
            <aside class="hidden lg:flex flex-col justify-between bg-[#d5ebe5] text-[#1D3B47] p-10 xl:p-16 border-r border-[#c2ded7]">
                <!-- Brand Logo -->
                <a href="{{ url('/') }}" class="flex items-center gap-3 w-fit">
                    <img src="{{ asset('brand/mypos-wordmark.png') }}" alt="MyPos" width="900" height="331" class="h-10 w-auto">
                </a>

                <!-- Hero Content Slot -->
                <div class="my-auto py-6 max-w-lg">
                    @yield('auth-hero')
                </div>

                <!-- Footer note -->
                <div class="text-xs text-[#75929E]">
                    &copy; {{ date('Y') }} MyPos. Seluruh hak cipta dilindungi.
                </div>
            </aside>

            <!-- Form side -->
            <div class="flex flex-col min-h-screen bg-white">
                <header class="flex items-center justify-between gap-4 px-6 py-5 sm:px-10">
                    <a href="{{ url('/') }}" class="lg:hidden flex items-center gap-2.5">
                        <img src="{{ asset('brand/mypos-wordmark.png') }}" alt="MyPos" width="900" height="331" class="h-8 w-auto">
                    </a>
                    <div class="ml-auto text-sm">
                        @yield('auth-alt')
                    </div>
                </header>

                <main class="flex-1 flex items-center justify-center px-6 sm:px-12 pb-12">
                    <div class="w-full max-w-md">
                        @yield('content')
                    </div>
                </main>
            </div>
        </div>
        @stack('scripts')
    </body>
</html>

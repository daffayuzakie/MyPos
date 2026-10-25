<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', config('app.name', 'MyPos'))</title>

        <link rel="icon" type="image/png" href="{{ Storage::url('logo mypos.png') }}">
        <link rel="apple-touch-icon" href="{{ Storage::url('logo mypos.png') }}">

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
                    <div class="w-10 h-10 rounded-2xl bg-[#4D869C] text-white font-black text-xl flex items-center justify-center shrink-0 shadow-xs">
                        P
                    </div>
                    <span class="text-xl font-black text-[#1D3B47] tracking-tight">MyPos</span>
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
                        <div class="w-9 h-9 rounded-2xl bg-[#4D869C] text-white font-black text-lg flex items-center justify-center shrink-0 shadow-xs">
                            P
                        </div>
                        <span class="font-black text-lg text-[#1D3B47] tracking-tight">MyPos</span>
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

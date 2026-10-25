@extends('layouts.auth')

@section('title', 'Masuk — MyPos')

@section('auth-hero')
    <h2 class="text-3xl xl:text-4xl font-extrabold text-[#1D3B47] leading-tight">
        Masuk dan lanjutkan<br>jualan.
    </h2>

    <ul class="mt-8 space-y-4 text-sm font-semibold text-[#1D3B47]">
        <li class="flex items-center gap-3">
            <span class="w-6 h-6 rounded-full bg-[#4D869C] text-white flex items-center justify-center shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
            </span>
            <span>Kasir dengan scan barcode</span>
        </li>
        <li class="flex items-center gap-3">
            <span class="w-6 h-6 rounded-full bg-[#4D869C] text-white flex items-center justify-center shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
            </span>
            <span>Bayar tunai atau Midtrans</span>
        </li>
        <li class="flex items-center gap-3">
            <span class="w-6 h-6 rounded-full bg-[#4D869C] text-white flex items-center justify-center shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
            </span>
            <span>Laporan penjualan dan stok</span>
        </li>
    </ul>

    <!-- Floating Preview Cards -->
    <div class="mt-12 flex flex-wrap gap-4">
        <div class="bg-[#edf5fa] rounded-2xl p-4 w-44 shadow-xs border border-[#d6e5ed]">
            <p class="text-xs font-semibold text-[#75929E]">Penjualan hari ini</p>
            <p class="text-lg font-black text-[#1D3B47] mt-1.5">Rp 107.520</p>
        </div>
        <div class="bg-[#edf5fa] rounded-2xl p-4 w-36 shadow-xs border border-[#d6e5ed]">
            <p class="text-xs font-semibold text-[#75929E]">Stok rendah</p>
            <p class="text-lg font-black text-[#d9383a] mt-1.5">1</p>
        </div>
    </div>
@endsection

@section('content')
    <h1 class="text-2xl sm:text-3xl font-black text-[#1D3B47] tracking-tight">Masuk</h1>

    <!-- Tabs: Email & Kasir (PIN) -->
    <div class="mt-5 flex items-center gap-2">
        <span class="bg-[#4D869C] text-white font-bold px-5 py-2 rounded-full text-xs shadow-xs">
            Email
        </span>
        <a href="{{ route('pin.login') }}" class="bg-[#CDE8E5] text-[#1D3B47] hover:bg-[#b8ded9] font-bold px-5 py-2 rounded-full text-xs transition-colors">
            Kasir (PIN)
        </a>
    </div>

    <x-auth-session-status class="mt-5 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs font-bold" :status="session('status')" />

    @if ($errors->any())
        <div class="mt-5 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-xs font-bold">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-xs font-bold text-[#1D3B47] mb-1.5">Email atau nomor HP</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                   class="block w-full px-4 py-3 bg-white border border-[#c4d6df] rounded-2xl text-sm text-[#1D3B47] placeholder-[#75929E] focus:outline-none focus:ring-2 focus:ring-[#4D869C] focus:border-[#4D869C] transition-all"
                   placeholder="nama@usaha.com">
        </div>

        <div>
            <label for="password" class="block text-xs font-bold text-[#1D3B47] mb-1.5">Password</label>
            <input id="password" name="password" type="password" required autocomplete="current-password"
                   class="block w-full px-4 py-3 bg-white border border-[#c4d6df] rounded-2xl text-sm text-[#1D3B47] placeholder-[#75929E] focus:outline-none focus:ring-2 focus:ring-[#4D869C] focus:border-[#4D869C] transition-all"
                   placeholder="Password">
        </div>

        <div class="flex items-center justify-between pt-1">
            <label for="remember_me" class="inline-flex items-center gap-2 text-xs font-bold text-[#1D3B47] cursor-pointer">
                <input id="remember_me" type="checkbox" name="remember" class="w-4 h-4 rounded border-[#c4d6df] text-[#4D869C] focus:ring-[#4D869C]">
                <span>Ingat saya</span>
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-xs font-bold text-[#1D3B47] underline hover:text-[#4D869C]">
                    Lupa password?
                </a>
            @endif
        </div>

        <button type="submit" class="w-full mt-2 py-3 bg-[#4D869C] hover:bg-[#3d7083] text-white font-bold rounded-full text-sm shadow-xs transition-colors">
            Masuk
        </button>
    </form>

    <div class="relative my-6 text-center">
        <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-[#d6e5ed]"></div></div>
        <span class="relative px-3 bg-white text-xs font-semibold text-[#75929E]">atau</span>
    </div>

    <a href="{{ route('pin.login') }}" class="block w-full text-center py-3 bg-[#CDE8E5] hover:bg-[#b8ded9] text-[#1D3B47] font-bold rounded-full text-sm transition-colors">
        Masuk sebagai kasir dengan PIN
    </a>

    <div class="mt-8 text-center text-xs font-semibold text-[#75929E]">
        Belum punya usaha di MyPos? 
        <a href="{{ route('business.register') }}" class="font-bold text-[#1D3B47] underline hover:text-[#4D869C] ml-1">
            Daftar usaha
        </a>
    </div>
@endsection

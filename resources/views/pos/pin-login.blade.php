@extends('layouts.auth')

@section('title', 'Masuk Kasir — MyPos')

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
    <div x-data="{ pin: '' }">
        <h1 class="text-2xl sm:text-3xl font-black text-[#1D3B47] tracking-tight">Masuk kasir</h1>

        <!-- Tabs: Email & Kasir (PIN) -->
        <div class="mt-5 flex items-center gap-2">
            <a href="{{ route('login') }}" class="bg-[#CDE8E5] text-[#1D3B47] hover:bg-[#b8ded9] font-bold px-5 py-2 rounded-full text-xs transition-colors">
                Email
            </a>
            <span class="bg-[#4D869C] text-white font-bold px-5 py-2 rounded-full text-xs shadow-xs">
                Kasir (PIN)
            </span>
        </div>

        @if ($errors->any())
            <div class="mt-5 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-xs font-bold">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="pin-login-form" action="{{ route('pin.login.submit') }}" method="POST" class="mt-6 space-y-5">
            @csrf
            <input type="hidden" name="pin" :value="pin">

            <div>
                <label for="business_code" class="block text-xs font-bold text-[#1D3B47] mb-1.5">Kode Usaha</label>
                <input
                    type="text"
                    name="business_code"
                    id="business_code"
                    value="{{ $businessCode ?? old('business_code') }}"
                    required
                    autofocus
                    autocomplete="off"
                    autocapitalize="none"
                    placeholder="kode-usaha"
                    class="block w-full px-4 py-3 bg-white border border-[#c4d6df] rounded-2xl text-sm text-[#1D3B47] placeholder-[#75929E] focus:outline-none focus:ring-2 focus:ring-[#4D869C] focus:border-[#4D869C] transition-all"
                >
            </div>

            <div>
                <div class="text-xs font-bold text-[#1D3B47] mb-3">PIN kasir (4 sampai 6 angka)</div>
                
                <!-- PIN indicator dots matching Login kasir.png -->
                <div class="flex items-center justify-center gap-3 my-4">
                    <template x-for="i in 6" :key="i">
                        <div class="w-3.5 h-3.5 rounded-full transition-all"
                             :class="pin.length >= i ? 'bg-[#4D869C]' : 'border-2 border-[#9bb3bd] bg-transparent'">
                        </div>
                    </template>
                </div>

                <!-- Number keypad grid matching Login kasir.png -->
                <div class="grid grid-cols-3 gap-2.5 sm:gap-3 mt-4">
                    <template x-for="num in [1, 2, 3, 4, 5, 6, 7, 8, 9]" :key="num">
                        <button type="button" 
                                @click="if (pin.length < 6) pin += num.toString()"
                                class="h-14 rounded-2xl bg-[#edf5fa] hover:bg-[#dce9f2] active:scale-95 text-[#1D3B47] font-black text-xl flex items-center justify-center transition-all shadow-xs">
                            <span x-text="num"></span>
                        </button>
                    </template>

                    <!-- Hapus button -->
                    <button type="button" 
                            @click="pin = pin.slice(0, -1)"
                            class="h-14 rounded-2xl bg-[#edf5fa] hover:bg-[#dce9f2] active:scale-95 text-[#1D3B47] font-bold text-sm flex items-center justify-center transition-all shadow-xs">
                        Hapus
                    </button>

                    <!-- Zero 0 -->
                    <button type="button" 
                            @click="if (pin.length < 6) pin += '0'"
                            class="h-14 rounded-2xl bg-[#edf5fa] hover:bg-[#dce9f2] active:scale-95 text-[#1D3B47] font-black text-xl flex items-center justify-center transition-all shadow-xs">
                        0
                    </button>

                    <!-- Masuk submit button -->
                    <button type="submit" 
                            :disabled="pin.length < 4"
                            class="h-14 rounded-2xl bg-[#4D869C] hover:bg-[#3d7083] disabled:opacity-50 disabled:cursor-not-allowed active:scale-95 text-white font-bold text-sm flex items-center justify-center transition-all shadow-xs">
                        Masuk
                    </button>
                </div>
            </div>
        </form>

        <div class="mt-8 text-center text-xs font-semibold text-[#75929E]">
            Admin atau manajer? 
            <a href="{{ route('login') }}" class="font-bold text-[#1D3B47] underline hover:text-[#4D869C] ml-1">
                Masuk dengan email
            </a>
        </div>
    </div>
@endsection

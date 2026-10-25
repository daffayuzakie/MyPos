@extends('layouts.auth')

@section('title', 'Daftar Usaha — MyPos')

@section('auth-hero')
    <h2 class="text-3xl xl:text-4xl font-extrabold text-[#1D3B47] leading-tight">
        Daftarkan usaha Anda.
    </h2>

    <div class="mt-8 space-y-6 text-sm font-semibold text-[#1D3B47]">
        <div class="flex items-center gap-4">
            <span class="w-8 h-8 rounded-full bg-[#4D869C] text-white flex items-center justify-center font-bold text-sm shrink-0">
                1
            </span>
            <span class="text-base">Buat akun admin dan kode usaha</span>
        </div>
        <div class="flex items-center gap-4">
            <span class="w-8 h-8 rounded-full bg-[#4D869C] text-white flex items-center justify-center font-bold text-sm shrink-0">
                2
            </span>
            <span class="text-base">Isi profil, cabang, dan pajak</span>
        </div>
        <div class="flex items-center gap-4">
            <span class="w-8 h-8 rounded-full bg-[#4D869C] text-white flex items-center justify-center font-bold text-sm shrink-0">
                3
            </span>
            <span class="text-base">Tambah produk pertama</span>
        </div>
    </div>
@endsection

@section('content')
    <h1 class="text-2xl sm:text-3xl font-black text-[#1D3B47] tracking-tight">Daftar usaha</h1>

    @if ($errors->any())
        <div class="mt-5 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-xs font-bold">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('business.register.submit') }}" class="mt-6 space-y-5">
        @csrf

        <!-- SECTION: AKUN ADMIN -->
        <div>
            <div class="text-[11px] font-black tracking-wider text-[#75929E] uppercase mb-3">AKUN ADMIN</div>
            <div class="space-y-3.5">
                <div>
                    <label for="name" class="block text-xs font-bold text-[#1D3B47] mb-1.5">Nama lengkap</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus
                           class="block w-full px-4 py-2.5 bg-white border border-[#c4d6df] rounded-2xl text-sm text-[#1D3B47] placeholder-[#75929E] focus:outline-none focus:ring-2 focus:ring-[#4D869C] focus:border-[#4D869C] transition-all"
                           placeholder="Nama Anda">
                </div>

                <div>
                    <label for="email" class="block text-xs font-bold text-[#1D3B47] mb-1.5">Email atau nomor HP</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required
                           class="block w-full px-4 py-2.5 bg-white border border-[#c4d6df] rounded-2xl text-sm text-[#1D3B47] placeholder-[#75929E] focus:outline-none focus:ring-2 focus:ring-[#4D869C] focus:border-[#4D869C] transition-all"
                           placeholder="nama@usaha.com">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="password" class="block text-xs font-bold text-[#1D3B47] mb-1.5">Password</label>
                        <input id="password" name="password" type="password" required minlength="8"
                               class="block w-full px-4 py-2.5 bg-white border border-[#c4d6df] rounded-2xl text-sm text-[#1D3B47] placeholder-[#75929E] focus:outline-none focus:ring-2 focus:ring-[#4D869C] focus:border-[#4D869C] transition-all"
                               placeholder="Password">
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-xs font-bold text-[#1D3B47] mb-1.5">Ulangi password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8"
                               class="block w-full px-4 py-2.5 bg-white border border-[#c4d6df] rounded-2xl text-sm text-[#1D3B47] placeholder-[#75929E] focus:outline-none focus:ring-2 focus:ring-[#4D869C] focus:border-[#4D869C] transition-all"
                               placeholder="Ulangi">
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION: USAHA -->
        <div class="pt-2">
            <div class="text-[11px] font-black tracking-wider text-[#75929E] uppercase mb-3">USAHA</div>
            <div class="space-y-3.5">
                <div>
                    <label for="business_name" class="block text-xs font-bold text-[#1D3B47] mb-1.5">Nama usaha</label>
                    <input id="business_name" name="business_name" type="text" value="{{ old('business_name') }}" required
                           class="block w-full px-4 py-2.5 bg-white border border-[#c4d6df] rounded-2xl text-sm text-[#1D3B47] placeholder-[#75929E] focus:outline-none focus:ring-2 focus:ring-[#4D869C] focus:border-[#4D869C] transition-all"
                           placeholder="Nama usaha">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="business_code" class="block text-xs font-bold text-[#1D3B47] mb-1.5">Kode usaha</label>
                        <input id="business_code" name="business_code" type="text" value="{{ old('business_code') }}"
                               class="block w-full px-4 py-2.5 bg-white border border-[#c4d6df] rounded-2xl text-sm text-[#1D3B47] placeholder-[#75929E] focus:outline-none focus:ring-2 focus:ring-[#4D869C] focus:border-[#4D869C] transition-all"
                               placeholder="kode-usaha">
                    </div>

                    <div>
                        <label for="type" class="block text-xs font-bold text-[#1D3B47] mb-1.5">Jenis usaha</label>
                        <select id="type" name="type"
                                class="block w-full px-4 py-2.5 bg-white border border-[#c4d6df] rounded-2xl text-sm text-[#1D3B47] focus:outline-none focus:ring-2 focus:ring-[#4D869C] focus:border-[#4D869C] transition-all">
                            <option value="retail_store">Retail atau toko</option>
                            <option value="cafe_resto">Kafe / Resto</option>
                            <option value="grocery">Minimarket</option>
                            <option value="service">Jasa / Lainnya</option>
                        </select>
                    </div>
                </div>
                <p class="text-[11px] text-[#75929E]">
                    Kode usaha dipakai kasir untuk login PIN. Huruf kecil, angka, dan tanda hubung.
                </p>
            </div>
        </div>

        <div class="pt-1">
            <label class="inline-flex items-center gap-2 text-xs font-semibold text-[#1D3B47] cursor-pointer">
                <input type="checkbox" required class="w-4 h-4 rounded border-[#c4d6df] text-[#4D869C] focus:ring-[#4D869C]">
                <span>Saya setuju dengan <a href="#" class="underline font-bold">Syarat dan Privasi</a></span>
            </label>
        </div>

        <button type="submit" class="w-full mt-2 py-3 bg-[#4D869C] hover:bg-[#3d7083] text-white font-bold rounded-full text-sm shadow-xs transition-colors">
            Daftar usaha
        </button>
    </form>

    <div class="mt-8 text-center text-xs font-semibold text-[#75929E]">
        Sudah punya akun? 
        <a href="{{ route('login') }}" class="font-bold text-[#1D3B47] underline hover:text-[#4D869C] ml-1">
            Masuk
        </a>
    </div>
@endsection

@extends('layouts.auth')

@section('title', 'Masuk Kasir - '.config('app.name', 'MyPos'))

@section('auth-alt')
    <span class="text-gray-500">Bukan kasir?</span>
    <a href="{{ route('login') }}" class="ml-1 font-semibold text-teal-700 hover:text-teal-800">Masuk dengan email</a>
@endsection

@section('content')
    <h1 class="text-2xl font-extrabold text-gray-900">Masuk sebagai Kasir</h1>
    <p class="text-sm text-gray-500 mt-1">Masukkan kode usaha dan PIN Anda untuk mulai bekerja.</p>

    <div class="mt-8 bg-white rounded-2xl border border-gray-100 shadow-sm p-6" x-data="{ pin: '' }">
        <form action="{{ route('pin.login.submit') }}" method="POST">
            @csrf
            <input type="hidden" name="pin" x-model="pin">

            <div>
                <label for="business_code" class="block text-xs font-bold text-gray-700 mb-1.5">Kode Usaha</label>
                <input
                    type="text"
                    name="business_code"
                    id="business_code"
                    value="{{ $businessCode }}"
                    required
                    autofocus
                    autocomplete="off"
                    autocapitalize="none"
                    placeholder="contoh: toko-saya"
                    class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-900 placeholder-gray-400 focus:border-teal-600 focus:ring-teal-600"
                >
                @error('business_code')
                    <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1.5 text-[11px] text-gray-500">Kode usaha bisa dilihat pada pengaturan usaha.</p>
            </div>

            <div class="mt-6">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-gray-700">PIN</span>
                    @error('pin')
                        <span class="text-xs font-medium text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex justify-center gap-4 mb-6">
                    <template x-for="i in 6">
                        <div class="w-4 h-4 rounded-full" :class="pin.length >= i ? 'bg-teal-700' : 'bg-gray-200'"></div>
                    </template>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <template x-for="n in [1,2,3,4,5,6,7,8,9]">
                        <button type="button" @click="if(pin.length < 6) pin += n" class="h-14 rounded-xl bg-gray-50 hover:bg-gray-100 text-2xl font-medium text-gray-800 transition-colors" x-text="n"></button>
                    </template>
                    <button type="button" @click="pin = ''" class="h-14 rounded-xl bg-red-50 hover:bg-red-100 text-red-600 font-medium transition-colors">C</button>
                    <button type="button" @click="if(pin.length < 6) pin += '0'" class="h-14 rounded-xl bg-gray-50 hover:bg-gray-100 text-2xl font-medium text-gray-800 transition-colors">0</button>
                    <button type="button" @click="pin = pin.slice(0, -1)" class="h-14 rounded-xl bg-gray-50 hover:bg-gray-100 flex items-center justify-center text-gray-800 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414 6.414a2 2 0 001.414.586H19a2 2 0 002-2V7a2 2 0 00-2-2h-8.172a2 2 0 00-1.414.586L3 12z"></path></svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="mt-6 w-full py-4 bg-teal-800 hover:bg-teal-900 text-white rounded-xl font-bold text-base shadow transition-colors disabled:opacity-50 disabled:cursor-not-allowed" :disabled="pin.length < 4 || pin.length > 6">
                Masuk ke Kasir
            </button>
        </form>
    </div>
@endsection



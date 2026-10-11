@extends('layouts.auth')

@section('title', 'Daftar Usaha — KasirPOS')

@section('auth-alt')
    <span class="text-gray-500">Sudah punya akun?</span>
    <a href="{{ route('login') }}" class="ml-1 font-semibold text-teal-700 hover:text-teal-800">Masuk</a>
@endsection

@section('content')
    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Buat akun usaha</h1>
    <p class="mt-2 text-sm text-gray-500">Mulai gratis, tanpa kartu kredit. Siap dalam 5 menit.</p>

    @if ($errors->any())
        <div class="mt-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('business.register.submit') }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <label for="business_name" class="block text-sm font-semibold text-gray-700">Nama Usaha</label>
            <input id="business_name" name="business_name" type="text" value="{{ old('business_name') }}" required autofocus
                   class="block w-full mt-1.5 px-3.5 py-2.5 border border-gray-300 rounded-lg shadow-sm text-sm text-gray-900 focus:border-teal-600 focus:ring-teal-600"
                   placeholder="Contoh: Toko Kopi Nusantara">
        </div>

        <div>
            <label for="name" class="block text-sm font-semibold text-gray-700">Nama Anda</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required
                   class="block w-full mt-1.5 px-3.5 py-2.5 border border-gray-300 rounded-lg shadow-sm text-sm text-gray-900 focus:border-teal-600 focus:ring-teal-600"
                   placeholder="Nama pemilik / admin">
        </div>

        <div>
            <label for="email" class="block text-sm font-semibold text-gray-700">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required
                   class="block w-full mt-1.5 px-3.5 py-2.5 border border-gray-300 rounded-lg shadow-sm text-sm text-gray-900 focus:border-teal-600 focus:ring-teal-600"
                   placeholder="nama@email.com">
        </div>

        <div>
            <label for="password" class="block text-sm font-semibold text-gray-700">Kata Sandi</label>
            <input id="password" name="password" type="password" required minlength="8"
                   class="block w-full mt-1.5 px-3.5 py-2.5 border border-gray-300 rounded-lg shadow-sm text-sm text-gray-900 focus:border-teal-600 focus:ring-teal-600"
                   placeholder="Minimal 8 karakter">
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-semibold text-gray-700">Konfirmasi Kata Sandi</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8"
                   class="block w-full mt-1.5 px-3.5 py-2.5 border border-gray-300 rounded-lg shadow-sm text-sm text-gray-900 focus:border-teal-600 focus:ring-teal-600"
                   placeholder="Ulangi kata sandi">
        </div>

        <button type="submit" class="pos-btn-primary w-full py-3 text-sm">
            Daftar Sekarang
        </button>
    </form>

    <p class="mt-6 text-xs text-gray-400 text-center leading-relaxed">
        Dengan mendaftar, Anda menyetujui Syarat &amp; Ketentuan serta Kebijakan Privasi kami.
    </p>
@endsection

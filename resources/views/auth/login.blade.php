@extends('layouts.auth')

@section('title', 'Masuk — KasirPOS')

@section('auth-alt')
    <span class="text-gray-500">Belum punya akun?</span>
    <a href="{{ route('business.register') }}" class="ml-1 font-semibold text-teal-700 hover:text-teal-800">Daftar</a>
@endsection

@section('content')
    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Masuk ke akun Anda</h1>
    <p class="mt-2 text-sm text-gray-500">Selamat datang kembali. Lanjutkan mengelola toko Anda.</p>

    <x-auth-session-status class="mt-6 p-4 bg-teal-50 border border-teal-200 text-teal-800 rounded-xl text-sm" :status="session('status')" />

    @if ($errors->any())
        <div class="mt-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <label for="email" class="block text-sm font-semibold text-gray-700">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                   class="block w-full mt-1.5 px-3.5 py-2.5 border border-gray-300 rounded-lg shadow-sm text-sm text-gray-900 focus:border-teal-600 focus:ring-teal-600"
                   placeholder="nama@email.com">
        </div>

        <div>
            <label for="password" class="block text-sm font-semibold text-gray-700">Kata Sandi</label>
            <input id="password" name="password" type="password" required autocomplete="current-password"
                   class="block w-full mt-1.5 px-3.5 py-2.5 border border-gray-300 rounded-lg shadow-sm text-sm text-gray-900 focus:border-teal-600 focus:ring-teal-600"
                   placeholder="Kata sandi Anda">
        </div>

        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center gap-2 text-sm text-gray-600">
                <input id="remember_me" type="checkbox" name="remember" class="rounded border-gray-300 text-teal-600 shadow-sm focus:ring-teal-600">
                Ingat saya
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-sm font-medium text-teal-700 hover:text-teal-800">
                    Lupa kata sandi?
                </a>
            @endif
        </div>

        <button type="submit" class="pos-btn-primary w-full py-3 text-sm">
            Masuk
        </button>
    </form>
@endsection

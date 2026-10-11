<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk PIN - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    
    <div class="w-full max-w-sm" x-data="{ pin: '' }">
        <div class="text-center mb-8">
            <div class="w-16 h-16 mx-auto bg-teal-700 rounded-full flex items-center justify-center text-white text-3xl font-bold mb-4">
                B
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Masuk dengan PIN</h1>
            <p class="text-gray-500">Masukkan PIN kasir Anda</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
            <form action="{{ route('pin.login.submit') }}" method="POST">
                @csrf
                <input type="hidden" name="pin" x-model="pin">
                
                <div class="flex justify-center gap-4 mb-8">
                    <template x-for="i in 6">
                        <div class="w-4 h-4 rounded-full" :class="pin.length >= i ? 'bg-teal-700' : 'bg-gray-200'"></div>
                    </template>
                </div>

                @if($errors->any())
                <div class="mb-4 text-center text-red-500 text-sm font-medium">
                    PIN yang Anda masukkan salah.
                </div>
                @endif

                <div class="grid grid-cols-3 gap-4 mb-8">
                    <template x-for="n in [1,2,3,4,5,6,7,8,9]">
                        <button type="button" @click="if(pin.length < 6) pin += n" class="h-14 rounded-xl bg-gray-50 hover:bg-gray-100 text-2xl font-medium text-gray-800 transition-colors" x-text="n"></button>
                    </template>
                    <button type="button" @click="pin = ''" class="h-14 rounded-xl bg-red-50 hover:bg-red-100 text-red-600 font-medium transition-colors">C</button>
                    <button type="button" @click="if(pin.length < 6) pin += '0'" class="h-14 rounded-xl bg-gray-50 hover:bg-gray-100 text-2xl font-medium text-gray-800 transition-colors">0</button>
                    <button type="button" @click="pin = pin.slice(0, -1)" class="h-14 rounded-xl bg-gray-50 hover:bg-gray-100 flex items-center justify-center text-gray-800 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414 6.414a2 2 0 001.414.586H19a2 2 0 002-2V7a2 2 0 00-2-2h-8.172a2 2 0 00-1.414.586L3 12z"></path></svg>
                    </button>
                </div>

                <button type="submit" class="w-full py-4 bg-teal-800 hover:bg-teal-900 text-white rounded-xl font-bold text-base shadow transition-colors disabled:opacity-50" :disabled="pin.length < 4 || pin.length > 6">
                    Masuk ke Kasir
                </button>
            </form>
        </div>

        <div class="text-center mt-6">
            <a href="{{ route('login') }}" class="text-teal-700 font-medium hover:underline">Masuk dengan email</a>
        </div>
    </div>

</body>
</html>

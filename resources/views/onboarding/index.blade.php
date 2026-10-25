<x-guest-layout>
    <div class="py-2">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight text-center">
            {{ __('Siapkan Usaha Anda') }}
        </h2>
        <p class="mt-1 text-sm text-gray-500 text-center">
            {{ $business->name }}
        </p>

        <!-- Progress -->
        <ol class="mt-6 flex items-center justify-between">
            @foreach ($steps as $number => $label)
                <li class="flex-1 flex flex-col items-center text-center">
                    <span class="flex items-center justify-center w-8 h-8 rounded-full text-sm font-semibold
                        {{ $number < $step ? 'bg-indigo-600 text-white' : ($number === $step ? 'bg-indigo-600 text-white ring-4 ring-indigo-100' : 'bg-gray-200 text-gray-600') }}">
                        @if ($number < $step)
                            &#10003;
                        @else
                            {{ $number }}
                        @endif
                    </span>
                    <span class="mt-1 text-[11px] leading-tight {{ $number === $step ? 'font-semibold text-indigo-700' : 'text-gray-500' }}">
                        {{ $label }}
                    </span>
                </li>
            @endforeach
        </ol>

        <div class="mt-6">
            @if (session('status'))
                <div class="mb-4 text-sm text-green-700 bg-green-50 border border-green-200 rounded-md px-3 py-2">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 text-sm text-red-700 bg-red-50 border border-red-200 rounded-md px-3 py-2">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($step === 1)
                <form method="POST" action="{{ route('onboarding.business') }}" class="space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="name" :value="__('Nama Usaha')" />
                        <x-text-input id="name" name="name" class="block mt-1 w-full"
                            :value="old('name', $business->name)" required autofocus />
                    </div>
                    <div>
                        <x-input-label for="type" :value="__('Jenis Usaha')" />
                        <x-select id="type" name="type" class="block mt-1 w-full">
                            @php $currentType = old('type', $business->type); @endphp
                            <option value="">{{ __('Pilih jenis usaha') }}</option>
                            @foreach (['Retail', 'F&B', 'Fashion', 'Sembako', 'Apotek', 'Jasa', 'Lainnya'] as $type)
                                <option value="{{ $type }}" @selected($currentType === $type)>{{ $type }}</option>
                            @endforeach
                        </x-select>
                    </div>
                    <div>
                        <x-input-label for="timezone" :value="__('Zona Waktu')" />
                        <x-select id="timezone" name="timezone" class="block mt-1 w-full">
                            @php $currentTz = old('timezone', $business->timezone ?? 'Asia/Jakarta'); @endphp
                            @foreach (['Asia/Jakarta', 'Asia/Makassar', 'Asia/Jayapura'] as $tz)
                                <option value="{{ $tz }}" @selected($currentTz === $tz)>{{ $tz }}</option>
                            @endforeach
                        </x-select>
                    </div>
                    <x-primary-button class="w-full justify-center">{{ __('Lanjut') }}</x-primary-button>
                </form>
            @elseif ($step === 2)
                <form method="POST" action="{{ route('onboarding.branch') }}" class="space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="name" :value="__('Nama Cabang')" />
                        <x-text-input id="name" name="name" class="block mt-1 w-full"
                            :value="old('name', $branch->name ?? 'Cabang Utama')" required autofocus />
                    </div>
                    <div>
                        <x-input-label for="phone" :value="__('Telepon Cabang')" />
                        <x-text-input id="phone" name="phone" class="block mt-1 w-full"
                            :value="old('phone', $branch->phone ?? $business->phone)" />
                    </div>
                    <div>
                        <x-input-label for="address" :value="__('Alamat Cabang')" />
                        <textarea id="address" name="address" rows="3"
                            class="block mt-1 w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">{{ old('address', $branch->address ?? $business->address) }}</textarea>
                    </div>
                    <x-primary-button class="w-full justify-center">{{ __('Lanjut') }}</x-primary-button>
                </form>
            @elseif ($step === 3)
                <form method="POST" action="{{ route('onboarding.preferences') }}" class="space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="tax_percentage" :value="__('Pajak (%)')" />
                        <x-text-input id="tax_percentage" name="tax_percentage" type="number" step="0.01" min="0" max="100"
                            class="block mt-1 w-full" :value="old('tax_percentage', $business->tax_percentage ?? 0)" required />
                    </div>
                    <div>
                        <x-input-label :value="__('Metode Pembayaran')" />
                        @php
                            $selectedMethods = old('payment_methods', $business->payment_methods ?? ['cash']);
                        @endphp
                        <div class="mt-2 grid grid-cols-2 gap-2">
                            @foreach (['cash' => 'Tunai', 'qris' => 'QRIS', 'debit' => 'Debit', 'credit' => 'Kredit', 'ewallet' => 'E-Wallet', 'transfer' => 'Transfer'] as $value => $label)
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                    <input type="checkbox" name="payment_methods[]" value="{{ $value }}"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                        @checked(in_array($value, (array) $selectedMethods, true))>
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <x-primary-button class="w-full justify-center">{{ __('Lanjut') }}</x-primary-button>
                </form>
            @elseif ($step === 4)
                <div class="space-y-6">
                    <p class="text-sm text-gray-600">
                        {{ __('Tambahkan produk pertama Anda, atau lewati dan isi nanti.') }}
                        @if ($productCount > 0)
                            <span class="font-medium text-gray-800">({{ $productCount }} {{ __('produk tersimpan') }})</span>
                        @endif
                    </p>

                    <form method="POST" action="{{ route('onboarding.products') }}" class="space-y-3 border border-gray-200 rounded-md p-4">
                        @csrf
                        <div>
                            <x-input-label for="product_name" :value="__('Nama Produk')" />
                            <x-text-input id="product_name" name="name" class="block mt-1 w-full"
                                :value="old('name')" />
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <x-input-label for="selling_price" :value="__('Harga Jual')" />
                                <x-text-input id="selling_price" name="selling_price" type="number" min="0" step="0.01"
                                    class="block mt-1 w-full" :value="old('selling_price')" />
                            </div>
                            <div>
                                <x-input-label for="stock" :value="__('Stok Awal')" />
                                <x-text-input id="stock" name="stock" type="number" min="0"
                                    class="block mt-1 w-full" :value="old('stock', 0)" />
                            </div>
                        </div>
                        <div>
                            <x-input-label for="unit_name" :value="__('Satuan')" />
                            <x-text-input id="unit_name" name="unit_name" class="block mt-1 w-full"
                                :value="old('unit_name', 'Pcs')" />
                        </div>
                        <x-secondary-button type="submit">{{ __('Tambah Produk') }}</x-secondary-button>
                    </form>

                    <form method="POST" action="{{ route('onboarding.complete') }}">
                        @csrf
                        <x-primary-button class="w-full justify-center">{{ __('Selesai & Mulai Jualan') }}</x-primary-button>
                    </form>
                </div>
            @endif

            @if ($step < 4)
                <form method="POST" action="{{ route('onboarding.complete') }}" class="mt-4 text-center">
                    @csrf
                    <button type="submit" class="text-sm text-gray-500 hover:text-gray-700 underline">
                        {{ __('Lewati penyiapan') }}
                    </button>
                </form>
            @endif
        </div>
    </div>
</x-guest-layout>



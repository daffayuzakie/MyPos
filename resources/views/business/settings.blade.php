@extends('layouts.pos')

@section('content')
<div class="space-y-5 md:space-y-6" x-data="{ activeTab: 'profile' }">

    @if (session('status'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs font-bold flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-xs font-bold">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Top Right: Simpan Perubahan Button (Matching Pengaturan.png) -->
    <div class="flex justify-end items-center">
        <span class="sr-only">Business Settings</span>
        <button type="button" @click="document.getElementById('settings-form').submit()" 
                class="bg-[#1D3B47] hover:bg-[#1a1f2c] text-white font-black px-7 py-2.5 rounded-full text-xs sm:text-sm shadow-xs transition-all">
            Simpan perubahan
        </button>
    </div>

    <!-- Mobile Horizontal Tabs Navigation (Pengaturan Â· Mobile.png) -->
    <div class="lg:hidden w-full overflow-x-auto hide-scrollbar bg-white rounded-2xl p-2 flex items-center gap-2 text-xs">
        <button type="button" @click="activeTab = 'profile'" 
                :class="activeTab === 'profile' ? 'bg-[#4D869C] text-white font-bold shadow-xs' : 'text-[#1D3B47] font-semibold'"
                class="px-4 py-2 rounded-xl whitespace-nowrap transition-all">
            Profil usaha
        </button>
        <button type="button" @click="activeTab = 'tax'" 
                :class="activeTab === 'tax' ? 'bg-[#4D869C] text-white font-bold shadow-xs' : 'text-[#1D3B47] font-semibold'"
                class="px-4 py-2 rounded-xl whitespace-nowrap transition-all">
            Pajak dan biaya
        </button>
        <button type="button" @click="activeTab = 'receipt'" 
                :class="activeTab === 'receipt' ? 'bg-[#4D869C] text-white font-bold shadow-xs' : 'text-[#1D3B47] font-semibold'"
                class="px-4 py-2 rounded-xl whitespace-nowrap transition-all">
            Struk
        </button>
        <button type="button" @click="activeTab = 'payment'" 
                :class="activeTab === 'payment' ? 'bg-[#4D869C] text-white font-bold shadow-xs' : 'text-[#1D3B47] font-semibold'"
                class="px-4 py-2 rounded-xl whitespace-nowrap transition-all">
            Pembayaran
        </button>
        <button type="button" @click="activeTab = 'device'" 
                :class="activeTab === 'device' ? 'bg-[#4D869C] text-white font-bold shadow-xs' : 'text-[#1D3B47] font-semibold'"
                class="px-4 py-2 rounded-xl whitespace-nowrap transition-all">
            Perangkat
        </button>
        <button type="button" @click="activeTab = 'notification'" 
                :class="activeTab === 'notification' ? 'bg-[#4D869C] text-white font-bold shadow-xs' : 'text-[#1D3B47] font-semibold'"
                class="px-4 py-2 rounded-xl whitespace-nowrap transition-all">
            Notifikasi
        </button>
        <button type="button" @click="activeTab = 'security'" 
                :class="activeTab === 'security' ? 'bg-[#4D869C] text-white font-bold shadow-xs' : 'text-[#1D3B47] font-semibold'"
                class="px-4 py-2 rounded-xl whitespace-nowrap transition-all">
            Keamanan dan backup
        </button>
    </div>

    <!-- Main Workspace (Desktop: Left Menu ~25%, Right Content ~75%) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 md:gap-6 items-start">
        
        <!-- Left Side Sub-Navigation Menu (Desktop) -->
        <div class="hidden lg:block lg:col-span-4 xl:col-span-3 bg-white rounded-3xl p-3 shadow-xs space-y-1.5">
            <button type="button" @click="activeTab = 'profile'" 
                    :class="activeTab === 'profile' ? 'bg-[#4D869C] text-white font-bold shadow-xs' : 'text-[#1D3B47] hover:text-black hover:bg-white/40 font-semibold'"
                    class="w-full text-left px-5 py-3 rounded-2xl text-sm transition-all flex items-center justify-between">
                <span>Profil usaha</span>
            </button>

            <button type="button" @click="activeTab = 'tax'" 
                    :class="activeTab === 'tax' ? 'bg-[#4D869C] text-white font-bold shadow-xs' : 'text-[#1D3B47] hover:text-black hover:bg-white/40 font-semibold'"
                    class="w-full text-left px-5 py-3 rounded-2xl text-sm transition-all flex items-center justify-between">
                <span>Pajak dan biaya</span>
            </button>

            <button type="button" @click="activeTab = 'receipt'" 
                    :class="activeTab === 'receipt' ? 'bg-[#4D869C] text-white font-bold shadow-xs' : 'text-[#1D3B47] hover:text-black hover:bg-white/40 font-semibold'"
                    class="w-full text-left px-5 py-3 rounded-2xl text-sm transition-all flex items-center justify-between">
                <span>Struk</span>
            </button>

            <button type="button" @click="activeTab = 'payment'" 
                    :class="activeTab === 'payment' ? 'bg-[#4D869C] text-white font-bold shadow-xs' : 'text-[#1D3B47] hover:text-black hover:bg-white/40 font-semibold'"
                    class="w-full text-left px-5 py-3 rounded-2xl text-sm transition-all flex items-center justify-between">
                <span>Pembayaran</span>
            </button>

            <button type="button" @click="activeTab = 'device'" 
                    :class="activeTab === 'device' ? 'bg-[#4D869C] text-white font-bold shadow-xs' : 'text-[#1D3B47] hover:text-black hover:bg-white/40 font-semibold'"
                    class="w-full text-left px-5 py-3 rounded-2xl text-sm transition-all flex items-center justify-between">
                <span>Perangkat</span>
            </button>

            <button type="button" @click="activeTab = 'notification'" 
                    :class="activeTab === 'notification' ? 'bg-[#4D869C] text-white font-bold shadow-xs' : 'text-[#1D3B47] hover:text-black hover:bg-white/40 font-semibold'"
                    class="w-full text-left px-5 py-3 rounded-2xl text-sm transition-all flex items-center justify-between">
                <span>Notifikasi</span>
            </button>

            <button type="button" @click="activeTab = 'security'" 
                    :class="activeTab === 'security' ? 'bg-[#4D869C] text-white font-bold shadow-xs' : 'text-[#1D3B47] hover:text-black hover:bg-white/40 font-semibold'"
                    class="w-full text-left px-5 py-3 rounded-2xl text-sm transition-all flex items-center justify-between">
                <span>Keamanan dan backup</span>
            </button>
        </div>

        <!-- Right Side Form Content Card (Matching Pengaturan.png) -->
        <div class="lg:col-span-8 xl:col-span-9 bg-white rounded-3xl p-6 sm:p-8 shadow-xs">
            <form id="settings-form" method="POST" action="{{ route('business.settings.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- SECTION 1: PROFIL USAHA -->
                <div x-show="activeTab === 'profile'" class="space-y-6">
                    <!-- Logo Selector -->
                    <div x-data="{ preview: $el.dataset.logo || null }" data-logo="{{ $business->logo_url }}">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-full bg-[#4D869C] text-white font-black text-sm flex items-center justify-center overflow-hidden shrink-0 shadow-xs">
                                <template x-if="preview">
                                    <img :src="preview" alt="Logo usaha" class="w-full h-full object-contain">
                                </template>
                                <template x-if="!preview">
                                    <span>{{ strtoupper(substr($business->name ?? 'WB', 0, 2)) }}</span>
                                </template>
                            </div>
                            <div>
                                <label for="logo-upload" class="font-bold text-sm text-[#1D3B47] cursor-pointer hover:underline">
                                    Pilih logo
                                </label>
                                <input id="logo-upload" type="file" name="logo" accept="image/png,image/jpeg,image/webp" class="hidden"
                                       @change="if ($event.target.files[0]) preview = URL.createObjectURL($event.target.files[0])">
                            </div>
                        </div>
                    </div>

                    <!-- Row 1: Nama usaha & Kode usaha -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-5">
                        <div>
                            <label class="block text-xs font-bold text-[#1D3B47] mb-1.5">Nama usaha</label>
                            <input type="text" name="name" value="{{ old('name', $business->name) }}" required
                                   class="w-full px-4 py-3.5 bg-[#dfe1e5]/80 border-0 rounded-2xl text-sm text-[#1D3B47] placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-[#4D869C] transition-all" 
                                   placeholder="Nama usaha">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[#1D3B47] mb-1.5">Kode usaha</label>
                            <input type="text" name="slug" value="{{ old('slug', $business->slug) }}" required
                                   x-data
                                   @input="$event.target.value = $event.target.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '')"
                                   class="w-full px-4 py-3.5 bg-[#dfe1e5]/80 border-0 rounded-2xl text-sm text-[#1D3B47] placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-[#4D869C] transition-all" 
                                   placeholder="kode-usaha">
                        </div>
                    </div>

                    <!-- Row 2: Jenis usaha & Zona waktu -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-5">
                        <div>
                            <label class="block text-xs font-bold text-[#1D3B47] mb-1.5">Jenis usaha</label>
                            <select class="w-full px-4 py-3.5 bg-[#dfe1e5]/80 border-0 rounded-2xl text-sm text-[#1D3B47] focus:outline-none focus:ring-2 focus:ring-[#4D869C] transition-all">
                                <option>Retail atau toko</option>
                                <option>Kafe & Restoran</option>
                                <option>Jasa & Layanan</option>
                                <option>Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[#1D3B47] mb-1.5">Zona waktu</label>
                            <select class="w-full px-4 py-3.5 bg-[#dfe1e5]/80 border-0 rounded-2xl text-sm text-[#1D3B47] focus:outline-none focus:ring-2 focus:ring-[#4D869C] transition-all">
                                <option>WIB (Waktu Indonesia Barat)</option>
                                <option>WITA (Waktu Indonesia Tengah)</option>
                                <option>WIT (Waktu Indonesia Timur)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Row 3: Alamat -->
                    <div>
                        <label class="block text-xs font-bold text-[#1D3B47] mb-1.5">Alamat</label>
                        <input type="text" name="address" value="{{ old('address', $business->address) }}"
                               class="w-full px-4 py-3.5 bg-[#dfe1e5]/80 border-0 rounded-2xl text-sm text-[#1D3B47] placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-[#4D869C] transition-all" 
                               placeholder="Alamat usaha">
                    </div>

                    <!-- Row 4: Nomor telepon -->
                    <div>
                        <label class="block text-xs font-bold text-[#1D3B47] mb-1.5">Nomor telepon</label>
                        <input type="text" name="phone" value="{{ old('phone', $business->phone) }}"
                               class="w-full px-4 py-3.5 bg-[#dfe1e5]/80 border-0 rounded-2xl text-sm text-[#1D3B47] placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-[#4D869C] transition-all" 
                               placeholder="+628123456789">
                    </div>
                </div>

                <!-- SECTION 2: PAJAK DAN BIAYA -->
                <div x-show="activeTab === 'tax'" x-cloak class="space-y-5">
                    <h2 class="text-base font-black text-[#1D3B47] border-b border-[#D6E5ED] pb-3">Pajak dan biaya</h2>
                    <div>
                        <label class="block text-xs font-bold text-[#1D3B47] mb-1.5">Tarif Pajak PPN (%)</label>
                        <input type="number" name="tax_percentage" value="{{ old('tax_percentage', $business->tax_percentage ?? 11) }}" step="0.1" min="0" max="100"
                               class="w-full px-4 py-3.5 bg-[#dfe1e5]/80 border-0 rounded-2xl text-sm text-[#1D3B47] focus:outline-none focus:ring-2 focus:ring-[#4D869C]">
                    </div>
                </div>

                <!-- SECTION 3: STRUK -->
                <div x-show="activeTab === 'receipt'" x-cloak class="space-y-5">
                    <h2 class="text-base font-black text-[#1D3B47] border-b border-[#D6E5ED] pb-3">Pengaturan Struk</h2>
                    <div>
                        <label class="block text-xs font-bold text-[#1D3B47] mb-1.5">Prefix Nomor Nota</label>
                        <input type="text" name="invoice_prefix" value="{{ old('invoice_prefix', $business->invoice_prefix ?? 'INV') }}"
                               class="w-full px-4 py-3.5 bg-[#dfe1e5]/80 border-0 rounded-2xl text-sm text-[#1D3B47] focus:outline-none focus:ring-2 focus:ring-[#4D869C]">
                    </div>
                </div>

                <!-- SECTION 4: PEMBAYARAN -->
                <div x-show="activeTab === 'payment'" x-cloak class="space-y-4">
                    <h2 class="text-base font-black text-[#1D3B47] border-b border-[#D6E5ED] pb-3">Metode Pembayaran</h2>
                    <div class="p-4 bg-white/60 rounded-2xl flex items-center justify-between text-xs">
                        <span class="font-bold text-[#1D3B47]">Pembayaran Tunai (Cash)</span>
                        <input type="checkbox" checked disabled class="w-4 h-4 text-white rounded">
                    </div>
                    <div class="p-4 bg-white/60 rounded-2xl flex items-center justify-between text-xs">
                        <span class="font-bold text-[#1D3B47]">QRIS Dinamis & Midtrans</span>
                        <input type="checkbox" checked class="w-4 h-4 text-white rounded">
                    </div>
                </div>

                <!-- SECTION 5: PERANGKAT -->
                <div x-show="activeTab === 'device'" x-cloak class="space-y-4">
                    <h2 class="text-base font-black text-[#1D3B47] border-b border-[#D6E5ED] pb-3">Perangkat & Printer</h2>
                    <div class="p-4 bg-white/60 rounded-2xl flex items-center justify-between text-xs">
                        <span class="font-bold text-[#1D3B47]">Printer Thermal Kasir</span>
                        <span class="px-3 py-1 bg-[#CDE8E5] text-[#1D3B47] rounded-full text-xs font-bold">Siap</span>
                    </div>
                </div>

                <!-- SECTION 6: NOTIFIKASI -->
                <div x-show="activeTab === 'notification'" x-cloak class="space-y-4">
                    <h2 class="text-base font-black text-[#1D3B47] border-b border-[#D6E5ED] pb-3">Notifikasi & Peringatan</h2>
                    <div class="p-4 bg-white/60 rounded-2xl flex items-center justify-between text-xs">
                        <span class="font-bold text-[#1D3B47]">Peringatan Stok Menipis</span>
                        <input type="checkbox" checked class="w-4 h-4 text-white rounded">
                    </div>
                </div>

                <!-- SECTION 7: KEAMANAN DAN BACKUP -->
                <div x-show="activeTab === 'security'" x-cloak class="space-y-4">
                    <h2 class="text-base font-black text-[#1D3B47] border-b border-[#D6E5ED] pb-3">Keamanan dan Backup</h2>
                    <div class="p-4 bg-[#EEF7FF] rounded-2xl flex items-center justify-between text-xs">
                        <div>
                            <div class="font-bold text-[#1D3B47]">Kode Usaha (Login PIN)</div>
                            <div class="text-[#75929E] text-[11px] mt-0.5">Digunakan oleh kasir untuk masuk dengan PIN</div>
                        </div>
                        <code class="px-3 py-1.5 bg-[#4D869C] text-white rounded-xl font-bold">{{ $business->slug }}</code>
                    </div>
                </div>
            </form>
        </div>

    </div>

</div>
@endsection





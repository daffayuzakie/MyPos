@extends('layouts.pos')

@section('content')
<div class="p-6 max-w-7xl mx-auto" x-data="{ activeTab: 'profile' }">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Pengaturan <span class="sr-only">Business Settings</span></h1>
            <p class="text-sm text-gray-500">Sesuaikan POS dengan cara kerja usaha Anda</p>
        </div>
        <button type="button" @click="document.getElementById('settings-form').submit()" class="pos-btn-primary flex items-center gap-2 shadow-xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span>Simpan perubahan</span>
        </button>
    </div>

    @if (session('status'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-semibold flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <!-- Settings Container -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        
        <!-- Left Side Sub-Navigation Tabs Card -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl border border-gray-200/80 p-3 shadow-xs space-y-1">
                <button type="button" @click="activeTab = 'profile'" 
                        :class="activeTab === 'profile' ? 'bg-[#e6f4ea] text-[#0d6e53] font-bold' : 'text-gray-700 hover:bg-gray-50 font-medium'"
                        class="w-full text-left px-4 py-3 rounded-xl text-xs transition-colors flex items-center justify-between">
                    <span>Profil usaha</span>
                    <svg x-show="activeTab === 'profile'" class="w-4 h-4 text-[#0d6e53]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </button>

                <button type="button" @click="activeTab = 'tax'" 
                        :class="activeTab === 'tax' ? 'bg-[#e6f4ea] text-[#0d6e53] font-bold' : 'text-gray-700 hover:bg-gray-50 font-medium'"
                        class="w-full text-left px-4 py-3 rounded-xl text-xs transition-colors flex items-center justify-between">
                    <span>Pajak dan biaya</span>
                    <svg x-show="activeTab === 'tax'" class="w-4 h-4 text-[#0d6e53]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </button>

                <button type="button" @click="activeTab = 'receipt'" 
                        :class="activeTab === 'receipt' ? 'bg-[#e6f4ea] text-[#0d6e53] font-bold' : 'text-gray-700 hover:bg-gray-50 font-medium'"
                        class="w-full text-left px-4 py-3 rounded-xl text-xs transition-colors flex items-center justify-between">
                    <span>Struk</span>
                    <svg x-show="activeTab === 'receipt'" class="w-4 h-4 text-[#0d6e53]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </button>

                <button type="button" @click="activeTab = 'payment'" 
                        :class="activeTab === 'payment' ? 'bg-[#e6f4ea] text-[#0d6e53] font-bold' : 'text-gray-700 hover:bg-gray-50 font-medium'"
                        class="w-full text-left px-4 py-3 rounded-xl text-xs transition-colors flex items-center justify-between">
                    <span>Pembayaran</span>
                    <svg x-show="activeTab === 'payment'" class="w-4 h-4 text-[#0d6e53]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </button>

                <button type="button" @click="activeTab = 'device'" 
                        :class="activeTab === 'device' ? 'bg-[#e6f4ea] text-[#0d6e53] font-bold' : 'text-gray-700 hover:bg-gray-50 font-medium'"
                        class="w-full text-left px-4 py-3 rounded-xl text-xs transition-colors flex items-center justify-between">
                    <span>Perangkat</span>
                    <svg x-show="activeTab === 'device'" class="w-4 h-4 text-[#0d6e53]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </button>

                <button type="button" @click="activeTab = 'notification'" 
                        :class="activeTab === 'notification' ? 'bg-[#e6f4ea] text-[#0d6e53] font-bold' : 'text-gray-700 hover:bg-gray-50 font-medium'"
                        class="w-full text-left px-4 py-3 rounded-xl text-xs transition-colors flex items-center justify-between">
                    <span>Notifikasi</span>
                    <svg x-show="activeTab === 'notification'" class="w-4 h-4 text-[#0d6e53]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </button>

                <button type="button" @click="activeTab = 'security'" 
                        :class="activeTab === 'security' ? 'bg-[#e6f4ea] text-[#0d6e53] font-bold' : 'text-gray-700 hover:bg-gray-50 font-medium'"
                        class="w-full text-left px-4 py-3 rounded-xl text-xs transition-colors flex items-center justify-between">
                    <span>Keamanan dan backup</span>
                    <svg x-show="activeTab === 'security'" class="w-4 h-4 text-[#0d6e53]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </button>
            </div>
        </div>

        <!-- Right Side Form Content Card -->
        <div class="lg:col-span-3">
            <div class="bg-white rounded-2xl border border-gray-200/80 p-6 sm:p-8 shadow-xs">
                
                <form id="settings-form" method="POST" action="{{ route('business.settings.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- SECTION 1: PROFIL USAHA (Image 1) -->
                    <div x-show="activeTab === 'profile'" class="space-y-6">
                        <h2 class="text-base font-extrabold text-gray-900 border-b border-gray-100 pb-3">Profil usaha</h2>

                        <div x-data="{ preview: $el.dataset.logo || null }" data-logo="{{ $business->logo_url }}">
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Logo usaha</label>
                            <div class="flex items-center gap-4">
                                <div class="w-16 h-16 rounded-xl border border-gray-200 bg-gray-50 flex items-center justify-center overflow-hidden shrink-0">
                                    <img x-show="preview" :src="preview" alt="Logo usaha" class="w-full h-full object-contain">
                                    <span x-show="!preview" class="text-[10px] text-gray-400 text-center px-1">Belum ada logo</span>
                                </div>
                                <div class="flex-1">
                                    <input type="file" name="logo" accept="image/png,image/jpeg,image/webp"
                                           @change="if ($event.target.files[0]) preview = URL.createObjectURL($event.target.files[0])"
                                           class="w-full text-xs text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-teal-700 file:text-white hover:file:bg-teal-800 cursor-pointer">
                                    <p class="text-[11px] text-gray-400 mt-1">PNG, JPG, atau WEBP. Maks 2 MB.</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Nama usaha</label>
                            <input type="text" name="name" value="{{ old('name', $business->name) }}" required
                                   class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-xs text-gray-900 focus:ring-1 focus:ring-teal-700 focus:border-teal-700" 
                                   placeholder="[NAMA USAHA]">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Jenis usaha</label>
                            <select class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-xs text-gray-900 focus:ring-1 focus:ring-teal-700 focus:border-teal-700">
                                <option>Retail atau toko</option>
                                <option>Kafe & Restoran</option>
                                <option>Jasa & Layanan</option>
                                <option>Lainnya</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Alamat</label>
                            <input type="text" name="address" value="{{ old('address', $business->address) }}"
                                   class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-xs text-gray-900 focus:ring-1 focus:ring-teal-700 focus:border-teal-700" 
                                   placeholder="[ALAMAT USAHA]">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Nomor telepon</label>
                            <input type="text" name="phone" value="{{ old('phone', $business->phone) }}"
                                   class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-xs text-gray-900 focus:ring-1 focus:ring-teal-700 focus:border-teal-700" 
                                   placeholder="[NOMOR TELEPON]">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Zona waktu</label>
                            <select class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-xs text-gray-900 focus:ring-1 focus:ring-teal-700 focus:border-teal-700">
                                <option>WIB (Waktu Indonesia Barat)</option>
                                <option>WITA (Waktu Indonesia Tengah)</option>
                                <option>WIT (Waktu Indonesia Timur)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Mata uang</label>
                            <select class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-xs text-gray-900 focus:ring-1 focus:ring-teal-700 focus:border-teal-700">
                                <option>Rupiah (IDR)</option>
                                <option>US Dollar (USD)</option>
                            </select>
                        </div>
                    </div>

                    <!-- SECTION 2: PAJAK DAN BIAYA -->
                    <div x-show="activeTab === 'tax'" x-cloak class="space-y-6">
                        <h2 class="text-base font-extrabold text-gray-900 border-b border-gray-100 pb-3">Pajak dan biaya</h2>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Tarif Pajak PPN (%)</label>
                            <input type="number" name="tax_percentage" value="{{ old('tax_percentage', $business->tax_percentage ?? 11) }}" step="0.1" min="0" max="100"
                                   class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-xs text-gray-900 focus:ring-1 focus:ring-teal-700 focus:border-teal-700" 
                                   placeholder="11">
                            <p class="text-[11px] text-gray-400 mt-1">Diterapkan secara otomatis pada transaksi kasir.</p>
                        </div>

                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100">
                            <div>
                                <div class="text-xs font-bold text-gray-900">Aktifkan Perhitungan Pajak Otomatis</div>
                                <div class="text-[11px] text-gray-500">Hitung nilai PPN otomatis di struk kasir</div>
                            </div>
                            <input type="checkbox" checked class="w-4 h-4 text-teal-600 rounded border-gray-300 focus:ring-teal-500">
                        </div>
                    </div>

                    <!-- SECTION 3: STRUK -->
                    <div x-show="activeTab === 'receipt'" x-cloak class="space-y-6">
                        <h2 class="text-base font-extrabold text-gray-900 border-b border-gray-100 pb-3">Pengaturan Struk</h2>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Prefix Nomor Nota</label>
                            <input type="text" name="invoice_prefix" value="{{ old('invoice_prefix', $business->invoice_prefix ?? 'INV') }}"
                                   class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-xs text-gray-900 focus:ring-1 focus:ring-teal-700 focus:border-teal-700" 
                                   placeholder="INV">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Nomor Urut Faktur Awal</label>
                            <input type="number" name="invoice_sequence" value="{{ old('invoice_sequence', $business->invoice_sequence ?? 1) }}"
                                   class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-xs text-gray-900 focus:ring-1 focus:ring-teal-700 focus:border-teal-700">
                        </div>

                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100">
                            <div>
                                <div class="text-xs font-bold text-gray-900">Cetak Logo Toko di Struk</div>
                                <div class="text-[11px] text-gray-500">Menampilkan inisial / logo pada header struk termal</div>
                            </div>
                            <input type="checkbox" checked class="w-4 h-4 text-teal-600 rounded border-gray-300 focus:ring-teal-500">
                        </div>
                    </div>

                    <!-- SECTION 4: PEMBAYARAN -->
                    <div x-show="activeTab === 'payment'" x-cloak class="space-y-4">
                        <h2 class="text-base font-extrabold text-gray-900 border-b border-gray-100 pb-3">Metode Pembayaran</h2>

                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100">
                            <div>
                                <div class="text-xs font-bold text-gray-900">Pembayaran Tunai (Cash)</div>
                                <div class="text-[11px] text-gray-500">Fitur nominal cepat dan kalkulator kembalian</div>
                            </div>
                            <input type="checkbox" checked disabled class="w-4 h-4 text-teal-600 rounded border-gray-300">
                        </div>

                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100">
                            <div>
                                <div class="text-xs font-bold text-gray-900">QRIS Dinamis & Statis</div>
                                <div class="text-[11px] text-gray-500">Terima pembayaran dari seluruh e-wallet & m-banking</div>
                            </div>
                            <input type="checkbox" checked class="w-4 h-4 text-teal-600 rounded border-gray-300 focus:ring-teal-500">
                        </div>

                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100">
                            <div>
                                <div class="text-xs font-bold text-gray-900">Kartu Debit / Kredit (EDC)</div>
                                <div class="text-[11px] text-gray-500">Mencatat nomor referensi / approval code</div>
                            </div>
                            <input type="checkbox" checked class="w-4 h-4 text-teal-600 rounded border-gray-300 focus:ring-teal-500">
                        </div>

                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100">
                            <div>
                                <div class="text-xs font-bold text-gray-900">E-wallet (GoPay, OVO, ShopeePay, Dana)</div>
                                <div class="text-[11px] text-gray-500">Integrasi dompet digital</div>
                            </div>
                            <input type="checkbox" checked class="w-4 h-4 text-teal-600 rounded border-gray-300 focus:ring-teal-500">
                        </div>
                    </div>

                    <!-- SECTION 5: PERANGKAT -->
                    <div x-show="activeTab === 'device'" x-cloak class="space-y-4">
                        <h2 class="text-base font-extrabold text-gray-900 border-b border-gray-100 pb-3">Perangkat & Printer</h2>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100">
                            <div>
                                <div class="text-xs font-bold text-gray-900">Printer Thermal Kasir (58mm / 80mm)</div>
                                <div class="text-[11px] text-gray-500">Hubungkan via USB, Bluetooth, atau LAN</div>
                            </div>
                            <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 rounded-full text-[10px] font-bold">Siap</span>
                        </div>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100">
                            <div>
                                <div class="text-xs font-bold text-gray-900">Barcode Scanner</div>
                                <div class="text-[11px] text-gray-500">Mendukung scan otomatis pada input pencarian kasir</div>
                            </div>
                            <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 rounded-full text-[10px] font-bold">Aktif</span>
                        </div>
                    </div>

                    <!-- SECTION 6: NOTIFIKASI -->
                    <div x-show="activeTab === 'notification'" x-cloak class="space-y-4">
                        <h2 class="text-base font-extrabold text-gray-900 border-b border-gray-100 pb-3">Notifikasi & Peringatan</h2>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100">
                            <div>
                                <div class="text-xs font-bold text-gray-900">Peringatan Stok Menipis</div>
                                <div class="text-[11px] text-gray-500">Tampilkan badge merah saat stok produk &le; 5</div>
                            </div>
                            <input type="checkbox" checked class="w-4 h-4 text-teal-600 rounded border-gray-300 focus:ring-teal-500">
                        </div>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100">
                            <div>
                                <div class="text-xs font-bold text-gray-900">Notifikasi WhatsApp ke Pelanggan</div>
                                <div class="text-[11px] text-gray-500">Kirim nota digital via nomor WhatsApp pelanggan</div>
                            </div>
                            <input type="checkbox" checked class="w-4 h-4 text-teal-600 rounded border-gray-300 focus:ring-teal-500">
                        </div>
                    </div>

                    <!-- SECTION 7: KEAMANAN DAN BACKUP -->
                    <div x-show="activeTab === 'security'" x-cloak class="space-y-4">
                        <h2 class="text-base font-extrabold text-gray-900 border-b border-gray-100 pb-3">Keamanan dan Backup</h2>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100">
                            <div>
                                <div class="text-xs font-bold text-gray-900">Login Kasir dengan PIN</div>
                                <div class="text-[11px] text-gray-500">Gunakan 4-6 digit angka PIN untuk pergantian kasir cepat</div>
                            </div>
                            <input type="checkbox" checked class="w-4 h-4 text-teal-600 rounded border-gray-300 focus:ring-teal-500">
                        </div>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100">
                            <div>
                                <div class="text-xs font-bold text-gray-900">Sinkronisasi Offline Mode</div>
                                <div class="text-[11px] text-gray-500">Otomatis simpan antrean saat koneksi internet terputus</div>
                            </div>
                            <input type="checkbox" checked class="w-4 h-4 text-teal-600 rounded border-gray-300 focus:ring-teal-500">
                        </div>
                    </div>
                </form>

            </div>
        </div>

    </div>
</div>
@endsection
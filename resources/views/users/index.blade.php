@extends('layouts.pos')

@section('topbar-search')
    <x-topbar-search :action="route('users.index')" placeholder="Cari nama atau email..." />
@endsection

@section('content')
<div class="p-6 max-w-7xl mx-auto" x-data="{ activeTab: 'employees' }">
    
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Karyawan & hak akses</h1>
            <p class="text-sm text-gray-500">Atur akun, peran, dan catatan aktivitas</p>
        </div>
        <button @click="document.getElementById('employee-form')?.scrollIntoView({ behavior: 'smooth' })" class="pos-btn-primary text-xs shadow-xs">
            Tambah karyawan
        </button>
    </div>

    @if (session('status'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-semibold flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs font-semibold">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Main Workspace (2 Columns: Left Tabs Table + Right Employee Form) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left 2 Cols: Tabs & Tables -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-200/80 p-5 shadow-xs flex flex-col justify-between">
            <div>
                <!-- Horizontal Tabs: Karyawan, Hak akses, Log aktivitas -->
                <div class="flex items-center gap-1.5 p-1 bg-gray-100/80 rounded-xl w-fit mb-4 overflow-x-auto">
                    <button @click="activeTab = 'employees'" 
                            :class="activeTab === 'employees' ? 'bg-white text-gray-900 font-bold shadow-xs' : 'text-gray-600 hover:text-gray-900 font-medium'"
                            class="px-4 py-1.5 rounded-lg text-xs transition-all shrink-0">
                        Karyawan
                    </button>
                    <button @click="activeTab = 'permissions'" 
                            :class="activeTab === 'permissions' ? 'bg-white text-gray-900 font-bold shadow-xs' : 'text-gray-600 hover:text-gray-900 font-medium'"
                            class="px-4 py-1.5 rounded-lg text-xs transition-all shrink-0">
                        Hak akses
                    </button>
                    <button @click="activeTab = 'logs'" 
                            :class="activeTab === 'logs' ? 'bg-white text-gray-900 font-bold shadow-xs' : 'text-gray-600 hover:text-gray-900 font-medium'"
                            class="px-4 py-1.5 rounded-lg text-xs transition-all shrink-0">
                        Log aktivitas
                    </button>
                </div>

                <!-- TAB 1: KARYAWAN TABLE (Image 5) -->
                <div x-show="activeTab === 'employees'" class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-gray-200 text-gray-400 font-bold uppercase tracking-wider text-[10px]">
                                <th class="py-3 px-3">NAMA</th>
                                <th class="py-3 px-3">PERAN</th>
                                <th class="py-3 px-3">CABANG</th>
                                <th class="py-3 px-3">STATUS</th>
                                <th class="py-3 px-3 text-right">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700 font-medium">
                            @forelse($users as $u)
                            <tr class="hover:bg-gray-50/70 transition-colors">
                                <td class="py-3 px-3">
                                    <div class="font-bold text-gray-900">{{ $u->name }}</div>
                                    <div class="text-[11px] text-gray-400">{{ $u->email }}</div>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $u->hasRole('admin') ? 'bg-purple-100 text-purple-800' : 'bg-teal-100 text-teal-800' }}">
                                        {{ ucfirst($u->getRoleNames()->first() ?? 'Kasir') }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-gray-500">{{ $u->branch?->name ?? 'Semua cabang' }}</td>
                                <td class="py-3 px-3">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $u->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                        {{ $u->status === 'active' ? 'AKTIF' : 'NONAKTIF' }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-right">
                                    @if($u->status === 'active' && $u->id !== auth()->id())
                                        <form method="POST" action="{{ route('users.deactivate', $u) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="text-[11px] font-bold text-rose-600 hover:underline" onclick="return confirm('Nonaktifkan karyawan ini?')">
                                                Nonaktifkan
                                            </button>
                                        </form>
                                    @elseif($u->status === 'inactive')
                                        <form method="POST" action="{{ route('users.activate', $u) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="text-[11px] font-bold text-emerald-600 hover:underline">
                                                Aktifkan
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-gray-400 text-[11px] font-bold">Akun Anda</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-gray-400">Belum ada karyawan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- TAB 2: HAK AKSES MATRIX TABLE -->
                <div x-show="activeTab === 'permissions'" x-cloak class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-gray-200 text-gray-400 font-bold uppercase tracking-wider text-[10px]">
                                <th class="py-3 px-3">FITUR / MODUL</th>
                                <th class="py-3 px-3 text-center">OWNER / ADMIN</th>
                                <th class="py-3 px-3 text-center">MANAJER</th>
                                <th class="py-3 px-3 text-center">KASIR</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700 font-medium">
                            <tr class="hover:bg-gray-50/70">
                                <td class="py-3 px-3 font-bold text-gray-900">Kasir & Checkout Penjualan</td>
                                <td class="py-3 px-3 text-center"><input type="checkbox" checked disabled class="w-4 h-4 text-teal-600 rounded"></td>
                                <td class="py-3 px-3 text-center"><input type="checkbox" checked disabled class="w-4 h-4 text-teal-600 rounded"></td>
                                <td class="py-3 px-3 text-center"><input type="checkbox" checked disabled class="w-4 h-4 text-teal-600 rounded"></td>
                            </tr>
                            <tr class="hover:bg-gray-50/70">
                                <td class="py-3 px-3 font-bold text-gray-900">Retur & Refund Transaksi</td>
                                <td class="py-3 px-3 text-center"><input type="checkbox" checked disabled class="w-4 h-4 text-teal-600 rounded"></td>
                                <td class="py-3 px-3 text-center"><input type="checkbox" checked disabled class="w-4 h-4 text-teal-600 rounded"></td>
                                <td class="py-3 px-3 text-center"><input type="checkbox" disabled class="w-4 h-4 text-gray-300 rounded"></td>
                            </tr>
                            <tr class="hover:bg-gray-50/70">
                                <td class="py-3 px-3 font-bold text-gray-900">Kelola Produk, Kategori & Stok</td>
                                <td class="py-3 px-3 text-center"><input type="checkbox" checked disabled class="w-4 h-4 text-teal-600 rounded"></td>
                                <td class="py-3 px-3 text-center"><input type="checkbox" checked disabled class="w-4 h-4 text-teal-600 rounded"></td>
                                <td class="py-3 px-3 text-center"><input type="checkbox" disabled class="w-4 h-4 text-gray-300 rounded"></td>
                            </tr>
                            <tr class="hover:bg-gray-50/70">
                                <td class="py-3 px-3 font-bold text-gray-900">Supplier & Purchase Order</td>
                                <td class="py-3 px-3 text-center"><input type="checkbox" checked disabled class="w-4 h-4 text-teal-600 rounded"></td>
                                <td class="py-3 px-3 text-center"><input type="checkbox" checked disabled class="w-4 h-4 text-teal-600 rounded"></td>
                                <td class="py-3 px-3 text-center"><input type="checkbox" disabled class="w-4 h-4 text-gray-300 rounded"></td>
                            </tr>
                            <tr class="hover:bg-gray-50/70">
                                <td class="py-3 px-3 font-bold text-gray-900">Laporan Penjualan & Margin</td>
                                <td class="py-3 px-3 text-center"><input type="checkbox" checked disabled class="w-4 h-4 text-teal-600 rounded"></td>
                                <td class="py-3 px-3 text-center"><input type="checkbox" checked disabled class="w-4 h-4 text-teal-600 rounded"></td>
                                <td class="py-3 px-3 text-center"><input type="checkbox" disabled class="w-4 h-4 text-gray-300 rounded"></td>
                            </tr>
                            <tr class="hover:bg-gray-50/70">
                                <td class="py-3 px-3 font-bold text-gray-900">Pengaturan Usaha & Karyawan</td>
                                <td class="py-3 px-3 text-center"><input type="checkbox" checked disabled class="w-4 h-4 text-teal-600 rounded"></td>
                                <td class="py-3 px-3 text-center"><input type="checkbox" disabled class="w-4 h-4 text-gray-300 rounded"></td>
                                <td class="py-3 px-3 text-center"><input type="checkbox" disabled class="w-4 h-4 text-gray-300 rounded"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- TAB 3: LOG AKTIVITAS -->
                <div x-show="activeTab === 'logs'" x-cloak class="overflow-x-auto">
                    <div class="space-y-3 py-2">
                        <div class="p-3 bg-gray-50 rounded-xl border border-gray-100 flex items-center justify-between text-xs">
                            <div>
                                <span class="font-bold text-gray-900">{{ auth()->user()->name }}</span>
                                <span class="text-gray-600">membuka sesi kasir POS</span>
                            </div>
                            <span class="text-[11px] text-gray-400">Hari ini</span>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-xl border border-gray-100 flex items-center justify-between text-xs">
                            <div>
                                <span class="font-bold text-gray-900">Sistem</span>
                                <span class="text-gray-600">memperbarui stok produk dan transaksi</span>
                            </div>
                            <span class="text-[11px] text-gray-400">Hari ini</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            @if(isset($users) && $users->hasPages())
                <div class="pt-4 border-t border-gray-100">
                    {{ $users->links() }}
                </div>
            @endif
        </div>

        <!-- Right 1 Col: "Tambah karyawan" Card (Image 5) -->
        <div class="bg-white rounded-2xl border border-gray-200/80 p-5 shadow-xs">
            <h3 class="text-sm font-extrabold text-gray-900 mb-4">Tambah karyawan</h3>
            
            <form id="employee-form" method="POST" action="{{ route('users.store') }}" class="space-y-4">
                @csrf

                <!-- Nama Lengkap -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Nama lengkap</label>
                    <input type="text" name="name" required value="{{ old('name') }}"
                           class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl text-xs text-gray-900 focus:ring-1 focus:ring-teal-700 focus:border-teal-700" 
                           placeholder="Nama karyawan">
                </div>

                <!-- Email atau nomor HP -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Email atau nomor HP</label>
                    <input type="text" name="contact" required value="{{ old('contact') }}"
                           class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl text-xs text-gray-900 focus:ring-1 focus:ring-teal-700 focus:border-teal-700" 
                           placeholder="Untuk login dan notifikasi">
                </div>

                <!-- Peran -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Peran</label>
                    <select name="role" required class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl text-xs text-gray-900 focus:ring-1 focus:ring-teal-700 focus:border-teal-700">
                        <option value="kasir">Kasir</option>
                        <option value="manajer">Manajer</option>
                        <option value="admin">Owner / Admin</option>
                    </select>
                </div>

                <!-- Cabang -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Cabang</label>
                    <select name="branch_id" class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl text-xs text-gray-900 focus:ring-1 focus:ring-teal-700 focus:border-teal-700">
                        <option value="">Semua cabang</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected(old('branch_id') == $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- PIN Kasir -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">PIN kasir (4 sampai 6 angka)</label>
                    <input type="password" name="pin" maxlength="6"
                           class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl text-xs text-gray-900 focus:ring-1 focus:ring-teal-700 focus:border-teal-700" 
                           placeholder="••••••">
                </div>

                <!-- Buttons: Batal | Simpan karyawan -->
                <div class="flex gap-2 pt-2">
                    <button type="reset" class="w-1/3 py-2.5 border border-gray-300 rounded-xl text-xs font-bold text-gray-700 hover:bg-gray-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 py-2.5 bg-[#0d6e53] hover:bg-[#0a5742] text-white rounded-xl text-xs font-bold shadow-xs transition-colors">
                        Simpan karyawan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
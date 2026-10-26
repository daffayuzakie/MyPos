@extends('layouts.pos')

@section('topbar-search')
    <form method="GET" action="{{ route('users.index') }}" class="w-full relative">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email..." 
               class="w-full bg-[#edf5fa] border border-[#D6E5ED] rounded-full px-5 py-2.5 text-sm text-[#1D3B47] placeholder-[#75929E] focus:outline-none focus:ring-2 focus:ring-[#4D869C] transition-all">
    </form>
@endsection

@section('content')
<div class="space-y-5 md:space-y-6" x-data="{ 
    activeTab: 'employees',
    showEditModal: false,
    showCreateModal: false,
    editForm: {
        id: null,
        name: '',
        contact: '',
        role: 'kasir',
        branch_id: '',
        pin: '',
        is_active: true,
        update_url: '',
        deactivate_url: '',
        activate_url: '',
        is_current: false
    },
    init() {
        this.$watch('showEditModal', val => {
            if (window.Alpine && Alpine.store('modal')) Alpine.store('modal').open = Boolean(val || this.showCreateModal);
        });
        this.$watch('showCreateModal', val => {
            if (window.Alpine && Alpine.store('modal')) Alpine.store('modal').open = Boolean(val || this.showEditModal);
        });
    },
    openEditModal(user) {
        this.editForm.id = user.id;
        this.editForm.name = user.name;
        this.editForm.contact = user.email;
        this.editForm.role = user.role;
        this.editForm.branch_id = user.branch_id || '';
        this.editForm.pin = '';
        this.editForm.is_active = (user.status === 'active');
        this.editForm.update_url = '{{ url('/users') }}/' + user.id;
        this.editForm.deactivate_url = '{{ url('/users') }}/' + user.id + '/deactivate';
        this.editForm.activate_url = '{{ url('/users') }}/' + user.id + '/activate';
        this.editForm.is_current = user.is_current;
        this.showEditModal = true;
    }
}">

    @if (session('status'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs font-bold flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-xs font-bold">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Top Tabs & Action Row -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <!-- Tabs -->
        <div class="flex flex-wrap items-center gap-2">
            <button @click="activeTab = 'employees'" 
                    :class="activeTab === 'employees' ? 'bg-[#4D869C] text-white font-bold shadow-xs' : 'bg-[#CDE8E5] text-[#1D3B47] hover:bg-[#b8ded9] font-semibold'"
                    class="px-5 py-2 rounded-full text-xs sm:text-sm transition-all">
                Karyawan
            </button>
            <button @click="activeTab = 'permissions'" 
                    :class="activeTab === 'permissions' ? 'bg-[#4D869C] text-white font-bold shadow-xs' : 'bg-[#CDE8E5] text-[#1D3B47] hover:bg-[#b8ded9] font-semibold'"
                    class="px-5 py-2 rounded-full text-xs sm:text-sm transition-all">
                Hak akses
            </button>
            <button @click="activeTab = 'logs'" 
                    :class="activeTab === 'logs' ? 'bg-[#4D869C] text-white font-bold shadow-xs' : 'bg-[#CDE8E5] text-[#1D3B47] hover:bg-[#b8ded9] font-semibold'"
                    class="px-5 py-2 rounded-full text-xs sm:text-sm transition-all">
                Log aktivitas
            </button>
        </div>

        <!-- Action Button -->
        @can('users create')
        <div>
            <button @click="showCreateModal = true" 
                    class="bg-[#4D869C] hover:bg-[#3D6B7D] text-white font-bold px-6 py-2.5 rounded-full text-xs sm:text-sm transition-all shadow-xs flex items-center gap-1.5 cursor-pointer">
                + Tambah karyawan
            </button>
        </div>
        @endcan
    </div>

    <!-- Main Workspace: FULL-WIDTH TABLE DATA KARYAWAN (Matching Karyawan.png) -->
    <div class="bg-white border border-[#D6E5ED] rounded-3xl p-5 sm:p-6 shadow-xs overflow-hidden">
        
        <!-- TAB 1: Karyawan Table -->
        <div x-show="activeTab === 'employees'" class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="text-[11px] sm:text-xs font-black text-[#75929E] uppercase tracking-wider border-b border-[#D6E5ED] pb-3">
                        <th class="pb-3 font-black">NAMA</th>
                        <th class="pb-3 font-black">PERAN</th>
                        <th class="hidden md:table-cell pb-3 font-black">CABANG</th>
                        <th class="hidden md:table-cell pb-3 font-black">STATUS</th>
                        <th class="pb-3 text-right font-black"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-300/70">
                    @forelse($users as $u)
                    <tr class="hover:bg-[#EEF7FF] transition-colors">
                        <!-- Nama & Avatar -->
                        <td class="py-3.5 pr-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-9 h-9 rounded-2xl bg-[#4D869C] text-white font-black text-sm flex items-center justify-center shrink-0 shadow-xs">
                                    {{ strtoupper(substr($u->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold text-[#1D3B47] text-sm sm:text-base leading-tight truncate">{{ $u->name }}</div>
                                    <div class="text-xs text-[#75929E] font-medium truncate mt-0.5">{{ $u->email }}</div>
                                </div>
                            </div>
                        </td>

                        <!-- Peran -->
                        <td class="py-3.5 pr-3 whitespace-nowrap">
                            @if($u->hasRole('admin'))
                                <span class="bg-[#4D869C] text-white px-3.5 py-1 rounded-full text-xs font-bold shadow-xs">
                                    Admin
                                </span>
                            @else
                                <span class="bg-[#CDE8E5] text-[#1D3B47] px-3.5 py-1 rounded-full text-xs font-bold shadow-xs">
                                    Kasir
                                </span>
                            @endif
                        </td>

                        <!-- Cabang (Desktop) -->
                        <td class="hidden md:table-cell py-3.5 pr-3 text-xs sm:text-sm text-[#1D3B47] font-medium whitespace-nowrap">
                            {{ $u->branch?->name ?? 'Semua cabang' }}
                        </td>

                        <!-- Status (Desktop) -->
                        <td class="hidden md:table-cell py-3.5 pr-3 whitespace-nowrap">
                            @if($u->status === 'active')
                                <span class="bg-[#CDE8E5] text-[#1D3B47] px-3 py-1 rounded-full text-xs font-bold">
                                    Aktif
                                </span>
                            @else
                                <span class="bg-gray-300 text-[#1D3B47] px-3 py-1 rounded-full text-xs font-bold">
                                    Nonaktif
                                </span>
                            @endif
                        </td>

                        <!-- Aksi (Edit + Nonaktifkan) -->
                        <td class="py-3.5 pl-3 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-3">
                                @can('users edit')
                                <button type="button" 
                                        @click="openEditModal({{ json_encode([
                                            'id' => $u->id,
                                            'name' => $u->name,
                                            'email' => $u->email,
                                            'role' => $u->roles->first()?->name ?? 'kasir',
                                            'branch_id' => $u->branch_id,
                                            'status' => $u->status,
                                            'is_current' => $u->id === auth()->id(),
                                        ]) }})"
                                        class="text-[#4D869C] hover:text-[#3D6B7D] font-bold text-xs sm:text-sm underline decoration-[#4D869C] decoration-2 transition-all cursor-pointer">
                                    Edit
                                </button>
                                @endcan

                                @can('users deactivate')
                                @if($u->status === 'active' && $u->id !== auth()->id())
                                    <form method="POST" action="{{ route('users.deactivate', $u) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-[#d9383a] hover:text-black font-bold text-xs sm:text-sm underline decoration-[#d9383a] decoration-2 transition-all" onclick="return confirm('Nonaktifkan karyawan ini?')">
                                            Nonaktifkan
                                        </button>
                                    </form>
                                @elseif($u->status === 'inactive')
                                    <form method="POST" action="{{ route('users.activate', $u) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-[#4D869C] hover:text-black font-bold text-xs sm:text-sm underline decoration-[#4D869C] decoration-2 transition-all">
                                            Aktifkan
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs font-bold text-gray-400">Akun Anda</span>
                                @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-10 text-center text-[#75929E] text-sm font-medium">Belum ada karyawan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- TAB 2: Hak Akses Matrix -->
        <div x-show="activeTab === 'permissions'" x-cloak class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="text-[11px] sm:text-xs font-black text-[#75929E] uppercase tracking-wider border-b border-[#D6E5ED] pb-3">
                        <th class="pb-3 font-black">FITUR / MODUL</th>
                        <th class="pb-3 text-center font-black">ADMIN</th>
                        <th class="pb-3 text-center font-black">KASIR</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-300/70">
                    <tr class="hover:bg-[#EEF7FF]">
                        <td class="py-3 font-bold text-[#1D3B47]">Kasir & Checkout Penjualan</td>
                        <td class="py-3 text-center"><input type="checkbox" checked disabled class="w-4 h-4 text-[#4D869C] rounded"></td>
                        <td class="py-3 text-center"><input type="checkbox" checked disabled class="w-4 h-4 text-[#4D869C] rounded"></td>
                    </tr>
                    <tr class="hover:bg-[#EEF7FF]">
                        <td class="py-3 font-bold text-[#1D3B47]">Retur & Refund Transaksi</td>
                        <td class="py-3 text-center"><input type="checkbox" checked disabled class="w-4 h-4 text-[#4D869C] rounded"></td>
                        <td class="py-3 text-center"><input type="checkbox" disabled class="w-4 h-4 text-gray-300 rounded"></td>
                    </tr>
                    <tr class="hover:bg-[#EEF7FF]">
                        <td class="py-3 font-bold text-[#1D3B47]">Kelola Produk & Stok</td>
                        <td class="py-3 text-center"><input type="checkbox" checked disabled class="w-4 h-4 text-[#4D869C] rounded"></td>
                        <td class="py-3 text-center"><input type="checkbox" disabled class="w-4 h-4 text-gray-300 rounded"></td>
                    </tr>
                    <tr class="hover:bg-[#EEF7FF]">
                        <td class="py-3 font-bold text-[#1D3B47]">Laporan Penjualan & Keuangan</td>
                        <td class="py-3 text-center"><input type="checkbox" checked disabled class="w-4 h-4 text-[#4D869C] rounded"></td>
                        <td class="py-3 text-center"><input type="checkbox" disabled class="w-4 h-4 text-gray-300 rounded"></td>
                    </tr>
                    <tr class="hover:bg-[#EEF7FF]">
                        <td class="py-3 font-bold text-[#1D3B47]">Pengaturan Usaha & Karyawan</td>
                        <td class="py-3 text-center"><input type="checkbox" checked disabled class="w-4 h-4 text-[#4D869C] rounded"></td>
                        <td class="py-3 text-center"><input type="checkbox" disabled class="w-4 h-4 text-gray-300 rounded"></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- TAB 3: Log Aktivitas -->
        <div x-show="activeTab === 'logs'" x-cloak class="space-y-3 py-2">
            <div class="p-4 bg-[#EEF7FF] rounded-2xl border border-gray-200/80 flex items-center justify-between text-xs">
                <div>
                    <span class="font-bold text-[#1D3B47]">{{ auth()->user()->name }}</span>
                    <span class="text-[#75929E]">membuka sesi kasir POS</span>
                </div>
                <span class="text-xs text-gray-400 font-semibold">Hari ini</span>
            </div>
            <div class="p-4 bg-[#EEF7FF] rounded-2xl border border-gray-200/80 flex items-center justify-between text-xs">
                <div>
                    <span class="font-bold text-[#1D3B47]">Sistem</span>
                    <span class="text-[#75929E]">memperbarui stok produk dan transaksi</span>
                </div>
                <span class="text-xs text-gray-400 font-semibold">Hari ini</span>
            </div>
        </div>

        @if(isset($users) && $users->hasPages())
            <div class="pt-4 border-t border-gray-300/70">
                {{ $users->links() }}
            </div>
        @endif
    </div>
    <!-- POP UP: EDIT KARYAWAN                      -->
    <!-- Persis seperti Edit karyawan.png           -->
    <!-- ========================================== -->
    <div x-show="showEditModal" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center bg-black/60 backdrop-blur-xs p-0 sm:p-4">
        <div @click.away="showEditModal = false"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-t-[32px] sm:rounded-[32px] p-6 sm:p-8 max-w-lg w-full relative shadow-2xl overflow-hidden max-h-[92vh] flex flex-col justify-between">
            
            <!-- Mobile Top Handle Bar -->
            <div class="w-12 h-1.5 bg-gray-300 rounded-full mx-auto mb-3 sm:hidden shrink-0"></div>

            <!-- Modal Header -->
            <div class="flex items-center justify-between mb-5 shrink-0">
                <h3 class="text-lg sm:text-xl font-black text-[#1D3B47]">Edit karyawan</h3>
                <button type="button" @click="showEditModal = false" 
                        class="w-9 h-9 bg-[#CDE8E5] hover:bg-[#b8ded9] text-[#1D3B47] rounded-full flex items-center justify-center shrink-0 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Modal Form Body -->
            <form :action="editForm.update_url" method="POST" id="editEmployeeForm" class="space-y-4 overflow-y-auto flex-1 pr-1">
                @csrf
                @method('PUT')

                <!-- Nama Lengkap -->
                <div>
                    <label class="block text-xs sm:text-sm font-bold text-[#1D3B47] mb-1.5">Nama lengkap</label>
                    <input type="text" name="name" required x-model="editForm.name" 
                           class="w-full bg-[#edf5fa] border border-[#D6E5ED] rounded-2xl px-4 py-3 text-xs sm:text-sm font-semibold text-[#1D3B47] focus:outline-none focus:ring-2 focus:ring-[#4D869C]" 
                           placeholder="Nama karyawan">
                </div>

                <!-- 2 Kolom: Email atau nomor HP & Peran -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs sm:text-sm font-bold text-[#1D3B47] mb-1.5">Email atau nomor HP</label>
                        <input type="text" name="contact" required x-model="editForm.contact" 
                               class="w-full bg-[#edf5fa] border border-[#D6E5ED] rounded-2xl px-4 py-3 text-xs sm:text-sm font-semibold text-[#1D3B47] focus:outline-none focus:ring-2 focus:ring-[#4D869C]" 
                               placeholder="Email atau nomor HP">
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-bold text-[#1D3B47] mb-1.5">Peran</label>
                        <select name="role" required x-model="editForm.role" 
                                class="w-full bg-[#edf5fa] border border-[#D6E5ED] rounded-2xl px-4 py-3 text-xs sm:text-sm font-semibold text-[#1D3B47] focus:outline-none focus:ring-2 focus:ring-[#4D869C]">
                            <option value="kasir">Kasir</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>

                <!-- 2 Kolom: Cabang & PIN kasir -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs sm:text-sm font-bold text-[#1D3B47] mb-1.5">Cabang</label>
                        <select name="branch_id" x-model="editForm.branch_id" 
                                class="w-full bg-[#edf5fa] border border-[#D6E5ED] rounded-2xl px-4 py-3 text-xs sm:text-sm font-semibold text-[#1D3B47] focus:outline-none focus:ring-2 focus:ring-[#4D869C]">
                            <option value="">Semua cabang</option>
                            @foreach ($branches ?? [] as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-bold text-[#1D3B47] mb-1.5">PIN kasir (4â€“6 angka)</label>
                        <input type="password" name="pin" maxlength="6" x-model="editForm.pin" 
                               class="w-full bg-[#edf5fa] border border-[#D6E5ED] rounded-2xl px-4 py-3 text-xs sm:text-sm font-semibold text-[#1D3B47] focus:outline-none focus:ring-2 focus:ring-[#4D869C]" 
                               placeholder="â€¢â€¢â€¢â€¢â€¢â€¢">
                    </div>
                </div>

                <!-- Toggle Switch: Akun aktif -->
                <div class="pt-2 flex items-center justify-between">
                    <div>
                        <div class="font-bold text-[#1D3B47] text-xs sm:text-sm">Akun aktif</div>
                        <div class="text-[11px] sm:text-xs text-[#75929E] font-medium">Bisa login dan akses POS</div>
                    </div>
                    <input type="hidden" name="is_active" :value="editForm.is_active ? 1 : 0">
                    <button type="button" 
                            @click="if(!editForm.is_current) { editForm.is_active = !editForm.is_active }"
                            :class="editForm.is_active ? 'bg-[#4D869C]' : 'bg-[#CDE8E5]'"
                            :disabled="editForm.is_current"
                            class="w-12 h-6.5 border-2 border-[#4D869C] rounded-full p-0.5 transition-colors relative flex items-center shrink-0 cursor-pointer disabled:opacity-50">
                        <div :class="editForm.is_active ? 'translate-x-5.5' : 'translate-x-0'"
                             class="w-4.5 h-4.5 rounded-full bg-white shadow-xs transition-transform"></div>
                    </button>
                </div>
            </form>

            <!-- Modal Footer Actions (Nonaktifkan kiri, Batal + Simpan kanan) -->
            <div class="flex items-center justify-between pt-5 mt-4 border-t border-[#D6E5ED] shrink-0">
                <div>
                    <template x-if="!editForm.is_current && editForm.is_active">
                        <form :action="editForm.deactivate_url" method="POST" onsubmit="return confirm('Nonaktifkan karyawan ini?')">
                            @csrf
                            <button type="submit" class="text-[#d9383a] font-bold text-xs sm:text-sm underline decoration-[#d9383a] decoration-2 hover:opacity-80 transition-all cursor-pointer">
                                Nonaktifkan
                            </button>
                        </form>
                    </template>
                    <template x-if="!editForm.is_current && !editForm.is_active">
                        <form :action="editForm.activate_url" method="POST">
                            @csrf
                            <button type="submit" class="text-white font-bold text-xs sm:text-sm underline decoration-[#4D869C] decoration-2 hover:opacity-80 transition-all cursor-pointer">
                                Aktifkan
                            </button>
                        </form>
                    </template>
                    <template x-if="editForm.is_current">
                        <span class="text-xs text-gray-400 font-bold">Akun Anda</span>
                    </template>
                </div>

                <div class="flex items-center gap-4">
                    <button type="button" @click="showEditModal = false" class="text-[#75929E] font-bold hover:text-black text-xs sm:text-sm transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" form="editEmployeeForm" 
                            class="bg-[#4D869C] hover:bg-[#3D6B7D] text-white font-bold px-7 py-2.5 rounded-full text-xs sm:text-sm shadow-xs transition-all cursor-pointer">
                        Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- POP UP: TAMBAH KARYAWAN (Mobile / Direct)  -->
    <!-- ========================================== -->
    <div x-show="showCreateModal" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center bg-black/60 backdrop-blur-xs p-0 sm:p-4">
        <div @click.away="showCreateModal = false"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-t-[32px] sm:rounded-[32px] p-6 sm:p-8 max-w-lg w-full relative shadow-2xl overflow-hidden max-h-[92vh] flex flex-col justify-between">
            
            <!-- Mobile Top Handle Bar -->
            <div class="w-12 h-1.5 bg-gray-300 rounded-full mx-auto mb-3 sm:hidden shrink-0"></div>

            <div class="flex items-center justify-between mb-5 shrink-0">
                <h3 class="text-lg sm:text-xl font-black text-[#1D3B47]">Tambah karyawan</h3>
                <button type="button" @click="showCreateModal = false" 
                        class="w-9 h-9 bg-[#4D869C] hover:bg-[#3D6B7D] text-white rounded-full flex items-center justify-center shrink-0 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('users.store') }}" method="POST" id="createEmployeeModalForm" class="space-y-4 overflow-y-auto flex-1 pr-1">
                @csrf

                <div>
                    <label class="block text-xs sm:text-sm font-bold text-[#1D3B47] mb-1.5">Nama lengkap <span class="text-[#d9383a]">*</span></label>
                    <input type="text" name="name" required value="{{ old('name') }}" 
                           class="w-full bg-[#edf5fa] border border-[#D6E5ED] rounded-2xl px-4 py-3 text-xs sm:text-sm font-semibold text-[#1D3B47] focus:outline-none focus:ring-2 focus:ring-[#4D869C]" placeholder="Nama karyawan">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs sm:text-sm font-bold text-[#1D3B47] mb-1.5">Email atau nomor HP <span class="text-[#d9383a]">*</span></label>
                        <input type="text" name="contact" required value="{{ old('contact') }}" 
                               class="w-full bg-[#edf5fa] border border-[#D6E5ED] rounded-2xl px-4 py-3 text-xs sm:text-sm font-semibold text-[#1D3B47] focus:outline-none focus:ring-2 focus:ring-[#4D869C]" placeholder="Untuk login">
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-bold text-[#1D3B47] mb-1.5">Peran</label>
                        <select name="role" required class="w-full bg-[#edf5fa] border border-[#D6E5ED] rounded-2xl px-4 py-3 text-xs sm:text-sm font-semibold text-[#1D3B47] focus:outline-none focus:ring-2 focus:ring-[#4D869C]">
                            <option value="kasir">Kasir</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs sm:text-sm font-bold text-[#1D3B47] mb-1.5">Cabang</label>
                        <select name="branch_id" class="w-full bg-[#edf5fa] border border-[#D6E5ED] rounded-2xl px-4 py-3 text-xs sm:text-sm font-semibold text-[#1D3B47] focus:outline-none focus:ring-2 focus:ring-[#4D869C]">
                            <option value="">Semua cabang</option>
                            @foreach ($branches ?? [] as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-bold text-[#1D3B47] mb-1.5">PIN kasir (4â€“6 angka)</label>
                        <input type="password" name="pin" maxlength="6" 
                               class="w-full bg-[#edf5fa] border border-[#D6E5ED] rounded-2xl px-4 py-3 text-xs sm:text-sm font-semibold text-[#1D3B47] focus:outline-none focus:ring-2 focus:ring-[#4D869C]" placeholder="â€¢â€¢â€¢â€¢â€¢â€¢">
                    </div>
                </div>
            </form>

            <div class="flex items-center justify-end gap-4 pt-5 mt-4 border-t border-[#D6E5ED] shrink-0">
                <button type="button" @click="showCreateModal = false" class="text-[#75929E] font-bold hover:text-black text-xs sm:text-sm transition-colors cursor-pointer">
                    Batal
                </button>
                <button type="submit" form="createEmployeeModalForm" 
                        class="bg-[#4D869C] hover:bg-[#3D6B7D] text-white font-bold px-7 sm:px-8 py-3 rounded-full text-xs sm:text-sm shadow-sm transition-all cursor-pointer">
                    Simpan
                </button>
            </div>
        </div>
    </div>

</div>
@endsection





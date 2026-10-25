@extends('layouts.pos')

@section('topbar-search')
    <form method="GET" action="{{ route('customers.index') }}" class="w-full relative">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau telepon..." 
               class="w-full bg-[#e6e8eb] border-0 rounded-full px-5 py-2.5 text-sm text-[#1D3B47] placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#4D869C] transition-all">
    </form>
@endsection

@section('content')
<div class="space-y-5 md:space-y-6" x-data="customerManager()">

    @if(session('success'))
    <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs font-bold flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif

    <!-- Top Action Button -->
    <div class="flex justify-end">
        <button type="button" @click="openCreateModal()" 
                class="bg-[#4D869C] hover:bg-[#3D6B7D] text-white font-bold px-6 py-2.5 rounded-full text-xs sm:text-sm transition-all shadow-xs flex items-center gap-1.5">
            + Tambah pelanggan
        </button>
    </div>

    <!-- Customers Table Card Container -->
    <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="text-[11px] sm:text-xs font-black text-[#75929E] uppercase tracking-wider border-b border-[#D6E5ED] pb-3">
                        <th class="pb-3 font-black">NAMA</th>
                        <th class="hidden md:table-cell pb-3 font-black">TELEPON</th>
                        <th class="hidden md:table-cell pb-3 font-black">LEVEL</th>
                        <th class="pb-3 font-black">POIN</th>
                        <th class="pb-3 font-black">TOTAL BELANJA</th>
                        <th class="pb-3 text-right font-black">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-300/70">
                    @forelse($customers ?? [] as $customer)
                    <tr class="hover:bg-white/40 transition-colors">
                        <!-- Nama & Avatar -->
                        <td class="py-4 pr-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-9 h-9 rounded-full bg-[#4D869C] text-white font-black text-sm flex items-center justify-center shrink-0 shadow-xs">
                                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold text-[#1D3B47] text-sm sm:text-base leading-tight truncate">{{ $customer->name }}</div>
                                    <div class="md:hidden text-xs text-[#75929E] font-medium mt-0.5">{{ $customer->phone ?? '-' }}</div>
                                </div>
                            </div>
                        </td>

                        <!-- Telepon (Desktop) -->
                        <td class="hidden md:table-cell py-4 pr-3 text-xs sm:text-sm text-[#1D3B47] font-medium">
                            {{ $customer->phone ?? '-' }}
                        </td>

                        <!-- Level (Desktop) -->
                        <td class="hidden md:table-cell py-4 pr-3">
                            <span class="bg-gray-300/70 text-[#1D3B47] px-3.5 py-1 rounded-full text-xs font-semibold capitalize">
                                {{ $customer->level ?? 'Reguler' }}
                            </span>
                        </td>

                        <!-- Poin -->
                        <td class="py-4 pr-3 font-bold text-[#1D3B47] text-sm sm:text-base">
                            {{ $customer->points ?? 0 }}
                        </td>

                        <!-- Total Belanja -->
                        <td class="py-4 pr-3 font-bold text-[#1D3B47] text-sm sm:text-base whitespace-nowrap">
                            Rp {{ number_format($customer->total_spent ?? 0, 0, ',', '.') }}
                        </td>

                        <!-- Aksi (Edit & Hapus) -->
                        <td class="py-4 pl-3 text-right whitespace-nowrap">
                            <button type="button" 
                                    @click="openEditModal({
                                        id: {{ $customer->id }},
                                        name: '{{ addslashes($customer->name) }}',
                                        phone: '{{ addslashes($customer->phone ?? '') }}',
                                        email: '{{ addslashes($customer->email ?? '') }}',
                                        level: '{{ strtolower($customer->level ?? 'reguler') }}',
                                        points: {{ (int)$customer->points }},
                                        total_spent: {{ (float)$customer->total_spent }},
                                        address: '{{ addslashes($customer->address ?? '') }}',
                                        notes: '{{ addslashes($customer->notes ?? '') }}',
                                        update_url: '{{ route('customers.update', $customer) }}',
                                        delete_url: '{{ route('customers.destroy', $customer) }}'
                                    })" 
                                    class="text-[#1D3B47] hover:text-black font-bold text-xs sm:text-sm underline decoration-[#4D869C] decoration-2 transition-all">
                                Edit
                            </button>
                            <form method="POST" action="{{ route('customers.destroy', $customer) }}" class="inline ml-3" onsubmit="return confirm('Hapus pelanggan ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-[#1D3B47] hover:text-black font-bold text-xs sm:text-sm underline decoration-[#d9383a] decoration-2 transition-all">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-10 text-center text-[#75929E] text-sm font-medium">
                            Tidak ada data pelanggan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($customers) && $customers->hasPages())
        <div class="pt-4 border-t border-gray-300/70">
            {{ $customers->links() }}
        </div>
        @endif
    </div>

    <!-- POP UP: EDIT / TAMBAH PELANGGAN (Matching Edit pelanggan.png & Edit pelanggan Â· Mobile.png) -->
    <div x-show="showModal" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center bg-black/60 backdrop-blur-xs p-0 sm:p-4">
        <div @click.away="showModal = false"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-t-[32px] sm:rounded-[32px] p-6 sm:p-8 max-w-lg w-full relative shadow-2xl overflow-hidden max-h-[92vh] flex flex-col justify-between">
            
            <!-- Mobile Top Handle Bar -->
            <div class="w-12 h-1.5 bg-gray-300 rounded-full mx-auto mb-3 sm:hidden shrink-0"></div>

            <!-- Header -->
            <div class="flex items-center justify-between mb-5 shrink-0">
                <h3 class="text-lg sm:text-xl font-black text-[#1D3B47]" x-text="isEdit ? 'Edit pelanggan' : 'Tambah pelanggan'"></h3>
                <button type="button" @click="showModal = false" 
                        class="w-9 h-9 bg-[#4D869C] hover:bg-[#3D6B7D] text-white rounded-full flex items-center justify-center shrink-0 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Form Body -->
            <form :action="isEdit ? form.update_url : '{{ route('customers.store') }}'" method="POST" id="customerForm" class="space-y-4 overflow-y-auto flex-1 pr-1">
                @csrf
                <template x-if="isEdit">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <!-- Top 2 Summary Cards (Poin & Total Belanja) - When in Edit Mode -->
                <div class="grid grid-cols-2 gap-3" x-show="isEdit">
                    <div class="bg-[#1D3B47] rounded-2xl p-4 text-white">
                        <span class="text-xs text-gray-400 font-medium">Poin</span>
                        <div class="text-2xl font-black text-white mt-1" x-text="form.points"></div>
                    </div>
                    <div class="bg-[#1D3B47] rounded-2xl p-4 text-white">
                        <span class="text-xs text-gray-400 font-medium">Total belanja</span>
                        <div class="text-xl sm:text-2xl font-black text-white mt-1" x-text="formatRupiah(form.total_spent)"></div>
                    </div>
                </div>

                <!-- Nama Lengkap -->
                <div>
                    <label class="block text-xs sm:text-sm font-bold text-[#1D3B47] mb-1.5">Nama lengkap <span class="text-[#d9383a]">*</span></label>
                    <input type="text" name="name" x-model="form.name" required 
                           class="w-full bg-white border border-[#D6E5ED] rounded-2xl px-4 py-3 text-xs sm:text-sm font-semibold text-[#1D3B47] focus:outline-none focus:ring-2 focus:ring-[#4D869C]" placeholder="Budi Santoso">
                </div>

                <!-- 2 Kolom: Nomor Telepon & Level -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs sm:text-sm font-bold text-[#1D3B47] mb-1.5">Nomor telepon</label>
                        <input type="text" name="phone" x-model="form.phone" 
                               class="w-full bg-white border border-[#D6E5ED] rounded-2xl px-4 py-3 text-xs sm:text-sm font-semibold text-[#1D3B47] focus:outline-none focus:ring-2 focus:ring-[#4D869C]" placeholder="081234567890">
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-bold text-[#1D3B47] mb-1.5">Level</label>
                        <select name="level" x-model="form.level" 
                                class="w-full bg-white border border-[#D6E5ED] rounded-2xl px-4 py-3 text-xs sm:text-sm font-semibold text-[#1D3B47] focus:outline-none focus:ring-2 focus:ring-[#4D869C]">
                            <option value="regular">Reguler</option>
                            <option value="silver">Silver</option>
                            <option value="gold">Gold</option>
                            <option value="platinum">Platinum</option>
                        </select>
                    </div>
                </div>

                <!-- Email & Alamat (Opsional) -->
                <div>
                    <label class="block text-xs sm:text-sm font-bold text-[#1D3B47] mb-1.5">Email (Opsional)</label>
                    <input type="email" name="email" x-model="form.email" 
                           class="w-full bg-white border border-[#D6E5ED] rounded-2xl px-4 py-3 text-xs sm:text-sm font-semibold text-[#1D3B47] focus:outline-none focus:ring-2 focus:ring-[#4D869C]" placeholder="email@contoh.com">
                </div>
            </form>

            <!-- Bottom Actions -->
            <div class="pt-4 flex items-center justify-between border-t border-[#D6E5ED] mt-2 shrink-0">
                <template x-if="isEdit">
                    <form :action="form.delete_url" method="POST" onsubmit="return confirm('Hapus pelanggan ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-[#d9383a] font-bold text-xs sm:text-sm underline decoration-[#d9383a] decoration-2 hover:opacity-80 transition-all">
                            Hapus
                        </button>
                    </form>
                </template>
                <template x-if="!isEdit">
                    <div></div>
                </template>

                <div class="flex items-center gap-4">
                    <button type="button" @click="showModal = false" class="text-[#75929E] font-bold hover:text-black text-xs sm:text-sm transition-colors">
                        Batal
                    </button>
                    <button type="submit" form="customerForm" 
                            class="bg-[#4D869C] hover:bg-[#3D6B7D] text-white font-bold px-7 sm:px-8 py-3 rounded-full text-xs sm:text-sm shadow-sm transition-all">
                        Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    function customerManager() {
        return {
            showModal: false,
            isEdit: false,
            init() {
                this.$watch('showModal', val => {
                    if (window.Alpine && Alpine.store('modal')) {
                        Alpine.store('modal').open = Boolean(val);
                    }
                });
            },
            form: {
                id: null,
                name: '',
                phone: '',
                email: '',
                level: 'regular',
                points: 0,
                total_spent: 0,
                address: '',
                notes: '',
                update_url: '',
                delete_url: ''
            },
            openCreateModal() {
                this.isEdit = false;
                this.form = {
                    id: null,
                    name: '',
                    phone: '',
                    email: '',
                    level: 'regular',
                    points: 0,
                    total_spent: 0,
                    address: '',
                    notes: '',
                    update_url: '',
                    delete_url: ''
                };
                this.showModal = true;
            },
            openEditModal(cust) {
                this.isEdit = true;
                this.form = { ...cust };
                this.showModal = true;
            },
            formatRupiah(val) {
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(val || 0);
            }
        };
    }
</script>
@endsection





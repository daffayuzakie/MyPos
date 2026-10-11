@extends('layouts.pos')

@section('topbar-search')
    <x-topbar-search :action="route('purchases.index')" placeholder="Cari supplier..." />
@endsection

@section('content')
<div class="p-6 max-w-7xl mx-auto" x-data="{ 
    activeTab: 'supplier', 
    showSupplierModal: false,
    poItems: [{ product_id: '', quantity: 1 }]
}">
    
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Supplier & pembelian</h1>
            <p class="text-sm text-gray-500">Data supplier, purchase order, barang masuk, dan utang</p>
        </div>
        <div class="flex items-center gap-3">
            <button @click="showSupplierModal = true" class="pos-btn-secondary text-xs shadow-xs">
                Tambah supplier
            </button>
            <button @click="activeTab = 'po'; document.getElementById('po-form')?.scrollIntoView({ behavior: 'smooth' })" class="pos-btn-primary text-xs shadow-xs">
                Buat purchase order
            </button>
        </div>
    </div>

    @if (session('status'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-semibold flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <!-- 3 Summary Cards (Image 2) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <!-- Total Utang -->
        <div class="bg-white rounded-2xl border border-gray-200/80 p-5 shadow-xs">
            <span class="text-xs text-gray-500 font-medium">Total utang ke supplier</span>
            <div class="text-2xl font-black text-gray-900 mt-2">
                {{ $totalDebt > 0 ? 'Rp ' . number_format($totalDebt, 0, ',', '.') : 'Rp [—]' }}
            </div>
        </div>

        <!-- PO Berjalan -->
        <div class="bg-white rounded-2xl border border-gray-200/80 p-5 shadow-xs">
            <span class="text-xs text-gray-500 font-medium">PO berjalan</span>
            <div class="text-2xl font-black text-gray-900 mt-2">
                {{ $activePOCount > 0 ? $activePOCount : '[—]' }}
            </div>
        </div>

        <!-- Jatuh Tempo Minggu Ini -->
        <div class="bg-white rounded-2xl border border-gray-200/80 p-5 shadow-xs">
            <span class="text-xs text-gray-500 font-medium">Jatuh tempo minggu ini</span>
            <div class="text-2xl font-black text-gray-900 mt-2">
                {{ $dueThisWeekCount > 0 ? $dueThisWeekCount : '[—]' }}
            </div>
        </div>
    </div>

    <!-- Main Workspace (2 Columns: Left Tabs Table + Right PO Form) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left 2 Cols: Tabs & Tables -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-200/80 p-5 shadow-xs flex flex-col justify-between">
            <div>
                <!-- Horizontal Tabs: Supplier, Purchase Order, Utang -->
                <div class="flex items-center gap-1.5 p-1 bg-gray-100/80 rounded-xl w-fit mb-4">
                    <button @click="activeTab = 'supplier'" 
                            :class="activeTab === 'supplier' ? 'bg-white text-gray-900 font-bold shadow-xs' : 'text-gray-600 hover:text-gray-900 font-medium'"
                            class="px-4 py-1.5 rounded-lg text-xs transition-all">
                        Supplier
                    </button>
                    <button @click="activeTab = 'po'" 
                            :class="activeTab === 'po' ? 'bg-white text-gray-900 font-bold shadow-xs' : 'text-gray-600 hover:text-gray-900 font-medium'"
                            class="px-4 py-1.5 rounded-lg text-xs transition-all">
                        Purchase order
                    </button>
                    <button @click="activeTab = 'debt'" 
                            :class="activeTab === 'debt' ? 'bg-white text-gray-900 font-bold shadow-xs' : 'text-gray-600 hover:text-gray-900 font-medium'"
                            class="px-4 py-1.5 rounded-lg text-xs transition-all">
                        Utang
                    </button>
                </div>

                <!-- TAB 1: SUPPLIER TABLE -->
                <div x-show="activeTab === 'supplier'" class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-gray-200 text-gray-400 font-bold uppercase tracking-wider text-[10px]">
                                <th class="py-3 px-3">SUPPLIER</th>
                                <th class="py-3 px-3">KONTAK</th>
                                <th class="py-3 px-3">BARANG DIPASOK</th>
                                <th class="py-3 px-3 text-right">UTANG</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700 font-medium">
                            @forelse($suppliers as $sup)
                            <tr class="hover:bg-gray-50/70 transition-colors">
                                <td class="py-3 px-3 font-bold text-gray-900">{{ $sup->name }}</td>
                                <td class="py-3 px-3 text-gray-500">{{ $sup->phone ?? $sup->email ?? '-' }}</td>
                                <td class="py-3 px-3 text-gray-500">{{ $sup->purchases_count ?? 0 }} PO</td>
                                <td class="py-3 px-3 text-right font-bold text-gray-900">
                                    {{ $sup->total_debt > 0 ? 'Rp ' . number_format($sup->total_debt, 0, ',', '.') : 'Rp [—]' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-gray-400">Belum ada data supplier. Klik "Tambah supplier" untuk menambahkan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- TAB 2: PURCHASE ORDER TABLE -->
                <div x-show="activeTab === 'po'" x-cloak class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-gray-200 text-gray-400 font-bold uppercase tracking-wider text-[10px]">
                                <th class="py-3 px-3">NO. PO</th>
                                <th class="py-3 px-3">SUPPLIER</th>
                                <th class="py-3 px-3">TANGGAL</th>
                                <th class="py-3 px-3">TOTAL</th>
                                <th class="py-3 px-3">STATUS</th>
                                <th class="py-3 px-3 text-right">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700 font-medium">
                            @forelse($purchases as $p)
                            <tr class="hover:bg-gray-50/70 transition-colors">
                                <td class="py-3 px-3 font-bold text-gray-900">{{ $p->invoice_number }}</td>
                                <td class="py-3 px-3 text-gray-600">{{ $p->supplier->name ?? '-' }}</td>
                                <td class="py-3 px-3 text-gray-500">{{ $p->purchase_date ? $p->purchase_date->format('d/m/Y') : '-' }}</td>
                                <td class="py-3 px-3 font-bold text-gray-900">Rp {{ number_format($p->total_amount, 0, ',', '.') }}</td>
                                <td class="py-3 px-3">
                                    @if($p->status === 'completed')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Selesai</span>
                                    @elseif($p->status === 'draft')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Draft / PO Berjalan</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-600">{{ ucfirst($p->status) }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-right">
                                    @if($p->status === 'draft')
                                        <form method="POST" action="{{ route('purchases.complete', $p) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="text-xs font-bold text-teal-700 hover:text-teal-900" onclick="return confirm('Selesaikan PO ini dan tambahkan ke stok masuk?')">
                                                Selesaikan &amp; Masuk Stok
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-gray-400">Belum ada purchase order dibuat.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- TAB 3: UTANG TABLE -->
                <div x-show="activeTab === 'debt'" x-cloak class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-gray-200 text-gray-400 font-bold uppercase tracking-wider text-[10px]">
                                <th class="py-3 px-3">SUPPLIER</th>
                                <th class="py-3 px-3">TOTAL UTANG</th>
                                <th class="py-3 px-3">JATUH TEMPO</th>
                                <th class="py-3 px-3">STATUS</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700 font-medium">
                            @php $hasDebt = false; @endphp
                            @foreach($suppliers as $sup)
                                @if($sup->total_debt > 0)
                                @php $hasDebt = true; @endphp
                                <tr class="hover:bg-gray-50/70 transition-colors">
                                    <td class="py-3 px-3 font-bold text-gray-900">{{ $sup->name }}</td>
                                    <td class="py-3 px-3 font-bold text-rose-600">Rp {{ number_format($sup->total_debt, 0, ',', '.') }}</td>
                                    <td class="py-3 px-3 text-gray-500">Minggu ini</td>
                                    <td class="py-3 px-3"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">Belum Lunas</span></td>
                                </tr>
                                @endif
                            @endforeach
                            @if(!$hasDebt)
                            <tr>
                                <td colspan="4" class="py-8 text-center text-gray-400">Tidak ada utang supplier yang tercatat.</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination if needed -->
            @if(isset($purchases) && $purchases->hasPages())
                <div class="pt-4 border-t border-gray-100">
                    {{ $purchases->links() }}
                </div>
            @endif
        </div>

        <!-- Right 1 Col: "Purchase order baru" Card (Image 2) -->
        <div class="bg-white rounded-2xl border border-gray-200/80 p-5 shadow-xs">
            <h3 class="text-sm font-extrabold text-gray-900 mb-4">Purchase order baru</h3>
            
            <form id="po-form" method="POST" action="{{ route('purchases.store') }}" class="space-y-4">
                @csrf

                <!-- Supplier Dropdown -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Supplier</label>
                    <select name="supplier_id" required class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl text-xs text-gray-900 focus:ring-1 focus:ring-teal-700 focus:border-teal-700">
                        <option value="">Pilih supplier</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Tanggal Pengiriman -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Tanggal pengiriman</label>
                    <input type="date" name="purchase_date" value="{{ date('Y-m-d') }}" required
                           class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl text-xs text-gray-900 focus:ring-1 focus:ring-teal-700 focus:border-teal-700">
                </div>

                <!-- Item Pesanan (Dynamic Rows) -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Item pesanan</label>
                    <div class="space-y-2">
                        <template x-for="(item, idx) in poItems" :key="idx">
                            <div class="flex items-center gap-2">
                                <select :name="'items[' + idx + '][product_id]'" class="flex-1 px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs text-gray-900 focus:ring-1 focus:ring-teal-700">
                                    <option value="">Pilih Produk</option>
                                    @foreach($products as $prod)
                                        <option value="{{ $prod->id }}">{{ $prod->name }}</option>
                                    @endforeach
                                </select>
                                <input type="number" :name="'items[' + idx + '][quantity]'" x-model.number="item.quantity" min="1" placeholder="Jumlah" class="w-20 px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs text-gray-900 focus:ring-1 focus:ring-teal-700 text-center">
                                <button type="button" @click="poItems.length > 1 ? poItems.splice(idx, 1) : null" class="text-gray-400 hover:text-rose-500 font-bold px-1">&times;</button>
                            </div>
                        </template>
                    </div>

                    <button type="button" @click="poItems.push({ product_id: '', quantity: 1 })" 
                            class="w-full mt-2.5 py-2 border border-dashed border-gray-300 hover:border-gray-400 rounded-xl text-xs font-bold text-gray-600 hover:bg-gray-50 transition-colors">
                        Tambah item
                    </button>
                </div>

                <!-- Catatan -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Catatan</label>
                    <textarea name="notes" rows="3" placeholder="Catatan untuk supplier" class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl text-xs text-gray-900 focus:ring-1 focus:ring-teal-700 focus:border-teal-700"></textarea>
                </div>

                <!-- Buttons: Batal | Simpan PO -->
                <div class="flex gap-2 pt-2">
                    <button type="reset" @click="poItems = [{ product_id: '', quantity: 1 }]" class="w-1/3 py-2.5 border border-gray-300 rounded-xl text-xs font-bold text-gray-700 hover:bg-gray-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 py-2.5 bg-[#0d6e53] hover:bg-[#0a5742] text-white rounded-xl text-xs font-bold shadow-xs transition-colors">
                        Simpan PO
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Tambah Supplier Baru -->
    <div x-show="showSupplierModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4">
        <div @click.away="showSupplierModal = false" class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden p-6 space-y-4">
            <div class="flex justify-between items-center border-b pb-3">
                <h3 class="text-sm font-extrabold text-gray-900">Tambah Supplier Baru</h3>
                <button @click="showSupplierModal = false" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
            </div>

            <form method="POST" action="{{ route('suppliers.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Nama Supplier</label>
                    <input type="text" name="name" required class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs text-gray-900 focus:ring-teal-700" placeholder="Contoh: PT Sumber Pangan">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Nomor Telepon / WhatsApp</label>
                    <input type="text" name="phone" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs text-gray-900 focus:ring-teal-700" placeholder="08xxxxxxxx">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs text-gray-900 focus:ring-teal-700" placeholder="supplier@example.com">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Alamat</label>
                    <textarea name="address" rows="2" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs text-gray-900 focus:ring-teal-700" placeholder="Alamat gudang / kantor supplier"></textarea>
                </div>
                <div class="flex gap-2 pt-2">
                    <button type="button" @click="showSupplierModal = false" class="w-1/3 py-2 border rounded-xl text-xs font-bold text-gray-700 hover:bg-gray-50">Batal</button>
                    <button type="submit" class="flex-1 py-2 bg-[#0d6e53] hover:bg-[#0a5742] text-white rounded-xl text-xs font-bold">Simpan Supplier</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
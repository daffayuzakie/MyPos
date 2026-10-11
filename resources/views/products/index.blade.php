@extends('layouts.pos')

@section('topbar-search')
    <x-topbar-search :action="route('products.index')" placeholder="Cari produk / SKU..." />
@endsection

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="flex flex-wrap justify-between items-start gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Produk & Stok</h1>
            <p class="text-gray-500 text-sm">Kelola katalog produk, harga, dan inventaris barang</p>
        </div>
        <div class="flex gap-2">
            <button type="button" x-data="" x-on:click.prevent="$dispatch('open-modal', 'create-product')"
                    class="pos-btn-primary text-xs flex items-center gap-1.5 bg-[#0d6e53] hover:bg-[#095741]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Buat Produk
            </button>
            <a href="{{ route('stock.index') }}" class="pos-btn-secondary text-xs flex items-center gap-1.5">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Mutasi Stok
            </a>
            <a href="{{ route('pos.index') }}" class="pos-btn-secondary text-xs flex items-center gap-1.5">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Buka Kasir (POS)
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-semibold flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif

    <!-- Full Width Products Table -->
    <div class="pos-card p-0 overflow-hidden">
        <!-- Filters & Search Toolbar -->
        <div class="p-4 border-b border-gray-200 flex flex-wrap gap-4 justify-between items-center bg-gray-50">
            <div class="flex space-x-2">
                <a href="?filter=all{{ $search ? '&search=' . urlencode($search) : '' }}"
                   class="px-4 py-2 rounded-full text-xs font-bold transition-colors {{ ($filter ?? 'all') === 'all' ? 'bg-gray-900 text-white' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' }}">
                    Semua
                </a>
                <a href="?filter=low{{ $search ? '&search=' . urlencode($search) : '' }}"
                   class="px-4 py-2 rounded-full text-xs font-bold transition-colors {{ ($filter ?? 'all') === 'low' ? 'bg-amber-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' }}">
                    Stok Menipis (≤5)
                </a>
                <a href="?filter=empty{{ $search ? '&search=' . urlencode($search) : '' }}"
                   class="px-4 py-2 rounded-full text-xs font-bold transition-colors {{ ($filter ?? 'all') === 'empty' ? 'bg-rose-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' }}">
                    Habis (0)
                </a>
            </div>
        </div>

        <!-- Products Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-600">
                <thead class="text-[11px] text-gray-500 uppercase bg-gray-50 border-b border-gray-200 font-bold">
                    <tr>
                        <th scope="col" class="px-5 py-3">Produk</th>
                        <th scope="col" class="px-5 py-3">Kategori</th>
                        <th scope="col" class="px-5 py-3">SKU</th>
                        <th scope="col" class="px-5 py-3">Harga Jual</th>
                        <th scope="col" class="px-5 py-3">Stok Saat Ini</th>
                        <th scope="col" class="px-5 py-3 text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($products ?? [] as $product)
                    <tr class="bg-white hover:bg-gray-50/80 transition-colors">
                        <td class="px-5 py-3.5 font-bold text-gray-900 flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-teal-50 text-teal-800 flex items-center justify-center font-black text-sm shrink-0">
                                {{ substr($product->name, 0, 1) }}
                            </div>
                            <div>
                                <div class="font-bold text-gray-900">{{ $product->name }}</div>
                                <div class="text-[10px] text-gray-400 font-normal">{{ $product->unit->name ?? 'Pcs' }}</div>
                            </div>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-100 text-gray-700">
                                {{ $product->category->name ?? 'Umum' }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 font-mono text-[11px] text-gray-500">{{ $product->sku ?? '-' }}</td>
                        <td class="px-5 py-3.5 font-extrabold text-gray-900">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-black {{ $product->stock <= 0 ? 'bg-rose-50 text-rose-700' : ($product->stock <= 5 ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-800') }}">
                                {{ $product->stock }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            @if($product->is_active)
                                <span class="inline-block w-2 h-2 rounded-full bg-emerald-500" title="Aktif"></span>
                            @else
                                <span class="inline-block w-2 h-2 rounded-full bg-gray-300" title="Nonaktif"></span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-12 text-gray-400 font-medium">
                            Belum ada produk yang cocok dengan filter ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($products) && $products->hasPages())
        <div class="p-4 border-t border-gray-200">
            {{ $products->links() }}
        </div>
        @endif
    </div>

    <!-- Modal: Buat Produk -->
    <x-modal name="create-product" maxWidth="lg" :show="$errors->any()" focusable>
        <form action="{{ route('products.store') }}" method="POST" class="text-xs">
            @csrf
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <h3 class="text-sm font-extrabold text-gray-900">Tambah Produk Baru</h3>
                <button type="button" x-on:click="$dispatch('close')" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            @if($errors->any())
            <div class="mx-6 mt-4 p-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs space-y-1">
                <span class="font-bold">Gagal menyimpan produk:</span>
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="p-6 space-y-4">
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Nama Produk <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" required value="{{ old('name') }}" class="w-full px-3 py-2 border border-gray-300 rounded-xl text-xs focus:ring-teal-700 focus:border-teal-700" placeholder="contoh: Cappuccino Hangat">
                </div>

                <div class="grid grid-cols-2 gap-2.5">
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Kategori</label>
                        <select name="category_id" class="w-full px-3 py-2 border border-gray-300 rounded-xl text-xs focus:ring-teal-700 focus:border-teal-700 bg-white">
                            <option value="">(Umum)</option>
                            @foreach($categories ?? [] as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Satuan</label>
                        <select name="unit_id" class="w-full px-3 py-2 border border-gray-300 rounded-xl text-xs focus:ring-teal-700 focus:border-teal-700 bg-white">
                            <option value="">(Pcs)</option>
                            @foreach($units ?? [] as $unit)
                            <option value="{{ $unit->id }}" @selected(old('unit_id') == $unit->id)>{{ $unit->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2.5">
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Harga Jual (Rp) <span class="text-rose-500">*</span></label>
                        <input type="number" name="selling_price" required min="0" value="{{ old('selling_price') }}" class="w-full px-3 py-2 border border-gray-300 rounded-xl text-xs font-bold focus:ring-teal-700 focus:border-teal-700" placeholder="20000">
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Harga Modal (Rp)</label>
                        <input type="number" name="purchase_price" min="0" value="{{ old('purchase_price') }}" class="w-full px-3 py-2 border border-gray-300 rounded-xl text-xs focus:ring-teal-700 focus:border-teal-700" placeholder="Otomatis jika kosong">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2.5">
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Stok Awal <span class="text-rose-500">*</span></label>
                        <input type="number" name="stock" value="{{ old('stock', 50) }}" min="0" required class="w-full px-3 py-2 border border-gray-300 rounded-xl text-xs font-bold focus:ring-teal-700 focus:border-teal-700">
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">SKU (Kode)</label>
                        <input type="text" name="sku" value="{{ old('sku') }}" class="w-full px-3 py-2 border border-gray-300 rounded-xl text-xs font-mono uppercase focus:ring-teal-700 focus:border-teal-700" placeholder="Otomatis jika kosong">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-gray-700 mb-1">Barcode (Opsional)</label>
                    <input type="text" name="barcode" value="{{ old('barcode') }}" class="w-full px-3 py-2 border border-gray-300 rounded-xl text-xs focus:ring-teal-700 focus:border-teal-700" placeholder="Scan atau ketik barcode...">
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-200 flex gap-2">
                <button type="button" x-on:click="$dispatch('close')" class="w-1/3 py-2.5 border border-gray-200 rounded-xl text-gray-700 font-bold text-xs hover:bg-gray-50">Batal</button>
                <button type="submit" class="flex-1 py-2.5 bg-[#0d6e53] hover:bg-[#095741] text-white rounded-xl text-xs font-extrabold shadow-sm transition-colors">Simpan Produk</button>
            </div>
        </form>
    </x-modal>
</div>
@endsection

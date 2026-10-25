@extends('layouts.pos')

@section('topbar-search')
    <form method="GET" action="{{ route('products.index') }}" class="w-full relative">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari produk / SKU..." 
               class="w-full bg-[#e6e8eb] border-0 rounded-full px-5 py-2.5 text-sm text-[#1D3B47] placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#4D869C] transition-all">
    </form>
@endsection

@section('content')
@php
    $avatarColors = [
        'bg-[#CDE8E5] text-[#1D3B47]',
        'bg-[#CDE8E5] text-[#1D3B47]',
        'bg-[#CDE8E5] text-[#1D3B47]',
        'bg-[#CDE8E5] text-[#1D3B47]',
        'bg-[#d9383a] text-white',
        'bg-[#d9383a] text-white',
        'bg-[#d9383a] text-white',
        'bg-[#d9383a] text-white',
    ];
@endphp

<div class="space-y-5 md:space-y-6" x-data="productManager()">

    @if(session('success'))
    <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs font-bold flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-xs font-bold flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
        {{ session('error') }}
    </div>
    @endif

    <!-- Controls Row: Filter Tabs on Left, Actions on Right (Matching Produk & Stok.png) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <!-- Filter Tabs -->
        <div class="flex flex-wrap items-center gap-2">
            <a href="?filter=all{{ request('search') ? '&search=' . urlencode(request('search')) : '' }}" 
               class="{{ ($filter ?? 'all') === 'all' ? 'bg-[#4D869C] text-white font-bold shadow-xs' : 'bg-white text-[#1D3B47] hover:text-black font-semibold' }} px-5 py-2 rounded-full text-xs sm:text-sm transition-all">
                Semua
            </a>
            <a href="?filter=low{{ request('search') ? '&search=' . urlencode(request('search')) : '' }}" 
               class="{{ ($filter ?? 'all') === 'low' ? 'bg-[#4D869C] text-white font-bold shadow-xs' : 'bg-white text-[#1D3B47] hover:text-black font-semibold' }} px-5 py-2 rounded-full text-xs sm:text-sm transition-all">
                Stok menipis
            </a>
            <a href="?filter=empty{{ request('search') ? '&search=' . urlencode(request('search')) : '' }}" 
               class="{{ ($filter ?? 'all') === 'empty' ? 'bg-[#4D869C] text-white font-bold shadow-xs' : 'bg-white text-[#1D3B47] hover:text-black font-semibold' }} px-5 py-2 rounded-full text-xs sm:text-sm transition-all">
                Habis
            </a>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap items-center gap-2">
            @can('categories view')
            <button type="button" @click="showCategoryModal = true"
                    data-testid="manage-categories"
                    class="bg-white hover:bg-gray-200 text-[#1D3B47] font-semibold px-5 py-2 rounded-full text-xs sm:text-sm transition-all">
                Kategori
            </button>
            @endcan

            <a href="{{ route('stock.index') }}" 
               class="bg-white hover:bg-gray-200 text-[#1D3B47] font-semibold px-5 py-2 rounded-full text-xs sm:text-sm transition-all">
                Mutasi stok
            </a>

            <button type="button" @click="showCreateModal = true"
                    class="bg-[#1D3B47] hover:bg-[#1a1f2c] text-white font-bold px-5 py-2 rounded-full text-xs sm:text-sm transition-all shadow-xs flex items-center gap-1.5">
                + Buat produk
            </button>
        </div>
    </div>

    <!-- Products Table Card Container -->
    <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="text-[11px] sm:text-xs font-black text-[#75929E] uppercase tracking-wider border-b border-[#D6E5ED] pb-3">
                        <th class="pb-3 font-black">PRODUK</th>
                        <th class="hidden md:table-cell pb-3 font-black">KATEGORI</th>
                        <th class="hidden md:table-cell pb-3 font-black">SKU</th>
                        <th class="pb-3 font-black">HARGA</th>
                        <th class="pb-3 text-center font-black">STOK</th>
                        <th class="pb-3 text-right font-black"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-300/70">
                    @forelse($products ?? [] as $product)
                    @php
                        $avatarClass = $avatarColors[$loop->index % count($avatarColors)];
                    @endphp
                    <tr class="hover:bg-white/40 transition-colors">
                        <!-- Produk (Avatar/Foto + Nama) -->
                        <td class="py-3.5 pr-3">
                            <div class="flex items-center gap-3 min-w-0">
                                @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-10 h-10 rounded-2xl object-cover shrink-0 shadow-xs border border-[#D6E5ED]">
                                @else
                                    <div class="w-10 h-10 rounded-2xl flex items-center justify-center font-black text-sm shrink-0 shadow-xs {{ $avatarClass }}">
                                        {{ strtoupper(substr($product->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <div class="font-bold text-[#1D3B47] text-sm sm:text-base truncate leading-tight">{{ $product->name }}</div>
                                    <div class="md:hidden text-[11px] text-[#75929E] font-medium mt-0.5">{{ $product->category->name ?? 'Umum' }}</div>
                                </div>
                            </div>
                        </td>

                        <!-- Kategori (Desktop) -->
                        <td class="hidden md:table-cell py-3.5 pr-3">
                            <span class="bg-gray-300/70 text-[#1D3B47] px-3.5 py-1 rounded-full text-xs font-semibold">
                                {{ $product->category->name ?? 'Umum' }}
                            </span>
                        </td>

                        <!-- SKU (Desktop) -->
                        <td class="hidden md:table-cell py-3.5 pr-3 font-mono text-xs font-semibold text-[#75929E] uppercase">
                            {{ $product->sku ?? '-' }}
                        </td>

                        <!-- Harga -->
                        <td class="py-3.5 pr-3 font-bold text-[#1D3B47] text-sm sm:text-base whitespace-nowrap">
                            Rp {{ number_format($product->selling_price, 0, ',', '.') }}
                        </td>

                        <!-- Stok Pill Badge -->
                        <td class="py-3.5 px-2 text-center whitespace-nowrap">
                            <span class="inline-block px-3.5 py-0.5 rounded-full text-xs sm:text-sm font-black {{ $product->stock <= 5 ? 'bg-[#d9383a] text-white' : 'bg-[#CDE8E5] text-[#1D3B47]' }}">
                                {{ $product->stock }}
                            </span>
                        </td>

                        <!-- Status Dot Ring & Edit Action (Matching Edit produk.png) -->
                        <td class="py-3.5 pl-3 text-right whitespace-nowrap">
                            <div class="inline-flex items-center gap-2.5">
                                @if($product->is_active && $product->stock > 0)
                                    <span class="inline-block w-3 h-3 rounded-full border-2 border-[#4D869C] bg-[#4D869C]" title="Aktif"></span>
                                @else
                                    <span class="inline-block w-3 h-3 rounded-full border-2 border-[#4D869C] bg-[#d9383a]" title="Perhatian / Habis"></span>
                                @endif
                                
                                <button type="button" 
                                        @click="openEditModal({
                                            id: {{ $product->id }},
                                            name: '{{ addslashes($product->name) }}',
                                            category_id: '{{ $product->category_id }}',
                                            unit_id: '{{ $product->unit_id }}',
                                            sku: '{{ addslashes($product->sku ?? '') }}',
                                            barcode: '{{ addslashes($product->barcode ?? '') }}',
                                            selling_price: {{ (float)$product->selling_price }},
                                            purchase_price: {{ (float)$product->purchase_price }},
                                            stock: {{ (int)$product->stock }},
                                            is_active: {{ $product->is_active ? 'true' : 'false' }},
                                            image_url: '{{ $product->image ? asset('storage/' . $product->image) : '' }}',
                                            update_url: '{{ route('products.update', $product) }}',
                                            delete_url: '{{ route('products.destroy', $product) }}'
                                        })"
                                        class="text-[#1D3B47] hover:text-black font-bold text-xs sm:text-sm underline decoration-[#4D869C] decoration-2 transition-all">
                                    Edit
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-10 text-center text-[#75929E] text-sm font-medium">
                            Belum ada produk yang cocok dengan filter ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($products) && $products->hasPages())
        <div class="pt-4 border-t border-gray-300/70">
            {{ $products->links() }}
        </div>
        @endif
    </div>

    <!-- POP UP: EDIT PRODUK (Matching Edit produk.png & Edit produk Â· Mobile.png) -->
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

            <!-- Header -->
            <div class="flex items-center justify-between mb-5 shrink-0">
                <h3 class="text-lg sm:text-xl font-black text-[#1D3B47]">Edit produk</h3>
                <!-- Close Button Dark Circle -->
                <button type="button" @click="showEditModal = false" 
                        class="w-9 h-9 bg-[#4D869C] hover:bg-[#3D6B7D] text-white rounded-full flex items-center justify-center shrink-0 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Edit Form -->
            <form :action="editForm.update_url" method="POST" enctype="multipart/form-data" id="editProductForm" class="space-y-4 overflow-y-auto flex-1 pr-1">
                @csrf
                @method('PUT')
                <input type="hidden" name="unit_id" :value="editForm.unit_id">

                <!-- Foto Produk -->
                <div>
                    <label class="block text-xs sm:text-sm font-bold text-[#1D3B47] mb-1.5">Foto produk</label>
                    <div class="flex items-center gap-3">
                        <template x-if="editImagePreview || editForm.image_url">
                            <img :src="editImagePreview || editForm.image_url" class="w-14 h-14 rounded-2xl object-cover border border-[#D6E5ED] shrink-0 shadow-xs">
                        </template>
                        <template x-if="!editImagePreview && !editForm.image_url">
                            <div class="w-14 h-14 rounded-2xl bg-gray-200/80 border border-dashed border-gray-400 flex items-center justify-center text-gray-400 shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                        </template>
                        <label class="cursor-pointer bg-white/80 hover:bg-white text-[#1D3B47] border border-[#D6E5ED] rounded-2xl px-4 py-2.5 text-xs font-bold transition-all shadow-xs flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#75929E]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>Ubah Foto</span>
                            <input type="file" name="image" accept="image/*" class="hidden" 
                                   @change="const file = $event.target.files[0]; if (file) { const reader = new FileReader(); reader.onload = (e) => editImagePreview = e.target.result; reader.readAsDataURL(file); }">
                        </label>
                    </div>
                </div>

                <!-- Nama Produk -->
                <div>
                    <label class="block text-xs sm:text-sm font-bold text-[#1D3B47] mb-1.5">Nama produk</label>
                    <input type="text" name="name" x-model="editForm.name" required 
                           class="w-full bg-white border border-[#D6E5ED] rounded-2xl px-4 py-3 text-xs sm:text-sm font-semibold text-[#1D3B47] focus:outline-none focus:ring-2 focus:ring-[#4D869C]">
                </div>

                <!-- 2 Kolom: Kategori & SKU -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs sm:text-sm font-bold text-[#1D3B47] mb-1.5">Kategori</label>
                        <select name="category_id" x-model="editForm.category_id" required 
                                class="w-full bg-white border border-[#D6E5ED] rounded-2xl px-4 py-3 text-xs sm:text-sm font-semibold text-[#1D3B47] focus:outline-none focus:ring-2 focus:ring-[#4D869C]">
                            <option value="">Pilih Kategori</option>
                            @foreach($categories ?? [] as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-bold text-[#1D3B47] mb-1.5">SKU</label>
                        <input type="text" name="sku" x-model="editForm.sku" required 
                               class="w-full bg-white border border-[#D6E5ED] rounded-2xl px-4 py-3 text-xs sm:text-sm font-semibold text-[#1D3B47] font-mono uppercase focus:outline-none focus:ring-2 focus:ring-[#4D869C]">
                    </div>
                </div>

                <!-- 2 Kolom: Harga Jual & Harga Modal -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs sm:text-sm font-bold text-[#1D3B47] mb-1.5">Harga jual</label>
                        <input type="number" name="selling_price" x-model="editForm.selling_price" required min="0" 
                               class="w-full bg-white border border-[#D6E5ED] rounded-2xl px-4 py-3 text-xs sm:text-sm font-semibold text-[#1D3B47] focus:outline-none focus:ring-2 focus:ring-[#4D869C]">
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-bold text-[#1D3B47] mb-1.5">Harga modal</label>
                        <input type="number" name="purchase_price" x-model="editForm.purchase_price" required min="0" 
                               class="w-full bg-white border border-[#D6E5ED] rounded-2xl px-4 py-3 text-xs sm:text-sm font-semibold text-[#1D3B47] focus:outline-none focus:ring-2 focus:ring-[#4D869C]">
                    </div>
                </div>

                <!-- Stok Saat Ini -->
                <div>
                    <label class="block text-xs sm:text-sm font-bold text-[#1D3B47] mb-1.5">Stok saat ini</label>
                    <input type="number" name="stock" x-model="editForm.stock" min="0" 
                           class="w-full bg-white border border-[#D6E5ED] rounded-2xl px-4 py-3 text-xs sm:text-sm font-semibold text-[#1D3B47] focus:outline-none focus:ring-2 focus:ring-[#4D869C]">
                </div>

                <!-- Toggle Switch: Tampil di kasir -->
                <div class="flex items-center justify-between py-2">
                    <span class="text-xs sm:text-sm font-bold text-[#1D3B47]">Tampil di kasir</span>
                    <button type="button" @click="editForm.is_active = !editForm.is_active" 
                            class="w-12 h-6.5 flex items-center rounded-full p-1 transition-colors duration-200 ease-in-out border-2 border-[#4D869C]"
                            :class="editForm.is_active ? 'bg-[#4D869C]' : 'bg-gray-300'">
                        <div class="w-4.5 h-4.5 rounded-full bg-[#4D869C] transition-transform duration-200 ease-in-out"
                             :class="editForm.is_active ? 'translate-x-5' : 'translate-x-0'"></div>
                    </button>
                    <input type="hidden" name="is_active" :value="editForm.is_active ? '1' : '0'">
                </div>
            </form>

            <!-- Bottom Actions: Hapus (kiri) | Batal + Simpan (kanan) -->
            <div class="pt-4 flex items-center justify-between border-t border-[#D6E5ED] mt-2 shrink-0">
                <form :action="editForm.delete_url" method="POST" onsubmit="return confirm('Yakin ingin menghapus produk ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-[#d9383a] font-bold text-xs sm:text-sm underline decoration-[#d9383a] decoration-2 hover:opacity-80 transition-all">
                        Hapus
                    </button>
                </form>

                <div class="flex items-center gap-4">
                    <button type="button" @click="showEditModal = false" class="text-[#75929E] font-bold hover:text-black text-xs sm:text-sm transition-colors">
                        Batal
                    </button>
                    <button type="submit" form="editProductForm" 
                            class="bg-[#4D869C] hover:bg-[#3D6B7D] text-white font-bold px-7 sm:px-8 py-3 rounded-full text-xs sm:text-sm shadow-sm transition-all">
                        Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- POP UP: BUAT PRODUK BARU (Styling konsisten #e9ebed rounded-[32px]) -->
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
                <h3 class="text-lg sm:text-xl font-black text-[#1D3B47]">Tambah produk</h3>
                <button type="button" @click="showCreateModal = false" 
                        class="w-9 h-9 bg-[#4D869C] hover:bg-[#3D6B7D] text-white rounded-full flex items-center justify-center shrink-0 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" id="createProductForm" class="space-y-4 overflow-y-auto flex-1 pr-1">
                @csrf

                <!-- Foto Produk -->
                <div>
                    <label class="block text-xs sm:text-sm font-bold text-[#1D3B47] mb-1.5">Foto produk (opsional)</label>
                    <div class="flex items-center gap-3">
                        <template x-if="createImagePreview">
                            <img :src="createImagePreview" class="w-14 h-14 rounded-2xl object-cover border border-[#D6E5ED] shrink-0 shadow-xs">
                        </template>
                        <template x-if="!createImagePreview">
                            <div class="w-14 h-14 rounded-2xl bg-gray-200/80 border border-dashed border-gray-400 flex items-center justify-center text-gray-400 shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                        </template>
                        <label class="cursor-pointer bg-white/80 hover:bg-white text-[#1D3B47] border border-[#D6E5ED] rounded-2xl px-4 py-2.5 text-xs font-bold transition-all shadow-xs flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#75929E]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>Pilih Foto</span>
                            <input type="file" name="image" accept="image/*" class="hidden" 
                                   @change="const file = $event.target.files[0]; if (file) { const reader = new FileReader(); reader.onload = (e) => createImagePreview = e.target.result; reader.readAsDataURL(file); }">
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-xs sm:text-sm font-bold text-[#1D3B47] mb-1.5">Nama produk <span class="text-[#d9383a]">*</span></label>
                    <input type="text" name="name" required value="{{ old('name') }}" 
                           class="w-full bg-white border border-[#D6E5ED] rounded-2xl px-4 py-3 text-xs sm:text-sm font-semibold text-[#1D3B47] focus:outline-none focus:ring-2 focus:ring-[#4D869C]" placeholder="contoh: Kentang Goreng">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs sm:text-sm font-bold text-[#1D3B47] mb-1.5">Kategori</label>
                        <select name="category_id" class="w-full bg-white border border-[#D6E5ED] rounded-2xl px-4 py-3 text-xs sm:text-sm font-semibold text-[#1D3B47] focus:outline-none focus:ring-2 focus:ring-[#4D869C]">
                            <option value="">(Pilih Kategori)</option>
                            @foreach($categories ?? [] as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-bold text-[#1D3B47] mb-1.5">SKU (Kode)</label>
                        <input type="text" name="sku" value="{{ old('sku') }}" 
                                class="w-full bg-white border border-[#D6E5ED] rounded-2xl px-4 py-3 text-xs sm:text-sm font-semibold text-[#1D3B47] font-mono uppercase focus:outline-none focus:ring-2 focus:ring-[#4D869C]" placeholder="Otomatis jika kosong">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs sm:text-sm font-bold text-[#1D3B47] mb-1.5">Harga jual <span class="text-[#d9383a]">*</span></label>
                        <input type="number" name="selling_price" required min="0" value="{{ old('selling_price') }}" 
                               class="w-full bg-white border border-[#D6E5ED] rounded-2xl px-4 py-3 text-xs sm:text-sm font-semibold text-[#1D3B47] focus:outline-none focus:ring-2 focus:ring-[#4D869C]" placeholder="20000">
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-bold text-[#1D3B47] mb-1.5">Harga modal</label>
                        <input type="number" name="purchase_price" min="0" value="{{ old('purchase_price') }}" 
                               class="w-full bg-white border border-[#D6E5ED] rounded-2xl px-4 py-3 text-xs sm:text-sm font-semibold text-[#1D3B47] focus:outline-none focus:ring-2 focus:ring-[#4D869C]" placeholder="9000">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs sm:text-sm font-bold text-[#1D3B47] mb-1.5">Stok awal</label>
                        <input type="number" name="stock" value="{{ old('stock', 50) }}" min="0" required 
                               class="w-full bg-white border border-[#D6E5ED] rounded-2xl px-4 py-3 text-xs sm:text-sm font-semibold text-[#1D3B47] focus:outline-none focus:ring-2 focus:ring-[#4D869C]">
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-bold text-[#1D3B47] mb-1.5">Satuan</label>
                        <select name="unit_id" class="w-full bg-white border border-[#D6E5ED] rounded-2xl px-4 py-3 text-xs sm:text-sm font-semibold text-[#1D3B47] focus:outline-none focus:ring-2 focus:ring-[#4D869C]">
                            <option value="">(Pcs)</option>
                            @foreach($units ?? [] as $unit)
                            <option value="{{ $unit->id }}" @selected(old('unit_id') == $unit->id)>{{ $unit->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>

            <div class="pt-4 flex items-center justify-end gap-4 border-t border-[#D6E5ED] mt-2 shrink-0">
                <button type="button" @click="showCreateModal = false" class="text-[#75929E] font-bold hover:text-black text-xs sm:text-sm transition-colors">
                    Batal
                </button>
                <button type="submit" form="createProductForm" 
                        class="bg-[#4D869C] hover:bg-[#3D6B7D] text-white font-bold px-7 sm:px-8 py-3 rounded-full text-xs sm:text-sm shadow-sm transition-all">
                    Simpan
                </button>
            </div>
        </div>
    </div>

    <!-- POP UP: KELOLA KATEGORI (Styling konsisten #e9ebed rounded-[32px]) -->
    @can('categories view')
    <div x-show="showCategoryModal" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center bg-black/60 backdrop-blur-xs p-0 sm:p-4">
        <div @click.away="showCategoryModal = false"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-t-[32px] sm:rounded-[32px] p-6 sm:p-8 max-w-lg w-full relative shadow-2xl overflow-hidden max-h-[92vh] flex flex-col justify-between"
             x-data="{ openEditCat: null }">
            
            <div class="w-12 h-1.5 bg-gray-300 rounded-full mx-auto mb-3 sm:hidden shrink-0"></div>

            <div class="flex items-center justify-between mb-4 shrink-0">
                <h3 class="text-lg sm:text-xl font-black text-[#1D3B47]">Kelola kategori</h3>
                <button type="button" @click="showCategoryModal = false" 
                        class="w-9 h-9 bg-[#4D869C] hover:bg-[#3D6B7D] text-white rounded-full flex items-center justify-center shrink-0 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Form Tambah Kategori -->
            <form action="{{ route('categories.store') }}" method="POST" class="mb-4 space-y-3 bg-[#EEF7FF] p-4 rounded-2xl border border-[#D6E5ED] shrink-0">
                @csrf
                <input type="hidden" name="redirect" value="products">
                <input type="hidden" name="is_active" value="1">
                <div>
                    <label class="block text-xs sm:text-sm font-bold text-[#1D3B47] mb-1.5">Nama kategori baru</label>
                    <div class="flex gap-2">
                        <input type="text" name="name" required class="flex-1 bg-white border border-[#D6E5ED] rounded-2xl px-4 py-2.5 text-xs sm:text-sm font-semibold text-[#1D3B47] focus:outline-none focus:ring-2 focus:ring-[#4D869C]" placeholder="contoh: Camilan">
                        <button type="submit" class="bg-[#4D869C] hover:bg-[#3D6B7D] text-white font-bold px-6 py-2.5 rounded-full text-xs transition-all shadow-xs cursor-pointer">Tambah</button>
                    </div>
                </div>
            </form>

            <!-- List Kategori -->
            <div class="overflow-y-auto flex-1 space-y-2 pr-1 max-h-60">
                @forelse($categories ?? [] as $category)
                <div class="p-3.5 bg-[#EEF7FF] rounded-2xl border border-[#D6E5ED] flex justify-between items-center text-xs">
                    <div>
                        <div class="font-bold text-[#1D3B47] text-sm">{{ $category->name }}</div>
                        <div class="text-[11px] text-[#75929E] font-medium">{{ $category->products_count ?? 0 }} produk</div>
                    </div>
                    @can('categories delete')
                    <form method="POST" action="{{ route('categories.destroy', $category) }}" onsubmit="return confirm('Hapus kategori ini?')">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="redirect" value="products">
                        <button type="submit" class="text-[#d9383a] font-bold text-xs underline decoration-[#d9383a] decoration-2 hover:opacity-80 transition-all cursor-pointer">Hapus</button>
                    </form>
                    @endcan
                </div>
                @empty
                <div class="text-center py-6 text-gray-400 text-xs font-semibold">Belum ada kategori.</div>
                @endforelse
            </div>

            <div class="pt-4 border-t border-[#D6E5ED] mt-2 flex justify-end shrink-0">
                <button type="button" @click="showCategoryModal = false" class="bg-[#4D869C] hover:bg-[#3D6B7D] text-white font-bold px-8 py-3 rounded-full text-xs sm:text-sm shadow-sm transition-all cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>
    @endcan

</div>

<script>
    function productManager() {
        return {
            showEditModal: false,
            showCreateModal: false,
            showCategoryModal: false,
            createImagePreview: null,
            editImagePreview: null,
            editForm: {
                id: null,
                name: '',
                category_id: '',
                unit_id: '',
                sku: '',
                barcode: '',
                selling_price: 0,
                purchase_price: 0,
                stock: 0,
                is_active: true,
                image_url: '',
                update_url: '',
                delete_url: ''
            },
            init() {
                this.$watch('showEditModal', val => this.syncModalState());
                this.$watch('showCreateModal', val => this.syncModalState());
                this.$watch('showCategoryModal', val => this.syncModalState());
            },
            syncModalState() {
                const anyOpen = Boolean(this.showEditModal || this.showCreateModal || this.showCategoryModal);
                if (window.Alpine && Alpine.store('modal')) {
                    Alpine.store('modal').open = anyOpen;
                }
            },
            openEditModal(prod) {
                this.editForm = { ...prod };
                this.editImagePreview = null;
                this.showEditModal = true;
            },
            openCreateModal() {
                this.createImagePreview = null;
                this.showCreateModal = true;
            }
        };
    }
</script>
@endsection





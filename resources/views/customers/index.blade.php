@extends('layouts.pos')

@section('topbar-search')
    <x-topbar-search :action="route('customers.index')" placeholder="Cari nama atau telepon..." />
@endsection

@section('content')
<div class="p-6" x-data="{ showModal: false }">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Pelanggan</h1>
            <p class="text-gray-500 text-sm">Kelola data pelanggan Anda</p>
        </div>
        <button @click="showModal = true" class="pos-btn-primary flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tambah Pelanggan
        </button>
    </div>

    <div class="pos-card p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th scope="col" class="px-6 py-3">Nama</th>
                        <th scope="col" class="px-6 py-3">Telepon</th>
                        <th scope="col" class="px-6 py-3">Level</th>
                        <th scope="col" class="px-6 py-3">Poin</th>
                        <th scope="col" class="px-6 py-3">Total Belanja</th>
                        <th scope="col" class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers ?? [] as $customer)
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $customer->name }}</td>
                        <td class="px-6 py-4">{{ $customer->phone ?? '-' }}</td>
                        <td class="px-6 py-4">
                            <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded border border-blue-200">Reguler</span>
                        </td>
                        <td class="px-6 py-4">{{ $customer->points ?? 0 }}</td>
                        <td class="px-6 py-4">Rp {{ number_format($customer->total_spent ?? 0, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right">
                            <button class="text-teal-600 hover:text-teal-900 mr-3">Edit</button>
                            <button class="text-red-600 hover:text-red-900">Hapus</button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-8">Tidak ada data pelanggan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if(isset($customers) && $customers->hasPages())
        <div class="p-4 border-t border-gray-200">
            {{ $customers->links() }}
        </div>
        @endif
    </div>

    <!-- Add/Edit Modal -->
    <div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" style="display: none;">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden">
            <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-900">Tambah Pelanggan</h3>
                <button @click="showModal = false" class="text-gray-400 hover:text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <form action="{{ route('customers.store') }}" method="POST" class="p-6">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                        <input type="text" name="name" required class="w-full p-2 border border-gray-300 rounded-lg text-sm focus:ring-teal-500 focus:border-teal-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon</label>
                        <input type="text" name="phone" class="w-full p-2 border border-gray-300 rounded-lg text-sm focus:ring-teal-500 focus:border-teal-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email (Opsional)</label>
                        <input type="email" name="email" class="w-full p-2 border border-gray-300 rounded-lg text-sm focus:ring-teal-500 focus:border-teal-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                        <textarea name="address" rows="2" class="w-full p-2 border border-gray-300 rounded-lg text-sm focus:ring-teal-500 focus:border-teal-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                        <textarea name="notes" rows="2" class="w-full p-2 border border-gray-300 rounded-lg text-sm focus:ring-teal-500 focus:border-teal-500"></textarea>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="showModal = false" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Batal</button>
                    <button type="submit" class="pos-btn-primary text-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

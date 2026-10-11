@extends('layouts.pos')

@section('topbar-search')
    <x-topbar-search :action="route('sales.index')" placeholder="Cari No. Nota..." />
@endsection

@section('content')
<div class="p-6" x-data="salesHistory()">
    <div class="flex justify-between items-start mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Riwayat Transaksi</h1>
            <p class="text-gray-500 text-sm">Lihat, cetak ulang struk, dan proses retur pesanan kasir</p>
        </div>
        <div>
            <a href="{{ route('pos.index') }}" class="pos-btn-primary text-xs flex items-center gap-1.5 bg-[#0d6e53] hover:bg-[#095741]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Ke Layar Kasir
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-semibold flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif

    <div class="pos-card p-0 overflow-hidden">
        <!-- Filter Toolbar -->
        <div class="p-4 border-b border-gray-200 bg-gray-50 flex flex-wrap gap-4 items-center justify-between">
            <div class="flex gap-2">
                <a href="?status={{ $search ? '&search=' . urlencode($search) : '' }}" 
                   class="px-4 py-2 rounded-full text-xs font-bold transition-colors {{ empty($status) ? 'bg-gray-900 text-white' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' }}">
                    Semua
                </a>
                <a href="?status=completed{{ $search ? '&search=' . urlencode($search) : '' }}" 
                   class="px-4 py-2 rounded-full text-xs font-bold transition-colors {{ ($status ?? '') === 'completed' ? 'bg-[#0d6e53] text-white' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' }}">
                    Selesai
                </a>
                <a href="?status=refunded{{ $search ? '&search=' . urlencode($search) : '' }}" 
                   class="px-4 py-2 rounded-full text-xs font-bold transition-colors {{ ($status ?? '') === 'refunded' ? 'bg-rose-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' }}">
                    Diretur
                </a>
            </div>
            
            <form method="GET" class="flex flex-wrap gap-2.5 items-center">
                <input type="hidden" name="status" value="{{ $status ?? '' }}">
                <input type="date" name="date_from" value="{{ $dateFrom ?? '' }}" class="border border-gray-300 rounded-xl text-xs px-3 py-1.5 focus:ring-teal-700 focus:border-teal-700 bg-white" title="Dari Tanggal">
                <span class="text-xs text-gray-400">-</span>
                <input type="date" name="date_to" value="{{ $dateTo ?? '' }}" class="border border-gray-300 rounded-xl text-xs px-3 py-1.5 focus:ring-teal-700 focus:border-teal-700 bg-white" title="Sampai Tanggal">
                <button type="submit" class="px-3 py-1.5 bg-gray-800 hover:bg-gray-900 text-white text-xs font-bold rounded-xl">Filter</button>
            </form>
        </div>

        <!-- Sales Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-600">
                <thead class="text-[11px] text-gray-500 uppercase bg-gray-50 border-b border-gray-200 font-bold">
                    <tr>
                        <th scope="col" class="px-5 py-3.5">No. Nota</th>
                        <th scope="col" class="px-5 py-3.5">Waktu Transaksi</th>
                        <th scope="col" class="px-5 py-3.5">Kasir</th>
                        <th scope="col" class="px-5 py-3.5">Pelanggan</th>
                        <th scope="col" class="px-5 py-3.5">Total Belanja</th>
                        <th scope="col" class="px-5 py-3.5">Metode</th>
                        <th scope="col" class="px-5 py-3.5">Status</th>
                        <th scope="col" class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($sales ?? [] as $sale)
                    <tr class="bg-white hover:bg-gray-50/80 transition-colors">
                        <td class="px-5 py-3.5 font-bold font-mono text-gray-900">{{ $sale->invoice_number }}</td>
                        <td class="px-5 py-3.5">{{ $sale->transaction_date ? \Carbon\Carbon::parse($sale->transaction_date)->format('d M Y, H:i') : $sale->created_at->format('d M Y, H:i') }}</td>
                        <td class="px-5 py-3.5">{{ $sale->user->name ?? '-' }}</td>
                        <td class="px-5 py-3.5 font-medium text-gray-800">{{ $sale->customer->name ?? 'Pelanggan Umum' }}</td>
                        <td class="px-5 py-3.5 font-extrabold text-gray-950">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                        <td class="px-5 py-3.5">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-gray-100 text-gray-800">
                                {{ $sale->payment_method ?? 'cash' }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            @if($sale->status == 'completed')
                                <span class="bg-emerald-50 text-emerald-800 text-[10px] font-bold px-2 py-0.5 rounded-full border border-emerald-200">Selesai</span>
                            @elseif($sale->status == 'pending')
                                <span class="bg-yellow-50 text-yellow-800 text-[10px] font-bold px-2 py-0.5 rounded-full border border-yellow-200">Menunggu</span>
                            @elseif($sale->status == 'refunded')
                                <span class="bg-rose-50 text-rose-800 text-[10px] font-bold px-2 py-0.5 rounded-full border border-rose-200">Diretur</span>
                            @elseif($sale->status == 'cancelled')
                                <span class="bg-gray-100 text-gray-700 text-[10px] font-bold px-2 py-0.5 rounded-full border border-gray-200">Dibatalkan</span>
                            @else
                                <span class="bg-gray-100 text-gray-700 text-[10px] font-bold px-2 py-0.5 rounded-full">{{ ucfirst($sale->status) }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right space-x-2">
                            <button @click="showDetail({{ json_encode($sale) }})" class="text-teal-700 hover:text-teal-900 font-bold text-xs">Detail</button>
                            <button @click="printInvoice({{ json_encode($sale) }})" class="text-gray-600 hover:text-gray-900 font-bold text-xs">Cetak</button>
                            @if($sale->status == 'completed')
                            <button @click="openRefundModal({{ json_encode($sale) }})" class="text-rose-600 hover:text-rose-800 font-bold text-xs">Retur</button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-12 text-gray-400 font-medium">
                            Belum ada riwayat transaksi yang tercatat.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if(isset($sales) && $sales->hasPages())
        <div class="p-4 border-t border-gray-200">
            {{ $sales->links() }}
        </div>
        @endif
    </div>

    <!-- MODAL DETAIL TRANSAKSI -->
    <div x-show="selectedSale" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div @click.away="selectedSale = null" class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden p-6 space-y-4">
            <div class="flex justify-between items-center border-b pb-3">
                <div>
                    <h3 class="font-extrabold text-sm text-gray-900" x-text="'Nota ' + selectedSale?.invoice_number"></h3>
                    <p class="text-[11px] text-gray-500" x-text="selectedSale?.transaction_date || selectedSale?.created_at"></p>
                </div>
                <button @click="selectedSale = null" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
            </div>

            <!-- Items -->
            <div class="max-h-56 overflow-y-auto divide-y divide-gray-100 text-xs">
                <template x-for="item in (selectedSale?.items || [])" :key="item.id">
                    <div class="py-2 flex justify-between items-center">
                        <div>
                            <div class="font-bold text-gray-900" x-text="item.product?.name || 'Produk'"></div>
                            <div class="text-[10px] text-gray-400" x-text="item.quantity + ' x ' + formatPrice(item.unit_price)"></div>
                        </div>
                        <div class="font-bold text-gray-900" x-text="formatPrice(item.subtotal)"></div>
                    </div>
                </template>
            </div>

            <div class="border-t pt-3 space-y-1.5 text-xs text-gray-600">
                <div class="flex justify-between"><span>Subtotal:</span><span class="font-semibold text-gray-900" x-text="formatPrice(selectedSale?.subtotal)"></span></div>
                <div class="flex justify-between text-rose-600" x-show="selectedSale?.discount_amount > 0">
                    <span>Diskon:</span><span class="font-semibold" x-text="'- ' + formatPrice(selectedSale?.discount_amount)"></span>
                </div>
                <div class="flex justify-between"><span>PPN:</span><span class="font-semibold text-gray-900" x-text="formatPrice(selectedSale?.tax_amount)"></span></div>
                <div class="flex justify-between pt-2 border-t font-black text-sm text-gray-950">
                    <span>Total:</span><span x-text="formatPrice(selectedSale?.total_amount)"></span>
                </div>
                <div class="flex justify-between text-[11px]"><span>Metode Pembayaran:</span><span class="font-bold uppercase text-gray-800" x-text="selectedSale?.payment_method"></span></div>
            </div>

            <div class="pt-2 flex gap-2">
                <button @click="printInvoice(selectedSale)" class="flex-1 py-2.5 bg-gray-900 hover:bg-black text-white rounded-xl text-xs font-bold">Cetak Struk</button>
                <button @click="selectedSale = null" class="w-1/3 py-2.5 border border-gray-200 rounded-xl text-xs font-bold text-gray-700 hover:bg-gray-50">Tutup</button>
            </div>
        </div>
    </div>

    <!-- MODAL RETUR TRANSAKSI -->
    <div x-show="refundSale" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div @click.away="refundSale = null" class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden p-6 space-y-4">
            <div class="flex justify-between items-center border-b pb-3">
                <div>
                    <h3 class="font-extrabold text-sm text-gray-900" x-text="'Proses Retur - ' + refundSale?.invoice_number"></h3>
                    <p class="text-[11px] text-gray-500">Pilih item yang ingin dikembalikan ke stok</p>
                </div>
                <button @click="refundSale = null" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
            </div>

            <div class="max-h-56 overflow-y-auto space-y-2 text-xs">
                <template x-for="item in refundItems" :key="item.id">
                    <div class="p-2.5 border rounded-xl flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" x-model="item.selected" class="rounded text-teal-700">
                            <div>
                                <div class="font-bold text-gray-900" x-text="item.name"></div>
                                <div class="text-[10px] text-gray-500" x-text="formatPrice(item.unit_price)"></div>
                            </div>
                        </div>
                        <div class="flex items-center gap-1" x-show="item.selected">
                            <span class="text-[11px] text-gray-500">Qty:</span>
                            <input type="number" x-model.number="item.qty" :max="item.max_qty" min="1" class="w-16 px-2 py-1 border rounded-lg text-center font-bold">
                        </div>
                    </div>
                </template>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Alasan Retur</label>
                <textarea x-model="refundReason" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-xl text-xs" placeholder="contoh: Barang cacat, pesanan salah..."></textarea>
            </div>

            <div class="pt-2 flex gap-2">
                <button @click="refundSale = null" class="w-1/3 py-2.5 border border-gray-200 rounded-xl text-xs font-bold text-gray-700 hover:bg-gray-50">Batal</button>
                <button @click="submitRefund()" :disabled="isProcessingRefund" class="flex-1 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold">
                    <span x-show="!isProcessingRefund">Konfirmasi Retur</span>
                    <span x-show="isProcessingRefund">Memproses...</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function salesHistory() {
        return {
            selectedSale: null,
            refundSale: null,
            refundItems: [],
            refundReason: '',
            isProcessingRefund: false,

            showDetail(sale) {
                this.selectedSale = sale;
            },

            openRefundModal(sale) {
                this.refundSale = sale;
                this.refundReason = '';
                this.refundItems = (sale.items || []).map(i => ({
                    id: i.id,
                    sale_item_id: i.id,
                    product_id: i.product_id,
                    name: i.product?.name || 'Produk',
                    unit_price: i.unit_price,
                    qty: i.quantity,
                    max_qty: i.quantity,
                    selected: true
                }));
            },

            async submitRefund() {
                const selected = this.refundItems.filter(i => i.selected && i.qty > 0);
                if (selected.length === 0) {
                    alert('Pilih minimal 1 item untuk diretur.');
                    return;
                }
                if (!this.refundReason.trim()) {
                    alert('Harap isi alasan retur.');
                    return;
                }

                this.isProcessingRefund = true;
                try {
                    const res = await fetch('{{ route("refunds.process") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            sale_id: this.refundSale.id,
                            reason: this.refundReason,
                            items: selected.map(i => ({
                                sale_item_id: i.sale_item_id,
                                quantity: i.qty,
                                reason: this.refundReason
                            }))
                        })
                    });

                    const data = await res.json();
                    if (!res.ok) throw new Error(data.error || 'Gagal memproses retur.');

                    alert('Retur berhasil diproses. Stok barang telah dikembalikan.');
                    window.location.reload();
                } catch (e) {
                    alert('Error: ' + e.message);
                } finally {
                    this.isProcessingRefund = false;
                }
            },

            printInvoice(sale) {
                const printWindow = window.open('', '_blank', 'width=350,height=600');
                if (!printWindow) return;
                
                let html = `<html><head><title>Struk ${sale.invoice_number}</title><style>
                    body { font-family: monospace; font-size: 12px; padding: 15px; }
                    .center { text-align: center; }
                    .dashed { border-bottom: 1px dashed #999; margin: 8px 0; }
                    .row { display: flex; justify-content: space-between; margin: 3px 0; }
                    .bold { font-weight: bold; }
                </style></head><body>`;
                
                html += `<div class="center bold">${sale.business?.name || 'POS'}</div>`;
                html += `<div class="dashed"></div>`;
                html += `<div class="row"><span>Nota:</span><span>${sale.invoice_number}</span></div>`;
                html += `<div class="row"><span>Kasir:</span><span>${sale.user?.name || '-'}</span></div>`;
                html += `<div class="row"><span>Waktu:</span><span>${sale.transaction_date || sale.created_at}</span></div>`;
                html += `<div class="dashed"></div>`;
                
                (sale.items || []).forEach(i => {
                    html += `<div><span class="bold">${i.product?.name || 'Item'}</span></div>`;
                    html += `<div class="row"><span>${i.quantity} x ${this.formatPrice(i.unit_price)}</span><span class="bold">${this.formatPrice(i.subtotal)}</span></div>`;
                });
                
                html += `<div class="dashed"></div>`;
                html += `<div class="row"><span>Subtotal:</span><span>${this.formatPrice(sale.subtotal)}</span></div>`;
                if (sale.discount_amount > 0) {
                    html += `<div class="row"><span>Diskon:</span><span>-${this.formatPrice(sale.discount_amount)}</span></div>`;
                }
                html += `<div class="row"><span>PPN:</span><span>${this.formatPrice(sale.tax_amount)}</span></div>`;
                html += `<div class="row bold" style="font-size: 14px;"><span>TOTAL:</span><span>${this.formatPrice(sale.total_amount)}</span></div>`;
                html += `<div class="row"><span>Metode:</span><span>${(sale.payment_method || 'CASH').toUpperCase()}</span></div>`;
                html += `<div class="dashed"></div>`;
                html += `<div class="center" style="margin-top: 15px;">Terima kasih atas pesanan Anda!</div>`;
                html += `</body></html>`;

                printWindow.document.write(html);
                printWindow.document.close();
                printWindow.focus();
                setTimeout(() => { printWindow.print(); printWindow.close(); }, 300);
            },

            formatPrice(val) {
                return new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                }).format(val || 0);
            }
        };
    }
</script>
@endsection
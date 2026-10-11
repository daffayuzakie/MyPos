@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <div class="row justify-center">
        <div class="col-12 col-md-10 col-lg-8">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="border-b border-gray-200">
                    <div class="p-4 flex justify-between items-center">
                        <h2 class="text-2xl font-bold">Stock Movement Detail</h2>
                        <a href="{{ route('stock.index') }}"
                           class="inline-block text-sm text-blue-600 hover:text-blue-800">
                            &larr; Back to List
                        </a>
                    </div>
                </div>
                <div class="p-4">
                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Type</label>
                            <div class="mt-1">
                                @if($stockMovement->type === 'in')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">IN</span>
                                @elseif($stockMovement->type === 'out')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">OUT</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">ADJUSTMENT</span>
                                @endif
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500">Quantity</label>
                            <div class="mt-1 text-lg font-medium">
                                @if($stockMovement->type === 'out')
                                    <span class="text-red-600">-{{ $stockMovement->quantity }}</span>
                                @elseif($stockMovement->type === 'adjustment' && $stockMovement->quantity < 0)
                                    <span class="text-red-600">{{ $stockMovement->quantity }}</span>
                                @else
                                    <span class="text-green-600">+{{ $stockMovement->quantity }}</span>
                                @endif
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500">Stock Before</label>
                            <div class="mt-1 text-lg font-mono font-medium">{{ number_format($stockMovement->stock_before) }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500">Stock After</label>
                            <div class="mt-1 text-lg font-mono font-medium">{{ number_format($stockMovement->stock_after) }}</div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-500">Reason</label>
                            <div class="mt-1">{{ $stockMovement->reason }}</div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-500">Note</label>
                            <div class="mt-1">{{ $stockMovement->note ?? '—' }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500">Product</label>
                            <div class="mt-1">
                                <a href="{{ route('products.edit', $stockMovement->product_id) }}" class="text-blue-600 hover:text-blue-900">
                                    {{ $stockMovement->product->name ?? 'N/A' }} ({{ $stockMovement->product->sku ?? 'N/A' }})
                                </a>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500">Recorded By</label>
                            <div class="mt-1">{{ $stockMovement->user->name ?? 'N/A' }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500">Date</label>
                            <div class="mt-1">{{ $stockMovement->created_at->format('Y-m-d H:i:s') }}</div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <a href="{{ route('stock.index') }}"
                           class="inline-block text-blue-600 hover:text-blue-900">
                            &larr; Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
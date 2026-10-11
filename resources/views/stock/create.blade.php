@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <div class="row justify-center">
        <div class="col-12 col-md-10 col-lg-8">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="border-b border-gray-200">
                    <h2 class="text-2xl font-bold p-4">Create Stock Movement</h2>
                </div>
                <div class="p-4">
                    @if($errors->any())
                        <div class="bg-red-100 border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('stock.store') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <x-input-label for="product_id" :value="__('Product')" />
                            <x-select-input
                                id="product_id"
                                class="block mt-1 w-full"
                                name="product_id"
                                required
                                >
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}"
                                        {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                        {{ $product->name }} (SKU: {{ $product->sku }}, Stock: {{ $product->stock }})
                                    </option>
                                @endforeach
                            </x-select-input>
                            <x-input-error :messages="$errors->get('product_id')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="type" :value="__('Type')" />
                            <x-select-input
                                id="type"
                                class="block mt-1 w-full"
                                name="type"
                                required
                                >
                                <option value="in" {{ old('type') == 'in' ? 'selected' : '' }}>IN (Stock In)</option>
                                <option value="out" {{ old('type') == 'out' ? 'selected' : '' }}>OUT (Stock Out)</option>
                                <option value="adjustment" {{ old('type') == 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                            </x-select-input>
                            <x-input-error :messages="$errors->get('type')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="quantity" :value="__('Quantity')" />
                            <x-text-input
                                id="quantity"
                                class="block mt-1 w-full"
                                name="quantity"
                                required
                                />
                            <x-input-error :messages="$errors->get('quantity')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="reason" :value="__('Reason')" />
                            <x-text-input
                                id="reason"
                                class="block mt-1 w-full"
                                name="reason"
                                required
                                />
                            <x-input-error :messages="$errors->get('reason')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="note" :value="__('Note')" />
                            <x-text-input
                                id="note"
                                class="block mt-1 w-full"
                                name="note"
                                />
                            <x-input-error :messages="$errors->get('note')" class="mt-2" />
                        </div>

                        <div class="mt-6">
                            <x-primary-button class="w-full">
                                {{ __('Create Stock Movement') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
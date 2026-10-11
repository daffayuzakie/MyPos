@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <div class="row justify-center">
        <div class="col-12 col-md-10 col-lg-8">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="border-b border-gray-200">
                    <h2 class="text-2xl font-bold p-4">Edit Product</h2>
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

                    <form action="{{ route('products.update', $product->id) }}" method="POST">
                        @csrf @method('PUT')

                        <div class="mb-4">
                            <x-input-label for="name" :value="__('Name')" />
                            <x-text-input
                                id="name"
                                class="block mt-1 w-full"
                                name="name"
                                value="{{ old('name', $product->name) }}"
                                required
                                />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="sku" :value="__('SKU')" />
                            <x-text-input
                                id="sku"
                                class="block mt-1 w-full"
                                name="sku"
                                value="{{ old('sku', $product->sku) }}"
                                required
                                />
                            <x-input-error :messages="$errors->get('sku')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="barcode" :value="__('Barcode')" />
                            <x-text-input
                                id="barcode"
                                class="block mt-1 w-full"
                                name="barcode"
                                value="{{ old('barcode', $product->barcode) }}"
                                />
                            <x-input-error :messages="$errors->get('barcode')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="category_id" :value="__('Category')" />
                            <x-select-input
                                id="category_id"
                                class="block mt-1 w-full"
                                name="category_id"
                                >
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ old('category_id') == $category->id ? 'selected' : '' }}
                                        {{ $category->business_id == auth()->user()->business_id ? '' : 'disabled' }}
                                        {{ $category->business_id != auth()->user()->business_id ? 'data-disabled' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </x-select-input>
                            <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="unit_id" :value="__('Unit')" />
                            <x-select-input
                                id="unit_id"
                                class="block mt-1 w-full"
                                name="unit_id"
                                >
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}"
                                        {{ old('unit_id') == $unit->id ? 'selected' : '' }}
                                        {{ $unit->business_id == auth()->user()->business_id ? '' : 'disabled' }}
                                        {{ $unit->business_id != auth()->user()->business_id ? 'data-disabled' : '' }}>
                                        {{ $unit->name }} ({{ $unit->code }})
                                    </option>
                                @endforeach
                            </x-select-input>
                            <x-input-error :messages="$errors->get('unit_id')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="purchase_price" :value="__('Purchase Price')" />
                            <x-text-input
                                id="purchase_price"
                                class="block mt-1 w-full"
                                name="purchase_price"
                                type="number"
                                value="{{ old('purchase_price', $product->purchase_price) }}"
                                required
                                />
                            <x-input-error :messages="$errors->get('purchase_price')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="selling_price" :value="__('Selling Price')" />
                            <x-text-input
                                id="selling_price"
                                class="block mt-1 w-full"
                                name="selling_price"
                                type="number"
                                value="{{ old('selling_price', $product->selling_price) }}"
                                required
                                />
                            <x-input-error :messages="$errors->get('selling_price')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="is_active" :value="__('Active')" />
                            <x-select-input
                                id="is_active"
                                class="block mt-1 w-full"
                                name="is_active"
                                >
                                <option value="1" {{ $product->is_active ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ !$product->is_active ? 'selected' : '' }}>No</option>
                            </x-select-input>
                            <x-input-error :messages="$errors->get('is_active')" class="mt-2" />
                        </div>

                        <div class="mt-6">
                            <x-primary-button class="w-full">
                                {{ __('Update Product') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
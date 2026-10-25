@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <div class="row justify-center">
        <div class="col-12 col-md-10 col-lg-8">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="border-b border-gray-200">
                    <h2 class="text-2xl font-bold p-4">Create Product</h2>
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

                    <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-4">
                            <x-input-label for="image" :value="__('Foto Produk (Opsional)')" />
                            <input type="file" id="image" name="image" accept="image/*" class="block mt-1 w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#4D869C] file:text-[#4D869C] hover:file:bg-black">
                            <x-input-error :messages="$errors->get('image')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="name" :value="__('Name')" />
                            <x-text-input
                                id="name"
                                class="block mt-1 w-full"
                                name="name"
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
                                        {{ old('category_id') == $category->id ? 'selected' : '' }}>
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
                                        {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
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
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </x-select-input>
                            <x-input-error :messages="$errors->get('is_active')" class="mt-2" />
                        </div>

                        <div class="mt-6">
                            <x-primary-button class="w-full">
                                {{ __('Create Product') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection



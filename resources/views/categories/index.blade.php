@extends('layouts.pos')

@section('topbar-search')
    <x-topbar-search :action="route('categories.index')" placeholder="Cari kategori..." />
@endsection

@section('content')
<div class="p-6 max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Categories</h1>
        <a href="{{ route('categories.create') }}" class="pos-btn-primary text-xs">Create Category</a>
    </div>
    <div class="bg-white rounded-xl shadow-xs border border-gray-200 p-4">
        <table class="w-full text-left text-xs">
            <thead>
                <tr class="border-b text-gray-400 font-bold uppercase"><th class="py-2">Name</th><th class="py-2">Description</th></tr>
            </thead>
            <tbody>
                @foreach($categories as $c)
                    <tr class="border-b"><td class="py-2 font-bold">{{ $c->name }}</td><td class="py-2">{{ $c->description }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection


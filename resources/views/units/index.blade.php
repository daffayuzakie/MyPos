@extends('layouts.pos')

@section('topbar-search')
    <x-topbar-search :action="route('units.index')" placeholder="Cari satuan atau kode..." />
@endsection

@section('content')
<div class="p-6 max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Units</h1>
        <a href="{{ route('units.create') }}" class="pos-btn-primary text-xs">Create Unit</a>
    </div>
    <div class="bg-white rounded-xl shadow-xs border border-gray-200 p-4">
        <table class="w-full text-left text-xs">
            <thead>
                <tr class="border-b text-gray-400 font-bold uppercase"><th class="py-2">Name</th><th class="py-2">Code</th></tr>
            </thead>
            <tbody>
                @foreach($units as $u)
                    <tr class="border-b"><td class="py-2 font-bold">{{ $u->name }}</td><td class="py-2">{{ $u->code ?? $u->symbol }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection




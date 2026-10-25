@extends('layouts.pos')

@section('content')
<div class="p-6 max-w-xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Edit Unit</h1>
    <form method="POST" action="{{ route('units.update', $unit) }}" class="bg-white p-6 rounded-xl border border-gray-200 space-y-4">
        @csrf
        @method('PUT')
        <div><label class="block text-xs font-bold mb-1">Name</label><input type="text" name="name" value="{{ $unit->name }}" required class="w-full px-3 py-2 border rounded-xl text-xs"></div>
        <div><label class="block text-xs font-bold mb-1">Code / Symbol</label><input type="text" name="code" value="{{ $unit->code ?? $unit->symbol }}" required class="w-full px-3 py-2 border rounded-xl text-xs"></div>
        <button type="submit" class="pos-btn-primary text-xs">Update</button>
    </form>
</div>
@endsection




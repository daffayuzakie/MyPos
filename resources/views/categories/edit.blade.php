@extends('layouts.pos')

@section('content')
<div class="p-6 max-w-xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Edit Category</h1>
    <form method="POST" action="{{ route('categories.update', $category) }}" class="bg-white p-6 rounded-xl border border-gray-200 space-y-4">
        @csrf
        @method('PUT')
        <div><label class="block text-xs font-bold mb-1">Name</label><input type="text" name="name" value="{{ $category->name }}" required class="w-full px-3 py-2 border rounded-xl text-xs"></div>
        <div><label class="block text-xs font-bold mb-1">Description</label><textarea name="description" class="w-full px-3 py-2 border rounded-xl text-xs">{{ $category->description }}</textarea></div>
        <button type="submit" class="pos-btn-primary text-xs">Update</button>
    </form>
</div>
@endsection


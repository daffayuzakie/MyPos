@props([
    'action' => null,
    'placeholder' => 'Cari...',
    'name' => 'search',
    'value' => null,
])

<form method="GET" action="{{ $action ?? url()->current() }}" class="relative w-full">
    @foreach (request()->except($name, 'page') as $key => $val)
        @continue(is_array($val))
        <input type="hidden" name="{{ $key }}" value="{{ $val }}">
    @endforeach

    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
    </div>
    <input type="text" name="{{ $name }}" value="{{ $value ?? request($name) }}"
           class="w-full pl-9 pr-4 py-2 bg-gray-50 hover:bg-gray-100/60 focus:bg-white border border-gray-200 rounded-xl text-xs text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-teal-700 focus:border-teal-700 shadow-2xs transition-all"
           placeholder="{{ $placeholder }}">
</form>



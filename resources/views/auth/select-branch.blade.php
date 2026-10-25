<x-guest-layout>
    <div class="py-2">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight text-center">
            {{ __('Pilih Cabang') }}
        </h2>
        <p class="mt-1 text-sm text-gray-500 text-center">
            {{ __('Pilih cabang yang ingin Anda kelola sekarang.') }}
        </p>

        @if ($errors->any())
            <div class="mt-4 text-sm text-red-700 bg-red-50 border border-red-200 rounded-md px-3 py-2">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('branches.switch') }}" class="mt-6 space-y-3">
            @csrf
            @foreach ($branches as $branch)
                <label class="flex items-center gap-3 border border-gray-200 rounded-md p-3 cursor-pointer hover:border-indigo-400">
                    <input type="radio" name="branch_id" value="{{ $branch->id }}"
                        class="text-indigo-600 focus:ring-indigo-500"
                        @checked((int) $activeBranchId === $branch->id)>
                    <span class="flex-1">
                        <span class="block font-medium text-gray-800">
                            {{ $branch->name }}
                            @if ($branch->is_primary)
                                <span class="ml-1 text-[11px] font-semibold text-indigo-600 uppercase">{{ __('Utama') }}</span>
                            @endif
                        </span>
                        @if ($branch->address)
                            <span class="block text-xs text-gray-500">{{ $branch->address }}</span>
                        @endif
                    </span>
                </label>
            @endforeach

            <x-primary-button class="w-full justify-center">{{ __('Masuk') }}</x-primary-button>
        </form>
    </div>
</x-guest-layout>



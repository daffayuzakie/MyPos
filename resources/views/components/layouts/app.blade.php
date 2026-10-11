@extends('layouts.pos')

@section('content')
    @isset($header)
        <div class="px-4 sm:px-6 lg:px-8 pt-4">
            {{ $header }}
        </div>
    @endisset

    {{ $slot }}
@endsection

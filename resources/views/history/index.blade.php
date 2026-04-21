@extends('layouts.app')

@section('title', 'Riwayat Pertanyaan')

@php $pageTitle = 'Riwayat Pertanyaan'; @endphp

@section('content')
<div class="glass-panel flex flex-col h-full overflow-hidden" x-data="{ search: '' }">

    {{-- Search bar --}}
    <div class="px-5 pt-5 pb-4">
        @include('components.search-bar', [
            'placeholder' => 'Cari Pertanyaan...',
            'model'       => 'search',
        ])
    </div>

    {{-- History list --}}
    <div class="flex-1 overflow-y-auto px-5 pb-5">
        @include('components.history-list', [
            'chatHistory' => $chatHistory ?? [],
        ])
    </div>

</div>
@endsection

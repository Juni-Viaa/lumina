@extends('layouts.app')
@section('title', 'Chat')

@push('topbar-actions')
    @include('components.chat.clear-button')
@endpush

@push('styles')
    @include('components.chat.markdown-styles')
@endpush

@section('content')
<div class="flex flex-col h-full" x-data="chatApp()">
    @include('components.chat.message-list')
    @include('components.chat.input-bar')
</div>
@endsection

@push('scripts')
    @include('components.chat.chat-script')
@endpush
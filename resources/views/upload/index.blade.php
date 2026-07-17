@extends('layouts.app')

@section('title', 'Upload Dokumen')

@php($pageTitle = 'Selamat datang, ' . auth()->user()->username)

@section('content')
<div class="glass-panel upload-form flex flex-col h-full overflow-hidden" x-data="uploadForm()" x-init="fetchDocuments()">
    @include('components.upload.upload-header')

    @include('components.upload.upload-view')

    @include('components.upload.document-view')

    @include('components.upload.delete-modal')
</div>
@endsection

@push('scripts')
    @include('components.upload.upload-script')
@endpush
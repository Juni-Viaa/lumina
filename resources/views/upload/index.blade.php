@extends('layouts.app')

@section('title', 'Upload Dokumen')

@php $pageTitle = 'Selamat datang, ' . (auth()->user()->name ?? 'User123'); @endphp

@section('content')

    {{-- Full-height upload form panel --}}
    @include('components.upload-form')

@endsection

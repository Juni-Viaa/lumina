@extends('layouts.app')

@section('title', 'Upload Dokumen')

@php($pageTitle = 'Selamat datang, ' . auth()->user()->username)

@section('content')

    {{-- Full-height upload form panel --}}
    @include('components.upload-form')

@endsection

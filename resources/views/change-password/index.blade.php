@extends('layouts.app')

@section('title', 'Ganti Password')

@php $pageTitle = 'Ganti Password'; @endphp

@section('content')
<div class="glass-panel h-full overflow-hidden">
    @include('components.password-form')
</div>
@endsection

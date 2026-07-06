@extends('layouts.app')

@section('title', 'Nueva factura')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-white">Nueva factura</h1>
        <p class="mt-1 text-sm text-muted">Número asignado: <span class="font-medium text-accent">{{ $nextNumber }}</span></p>
    </div>

    <form method="POST" action="{{ route('invoices.store') }}" data-invoice-form>
        @csrf
        @include('invoices._form')
    </form>
@endsection

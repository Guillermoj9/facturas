@extends('layouts.app')

@section('title', 'Editar factura ' . $invoice->invoice_number)

@section('content')
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Editar factura {{ $invoice->invoice_number }}</h1>
            <div class="mt-2"><x-status-badge :status="$invoice->status" /></div>
        </div>
    </div>

    <form method="POST" action="{{ route('invoices.update', $invoice) }}" data-invoice-form>
        @csrf
        @method('PUT')
        @include('invoices._form')
    </form>
@endsection

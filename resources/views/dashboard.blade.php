@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Dashboard</h1>
            <p class="mt-1 text-sm text-muted">{{ ucfirst(now()->translatedFormat('l, j \d\e F \d\e Y')) }} · Trimestre T{{ $quarter }}</p>
        </div>
        <a href="{{ route('invoices.create') }}" class="btn-primary">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M12 4v16m8-8H4"/></svg>
            Nueva factura
        </a>
    </div>

    {{-- Cards superiores --}}
    <div class="mb-6 grid grid-cols-2 gap-4 xl:grid-cols-5">
        <div class="card p-5">
            <p class="text-xs font-medium uppercase tracking-wide text-muted">Facturado este mes</p>
            <p class="mt-2 text-2xl font-bold text-white">{{ euro($stats['month']) }}</p>
        </div>
        <div class="card p-5">
            <p class="text-xs font-medium uppercase tracking-wide text-muted">Este trimestre (T{{ $quarter }})</p>
            <p class="mt-2 text-2xl font-bold text-white">{{ euro($stats['quarter']) }}</p>
        </div>
        <div class="card p-5">
            <p class="text-xs font-medium uppercase tracking-wide text-muted">Este año</p>
            <p class="mt-2 text-2xl font-bold text-white">{{ euro($stats['year']) }}</p>
        </div>
        <div class="card p-5">
            <p class="text-xs font-medium uppercase tracking-wide text-muted">Pendiente de cobro</p>
            <p class="mt-2 text-2xl font-bold {{ $stats['pending'] > 0 ? 'text-amber-400' : 'text-white' }}">{{ euro($stats['pending']) }}</p>
        </div>
        <div class="card p-5">
            <p class="text-xs font-medium uppercase tracking-wide text-muted">Facturas este mes</p>
            <p class="mt-2 text-2xl font-bold text-white">{{ $stats['invoices_month'] }}</p>
        </div>
    </div>

    {{-- Indicadores fiscales del trimestre --}}
    <div class="mb-6 grid gap-4 md:grid-cols-3">
        <div class="card border-accent/40 p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-muted">IVA a pagar este trimestre (T{{ $quarter }})</p>
                    <p class="mt-2 text-2xl font-bold text-accent">{{ euro($taxes['iva_a_pagar']) }}</p>
                    <p class="mt-1 text-xs text-muted">Repercutido {{ euro($taxes['iva_repercutido']) }} − soportado {{ euro($taxes['iva_soportado']) }} · Modelo 303</p>
                </div>
            </div>
        </div>
        <div class="card p-5">
            <p class="text-xs font-medium uppercase tracking-wide text-muted">IVA soportado (deducible) T{{ $quarter }}</p>
            <p class="mt-2 text-2xl font-bold text-white">{{ euro($taxes['iva_soportado']) }}</p>
            <p class="mt-1 text-xs text-muted">IVA de tus gastos deducibles</p>
        </div>
        <div class="card p-5">
            <p class="text-xs font-medium uppercase tracking-wide text-muted">IRPF retenido T{{ $quarter }}</p>
            <p class="mt-2 text-2xl font-bold text-white">{{ euro($taxes['irpf_retenido']) }}</p>
            <p class="mt-1 text-xs text-muted">Ya retenido por tus clientes · informativo (modelo 130)</p>
        </div>
    </div>

    {{-- Gráficos --}}
    <div class="mb-6 grid gap-4 xl:grid-cols-3">
        <div class="card p-5 xl:col-span-2">
            <h2 class="mb-4 text-sm font-semibold text-white">Facturación mensual (últimos 12 meses)</h2>
            <div class="h-64">
                <canvas data-chart="bar"
                        data-labels='@json($monthlyChart->pluck('label'))'
                        data-values='@json($monthlyChart->pluck('total'))'></canvas>
            </div>
        </div>
        <div class="card p-5">
            <h2 class="mb-4 text-sm font-semibold text-white">Trimestres {{ now()->year }}</h2>
            <div class="h-64">
                <canvas data-chart="bar"
                        data-labels='@json($quarterlyChart->pluck('label'))'
                        data-values='@json($quarterlyChart->pluck('total'))'></canvas>
            </div>
        </div>
    </div>

    {{-- Tablas --}}
    <div class="grid gap-4 xl:grid-cols-2">
        <div class="card overflow-hidden">
            <div class="flex items-center justify-between border-b border-edge px-5 py-4">
                <h2 class="text-sm font-semibold text-white">Últimas facturas</h2>
                <a href="{{ route('invoices.index') }}" class="text-xs text-accent hover:underline">Ver todas →</a>
            </div>
            <table class="table-app">
                <thead>
                    <tr><th>Número</th><th>Cliente</th><th>Fecha</th><th class="text-right">Total</th><th>Estado</th></tr>
                </thead>
                <tbody>
                    @forelse ($latestInvoices as $invoice)
                        <tr>
                            <td><a href="{{ route('invoices.show', $invoice) }}" class="font-medium text-accent hover:underline">{{ $invoice->invoice_number }}</a></td>
                            <td class="text-muted">{{ $invoice->client->name }}</td>
                            <td class="text-muted">{{ $invoice->issue_date->format('d/m/Y') }}</td>
                            <td class="text-right font-medium">{{ euro($invoice->total) }}</td>
                            <td><x-status-badge :status="$invoice->status" /></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-muted">Todavía no hay facturas. <a href="{{ route('invoices.create') }}" class="text-accent hover:underline">Crea la primera</a></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card overflow-hidden">
            <div class="border-b border-edge px-5 py-4">
                <h2 class="text-sm font-semibold text-white">Pendientes de cobro</h2>
            </div>
            <table class="table-app">
                <thead>
                    <tr><th>Número</th><th>Cliente</th><th>Vence</th><th class="text-right">Total</th><th>Estado</th></tr>
                </thead>
                <tbody>
                    @forelse ($pendingInvoices as $invoice)
                        <tr>
                            <td><a href="{{ route('invoices.show', $invoice) }}" class="font-medium text-accent hover:underline">{{ $invoice->invoice_number }}</a></td>
                            <td class="text-muted">{{ $invoice->client->name }}</td>
                            <td class="{{ $invoice->status === 'vencida' ? 'text-red-400' : 'text-muted' }}">{{ $invoice->due_date?->format('d/m/Y') ?? '—' }}</td>
                            <td class="text-right font-medium">{{ euro($invoice->total) }}</td>
                            <td><x-status-badge :status="$invoice->status" /></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-muted">🎉 Nada pendiente de cobro</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

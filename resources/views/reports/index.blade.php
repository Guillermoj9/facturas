@extends('layouts.app')

@section('title', 'Informes')

@section('content')
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Informes</h1>
            <p class="mt-1 text-sm text-muted">Del {{ $from->format('d/m/Y') }} al {{ $to->format('d/m/Y') }}</p>
        </div>
        <a href="{{ route('reports.export', ['from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]) }}" class="btn-primary">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Exportar CSV
        </a>
    </div>

    {{-- Filtro por fechas --}}
    <form method="GET" class="card mb-6 flex flex-wrap items-end gap-4 p-5">
        <div>
            <label class="label" for="from">Desde</label>
            <input class="field" type="date" id="from" name="from" value="{{ $from->format('Y-m-d') }}">
        </div>
        <div>
            <label class="label" for="to">Hasta</label>
            <input class="field" type="date" id="to" name="to" value="{{ $to->format('Y-m-d') }}">
        </div>
        <div>
            <label class="label" for="year">Año (resumen trimestral)</label>
            <select class="field" id="year" name="year">
                @foreach ($years as $y)
                    <option value="{{ $y }}" @selected($y === $year)>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn-secondary">Aplicar filtros</button>
        <a href="{{ route('reports.index') }}" class="text-sm text-muted hover:text-ink">Restablecer (año actual)</a>
    </form>

    {{-- Totales del periodo --}}
    <div class="mb-6 grid grid-cols-2 gap-4 xl:grid-cols-6">
        <div class="card p-5">
            <p class="text-xs font-medium uppercase tracking-wide text-muted">Base imponible</p>
            <p class="mt-2 text-xl font-bold text-white">{{ euro($totals['subtotal']) }}</p>
        </div>
        <div class="card p-5">
            <p class="text-xs font-medium uppercase tracking-wide text-muted">IVA repercutido</p>
            <p class="mt-2 text-xl font-bold text-white">{{ euro($totals['iva']) }}</p>
        </div>
        <div class="card p-5">
            <p class="text-xs font-medium uppercase tracking-wide text-muted">IRPF retenido</p>
            <p class="mt-2 text-xl font-bold text-white">{{ euro($totals['irpf']) }}</p>
        </div>
        <div class="card p-5">
            <p class="text-xs font-medium uppercase tracking-wide text-muted">Total facturado</p>
            <p class="mt-2 text-xl font-bold text-accent">{{ euro($totals['total']) }}</p>
        </div>
        <div class="card p-5">
            <p class="text-xs font-medium uppercase tracking-wide text-muted">Gastos</p>
            <p class="mt-2 text-xl font-bold text-red-400">{{ euro($totals['expenses']) }}</p>
        </div>
        <div class="card p-5">
            <p class="text-xs font-medium uppercase tracking-wide text-muted">Beneficio real</p>
            <p class="mt-2 text-xl font-bold {{ $totals['profit'] >= 0 ? 'text-green-400' : 'text-red-400' }}">{{ euro($totals['profit']) }}</p>
            <p class="mt-1 text-[11px] text-muted">Base imponible − gastos</p>
        </div>
    </div>

    {{-- Resumen trimestral --}}
    <div class="card mb-6 overflow-hidden">
        <div class="border-b border-edge px-5 py-4">
            <h2 class="text-sm font-semibold text-white">Resumen trimestral {{ $year }} — para modelos 303 (IVA) y 130 (IRPF)</h2>
        </div>
        <table class="table-app">
            <thead>
                <tr>
                    <th>Trimestre</th>
                    <th class="text-right">Base imponible</th>
                    <th class="text-right">IVA repercutido</th>
                    <th class="text-right">IVA soportado</th>
                    <th class="text-right">IVA a pagar (303)</th>
                    <th class="text-right">IRPF retenido (130)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($quarters as $q)
                    <tr>
                        <td class="font-medium text-white">{{ $q['label'] }}</td>
                        <td class="text-right">{{ euro($q['subtotal']) }}</td>
                        <td class="text-right">{{ euro($q['iva']) }}</td>
                        <td class="text-right">{{ euro($q['iva_soportado']) }}</td>
                        <td class="text-right font-semibold text-accent">{{ euro($q['iva'] - $q['iva_soportado']) }}</td>
                        <td class="text-right">{{ euro($q['irpf']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        {{-- Desglose por cliente --}}
        <div class="card overflow-hidden">
            <div class="border-b border-edge px-5 py-4">
                <h2 class="text-sm font-semibold text-white">Desglose por cliente</h2>
            </div>
            <table class="table-app" data-sortable>
                <thead>
                    <tr>
                        <th data-sort="text">Cliente</th>
                        <th data-sort="number" class="text-right">Facturas</th>
                        <th data-sort="number" class="text-right">Base imponible</th>
                        <th data-sort="number" class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($byClient as $row)
                        <tr>
                            <td class="font-medium text-white">{{ $row['client']->name }}</td>
                            <td class="text-right" data-value="{{ $row['count'] }}">{{ $row['count'] }}</td>
                            <td class="text-right" data-value="{{ $row['subtotal'] }}">{{ euro($row['subtotal']) }}</td>
                            <td class="text-right font-medium" data-value="{{ $row['total'] }}">{{ euro($row['total']) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-8 text-center text-muted">Sin facturas en el periodo seleccionado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Facturas del periodo --}}
        <div class="card overflow-hidden">
            <div class="border-b border-edge px-5 py-4">
                <h2 class="text-sm font-semibold text-white">Facturas del periodo ({{ $invoices->count() }})</h2>
            </div>
            <table class="table-app" data-sortable>
                <thead>
                    <tr>
                        <th data-sort="text">Número</th>
                        <th data-sort="text">Fecha</th>
                        <th data-sort="number" class="text-right">Total</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoices as $invoice)
                        <tr>
                            <td><a href="{{ route('invoices.show', $invoice) }}" class="font-medium text-accent hover:underline">{{ $invoice->invoice_number }}</a></td>
                            <td class="text-muted" data-value="{{ $invoice->issue_date->format('Y-m-d') }}">{{ $invoice->issue_date->format('d/m/Y') }}</td>
                            <td class="text-right font-medium" data-value="{{ $invoice->total }}">{{ euro($invoice->total) }}</td>
                            <td><x-status-badge :status="$invoice->status" /></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-8 text-center text-muted">Sin facturas en el periodo seleccionado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

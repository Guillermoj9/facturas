@extends('layouts.app')

@section('title', 'Facturas')

@section('content')
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Facturas</h1>
            <p class="mt-1 text-sm text-muted">{{ $invoices->total() }} {{ Str::plural('factura', $invoices->total()) }}</p>
        </div>
        <a href="{{ route('invoices.create') }}" class="btn-primary">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M12 4v16m8-8H4"/></svg>
            Nueva factura
        </a>
    </div>

    <div class="mb-4 flex flex-wrap items-center gap-3">
        <form method="GET" class="flex max-w-md gap-2">
            @if (request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <input class="field" type="search" name="q" value="{{ request('q') }}" placeholder="Buscar por número o cliente…">
            <button type="submit" class="btn-secondary">Buscar</button>
        </form>

        <div class="flex gap-1.5">
            <a href="{{ route('invoices.index', request()->except('status', 'page')) }}"
               class="badge {{ ! request('status') ? 'bg-accent/20 text-accent border border-accent/40' : 'bg-card text-muted border border-edge hover:text-ink' }}">Todas</a>
            @foreach (\App\Models\Invoice::STATUSES as $status)
                <a href="{{ route('invoices.index', [...request()->except('page'), 'status' => $status]) }}"
                   class="badge {{ request('status') === $status ? 'bg-accent/20 text-accent border border-accent/40' : 'bg-card text-muted border border-edge hover:text-ink' }}">{{ ucfirst($status) }}</a>
            @endforeach
        </div>
    </div>

    <div class="card overflow-hidden">
        <table class="table-app" data-sortable>
            <thead>
                <tr>
                    <th data-sort="text">Número</th>
                    <th data-sort="text">Cliente</th>
                    <th data-sort="text">Emisión</th>
                    <th data-sort="text">Vencimiento</th>
                    <th data-sort="number" class="text-right">Total</th>
                    <th>Estado</th>
                    <th class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($invoices as $invoice)
                    <tr>
                        <td data-value="{{ $invoice->invoice_number }}">
                            <a href="{{ route('invoices.show', $invoice) }}" class="font-medium text-accent hover:underline">{{ $invoice->invoice_number }}</a>
                        </td>
                        <td class="text-muted">{{ $invoice->client->name }}</td>
                        <td class="text-muted" data-value="{{ $invoice->issue_date->format('Y-m-d') }}">{{ $invoice->issue_date->format('d/m/Y') }}</td>
                        <td class="{{ $invoice->status === 'vencida' ? 'text-red-400' : 'text-muted' }}" data-value="{{ $invoice->due_date?->format('Y-m-d') }}">
                            {{ $invoice->due_date?->format('d/m/Y') ?? '—' }}
                        </td>
                        <td class="text-right font-medium" data-value="{{ $invoice->total }}">{{ euro($invoice->total) }}</td>
                        <td><x-status-badge :status="$invoice->status" /></td>
                        <td class="text-right">
                            <div class="flex items-center justify-end gap-2 text-xs">
                                @if ($invoice->status !== 'pagada')
                                    <form method="POST" action="{{ route('invoices.status', $invoice) }}">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="pagada">
                                        <button type="submit" class="text-green-400 hover:underline" title="Marcar como pagada hoy">✓ Cobrada</button>
                                    </form>
                                @endif
                                <a href="{{ route('invoices.pdf', $invoice) }}" target="_blank" class="text-accent hover:underline">PDF</a>
                                @if ($invoice->isEditable())
                                    <a href="{{ route('invoices.edit', $invoice) }}" class="text-accent hover:underline">Editar</a>
                                @endif
                                <form method="POST" action="{{ route('invoices.duplicate', $invoice) }}">
                                    @csrf
                                    <button type="submit" class="text-muted hover:text-ink hover:underline" title="Crear copia con nuevo número">Duplicar</button>
                                </form>
                                @if ($invoice->status === 'borrador')
                                    <form method="POST" action="{{ route('invoices.destroy', $invoice) }}"
                                          onsubmit="return confirm('¿Eliminar el borrador {{ $invoice->invoice_number }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:underline">Eliminar</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-10 text-center text-muted">
                        No hay facturas{{ request('status') ? ' con estado «' . request('status') . '»' : '' }}.
                        <a href="{{ route('invoices.create') }}" class="text-accent hover:underline">Crear factura</a>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $invoices->links() }}</div>
@endsection

@extends('layouts.app')

@section('title', 'Factura ' . $invoice->invoice_number)

@section('content')
    <div class="mb-8 flex flex-wrap items-start justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-white">Factura {{ $invoice->invoice_number }}</h1>
                <x-status-badge :status="$invoice->status" />
            </div>
            <p class="mt-1 text-sm text-muted">
                Emitida el {{ $invoice->issue_date->format('d/m/Y') }}
                @if ($invoice->due_date) · Vence el {{ $invoice->due_date->format('d/m/Y') }} @endif
                @if ($invoice->paid_at) · <span class="text-green-400">Pagada el {{ $invoice->paid_at->format('d/m/Y') }}</span> @endif
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            @if ($invoice->status === 'borrador')
                <form method="POST" action="{{ route('invoices.status', $invoice) }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="enviada">
                    <button type="submit" class="btn-secondary">Marcar como enviada</button>
                </form>
            @endif
            @if ($invoice->status !== 'pagada')
                <form method="POST" action="{{ route('invoices.status', $invoice) }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="pagada">
                    <button type="submit" class="btn bg-green-600/20 border border-green-500/40 text-green-400 hover:bg-green-600/35">✓ Marcar como pagada</button>
                </form>
            @endif
            <a href="{{ route('invoices.pdf', $invoice) }}" target="_blank" class="btn-secondary">Ver PDF</a>
            <a href="{{ route('invoices.pdf', ['invoice' => $invoice, 'download' => 1]) }}" class="btn-primary">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Descargar PDF
            </a>
            @if ($invoice->isEditable())
                <a href="{{ route('invoices.edit', $invoice) }}" class="btn-secondary">Editar</a>
            @endif
            <form method="POST" action="{{ route('invoices.duplicate', $invoice) }}">
                @csrf
                <button type="submit" class="btn-secondary">Duplicar</button>
            </form>
            @if ($invoice->status === 'borrador')
                <form method="POST" action="{{ route('invoices.destroy', $invoice) }}"
                      onsubmit="return confirm('¿Eliminar el borrador {{ $invoice->invoice_number }}?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-danger">Eliminar</button>
                </form>
            @endif
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="space-y-5 xl:col-span-2">
            <div class="card grid gap-6 p-6 md:grid-cols-2">
                <div>
                    <p class="label">Emisor</p>
                    <p class="font-semibold text-white">{{ $setting->company_name }}</p>
                    <p class="text-sm text-muted">{{ $setting->nif }}</p>
                    <p class="text-sm text-muted">{{ $setting->address }}</p>
                    <p class="text-sm text-muted">{{ $setting->postal_code }} {{ $setting->city }} ({{ $setting->province }})</p>
                </div>
                <div>
                    <p class="label">Cliente</p>
                    <p class="font-semibold text-white">{{ $invoice->client->name }}</p>
                    <p class="text-sm text-muted">{{ $invoice->client->nif_cif }}</p>
                    <p class="text-sm text-muted">{{ $invoice->client->address }}</p>
                    <p class="text-sm text-muted">{{ $invoice->client->postal_code }} {{ $invoice->client->city }} ({{ $invoice->client->province }})</p>
                </div>
            </div>

            <div class="card overflow-hidden">
                <table class="table-app">
                    <thead>
                        <tr>
                            <th>Descripción</th>
                            <th class="text-right">Cantidad</th>
                            <th class="text-right">Precio ud.</th>
                            <th class="text-right">Importe</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invoice->items as $item)
                            <tr>
                                <td>{{ $item->description }}</td>
                                <td class="text-right text-muted">{{ rtrim(rtrim(number_format((float) $item->quantity, 2, ',', '.'), '0'), ',') }}</td>
                                <td class="text-right text-muted">{{ euro($item->unit_price) }}</td>
                                <td class="text-right font-medium">{{ euro($item->total) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($invoice->notes)
                <div class="card p-5">
                    <p class="label">Notas</p>
                    <p class="text-sm text-muted">{{ $invoice->notes }}</p>
                </div>
            @endif
        </div>

        <div class="space-y-5">
            <div class="card p-5">
                <h2 class="mb-4 text-sm font-semibold text-white">Totales</h2>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-muted">Base imponible</dt>
                        <dd class="font-medium">{{ euro($invoice->subtotal) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-muted">IVA ({{ rtrim(rtrim(number_format((float) $invoice->iva_percentage, 2, ',', ''), '0'), ',') }}%)</dt>
                        <dd class="font-medium">{{ euro($invoice->iva_amount) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-muted">IRPF (−{{ rtrim(rtrim(number_format((float) $invoice->irpf_percentage, 2, ',', ''), '0'), ',') }}%)</dt>
                        <dd class="font-medium text-red-400">−{{ euro($invoice->irpf_amount) }}</dd>
                    </div>
                    <div class="flex justify-between border-t border-edge pt-3 text-base">
                        <dt class="font-semibold text-white">TOTAL</dt>
                        <dd class="font-bold text-accent">{{ euro($invoice->total) }}</dd>
                    </div>
                </dl>
            </div>

            <div class="card p-5">
                <h2 class="mb-3 text-sm font-semibold text-white">Pago</h2>
                <p class="text-sm text-muted">{{ $invoice->payment_method ?? '—' }}</p>
                @if ($setting->iban)
                    <p class="mt-2 font-mono text-sm text-ink">{{ $setting->iban }}</p>
                @endif
            </div>
        </div>
    </div>
@endsection

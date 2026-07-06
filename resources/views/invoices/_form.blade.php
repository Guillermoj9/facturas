@php
    $invoice = $invoice ?? null;
    $ivaValue = old('iva_percentage', $invoice?->iva_percentage ?? $ivaDefault ?? 21);
    $irpfValue = old('irpf_percentage', $invoice?->irpf_percentage ?? $irpfDefault ?? 15);
    $taxNoteValue = old('tax_note', $invoice?->tax_note);
    $ivaPreset = in_array((float) $ivaValue, [21.0, 10.0, 4.0, 0.0], true) ? (string) (float) $ivaValue : 'custom';
    $irpfPreset = in_array((float) $irpfValue, [15.0, 7.0, 0.0], true) ? (string) (float) $irpfValue : 'custom';
    $oldItems = old('items', $invoice?->items?->map(fn ($i) => [
        'description' => $i->description,
        'quantity' => (float) $i->quantity,
        'unit_price' => (float) $i->unit_price,
    ])->all() ?? []);
@endphp

<div class="grid gap-6 xl:grid-cols-3">
    <div class="space-y-5 xl:col-span-2">
        {{-- Cliente --}}
        <div class="card space-y-4 p-5">
            <h2 class="text-sm font-semibold text-white">Cliente</h2>
            <div>
                <label class="label" for="client_id">Selecciona un cliente *</label>
                <select class="field" id="client_id" name="client_id" required>
                    <option value="" disabled @selected(! old('client_id', $invoice?->client_id))>— Elegir cliente —</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}" @selected(old('client_id', $invoice?->client_id) == $client->id)>
                            {{ $client->name }} ({{ $client->nif_cif }})
                        </option>
                    @endforeach
                    <option value="new" @selected(old('client_id') === 'new')>➕ Crear cliente nuevo…</option>
                </select>
            </div>

            <div id="new-client-fields" class="hidden space-y-4 rounded-lg border border-dashed border-edge p-4">
                <p class="text-xs font-medium uppercase tracking-wide text-muted">Datos del nuevo cliente</p>
                <div class="grid gap-4 md:grid-cols-2">
                    <input class="field" type="text" name="new_client[name]" value="{{ old('new_client.name') }}" placeholder="Nombre o razón social *">
                    <input class="field" type="text" name="new_client[nif_cif]" value="{{ old('new_client.nif_cif') }}" placeholder="NIF/CIF *">
                </div>
                <input class="field" type="text" name="new_client[address]" value="{{ old('new_client.address') }}" placeholder="Dirección *">
                <div class="grid gap-4 md:grid-cols-3">
                    <input class="field" type="text" name="new_client[postal_code]" value="{{ old('new_client.postal_code') }}" placeholder="Código postal *">
                    <input class="field" type="text" name="new_client[city]" value="{{ old('new_client.city') }}" placeholder="Ciudad *">
                    <input class="field" type="text" name="new_client[province]" value="{{ old('new_client.province') }}" placeholder="Provincia *">
                </div>
                <input class="field" type="email" name="new_client[email]" value="{{ old('new_client.email') }}" placeholder="Email (opcional)">
            </div>
        </div>

        {{-- Conceptos --}}
        <div class="card p-5">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-white">Conceptos</h2>
                <button type="button" id="add-item" class="btn-secondary py-1.5 text-xs">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M12 4v16m8-8H4"/></svg>
                    Añadir línea
                </button>
            </div>

            <div class="mb-2 grid grid-cols-[1fr_90px_120px_110px_36px] gap-2 px-1 text-xs font-medium uppercase tracking-wide text-muted">
                <span>Descripción</span><span>Cantidad</span><span>Precio ud.</span><span class="text-right">Importe</span><span></span>
            </div>

            <div id="item-rows" class="space-y-2">
                @foreach ($oldItems as $index => $item)
                    <div class="grid grid-cols-[1fr_90px_120px_110px_36px] items-center gap-2" data-item-row>
                        <input class="field" type="text" name="items[{{ $index }}][description]" required
                               value="{{ $item['description'] }}" placeholder="Descripción del servicio">
                        <input class="field" type="number" step="0.01" min="0.01" name="items[{{ $index }}][quantity]" required
                               value="{{ $item['quantity'] }}" data-field="quantity">
                        <input class="field" type="number" step="0.01" min="0" name="items[{{ $index }}][unit_price]" required
                               value="{{ $item['unit_price'] }}" data-field="unit_price">
                        <span class="text-right text-sm font-medium" data-field="line-total">0,00 €</span>
                        <button type="button" data-remove-row class="flex h-8 w-8 items-center justify-center rounded-lg text-muted hover:bg-red-500/15 hover:text-red-400" title="Quitar línea">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Notas y forma de pago --}}
        <div class="card space-y-4 p-5">
            <div>
                <label class="label" for="payment_method">Forma de pago</label>
                <input class="field" type="text" id="payment_method" name="payment_method"
                       value="{{ old('payment_method', $invoice?->payment_method ?? 'Transferencia bancaria') }}"
                       placeholder="Transferencia bancaria, Bizum…">
            </div>
            <div>
                <label class="label" for="notes">Notas (aparecen al pie de la factura)</label>
                <textarea class="field" id="notes" name="notes" rows="2">{{ old('notes', $invoice?->notes) }}</textarea>
            </div>
            <div>
                <label class="label" for="tax_note">Nota fiscal</label>
                <input class="field" type="text" id="tax_note" name="tax_note"
                       value="{{ $taxNoteValue }}"
                       placeholder="Ej.: Operación no sujeta a IVA por reglas de localización">
            </div>
        </div>
    </div>

    {{-- Columna derecha: fechas, impuestos y totales --}}
    <div class="space-y-5">
        <div class="card space-y-4 p-5">
            <h2 class="text-sm font-semibold text-white">Datos de la factura</h2>
            <div>
                <label class="label">Número</label>
                <p class="rounded-lg border border-edge bg-base px-3 py-2 text-sm font-semibold text-accent">
                    {{ $invoice?->invoice_number ?? $nextNumber }}
                </p>
            </div>
            <div>
                <label class="label" for="issue_date">Fecha de emisión *</label>
                <input class="field" type="date" id="issue_date" name="issue_date" required
                       value="{{ old('issue_date', ($invoice?->issue_date ?? today())->format('Y-m-d')) }}">
            </div>
            <div>
                <label class="label" for="due_date">Fecha de vencimiento</label>
                <input class="field" type="date" id="due_date" name="due_date"
                       value="{{ old('due_date', $invoice?->due_date?->format('Y-m-d')) }}">
            </div>
            <div class="space-y-4">
                <div>
                    <label class="label" for="iva_preset">IVA</label>
                    <select class="field mb-2" id="iva_preset" data-tax-preset="iva_percentage">
                        <option value="21" @selected($ivaPreset === '21')>21 % — general</option>
                        <option value="10" @selected($ivaPreset === '10')>10 % — reducido</option>
                        <option value="4" @selected($ivaPreset === '4')>4 % — superreducido</option>
                        <option value="0" @selected($ivaPreset === '0')>0 % — exento / no sujeto</option>
                        <option value="custom" @selected($ivaPreset === 'custom')>Otro porcentaje</option>
                    </select>
                    <input class="field" type="number" step="0.01" min="0" max="100" id="iva_percentage" name="iva_percentage" required
                           value="{{ $ivaValue }}" data-tax-input="iva_percentage">
                </div>
                <label class="flex items-start gap-3 rounded-lg border border-edge bg-base p-3 text-sm text-muted">
                    <input type="checkbox" class="mt-1 h-4 w-4 rounded border-edge bg-base accent-accent"
                           data-canary-tax @checked((float) $ivaValue === 0.0 && str_contains(strtolower((string) $taxNoteValue), 'canarias'))>
                    <span>
                        <span class="block font-medium text-ink">Factura a Canarias / sin IVA</span>
                        <span class="block text-xs">Pone el IVA al 0 % y añade una nota fiscal editable.</span>
                    </span>
                </label>
                <div>
                    <label class="label" for="irpf_preset">IRPF</label>
                    <select class="field mb-2" id="irpf_preset" data-tax-preset="irpf_percentage">
                        <option value="15" @selected($irpfPreset === '15')>15 % — general profesional</option>
                        <option value="7" @selected($irpfPreset === '7')>7 % — nuevos autónomos</option>
                        <option value="0" @selected($irpfPreset === '0')>0 % — sin retención</option>
                        <option value="custom" @selected($irpfPreset === 'custom')>Otro porcentaje</option>
                    </select>
                    <input class="field" type="number" step="0.01" min="0" max="100" id="irpf_percentage" name="irpf_percentage" required
                           value="{{ $irpfValue }}" data-tax-input="irpf_percentage">
                </div>
            </div>
        </div>

        <div class="card p-5">
            <h2 class="mb-4 text-sm font-semibold text-white">Totales</h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <dt class="text-muted">Base imponible</dt>
                    <dd class="font-medium" data-total="subtotal">0,00 €</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-muted" data-label="iva">IVA ({{ $ivaValue }}%)</dt>
                    <dd class="font-medium" data-total="iva">0,00 €</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-muted" data-label="irpf">IRPF (−{{ $irpfValue }}%)</dt>
                    <dd class="font-medium text-red-400" data-total="irpf">0,00 €</dd>
                </div>
                <div class="flex justify-between border-t border-edge pt-3 text-base">
                    <dt class="font-semibold text-white">TOTAL</dt>
                    <dd class="font-bold text-accent" data-total="total">0,00 €</dd>
                </div>
            </dl>
        </div>

        <div class="flex flex-col gap-2">
            <button type="submit" class="btn-primary w-full justify-center py-3">
                {{ $invoice ? 'Guardar cambios' : 'Crear factura' }}
            </button>
            <a href="{{ $invoice ? route('invoices.show', $invoice) : route('invoices.index') }}" class="btn-secondary w-full justify-center">Cancelar</a>
        </div>
    </div>
</div>

<template id="item-row-template">
    <div class="grid grid-cols-[1fr_90px_120px_110px_36px] items-center gap-2" data-item-row>
        <input class="field" type="text" name="items[x][description]" required placeholder="Descripción del servicio">
        <input class="field" type="number" step="0.01" min="0.01" name="items[x][quantity]" value="1" required data-field="quantity">
        <input class="field" type="number" step="0.01" min="0" name="items[x][unit_price]" value="0" required data-field="unit_price">
        <span class="text-right text-sm font-medium" data-field="line-total">0,00 €</span>
        <button type="button" data-remove-row class="flex h-8 w-8 items-center justify-center rounded-lg text-muted hover:bg-red-500/15 hover:text-red-400" title="Quitar línea">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
</template>

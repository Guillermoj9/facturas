@php
    $setting = $setting ?? null;
    $ivaDefaultValue = old('iva_default', $setting?->iva_default ?? 21);
    $irpfDefaultValue = old('irpf_default', $setting?->irpf_default ?? 15);
    $ivaDefaultPreset = in_array((float) $ivaDefaultValue, [21.0, 10.0, 4.0, 0.0], true) ? (string) (float) $ivaDefaultValue : 'custom';
    $irpfDefaultPreset = in_array((float) $irpfDefaultValue, [15.0, 7.0, 0.0], true) ? (string) (float) $irpfDefaultValue : 'custom';
@endphp

<div class="grid gap-4 md:grid-cols-2">
    <div>
        <label class="label" for="company_name">Nombre completo *</label>
        <input class="field" type="text" id="company_name" name="company_name" required
               value="{{ old('company_name', $setting?->company_name) }}" placeholder="María García López">
    </div>
    <div>
        <label class="label" for="nif">DNI / NIF *</label>
        <input class="field" type="text" id="nif" name="nif" required
               value="{{ old('nif', $setting?->nif) }}" placeholder="12345678Z">
    </div>
</div>

<div>
    <label class="label" for="address">Dirección fiscal *</label>
    <input class="field" type="text" id="address" name="address" required
           value="{{ old('address', $setting?->address) }}" placeholder="Calle Mayor 15, 3ºB">
</div>

<div class="grid gap-4 md:grid-cols-3">
    <div>
        <label class="label" for="postal_code">Código postal *</label>
        <input class="field" type="text" id="postal_code" name="postal_code" required
               value="{{ old('postal_code', $setting?->postal_code) }}" placeholder="28013">
    </div>
    <div>
        <label class="label" for="city">Ciudad *</label>
        <input class="field" type="text" id="city" name="city" required
               value="{{ old('city', $setting?->city) }}" placeholder="Madrid">
    </div>
    <div>
        <label class="label" for="province">Provincia *</label>
        <input class="field" type="text" id="province" name="province" required
               value="{{ old('province', $setting?->province) }}" placeholder="Madrid">
    </div>
</div>

<div class="grid gap-4 md:grid-cols-2">
    <div>
        <label class="label" for="phone">Teléfono</label>
        <input class="field" type="text" id="phone" name="phone"
               value="{{ old('phone', $setting?->phone) }}" placeholder="600 123 456">
    </div>
    <div>
        <label class="label" for="email">Email</label>
        <input class="field" type="email" id="email" name="email"
               value="{{ old('email', $setting?->email) }}" placeholder="tu@email.es">
    </div>
</div>

<div>
    <label class="label" for="iban">IBAN (aparece en las facturas)</label>
    <input class="field" type="text" id="iban" name="iban"
           value="{{ old('iban', $setting?->iban) }}" placeholder="ES00 0000 0000 0000 0000 0000">
</div>

<div class="grid gap-4 md:grid-cols-3">
    <div>
        <label class="label" for="irpf_default_preset">IRPF por defecto *</label>
        <select class="field mb-2" id="irpf_default_preset" data-tax-preset="irpf_default">
            <option value="15" @selected($irpfDefaultPreset === '15')>15 % — general profesional</option>
            <option value="7" @selected($irpfDefaultPreset === '7')>7 % — nuevos autónomos</option>
            <option value="0" @selected($irpfDefaultPreset === '0')>0 % — sin retención</option>
            <option value="custom" @selected($irpfDefaultPreset === 'custom')>Otro porcentaje</option>
        </select>
        <input class="field" type="number" step="0.01" min="0" max="100" id="irpf_default" name="irpf_default" required
               value="{{ $irpfDefaultValue }}" data-tax-input="irpf_default">
    </div>
    <div>
        <label class="label" for="iva_default_preset">IVA por defecto *</label>
        <select class="field mb-2" id="iva_default_preset" data-tax-preset="iva_default">
            <option value="21" @selected($ivaDefaultPreset === '21')>21 % — general</option>
            <option value="10" @selected($ivaDefaultPreset === '10')>10 % — reducido</option>
            <option value="4" @selected($ivaDefaultPreset === '4')>4 % — superreducido</option>
            <option value="0" @selected($ivaDefaultPreset === '0')>0 % — exento / no sujeto</option>
            <option value="custom" @selected($ivaDefaultPreset === 'custom')>Otro porcentaje</option>
        </select>
        <input class="field" type="number" step="0.01" min="0" max="100" id="iva_default" name="iva_default" required
               value="{{ $ivaDefaultValue }}" data-tax-input="iva_default">
    </div>
    <div>
        <label class="label" for="invoice_prefix">Prefijo de facturas</label>
        <input class="field" type="text" id="invoice_prefix" name="invoice_prefix"
               value="{{ old('invoice_prefix', $setting?->invoice_prefix) }}" placeholder="Ej.: FAC-">
        <p class="mt-1 text-xs text-muted">Numeración: {{ old('invoice_prefix', $setting?->invoice_prefix) }}01-{{ now()->year }}, {{ old('invoice_prefix', $setting?->invoice_prefix) }}02-{{ now()->year }}…</p>
    </div>
</div>

<div>
    <label class="label" for="logo">Logo (opcional)</label>
    @if ($setting?->logo_path)
        <div class="mb-2 flex items-center gap-3">
            <img src="{{ asset('storage/' . $setting->logo_path) }}" alt="Logo" class="h-12 rounded bg-white/5 p-1">
            <label class="flex items-center gap-2 text-sm text-muted">
                <input type="checkbox" name="remove_logo" value="1" class="rounded border-edge bg-base"> Quitar logo
            </label>
        </div>
    @endif
    <input class="field file:mr-3 file:rounded file:border-0 file:bg-accent file:px-3 file:py-1 file:text-xs file:text-white"
           type="file" id="logo" name="logo" accept="image/*">
</div>

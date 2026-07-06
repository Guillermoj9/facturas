@php($client = $client ?? null)

<div class="grid gap-4 md:grid-cols-2">
    <div>
        <label class="label" for="name">Nombre o razón social *</label>
        <input class="field" type="text" id="name" name="name" required
               value="{{ old('name', $client?->name) }}" placeholder="Empresa S.L. o nombre y apellidos">
    </div>
    <div>
        <label class="label" for="nif_cif">NIF / CIF *</label>
        <input class="field" type="text" id="nif_cif" name="nif_cif" required
               value="{{ old('nif_cif', $client?->nif_cif) }}" placeholder="B12345678">
    </div>
</div>

<div>
    <label class="label" for="address">Dirección *</label>
    <input class="field" type="text" id="address" name="address" required
           value="{{ old('address', $client?->address) }}">
</div>

<div class="grid gap-4 md:grid-cols-3">
    <div>
        <label class="label" for="postal_code">Código postal *</label>
        <input class="field" type="text" id="postal_code" name="postal_code" required
               value="{{ old('postal_code', $client?->postal_code) }}">
    </div>
    <div>
        <label class="label" for="city">Ciudad *</label>
        <input class="field" type="text" id="city" name="city" required
               value="{{ old('city', $client?->city) }}">
    </div>
    <div>
        <label class="label" for="province">Provincia *</label>
        <input class="field" type="text" id="province" name="province" required
               value="{{ old('province', $client?->province) }}">
    </div>
</div>

<div class="grid gap-4 md:grid-cols-2">
    <div>
        <label class="label" for="email">Email</label>
        <input class="field" type="email" id="email" name="email"
               value="{{ old('email', $client?->email) }}">
    </div>
    <div>
        <label class="label" for="phone">Teléfono</label>
        <input class="field" type="text" id="phone" name="phone"
               value="{{ old('phone', $client?->phone) }}">
    </div>
</div>

<div>
    <label class="label" for="notes">Notas</label>
    <textarea class="field" id="notes" name="notes" rows="3" placeholder="Notas internas sobre este cliente…">{{ old('notes', $client?->notes) }}</textarea>
</div>

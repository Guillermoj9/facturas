@php($expense = $expense ?? null)

<div>
    <label class="label" for="description">Descripción *</label>
    <input class="field" type="text" id="description" name="description" required
           value="{{ old('description', $expense?->description) }}" placeholder="Hosting anual, licencia de software…">
</div>

<div class="grid gap-4 md:grid-cols-3">
    <div>
        <label class="label" for="amount">Importe (sin IVA) € *</label>
        <input class="field" type="number" step="0.01" min="0" id="amount" name="amount" required
               value="{{ old('amount', $expense?->amount) }}">
    </div>
    <div>
        <label class="label" for="iva_amount">IVA soportado € *</label>
        <input class="field" type="number" step="0.01" min="0" id="iva_amount" name="iva_amount" required
               value="{{ old('iva_amount', $expense?->iva_amount ?? 0) }}">
    </div>
    <div>
        <label class="label" for="date">Fecha *</label>
        <input class="field" type="date" id="date" name="date" required
               value="{{ old('date', ($expense?->date ?? today())->format('Y-m-d')) }}">
    </div>
</div>

<div class="grid gap-4 md:grid-cols-2">
    <div>
        <label class="label" for="category">Categoría *</label>
        <select class="field" id="category" name="category" required>
            @foreach (\App\Models\Expense::CATEGORIES as $category)
                <option value="{{ $category }}" @selected(old('category', $expense?->category) === $category)>{{ ucfirst($category) }}</option>
            @endforeach
        </select>
    </div>
    <div class="flex items-end pb-2">
        <label class="flex items-center gap-2 text-sm">
            <input type="hidden" name="deductible" value="0">
            <input type="checkbox" name="deductible" value="1" class="h-4 w-4 rounded border-edge bg-base accent-accent"
                   @checked(old('deductible', $expense?->deductible ?? true))>
            Gasto deducible (su IVA cuenta para el modelo 303)
        </label>
    </div>
</div>

<div>
    <label class="label" for="receipt">Ticket / factura (foto, opcional)</label>
    @if ($expense?->receipt_path)
        <p class="mb-2 text-sm">
            <a href="{{ asset('storage/' . $expense->receipt_path) }}" target="_blank" class="text-accent hover:underline">Ver ticket actual</a>
        </p>
    @endif
    <input class="field file:mr-3 file:rounded file:border-0 file:bg-accent file:px-3 file:py-1 file:text-xs file:text-white"
           type="file" id="receipt" name="receipt" accept="image/*">
</div>

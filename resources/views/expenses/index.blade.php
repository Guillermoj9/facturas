@extends('layouts.app')

@section('title', 'Gastos')

@section('content')
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Gastos</h1>
            <p class="mt-1 text-sm text-muted">
                Este año: {{ euro($yearTotals['amount']) }} en gastos · {{ euro($yearTotals['iva']) }} de IVA deducible
            </p>
        </div>
        <a href="{{ route('expenses.create') }}" class="btn-primary">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M12 4v16m8-8H4"/></svg>
            Nuevo gasto
        </a>
    </div>

    <div class="mb-4 flex flex-wrap items-center gap-3">
        <form method="GET" class="flex max-w-md gap-2">
            @if (request('category'))
                <input type="hidden" name="category" value="{{ request('category') }}">
            @endif
            <input class="field" type="search" name="q" value="{{ request('q') }}" placeholder="Buscar gasto…">
            <button type="submit" class="btn-secondary">Buscar</button>
        </form>

        <div class="flex flex-wrap gap-1.5">
            <a href="{{ route('expenses.index', request()->except('category', 'page')) }}"
               class="badge {{ ! request('category') ? 'bg-accent/20 text-accent border border-accent/40' : 'bg-card text-muted border border-edge hover:text-ink' }}">Todas</a>
            @foreach (\App\Models\Expense::CATEGORIES as $category)
                <a href="{{ route('expenses.index', [...request()->except('page'), 'category' => $category]) }}"
                   class="badge {{ request('category') === $category ? 'bg-accent/20 text-accent border border-accent/40' : 'bg-card text-muted border border-edge hover:text-ink' }}">{{ ucfirst($category) }}</a>
            @endforeach
        </div>
    </div>

    <div class="card overflow-hidden">
        <table class="table-app" data-sortable>
            <thead>
                <tr>
                    <th data-sort="text">Descripción</th>
                    <th data-sort="text">Categoría</th>
                    <th data-sort="text">Fecha</th>
                    <th data-sort="number" class="text-right">Importe</th>
                    <th data-sort="number" class="text-right">IVA</th>
                    <th>Deducible</th>
                    <th class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($expenses as $expense)
                    <tr>
                        <td class="font-medium text-white">
                            {{ $expense->description }}
                            @if ($expense->receipt_path)
                                <a href="{{ asset('storage/' . $expense->receipt_path) }}" target="_blank" title="Ver ticket" class="ml-1 text-accent">📎</a>
                            @endif
                        </td>
                        <td><span class="badge border border-edge bg-base text-muted">{{ ucfirst($expense->category) }}</span></td>
                        <td class="text-muted" data-value="{{ $expense->date->format('Y-m-d') }}">{{ $expense->date->format('d/m/Y') }}</td>
                        <td class="text-right font-medium" data-value="{{ $expense->amount }}">{{ euro($expense->amount) }}</td>
                        <td class="text-right text-muted" data-value="{{ $expense->iva_amount }}">{{ euro($expense->iva_amount) }}</td>
                        <td>{{ $expense->deductible ? '✅' : '—' }}</td>
                        <td class="text-right">
                            <div class="flex items-center justify-end gap-2 text-xs">
                                <a href="{{ route('expenses.edit', $expense) }}" class="text-accent hover:underline">Editar</a>
                                <form method="POST" action="{{ route('expenses.destroy', $expense) }}"
                                      onsubmit="return confirm('¿Eliminar el gasto «{{ $expense->description }}»?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:underline">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-10 text-center text-muted">
                        No hay gastos registrados. <a href="{{ route('expenses.create') }}" class="text-accent hover:underline">Registra el primero</a>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $expenses->links() }}</div>
@endsection

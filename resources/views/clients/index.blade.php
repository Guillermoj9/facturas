@extends('layouts.app')

@section('title', 'Clientes')

@section('content')
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Clientes</h1>
            <p class="mt-1 text-sm text-muted">{{ $clients->total() }} {{ Str::plural('cliente', $clients->total()) }}</p>
        </div>
        <a href="{{ route('clients.create') }}" class="btn-primary">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M12 4v16m8-8H4"/></svg>
            Nuevo cliente
        </a>
    </div>

    <form method="GET" class="mb-4 flex max-w-md gap-2">
        <input class="field" type="search" name="q" value="{{ request('q') }}" placeholder="Buscar por nombre o NIF…">
        <button type="submit" class="btn-secondary">Buscar</button>
        @if (request('q'))
            <a href="{{ route('clients.index') }}" class="btn-secondary">Limpiar</a>
        @endif
    </form>

    <div class="card overflow-hidden">
        <table class="table-app" data-sortable>
            <thead>
                <tr>
                    <th data-sort="text">Nombre</th>
                    <th data-sort="text">NIF/CIF</th>
                    <th data-sort="text">Ciudad</th>
                    <th>Email</th>
                    <th data-sort="number" class="text-right">Facturas</th>
                    <th class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($clients as $client)
                    <tr>
                        <td class="font-medium text-white">{{ $client->name }}</td>
                        <td class="text-muted">{{ $client->nif_cif }}</td>
                        <td class="text-muted">{{ $client->city }}</td>
                        <td class="text-muted">{{ $client->email ?? '—' }}</td>
                        <td class="text-right" data-value="{{ $client->invoices_count }}">{{ $client->invoices_count }}</td>
                        <td class="text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('clients.edit', $client) }}" class="text-accent hover:underline">Editar</a>
                                @if ($client->invoices_count === 0)
                                    <form method="POST" action="{{ route('clients.destroy', $client) }}"
                                          onsubmit="return confirm('¿Eliminar el cliente «{{ $client->name }}»?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:underline">Eliminar</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-10 text-center text-muted">
                        @if (request('q'))
                            Sin resultados para «{{ request('q') }}».
                        @else
                            Todavía no hay clientes. <a href="{{ route('clients.create') }}" class="text-accent hover:underline">Crea el primero</a>
                        @endif
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $clients->links() }}</div>
@endsection

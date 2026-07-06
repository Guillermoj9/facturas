@extends('layouts.app')

@section('title', 'Editar cliente')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-white">Editar cliente</h1>
        <p class="mt-1 text-sm text-muted">{{ $client->name }}</p>
    </div>

    <form method="POST" action="{{ route('clients.update', $client) }}" class="card max-w-2xl space-y-5 p-6">
        @csrf
        @method('PUT')
        @include('clients._form')

        <div class="flex justify-end gap-3">
            <a href="{{ route('clients.index') }}" class="btn-secondary">Cancelar</a>
            <button type="submit" class="btn-primary">Guardar cambios</button>
        </div>
    </form>
@endsection

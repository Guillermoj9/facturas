@extends('layouts.app')

@section('title', 'Editar gasto')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-white">Editar gasto</h1>
        <p class="mt-1 text-sm text-muted">{{ $expense->description }}</p>
    </div>

    <form method="POST" action="{{ route('expenses.update', $expense) }}" enctype="multipart/form-data" class="card max-w-2xl space-y-5 p-6">
        @csrf
        @method('PUT')
        @include('expenses._form')

        <div class="flex justify-end gap-3">
            <a href="{{ route('expenses.index') }}" class="btn-secondary">Cancelar</a>
            <button type="submit" class="btn-primary">Guardar cambios</button>
        </div>
    </form>
@endsection

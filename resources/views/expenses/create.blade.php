@extends('layouts.app')

@section('title', 'Nuevo gasto')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-white">Nuevo gasto</h1>
    </div>

    <form method="POST" action="{{ route('expenses.store') }}" enctype="multipart/form-data" class="card max-w-2xl space-y-5 p-6">
        @csrf
        @include('expenses._form')

        <div class="flex justify-end gap-3">
            <a href="{{ route('expenses.index') }}" class="btn-secondary">Cancelar</a>
            <button type="submit" class="btn-primary">Registrar gasto</button>
        </div>
    </form>
@endsection

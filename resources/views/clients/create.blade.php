@extends('layouts.app')

@section('title', 'Nuevo cliente')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-white">Nuevo cliente</h1>
    </div>

    <form method="POST" action="{{ route('clients.store') }}" class="card max-w-2xl space-y-5 p-6">
        @csrf
        @include('clients._form')

        <div class="flex justify-end gap-3">
            <a href="{{ route('clients.index') }}" class="btn-secondary">Cancelar</a>
            <button type="submit" class="btn-primary">Crear cliente</button>
        </div>
    </form>
@endsection

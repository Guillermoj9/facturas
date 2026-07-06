@extends('layouts.app')

@section('title', 'Configuración')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-white">Configuración</h1>
        <p class="mt-1 text-sm text-muted">Tus datos fiscales y preferencias de facturación.</p>
    </div>

    <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="card max-w-2xl space-y-5 p-6">
        @csrf
        @method('PUT')
        @include('settings._fields')

        <div class="flex justify-end">
            <button type="submit" class="btn-primary">Guardar cambios</button>
        </div>
    </form>
@endsection

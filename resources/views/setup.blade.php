<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración inicial — Facturador</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen">
    <div class="mx-auto max-w-2xl px-4 py-12">
        <div class="mb-8 text-center">
            <div class="mb-4 inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-accent/15">
                <svg class="h-8 w-8 text-accent" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-white">¡Bienvenido/a al Facturador!</h1>
            <p class="mt-2 text-sm text-muted">Antes de empezar, necesitamos tus datos fiscales. Aparecerán en todas tus facturas y podrás cambiarlos cuando quieras desde Configuración.</p>
        </div>

        @if ($errors->any())
            <div class="mb-6 rounded-lg border border-red-500/40 bg-red-500/10 px-4 py-3 text-sm text-red-400">
                <p class="mb-1 font-semibold">Revisa el formulario:</p>
                <ul class="list-inside list-disc space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('setup.store') }}" enctype="multipart/form-data" class="card space-y-5 p-6">
            @csrf
            @include('settings._fields')

            <button type="submit" class="btn-primary w-full justify-center py-3 text-base">
                Guardar y empezar a facturar
            </button>
        </form>
    </div>
</body>
</html>

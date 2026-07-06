@props(['status'])

@php
    $classes = match ($status) {
        'borrador' => 'bg-gray-500/15 text-gray-400 border border-gray-500/30',
        'enviada' => 'bg-blue-500/15 text-blue-400 border border-blue-500/30',
        'pagada' => 'bg-green-500/15 text-green-400 border border-green-500/30',
        'vencida' => 'bg-red-500/15 text-red-400 border border-red-500/30',
        default => 'bg-gray-500/15 text-gray-400',
    };
@endphp

<span {{ $attributes->merge(['class' => "badge $classes"]) }}>{{ ucfirst($status) }}</span>

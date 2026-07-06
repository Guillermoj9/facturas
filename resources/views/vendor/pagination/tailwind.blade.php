@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Paginación" class="flex items-center justify-between text-sm">
        <p class="text-muted">
            Mostrando {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} de {{ $paginator->total() }}
        </p>

        <div class="flex items-center gap-1">
            @if ($paginator->onFirstPage())
                <span class="cursor-default rounded-lg border border-edge px-3 py-1.5 text-muted/50">‹ Anterior</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="rounded-lg border border-edge px-3 py-1.5 text-ink hover:border-accent/60">‹ Anterior</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="px-2 text-muted">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="rounded-lg bg-accent px-3 py-1.5 font-medium text-white">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="rounded-lg border border-edge px-3 py-1.5 text-ink hover:border-accent/60">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="rounded-lg border border-edge px-3 py-1.5 text-ink hover:border-accent/60">Siguiente ›</a>
            @else
                <span class="cursor-default rounded-lg border border-edge px-3 py-1.5 text-muted/50">Siguiente ›</span>
            @endif
        </div>
    </nav>
@endif

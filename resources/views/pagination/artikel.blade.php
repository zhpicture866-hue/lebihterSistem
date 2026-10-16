@if ($paginator->hasPages())
<nav class="pg" role="navigation" aria-label="Paginasi">
    @if (! $paginator->onFirstPage())
        <a class="pg-i" href="{{ $paginator->previousPageUrl() }}" rel="prev">&laquo; Prev</a>
    @endif

    @foreach ($elements as $element)
        @if (is_string($element))
            <span class="pg-i pg-dots">{{ $element }}</span>
        @endif
        @if (is_array($element))
            @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                    <span class="pg-i pg-on" aria-current="page">{{ $page }}</span>
                @else
                    <a class="pg-i" href="{{ $url }}">{{ $page }}</a>
                @endif
            @endforeach
        @endif
    @endforeach

    @if ($paginator->hasMorePages())
        <a class="pg-i" href="{{ $paginator->nextPageUrl() }}" rel="next">Next &raquo;</a>
    @endif
</nav>
@endif
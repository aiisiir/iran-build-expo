@if ($paginator->hasPages())
<nav role="navigation" aria-label="{{ __('site.pagination') }}">
    <ul class="pagination">
        @if ($paginator->onFirstPage())
            <li aria-disabled="true"><span>‹</span></li>
        @else
            <li><a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('site.prev') }}">‹</a></li>
        @endif
        @foreach ($elements as $element)
            @if (is_string($element))
                <li aria-disabled="true"><span>{{ $element }}</span></li>
            @else
                @foreach ($element as $page => $url)
                    <li @class(['active' => $page == $paginator->currentPage()])>@if ($page == $paginator->currentPage())<span aria-current="page">{{ $page }}</span>@else<a href="{{ $url }}">{{ $page }}</a>@endif</li>
                @endforeach
            @endif
        @endforeach
        @if ($paginator->hasMorePages())
            <li><a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('site.next') }}">›</a></li>
        @else
            <li aria-disabled="true"><span>›</span></li>
        @endif
    </ul>
</nav>
@endif

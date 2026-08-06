@if ($paginator->hasPages())
    <div class="compact-pagination">
        <div class="compact-pagination__summary">
            Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} records
        </div>

        <nav class="compact-pagination__nav" aria-label="{{ __('Pagination Navigation') }}">
            @if ($paginator->onFirstPage())
                <span class="compact-pagination__item is-disabled" aria-disabled="true" aria-label="{{ __('Previous') }}">
                    Previous
                </span>
            @else
                <a class="compact-pagination__item" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('Previous') }}">
                    Previous
                </a>
            @endif

            @php
                $current = $paginator->currentPage();
                $lastPage = $paginator->lastPage();
                $pages = [];

                for ($page = max(1, $current - 1); $page <= min($lastPage, $current + 1); $page++) {
                    $pages[] = $page;
                }

                if (! in_array(1, $pages, true)) {
                    array_unshift($pages, 1);
                }

                if (! in_array($lastPage, $pages, true)) {
                    $pages[] = $lastPage;
                }

                $pages = array_values(array_unique($pages));
                sort($pages);
                $previous = null;
            @endphp

            @foreach ($pages as $page)
                @if ($previous !== null && $page > $previous + 1)
                    <span class="compact-pagination__ellipsis">...</span>
                @endif

                @if ($page === $current)
                    <span class="compact-pagination__item is-active" aria-current="page">
                        {{ $page }}
                    </span>
                @else
                    <a class="compact-pagination__item" href="{{ $paginator->url($page) }}">
                        {{ $page }}
                    </a>
                @endif

                @php $previous = $page; @endphp
            @endforeach

            @if ($paginator->hasMorePages())
                <a class="compact-pagination__item" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('Next') }}">
                    Next
                </a>
            @else
                <span class="compact-pagination__item is-disabled" aria-disabled="true" aria-label="{{ __('Next') }}">
                    Next
                </span>
            @endif
        </nav>
    </div>
@endif

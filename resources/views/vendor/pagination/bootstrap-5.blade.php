{{--
    Custom compact pagination — overrides Laravel's default Bootstrap-5 pager
    app-wide (AppServiceProvider calls Paginator::useBootstrapFive()).
    • Tidy window: 1 … (current ± 2) … last, instead of every page number
    • Chevron arrows + "Showing X–Y of Z" summary
    • On phones it collapses to  ‹ [current] ›  so it never overflows
--}}
@php
    $current = $paginator->currentPage();
    $last    = $paginator->lastPage();
    $side    = 2; // pages shown on each side of the current page (desktop)

    $pages = collect(range(1, $last))->filter(function ($p) use ($current, $last, $side) {
        return $p == 1 || $p == $last || ($p >= $current - $side && $p <= $current + $side);
    })->values();
@endphp

@if ($paginator->hasPages())
    <nav class="app-pagination d-flex flex-column flex-sm-row align-items-center justify-content-between gap-3 mt-3"
         role="navigation" aria-label="Pagination">

        <p class="small text-muted mb-0 order-2 order-sm-1">
            Showing <span class="fw-semibold text-body">{{ $paginator->firstItem() ?? 0 }}</span>–<span class="fw-semibold text-body">{{ $paginator->lastItem() ?? 0 }}</span>
            of <span class="fw-semibold text-body">{{ $paginator->total() }}</span> results
        </p>

        <ul class="pagination mb-0 order-1 order-sm-2">
            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link"><i class="bi bi-chevron-left"></i></span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous page">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                </li>
            @endif

            {{-- Page numbers (extra numbers & ellipses hide on phones) --}}
            @php $prev = 0; @endphp
            @foreach ($pages as $page)
                @if ($page - $prev > 1)
                    <li class="page-item disabled d-none d-sm-flex" aria-hidden="true"><span class="page-link">…</span></li>
                @endif

                @if ($page == $current)
                    <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                @else
                    <li class="page-item d-none d-sm-flex">
                        <a class="page-link" href="{{ $paginator->url($page) }}" aria-label="Go to page {{ $page }}">{{ $page }}</a>
                    </li>
                @endif

                @php $prev = $page; @endphp
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next page">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link"><i class="bi bi-chevron-right"></i></span>
                </li>
            @endif
        </ul>
    </nav>
@endif

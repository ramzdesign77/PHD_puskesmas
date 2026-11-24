@php
    $currentPage = $paginator->currentPage();
    $lastPage = $paginator->lastPage();
    $visiblePages = collect([1])
        ->merge(range(max(1, $currentPage - 1), min($lastPage, $currentPage + 1)))
        ->merge([$lastPage])
        ->unique()
        ->sort()
        ->values();
@endphp

@if($paginator->hasPages())
    <nav class="w-full sm:w-auto" role="navigation" aria-label="Pagination">
        <div class="flex items-center justify-center gap-2 sm:justify-end">
            @if($paginator->onFirstPage())
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-full text-slate-300" aria-disabled="true" aria-label="Halaman sebelumnya">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" aria-hidden="true">
                        <path d="m12.5 4.5-7 5.5 7 5.5" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"></path>
                    </svg>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex h-10 w-10 items-center justify-center rounded-full text-sky-600 transition hover:bg-sky-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-sky-500" aria-label="Halaman sebelumnya">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" aria-hidden="true">
                        <path d="m12.5 4.5-7 5.5 7 5.5" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"></path>
                    </svg>
                </a>
            @endif

            @foreach($visiblePages as $page)
                @if($loop->index > 0 && $page - $visiblePages[$loop->index - 1] > 1)
                    <span class="inline-flex h-10 min-w-6 items-center justify-center text-sm text-slate-400" aria-hidden="true">&hellip;</span>
                @endif

                @if($page === $currentPage)
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-sky-300 bg-sky-500 text-sm font-semibold text-white shadow-sm" aria-current="page">
                        {{ $page }}
                    </span>
                @else
                    <a href="{{ $paginator->url($page) }}" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-sm font-semibold text-slate-700 transition hover:border-sky-300 hover:bg-sky-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-sky-500" aria-label="Halaman {{ $page }}">
                        {{ $page }}
                    </a>
                @endif
            @endforeach

            @if($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex h-10 w-10 items-center justify-center rounded-full text-sky-600 transition hover:bg-sky-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-sky-500" aria-label="Halaman berikutnya">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" aria-hidden="true">
                        <path d="m7.5 4.5 7 5.5-7 5.5" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"></path>
                    </svg>
                </a>
            @else
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-full text-slate-300" aria-disabled="true" aria-label="Halaman berikutnya">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" aria-hidden="true">
                        <path d="m7.5 4.5 7 5.5-7 5.5" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"></path>
                    </svg>
                </span>
            @endif
        </div>
    </nav>
@endif

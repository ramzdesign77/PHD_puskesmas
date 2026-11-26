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

<div class="flex w-full min-w-0 flex-col items-center justify-between gap-3 sm:flex-row sm:gap-4">
    <div class="flex min-w-0 flex-wrap items-center justify-center gap-3 text-sm text-slate-600 sm:justify-start">
        <span class="text-xs font-medium text-slate-500">
            Menampilkan {{ $paginator->firstItem() ?? 0 }}–{{ $paginator->lastItem() ?? 0 }} dari {{ $paginator->total() }} data
        </span>
        @if($showPerPage ?? false)
            <span class="hidden h-5 border-l border-slate-200 sm:block" aria-hidden="true"></span>
            <div class="flex items-center gap-2">
                <label for="per-page-select-{{ $paginator->getPageName() }}" class="text-xs font-medium text-slate-500">Tampilkan:</label>
                <select id="per-page-select-{{ $paginator->getPageName() }}"
                    name="per_page"
                    data-page-param="{{ $paginator->getPageName() }}"
                    aria-label="Jumlah data per halaman"
                    onchange="const params = new URLSearchParams(window.location.search); params.set('per_page', this.value); for (const key of [...params.keys()]) { if (key === 'page' || key.startsWith('page_')) params.set(key, '1'); } params.set(this.dataset.pageParam, '1'); window.location.search = params.toString();"
                    class="cursor-pointer rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-700 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                    @foreach([20, 50, 80, 100] as $size)
                        <option value="{{ $size }}" {{ $paginator->perPage() === $size ? 'selected' : '' }}>
                            {{ $size }} data
                        </option>
                    @endforeach
                </select>
            </div>
        @endif
    </div>

    @if($paginator->hasPages())
        <nav class="w-full min-w-0 overflow-x-auto sm:w-auto sm:overflow-visible" role="navigation" aria-label="Pagination">
            <div class="flex min-w-max items-center justify-center gap-1 px-1 sm:justify-end sm:gap-2">
                @if($paginator->onFirstPage())
                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-slate-300 sm:h-10 sm:w-10" aria-disabled="true" aria-label="Halaman sebelumnya">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" aria-hidden="true">
                            <path d="m12.5 4.5-7 5.5 7 5.5" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"></path>
                        </svg>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-sky-600 transition hover:bg-sky-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-sky-500 sm:h-10 sm:w-10" aria-label="Halaman sebelumnya">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" aria-hidden="true">
                            <path d="m12.5 4.5-7 5.5 7 5.5" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"></path>
                        </svg>
                    </a>
                @endif

                @foreach($visiblePages as $page)
                    @if($loop->index > 0 && $page - $visiblePages[$loop->index - 1] > 1)
                        <span class="inline-flex h-8 min-w-5 shrink-0 items-center justify-center text-sm text-slate-400 sm:h-10 sm:min-w-6" aria-hidden="true">&hellip;</span>
                    @endif

                    @if($page === $currentPage)
                        <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-sky-300 bg-sky-500 text-sm font-semibold text-white shadow-sm sm:h-10 sm:w-10" aria-current="page">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $paginator->url($page) }}" class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-slate-200 bg-white text-sm font-semibold text-slate-700 transition hover:border-sky-300 hover:bg-sky-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-sky-500 sm:h-10 sm:w-10" aria-label="Halaman {{ $page }}">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach

                @if($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-sky-600 transition hover:bg-sky-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-sky-500 sm:h-10 sm:w-10" aria-label="Halaman berikutnya">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" aria-hidden="true">
                            <path d="m7.5 4.5 7 5.5-7 5.5" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"></path>
                        </svg>
                    </a>
                @else
                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-slate-300 sm:h-10 sm:w-10" aria-disabled="true" aria-label="Halaman berikutnya">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" aria-hidden="true">
                            <path d="m7.5 4.5 7 5.5-7 5.5" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"></path>
                        </svg>
                    </span>
                @endif
            </div>
        </nav>
    @endif
</div>

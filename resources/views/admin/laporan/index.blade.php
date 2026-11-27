@extends('layouts.app')

@section('content')
<div class="space-y-6 fade-in">

    <div class="flex items-center justify-between mb-3">
        <div>
            <h3 class="font-bold text-gray-800">Kelola Laporan Warga</h3>
            <p class="text-xs text-gray-400">Tinjau, terima, atau tolak laporan yang masuk</p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg p-3">
            {{ session('success') }}
        </div>
    @endif

    @php
        $hasReportFilters = collect(['q', 'status', 'status_laporan', 'kategori', 'id_desa', 'date_from', 'date_to', 'sort'])
            ->contains(fn ($filter) => filled(request($filter)));
        $hasAdvancedReportFilters = collect(['kategori', 'date_from', 'date_to', 'sort'])
            ->contains(fn ($filter) => filled(request($filter)));
        $selectedStatus = request('status', request('status_laporan'));
    @endphp
    <form method="GET" action="{{ route('admin.laporan.index') }}" class="mb-5 w-full">
        <input type="hidden" name="per_page" value="{{ request('per_page', 20) }}">
        <div class="w-full rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <div class="flex flex-col gap-2.5 sm:flex-row sm:items-center">
                <div class="relative min-w-0 flex-1">
                    <i class="fas fa-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari tiket, pelapor, WhatsApp, deskripsi..."
                        class="w-full min-w-0 rounded-xl border border-slate-200 bg-slate-50 py-2 pl-9 pr-9 text-sm outline-none transition focus:border-blue-400 focus:bg-white focus:ring-2 focus:ring-blue-500/20">
                    @if(request('q'))
                        <button type="button" onclick="this.form.q.value=''; this.form.requestSubmit()" aria-label="Hapus pencarian"
                            class="absolute right-2 top-1/2 -translate-y-1/2 text-lg leading-none text-slate-400 hover:text-slate-700">×</button>
                    @endif
                </div>
                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    <button type="button" data-toggle-advanced-filter="laporan-admin" data-advanced-open="{{ $hasAdvancedReportFilters ? 'true' : 'false' }}" aria-expanded="{{ $hasAdvancedReportFilters ? 'true' : 'false' }}"
                        class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-100">
                        <i class="fas fa-sliders text-[11px]"></i> Filter Lanjutan
                        @if($hasAdvancedReportFilters)
                            <span class="rounded-full bg-blue-100 px-1.5 text-blue-700">{{ collect(['kategori', 'date_from', 'date_to', 'sort'])->filter(fn ($filter) => filled(request($filter)))->count() }}</span>
                        @endif
                        <i class="fas fa-chevron-down transition-transform duration-200" data-advanced-filter-icon aria-hidden="true"></i>
                    </button>
                    <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">Terapkan</button>
                    @if($hasReportFilters)
                        <a href="{{ route('admin.laporan.index') }}" class="rounded-xl border border-slate-200 px-3.5 py-2 text-xs font-medium text-slate-600 hover:bg-slate-50">Reset</a>
                    @endif
                </div>
            </div>

            <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <select name="status" class="w-full truncate rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:ring-2 focus:ring-blue-500/20">
                    <option value="">Semua Status</option>
                    @foreach(['menunggu' => 'Menunggu', 'dibaca' => 'Dibaca', 'dijadwalkan' => 'Dijadwalkan', 'selesai' => 'Selesai', 'ditolak' => 'Ditolak'] as $status => $label)
                        <option value="{{ $status }}" {{ $selectedStatus === $status ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="id_desa" class="w-full truncate rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:ring-2 focus:ring-blue-500/20">
                    <option value="">Semua Desa</option>
                    @foreach($desaList as $desa)
                        <option value="{{ $desa->id_desa }}" {{ request('id_desa') == $desa->id_desa ? 'selected' : '' }}>{{ $desa->nama_desa }}</option>
                    @endforeach
                </select>
            </div>
            <div id="report-advanced-filters" data-advanced-filter-panel="laporan-admin" class="{{ $hasAdvancedReportFilters ? '' : 'hidden' }} mt-3 border-t border-slate-100 pt-3">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Urutkan</label>
                        <select name="sort" class="w-full truncate rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:ring-2 focus:ring-blue-500/20">
                            <option value="">Terbaru</option>
                            <option value="terlama" {{ request('sort') === 'terlama' ? 'selected' : '' }}>Terlama</option>
                        </select>
                    </div>
                    <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Kategori</label>
                    <select name="kategori" class="w-full truncate rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:ring-2 focus:ring-blue-500/20">
                        <option value="">Semua Kategori</option>
                        @foreach(['air_sumur_keruh_berbau' => 'Air Sumur Keruh / Berbau', 'sarang_nyamuk_dbd' => 'Sarang Nyamuk / DBD', 'jamban_limbah_stbm' => 'Jamban & Limbah (STBM)', 'tumpukan_sampah' => 'Tumpukan Sampah'] as $category => $label)
                            <option value="{{ $category }}" {{ request('kategori') === $category ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    </div>
                    <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Dari Tanggal</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:ring-2 focus:ring-blue-500/20">
                    </div>
                    <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Sampai Tanggal</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:ring-2 focus:ring-blue-500/20">
                    </div>
                </div>
            </div>

            <div class="mt-3 flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 pt-3">
                <div class="flex flex-wrap items-center gap-1.5 text-xs">
                    @if($hasReportFilters)
                        <span class="font-medium text-slate-500">Filter aktif:</span>
                        @foreach(['q' => 'Pencarian', 'status' => 'Status', 'status_laporan' => 'Status', 'id_desa' => 'Desa', 'kategori' => 'Kategori', 'date_from' => 'Dari', 'date_to' => 'Sampai', 'sort' => 'Urutan'] as $filterKey => $filterLabel)
                            @if(filled(request($filterKey)))
                                <a href="{{ request()->fullUrlWithQuery([$filterKey => null, 'page' => null]) }}" class="inline-flex max-w-full items-center gap-1 rounded-full border border-blue-200 bg-blue-50 px-2.5 py-1 font-medium text-blue-700">
                                    <span class="truncate">{{ $filterLabel }}: {{ $filterKey === 'id_desa' ? ($desaList->firstWhere('id_desa', request($filterKey))?->nama_desa ?? request($filterKey)) : request($filterKey) }}</span><span aria-hidden="true">×</span>
                                </a>
                            @endif
                        @endforeach
                    @else
                        <span class="text-xs text-slate-400">Tidak ada filter aktif</span>
                    @endif
                </div>
                <span class="text-xs font-medium text-slate-500">Ditemukan {{ $laporan->total() }} laporan</span>
            </div>
        </div>
    </form>

    <div class="card p-0 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 text-left">
                <tr>
                    <th class="p-3">Kode Tiket</th>
                    <th class="p-3">Pelapor</th>
                    <th class="p-3">Desa</th>
                    <th class="p-3">Kategori</th>
                    <th class="p-3">Status</th>
                    <th class="p-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($laporan as $item)
                <tr class="border-t">
                    <td class="p-3 font-mono text-xs">{{ $item->kode_tiket }}</td>
                    <td class="p-3">{{ $item->nama_pelapor }}</td>
                    <td class="p-3">{{ $item->desa->nama_desa ?? '-' }}</td>
                    <td class="p-3">{{ $item->kategori_laporan }}</td>
                    <td class="p-3">
                        <span class="text-xs px-2 py-1 rounded-full
                            @if($item->status_laporan === 'menunggu') bg-yellow-100 text-yellow-700
                            @elseif($item->status_laporan === 'dibaca') bg-blue-100 text-blue-700
                            @elseif($item->status_laporan === 'diterima') bg-green-100 text-green-700
                            @else bg-red-100 text-red-700 @endif">
                            {{ ucfirst($item->status_laporan) }}
                        </span>
                    </td>
                    <td class="p-3">
                        <a href="{{ route('admin.laporan.show', $item->id_laporan) }}" class="text-blue-600 hover:underline text-xs">
                            Lihat Detail
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="p-6 text-center text-gray-400">Belum ada laporan masuk.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="rounded-2xl border border-slate-200/80 bg-white px-4 py-3 shadow-sm">
        @include('partials.pagination-circles', ['paginator' => $laporan, 'showPerPage' => true])
    </div>
</div>
@endsection

@section('scripts')
<script>
(() => {
    const bindAdvancedFilterToggles = () => {
        document.querySelectorAll('[data-toggle-advanced-filter]').forEach(button => {
            if (button.dataset.advancedFilterBound === 'true') {
                return;
            }

            const panelName = button.dataset.toggleAdvancedFilter;
            const panel = document.querySelector(`[data-advanced-filter-panel="${panelName}"]`);
            const icon = button.querySelector('[data-advanced-filter-icon]');

            if (!panel) {
                return;
            }

            const initialOpen = button.dataset.advancedOpen === 'true';
            panel.classList.toggle('hidden', !initialOpen);
            button.setAttribute('aria-expanded', String(initialOpen));
            icon?.classList.toggle('rotate-180', initialOpen);

            button.addEventListener('click', () => {
                const isOpen = panel.classList.contains('hidden');
                panel.classList.toggle('hidden', !isOpen);
                button.setAttribute('aria-expanded', String(isOpen));
                icon?.classList.toggle('rotate-180', isOpen);
            });

            button.dataset.advancedFilterBound = 'true';
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindAdvancedFilterToggles, { once: true });
    } else {
        bindAdvancedFilterToggles();
    }
})();
</script>
@endsection

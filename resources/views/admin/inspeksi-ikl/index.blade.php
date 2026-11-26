@extends('layouts.app')

@section('title', 'Rekap Hasil IKL Air')

@section('page-title', 'Hasil Inspeksi IKL Air')
@section('page-subtitle', 'Rekap data inspeksi kualitas air dari seluruh laporan warga')

@section('content')
<div class="space-y-6 fade-in">

    {{-- ── Statistik Ringkasan ──────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="card relative overflow-hidden p-5">
            <p class="text-xs text-gray-400 font-medium uppercase tracking-wider">Total Inspeksi</p>
            <p class="text-3xl font-bold text-gray-800 mt-1">{{ $rekapStatistik->total_inspeksi ?? 0 }}</p>
        </div>
        <div class="card relative overflow-hidden p-5 border-l-4 border-emerald-400">
            <p class="text-xs text-emerald-600 font-medium uppercase tracking-wider">Aman</p>
            <p class="text-3xl font-bold text-emerald-700 mt-1">{{ $rekapStatistik->jumlah_aman ?? 0 }}</p>
        </div>
        <div class="card relative overflow-hidden p-5 border-l-4 border-amber-400">
            <p class="text-xs text-amber-600 font-medium uppercase tracking-wider">Risiko Sedang</p>
            <p class="text-3xl font-bold text-amber-700 mt-1">{{ $rekapStatistik->jumlah_sedang ?? 0 }}</p>
        </div>
        <div class="card relative overflow-hidden p-5 border-l-4 border-red-400">
            <p class="text-xs text-red-600 font-medium uppercase tracking-wider">Risiko Tinggi</p>
            <p class="text-3xl font-bold text-red-700 mt-1">{{ $rekapStatistik->jumlah_tinggi ?? 0 }}</p>
        </div>
    </div>

    {{-- ── Filter ────────────────────────────────────────────────────────── --}}
    @php
        $hasAdvancedIkl = collect(['date_from', 'date_to', 'kategori_ikl', 'intervensi'])
            ->contains(fn ($filter) => filled(request($filter)));
        $hasAnyIkl = collect(['q', 'risiko', 'kategori_risiko', 'id_operator', 'id_desa', 'date_from', 'date_to', 'kategori_ikl', 'intervensi'])
            ->contains(fn ($filter) => filled(request($filter)));
    @endphp
    <form method="GET" action="{{ route('admin.inspeksi-ikl.index') }}" id="filter-form-ikl" class="mb-5 w-full">
        <input type="hidden" name="per_page" value="{{ request('per_page', 20) }}">
        <div class="w-full rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
            {{-- Baris utama --}}
            <div class="flex flex-col gap-2.5 sm:flex-row sm:items-center">
                {{-- Search --}}
                <div class="relative min-w-0 flex-1">
                    <input type="text" name="q" value="{{ request('q') }}"
                        placeholder="Cari kode tiket, pelapor, rekomendasi..."
                        class="w-full min-w-0 rounded-xl border border-slate-200 bg-slate-50 py-2 pl-9 pr-9 text-sm outline-none transition focus:border-blue-400 focus:bg-white focus:ring-2 focus:ring-blue-500/20">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"><i class="fas fa-search text-xs"></i></span>
                    @if(request('q'))
                        <button type="button" onclick="this.form.q.value=''; this.form.requestSubmit()" aria-label="Hapus pencarian" class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-lg leading-none">×</button>
                    @endif
                </div>
                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    <button type="button" onclick="toggleAdvancedFilter('ikl')"
                        class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-100">
                        <i class="fas fa-sliders text-[11px]"></i> Filter Lanjutan
                        @if($hasAdvancedIkl)<span class="rounded-full bg-blue-100 px-1.5 text-blue-700">{{ collect(['date_from', 'date_to', 'kategori_ikl', 'intervensi'])->filter(fn ($filter) => filled(request($filter)))->count() }}</span>@endif
                    </button>
                    <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">Terapkan</button>
                    @if($hasAnyIkl)
                        <a href="{{ route('admin.inspeksi-ikl.index') }}" class="rounded-xl border border-slate-200 px-3.5 py-2 text-xs font-medium text-slate-600 hover:bg-slate-50">Reset</a>
                    @endif
                </div>
            </div>

            <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <select name="risiko" class="w-full truncate rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20">
                    <option value="">Semua Risiko</option>
                    <option value="aman"             {{ (request('risiko') ?? request('kategori_risiko')) === 'aman'             ? 'selected' : '' }}>Aman / Rendah</option>
                    <option value="risiko_sedang"    {{ (request('risiko') ?? request('kategori_risiko')) === 'risiko_sedang'    ? 'selected' : '' }}>Risiko Sedang</option>
                    <option value="risiko_tinggi"    {{ (request('risiko') ?? request('kategori_risiko')) === 'risiko_tinggi'    ? 'selected' : '' }}>Risiko Tinggi</option>
                    <option value="risiko_amat_tinggi" {{ (request('risiko') ?? request('kategori_risiko')) === 'risiko_amat_tinggi' ? 'selected' : '' }}>Risiko Amat Tinggi</option>
                </select>
                <select name="id_operator" class="w-full truncate rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20">
                    <option value="">Semua Petugas</option>
                    @foreach($semuaPetugas as $p)
                        <option value="{{ $p->id_user }}" {{ request('id_operator') == $p->id_user ? 'selected' : '' }}>
                            {{ $p->nama_lengkap }}
                        </option>
                    @endforeach
                </select>
                <select name="id_desa" class="w-full truncate rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20">
                    <option value="">Semua Desa</option>
                    @foreach($desaList as $desa)
                        <option value="{{ $desa->id_desa }}" {{ request('id_desa') == $desa->id_desa ? 'selected' : '' }}>{{ $desa->nama_desa }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Filter Lanjutan (Collapsible) --}}
            <div id="advanced-panel-ikl" class="{{ $hasAdvancedIkl ? 'block' : 'hidden' }} mt-3 pt-3 border-t border-slate-100">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <label class="text-xs font-medium text-slate-600 mb-1 block">Kategori Instrumen</label>
                        <select name="kategori_ikl" class="w-full text-sm bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:bg-white focus:ring-2 focus:ring-blue-500/20 outline-none">
                            <option value="">Semua Instrumen</option>
                            <option value="sumur_gali"       {{ request('kategori_ikl') === 'sumur_gali'       ? 'selected' : '' }}>Sumur Gali</option>
                            <option value="sumur_bor"        {{ request('kategori_ikl') === 'sumur_bor'        ? 'selected' : '' }}>Sumur Bor</option>
                            <option value="sarang_nyamuk_dbd" {{ request('kategori_ikl') === 'sarang_nyamuk_dbd' ? 'selected' : '' }}>Sarang Nyamuk / DBD</option>
                            <option value="jamban_limbah_stbm" {{ request('kategori_ikl') === 'jamban_limbah_stbm' ? 'selected' : '' }}>Jamban & Limbah</option>
                            <option value="tumpukan_sampah"  {{ request('kategori_ikl') === 'tumpukan_sampah'  ? 'selected' : '' }}>Tumpukan Sampah</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-600 mb-1 block">Intervensi Fisik</label>
                        <select name="intervensi" class="w-full text-sm bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:bg-white focus:ring-2 focus:ring-blue-500/20 outline-none">
                            <option value="">Semua</option>
                            <option value="ada"   {{ request('intervensi') === 'ada'   ? 'selected' : '' }}>Ada Intervensi Fisik</option>
                            <option value="tidak" {{ request('intervensi') === 'tidak' ? 'selected' : '' }}>Tanpa Intervensi</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-600 mb-1 block">Dari Tanggal Inspeksi</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                            class="w-full text-sm bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:bg-white focus:ring-2 focus:ring-blue-500/20 outline-none">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-600 mb-1 block">Sampai Tanggal Inspeksi</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}"
                            class="w-full text-sm bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:bg-white focus:ring-2 focus:ring-blue-500/20 outline-none">
                    </div>
                </div>
            </div>
            <div class="mt-3 flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 pt-3">
                <div class="flex flex-wrap items-center gap-1.5 text-xs">
                    @if($hasAnyIkl)
                        <span class="font-medium text-slate-500">Filter aktif:</span>
                        @foreach(['q' => 'Pencarian', 'risiko' => 'Risiko', 'kategori_risiko' => 'Risiko', 'id_operator' => 'Petugas', 'id_desa' => 'Desa', 'kategori_ikl' => 'Instrumen', 'intervensi' => 'Intervensi', 'date_from' => 'Dari', 'date_to' => 'Sampai'] as $filterKey => $filterLabel)
                            @if(filled(request($filterKey)))
                                <a href="{{ request()->fullUrlWithQuery([$filterKey => null, 'kategori_risiko' => $filterKey === 'risiko' ? null : request('kategori_risiko'), 'page' => null]) }}" class="inline-flex max-w-full items-center gap-1 rounded-full border border-blue-200 bg-blue-50 px-2.5 py-1 font-medium text-blue-700">
                                    <span class="truncate">{{ $filterLabel }}: {{ $filterKey === 'id_desa' ? ($desaList->firstWhere('id_desa', request($filterKey))?->nama_desa ?? request($filterKey)) : ($filterKey === 'id_operator' ? ($semuaPetugas->firstWhere('id_user', request($filterKey))?->nama_lengkap ?? request($filterKey)) : request($filterKey)) }}</span><span aria-hidden="true">×</span>
                                </a>
                            @endif
                        @endforeach
                    @else
                        <span class="text-xs text-slate-400">Tidak ada filter aktif</span>
                    @endif
                </div>
                <span class="text-xs font-medium text-slate-500">Ditemukan {{ $jadwalList->total() }} data</span>
            </div>
        </div>
    </form>

    {{-- ── Tabel Hasil IKL ──────────────────────────────────────────────── --}}
    <div class="card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800 text-sm">Data Inspeksi IKL</h3>
            <span class="text-xs text-gray-400">{{ $jadwalList->total() }} data ditemukan</span>
        </div>

        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Kode Tiket</th>
                        <th>Pelapor / Desa</th>
                        <th>Petugas</th>
                        <th>Tgl Inspeksi</th>
                        <th>Jenis Sarana</th>
                        <th>Skor Risiko</th>
                        <th>Kategori Risiko</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jadwalList as $jadwal)
                    @php $ikl = $jadwal->inspeksiIkl; @endphp
                    <tr>
                        <td>
                            <span class="font-mono text-xs text-gray-600">
                                {{ $jadwal->laporan->kode_tiket ?? '-' }}
                            </span>
                        </td>
                        <td>
                            <p class="font-medium text-gray-800 text-sm">{{ $jadwal->laporan->nama_pelapor ?? '-' }}</p>
                            <p class="text-xs text-gray-400">{{ $jadwal->laporan->desa->nama_desa ?? '-' }}</p>
                        </td>
                        <td class="text-sm">{{ $jadwal->petugas->nama_petugas ?? '-' }}</td>
                        <td class="text-sm text-gray-600">
                            {{ $ikl ? \Carbon\Carbon::parse($ikl->tanggal_inspeksi)->format('d M Y') : '-' }}
                        </td>
                        <td>
                            <span class="text-xs bg-blue-50 text-blue-700 px-2 py-1 rounded-full capitalize">
                                {{ str_replace('_', ' ', $ikl->jenis_sarana_air ?? '-') }}
                            </span>
                        </td>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="flex gap-0.5">
                                    @for($i = 1; $i <= 5; $i++)
                                        <div class="w-3 h-3 rounded-sm {{ $i <= ($ikl->total_skor_ya ?? 0) ? 'bg-red-400' : 'bg-gray-200' }}"></div>
                                    @endfor
                                </div>
                                <span class="text-xs font-bold text-gray-700">{{ $ikl->total_skor_ya ?? 0 }}/7</span>
                            </div>
                        </td>
                        <td>
                            @if($ikl)
                                @php
                                    $kategori = $ikl->kategori_risiko;
                                    $warna = match($kategori) {
                                        'aman'          => 'badge-resolved',
                                        'risiko_sedang' => 'badge-pending',
                                        'risiko_tinggi' => 'badge-rejected',
                                        default         => 'badge-pending',
                                    };
                                    $label = match($kategori) {
                                        'aman'          => 'Aman',
                                        'risiko_sedang' => 'Risiko Sedang',
                                        'risiko_tinggi' => 'Risiko Tinggi',
                                        default         => $kategori,
                                    };
                                @endphp
                                <span class="badge {{ $warna }}">{{ $label }}</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.inspeksi-ikl.show', $jadwal->id_jadwal) }}"
                               class="btn-secondary btn-sm">
                                <i class="fas fa-eye mr-1"></i> Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-gray-400">
                            <i class="fas fa-flask text-3xl mb-3 block opacity-30"></i>
                            Belum ada data hasil inspeksi IKL.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex flex-col gap-3 border-t border-gray-100 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            @include('partials.pagination-circles', ['paginator' => $jadwalList, 'showPerPage' => true])
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
function toggleAdvancedFilter(id) {
    const panel = document.getElementById('advanced-panel-' + id);
    if (panel) { panel.classList.toggle('hidden'); }
}
function clearFilterField(name, formId) {
    const form = document.getElementById(formId);
    const input = form ? form.querySelector('[name="' + name + '"]') : document.querySelector('[name="' + name + '"]');
    if (input) { input.value = ''; input.form ? input.form.submit() : form.submit(); }
}
</script>
@endsection

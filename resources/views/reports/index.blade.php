@extends('layouts.app')

@section('title', 'Laporan & Hasil IKL Air')
@section('page-title', 'Laporan & Hasil IKL Air')
@section('page-subtitle', 'Kelola laporan warga, penjadwalan, dan rekap inspeksi kualitas air')

@section('content')
<div class="space-y-6 fade-in">

    {{-- ════════════════════════════════════════════════════════════ --}}
    {{-- SUMMARY CARDS                                                --}}
    {{-- ════════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        @foreach([
            ['label' => 'Total',       'value' => $summary['total'],       'bg' => 'bg-slate-50',   'text' => 'text-slate-600',  'icon' => 'fa-file-alt'],
            ['label' => 'Menunggu',    'value' => $summary['menunggu'],    'bg' => 'bg-yellow-50',  'text' => 'text-yellow-600', 'icon' => 'fa-clock'],
            ['label' => 'Dijadwalkan', 'value' => $summary['dijadwalkan'], 'bg' => 'bg-blue-50',    'text' => 'text-blue-600',   'icon' => 'fa-calendar-check'],
            ['label' => 'Selesai',     'value' => $summary['selesai'],     'bg' => 'bg-green-50',   'text' => 'text-green-600',  'icon' => 'fa-check-circle'],
            ['label' => 'Ditolak',     'value' => $summary['ditolak'],     'bg' => 'bg-red-50',     'text' => 'text-red-600',    'icon' => 'fa-times-circle'],
        ] as $s)
        <div class="card relative overflow-hidden p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 {{ $s['bg'] }} rounded-xl flex items-center justify-center shrink-0">
                    <i class="fas {{ $s['icon'] }} {{ $s['text'] }}"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-xl font-bold text-gray-800">{{ $s['value'] }}</p>
                    <p class="text-xs text-gray-400 font-medium truncate">{{ $s['label'] }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ════════════════════════════════════════════════════════════ --}}
    {{-- TAB UI                                                        --}}
    {{-- ════════════════════════════════════════════════════════════ --}}
    <div x-data="{ tab: 'laporan' }">

        {{-- Tab Buttons --}}
        <div class="flex gap-2 border-b border-gray-200 mb-6">
            <button type="button" id="tab-btn-laporan"
                @click="tab = 'laporan'"
                :class="tab === 'laporan'
                    ? 'border-b-2 border-ocean-600 text-ocean-700 font-semibold'
                    : 'text-gray-500 hover:text-gray-700'"
                class="flex items-center gap-2 pb-3 px-1 text-sm transition-colors">
                <i class="fas fa-inbox"></i>
                Laporan Masuk & Penjadwalan
                @if($summary['menunggu'] > 0)
                <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold bg-yellow-100 text-yellow-700 rounded-full">
                    {{ $summary['menunggu'] }}
                </span>
                @endif
            </button>
            <button type="button" id="tab-btn-rekap"
                @click="tab = 'rekap'"
                :class="tab === 'rekap'
                    ? 'border-b-2 border-ocean-600 text-ocean-700 font-semibold'
                    : 'text-gray-500 hover:text-gray-700'"
                class="flex items-center gap-2 pb-3 px-1 text-sm transition-colors">
                <i class="fas fa-flask"></i>
                Rekap Hasil Inspeksi IKL Air
                <span class="inline-flex items-center justify-center px-2 h-5 text-xs font-bold bg-green-100 text-green-700 rounded-full">
                    {{ $summary['selesai'] }}
                </span>
            </button>
        </div>

        {{-- ════════════════════════════════════ --}}
        {{-- TAB 1: LAPORAN MASUK & PENJADWALAN  --}}
        {{-- ════════════════════════════════════ --}}
        <div x-show="tab === 'laporan'" x-transition.opacity>
            {{-- Filter Toolbar Tab 1 --}}
            @php
                $hasAdvancedLap = collect(['date_from', 'date_to', 'kategori', 'sort'])
                    ->contains(fn ($filter) => filled(request($filter)));
                $hasAnyLap = collect(['q', 'status', 'status_laporan', 'id_desa', 'date_from', 'date_to', 'kategori', 'sort'])
                    ->contains(fn ($filter) => filled(request($filter)));
            @endphp
            <form method="GET" action="{{ route('reports.index') }}" id="filter-form-laporan">
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-3.5 sm:p-4 mb-5">
                    <div class="flex flex-wrap gap-2 items-center">
                        {{-- Search --}}
                        <div class="relative flex-1 min-w-48">
                            <input type="text" name="q" value="{{ request('q') }}"
                                placeholder="Cari kode tiket, pelapor, No WA, deskripsi..."
                                class="w-full pl-9 pr-8 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 outline-none transition">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"><i class="fas fa-search text-xs"></i></span>
                            @if(request('q'))
                                <button type="button" onclick="clearFilterField('q', 'filter-form-laporan')" class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-lg leading-none">×</button>
                            @endif
                        </div>
                        {{-- Status --}}
                        <select name="status" class="text-sm bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 outline-none">
                            <option value="">Semua Status</option>
                            @foreach(['menunggu' => 'Menunggu', 'dibaca' => 'Dibaca', 'dijadwalkan' => 'Dijadwalkan', 'selesai' => 'Selesai', 'ditolak' => 'Ditolak'] as $statusValue => $statusLabel)
                                <option value="{{ $statusValue }}" {{ (request('status') ?? request('status_laporan')) === $statusValue ? 'selected' : '' }}>{{ $statusLabel }}</option>
                            @endforeach
                        </select>
                        {{-- Desa --}}
                        <select name="id_desa" class="text-sm bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 outline-none">
                            <option value="">Semua Desa</option>
                            @foreach($desaList as $desa)
                                <option value="{{ $desa->id_desa }}" {{ request('id_desa') == $desa->id_desa ? 'selected' : '' }}>{{ $desa->nama_desa }}</option>
                            @endforeach
                        </select>
                        {{-- Toggle Filter Lanjutan --}}
                        <button type="button" onclick="toggleAdvancedFilter('laporan')"
                            class="flex items-center gap-1.5 text-xs font-semibold px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-600 transition">
                            <i class="fas fa-sliders text-[11px]"></i> Filter Lanjutan
                            @if($hasAdvancedLap)<span class="w-1.5 h-1.5 rounded-full bg-blue-500 inline-block"></span>@endif
                        </button>
                        <button type="submit" class="text-sm font-semibold px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white transition">Terapkan</button>
                        @if($hasAnyLap)
                            <a href="{{ route('reports.index') }}" class="text-xs text-slate-500 hover:text-red-600 font-medium px-2 transition">Reset</a>
                        @endif
                    </div>

                    {{-- Filter Lanjutan --}}
                    <div id="advanced-panel-laporan" class="{{ $hasAdvancedLap ? 'block' : 'hidden' }} mt-3 pt-3 border-t border-slate-100">
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <div>
                                <label class="text-xs font-medium text-slate-600 mb-1 block">Kategori Laporan</label>
                                <select name="kategori" class="w-full text-sm bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:bg-white focus:ring-2 focus:ring-blue-500/20 outline-none">
                                    <option value="">Semua Kategori</option>
                                    <option value="air_sumur_keruh_berbau" {{ request('kategori') === 'air_sumur_keruh_berbau' ? 'selected' : '' }}>Air Sumur Keruh / Berbau</option>
                                    <option value="sarang_nyamuk_dbd"      {{ request('kategori') === 'sarang_nyamuk_dbd'      ? 'selected' : '' }}>Sarang Nyamuk / DBD</option>
                                    <option value="jamban_limbah_stbm"     {{ request('kategori') === 'jamban_limbah_stbm'     ? 'selected' : '' }}>Jamban & Limbah (STBM)</option>
                                    <option value="tumpukan_sampah"        {{ request('kategori') === 'tumpukan_sampah'        ? 'selected' : '' }}>Tumpukan Sampah</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-slate-600 mb-1 block">Urutkan</label>
                                <select name="sort" class="w-full text-sm bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:bg-white focus:ring-2 focus:ring-blue-500/20 outline-none">
                                    <option value="">Terbaru (default)</option>
                                    <option value="terlama" {{ request('sort') === 'terlama' ? 'selected' : '' }}>Terlama</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-slate-600 mb-1 block">Dari Tanggal Lapor</label>
                                <input type="date" name="date_from" value="{{ request('date_from') }}"
                                    class="w-full text-sm bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:bg-white focus:ring-2 focus:ring-blue-500/20 outline-none">
                            </div>
                            <div>
                                <label class="text-xs font-medium text-slate-600 mb-1 block">Sampai Tanggal Lapor</label>
                                <input type="date" name="date_to" value="{{ request('date_to') }}"
                                    class="w-full text-sm bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:bg-white focus:ring-2 focus:ring-blue-500/20 outline-none">
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            {{-- Active Filter Chips --}}
            @if($hasAnyLap)
            <div class="flex flex-wrap items-center gap-2 mb-4 text-xs">
                <span class="text-slate-500 font-medium">Filter aktif:</span>
                @if(request('q'))
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-slate-100 border border-slate-200 text-slate-700 font-medium">
                        Cari: "{{ request('q') }}"
                        <a href="{{ request()->fullUrlWithQuery(['q' => null]) }}" class="hover:text-red-600 ml-0.5">×</a>
                    </span>
                @endif
                @if(request('status') || request('status_laporan'))
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-blue-50 border border-blue-200 text-blue-700 font-medium">
                        Status: {{ ucfirst(request('status', request('status_laporan'))) }}
                        <a href="{{ request()->fullUrlWithQuery(['status' => null, 'status_laporan' => null, 'page_aktif' => null]) }}" class="hover:text-red-600 ml-0.5">×</a>
                    </span>
                @endif
                @if(request('id_desa'))
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-blue-50 border border-blue-200 text-blue-700 font-medium">
                        Desa: {{ $desaList->firstWhere('id_desa', request('id_desa'))?->nama_desa ?? request('id_desa') }}
                        <a href="{{ request()->fullUrlWithQuery(['id_desa' => null]) }}" class="hover:text-red-600 ml-0.5">×</a>
                    </span>
                @endif
                @if(request('kategori'))
                    @php $kategoriLabels = ['air_sumur_keruh_berbau'=>'Air Sumur Keruh / Berbau','sarang_nyamuk_dbd'=>'Sarang Nyamuk / DBD','jamban_limbah_stbm'=>'Jamban & Limbah (STBM)','tumpukan_sampah'=>'Tumpukan Sampah']; @endphp
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-blue-50 border border-blue-200 text-blue-700 font-medium">
                        Kategori: {{ $kategoriLabels[request('kategori')] ?? request('kategori') }}
                        <a href="{{ request()->fullUrlWithQuery(['kategori' => null]) }}" class="hover:text-red-600 ml-0.5">×</a>
                    </span>
                @endif
                @if(request('date_from') || request('date_to'))
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-blue-50 border border-blue-200 text-blue-700 font-medium">
                        Periode: {{ request('date_from') ?? '...' }} – {{ request('date_to') ?? '...' }}
                        <a href="{{ request()->fullUrlWithQuery(['date_from' => null, 'date_to' => null]) }}" class="hover:text-red-600 ml-0.5">×</a>
                    </span>
                @endif
                <span class="ml-auto text-slate-400">Menampilkan {{ $laporanAktif->count() }} dari {{ $laporanAktif->total() }} laporan</span>
            </div>
            @endif

            <div class="card overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h3 class="font-bold text-gray-800">Laporan Masuk</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Laporan berstatus menunggu verifikasi dan dijadwalkan</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Kode Tiket</th>
                                <th>Pelapor</th>
                                <th>Lokasi</th>
                                <th>Status</th>
                                <th>Jadwal Kunjungan</th>
                                <th>Tanggal Lapor</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($laporanAktif as $laporan)
                            <tr>
                                {{-- Kode Tiket --}}
                                <td>
                                    <span class="font-mono text-xs font-bold text-gray-600 bg-gray-100 px-2 py-1 rounded">
                                        {{ $laporan->kode_tiket }}
                                    </span>
                                </td>

                                {{-- Pelapor --}}
                                <td>
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 medical-gradient rounded-lg flex items-center justify-center text-white text-xs font-bold shrink-0">
                                            {{ strtoupper(substr($laporan->nama_pelapor, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-gray-700 truncate">{{ $laporan->nama_pelapor }}</p>
                                            @if($laporan->no_wa)
                                            <p class="text-xs text-gray-400">{{ $laporan->no_wa }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                {{-- Lokasi --}}
                                <td>
                                    <p class="text-sm text-gray-600">{{ $laporan->desa?->nama_desa ?? '-' }}</p>
                                    <p class="text-xs text-gray-400">RT {{ $laporan->rt }} / RW {{ $laporan->rw }}</p>
                                </td>

                                {{-- Status --}}
                                <td>
                                    @php
                                        $statusMap = [
                                            'menunggu'    => ['badge-pending',  'Menunggu'],
                                            'dibaca'      => ['badge-progress', 'Dibaca'],
                                            'diterima'    => ['badge-progress', 'Diterima'],
                                            'dijadwalkan' => ['badge-assigned', 'Dijadwalkan'],
                                        ];
                                        [$badgeClass, $statusLabel] = $statusMap[$laporan->status_laporan] ?? ['badge-pending', $laporan->status_laporan];
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                                </td>

                                {{-- Jadwal Kunjungan --}}
                                <td>
                                    @if($laporan->jadwal)
                                        <div class="text-xs">
                                            <p class="font-semibold text-blue-600">
                                                {{ $laporan->jadwal->tanggal_kunjungan?->format('d M Y') }}
                                                @if($laporan->jadwal->jam_mulai)
                                                    <br><span class="text-[10px] bg-blue-100 text-blue-800 px-1.5 py-0.5 rounded">{{ \Carbon\Carbon::parse($laporan->jadwal->jam_mulai)->format('H:i') }}</span>
                                                @endif
                                            </p>
                                            <p class="text-gray-400">{{ $laporan->jadwal->operator?->nama_lengkap ?? '-' }}</p>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400 italic">Belum dijadwalkan</span>
                                    @endif
                                </td>

                                {{-- Tanggal Lapor --}}
                                <td>
                                    <span class="text-xs text-gray-400">
                                        {{ $laporan->created_at ? \Carbon\Carbon::parse($laporan->created_at)->format('d M Y') : '-' }}
                                    </span>
                                </td>

                                {{-- Aksi --}}
                                <td class="text-center">
                                    <div class="flex items-center justify-center gap-2 flex-wrap">
                                        {{-- Tombol Detail --}}
                                        <button type="button"
                                            onclick="openDetailModal({{ json_encode([
                                                'kode_tiket'    => $laporan->kode_tiket,
                                                'nama_pelapor'  => $laporan->nama_pelapor,
                                                'no_wa'         => $laporan->no_wa,
                                                'desa'          => $laporan->desa?->nama_desa,
                                                'rt'            => $laporan->rt,
                                                'rw'            => $laporan->rw,
                                                'deskripsi'     => $laporan->deskripsi,
                                                'status'        => $laporan->status_laporan,
                                                'created_at'    => $laporan->created_at ? \Carbon\Carbon::parse($laporan->created_at)->format('d M Y') : '-',
                                                'foto_bukti'    => $laporan->foto_bukti,
                                            ]) }})"
                                            class="btn-secondary btn-sm" title="Lihat Detail">
                                            <i class="fas fa-eye text-gray-500"></i>
                                        </button>

                                        {{-- Tombol Jadwalkan (hanya jika belum dijadwalkan) --}}
                                        @if(in_array($laporan->status_laporan, ['menunggu', 'dibaca', 'diterima']))
                                        <button type="button"
                                            onclick="openJadwalModal({{ $laporan->id_laporan }}, '{{ $laporan->kode_tiket }}')"
                                            class="btn-secondary btn-sm text-blue-600" title="Jadwalkan Kunjungan">
                                            <i class="fas fa-calendar-plus"></i>
                                        </button>
                                        @endif

                                        {{-- Tombol Tolak --}}
                                        @if(in_array($laporan->status_laporan, ['menunggu', 'dibaca', 'diterima']))
                                        <button type="button"
                                            onclick="openTolakModal({{ $laporan->id_laporan }}, '{{ $laporan->kode_tiket }}')"
                                            class="btn-secondary btn-sm text-red-600" title="Tolak Laporan">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-12">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center">
                                            <i class="fas fa-inbox text-2xl text-gray-300"></i>
                                        </div>
                                        <p class="text-gray-400 font-medium">Tidak ada laporan aktif</p>
                                        <p class="text-xs text-gray-300">Semua laporan sudah diproses atau belum ada laporan masuk</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($laporanAktif->hasPages())
                <div class="flex flex-col gap-3 border-t border-gray-100 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <span class="text-xs font-medium text-slate-500">{{ $laporanAktif->firstItem() ?? 0 }}–{{ $laporanAktif->lastItem() ?? 0 }} dari {{ $laporanAktif->total() }} laporan</span>
                    @include('partials.pagination-circles', ['paginator' => $laporanAktif])
                </div>
                @endif
            </div>
        </div>

        {{-- ════════════════════════════════════ --}}
        {{-- TAB 2: REKAP HASIL INSPEKSI IKL AIR --}}
        {{-- ════════════════════════════════════ --}}
        <div x-show="tab === 'rekap'" x-transition.opacity x-cloak>

            {{-- Filter Toolbar Tab 2 --}}
            @php
                $hasAdvancedRekap = collect(['date_from', 'date_to', 'dari_tanggal', 'sampai_tanggal', 'kategori_ikl', 'intervensi', 'id_desa'])
                    ->contains(fn ($filter) => filled(request($filter)));
                $hasAnyRekap = collect(['q', 'risiko', 'kategori_risiko', 'date_from', 'date_to', 'dari_tanggal', 'sampai_tanggal', 'kategori_ikl', 'intervensi', 'id_desa', 'id_petugas'])
                    ->contains(fn ($filter) => filled(request($filter)));
            @endphp
            <form method="GET" action="{{ route('reports.index') }}" id="filter-form-rekap">
                <input type="hidden" name="tab" value="rekap">
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-3.5 sm:p-4 mb-5">
                    <div class="flex flex-wrap gap-2 items-center">
                        {{-- Search --}}
                        <div class="relative flex-1 min-w-48">
                            <input type="text" name="q" value="{{ request('q') }}"
                                placeholder="Cari kode tiket, pelapor, rekomendasi..."
                                class="w-full pl-9 pr-8 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 outline-none transition">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"><i class="fas fa-search text-xs"></i></span>
                            @if(request('q'))
                                <button type="button" onclick="clearFilterField('q', 'filter-form-rekap')" class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-lg leading-none">×</button>
                            @endif
                        </div>
                        {{-- Kategori Risiko --}}
                        <select name="risiko" class="text-sm bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 outline-none">
                            <option value="">Semua Risiko</option>
                            <option value="aman" {{ (request('risiko') ?? request('kategori_risiko')) === 'aman' ? 'selected' : '' }}>Aman / Rendah</option>
                            <option value="risiko_sedang" {{ (request('risiko') ?? request('kategori_risiko')) === 'risiko_sedang' ? 'selected' : '' }}>Risiko Sedang</option>
                            <option value="risiko_tinggi" {{ (request('risiko') ?? request('kategori_risiko')) === 'risiko_tinggi' ? 'selected' : '' }}>Risiko Tinggi</option>
                            <option value="risiko_amat_tinggi" {{ (request('risiko') ?? request('kategori_risiko')) === 'risiko_amat_tinggi' ? 'selected' : '' }}>Risiko Amat Tinggi</option>
                        </select>
                        {{-- Toggle Filter Lanjutan --}}
                        <button type="button" onclick="toggleAdvancedFilter('rekap')"
                            class="flex items-center gap-1.5 text-xs font-semibold px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-600 transition">
                            <i class="fas fa-sliders text-[11px]"></i> Filter Lanjutan
                            @if($hasAdvancedRekap)<span class="w-1.5 h-1.5 rounded-full bg-blue-500 inline-block"></span>@endif
                        </button>
                        <button type="submit" class="text-sm font-semibold px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white transition">Terapkan</button>
                        <a href="{{ route('reports.export', request()->query()) }}" class="text-sm font-semibold px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white transition" title="Ekspor CSV">
                            <i class="fas fa-file-csv mr-1"></i> Ekspor
                        </a>
                        @if($hasAnyRekap)
                            <a href="{{ route('reports.index') }}?tab=rekap" class="text-xs text-slate-500 hover:text-red-600 font-medium px-2 transition">Reset</a>
                        @endif
                    </div>

                    {{-- Filter Lanjutan --}}
                    <div id="advanced-panel-rekap" class="{{ $hasAdvancedRekap ? 'block' : 'hidden' }} mt-3 pt-3 border-t border-slate-100">
                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                            <div>
                                <label class="text-xs font-medium text-slate-600 mb-1 block">Desa</label>
                                <select name="id_desa" class="w-full text-sm bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:bg-white focus:ring-2 focus:ring-blue-500/20 outline-none">
                                    <option value="">Semua Desa</option>
                                    @foreach($desaList as $desa)
                                        <option value="{{ $desa->id_desa }}" {{ request('id_desa') == $desa->id_desa ? 'selected' : '' }}>{{ $desa->nama_desa }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-slate-600 mb-1 block">Kategori IKL</label>
                                <select name="kategori_ikl" class="w-full text-sm bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:bg-white focus:ring-2 focus:ring-blue-500/20 outline-none">
                                    <option value="">Semua Instrumen</option>
                                    <option value="sumur_gali" {{ request('kategori_ikl') === 'sumur_gali' ? 'selected' : '' }}>Sumur Gali</option>
                                    <option value="sumur_bor" {{ request('kategori_ikl') === 'sumur_bor' ? 'selected' : '' }}>Sumur Bor</option>
                                    <option value="sarang_nyamuk_dbd"      {{ request('kategori_ikl') === 'sarang_nyamuk_dbd'      ? 'selected' : '' }}>Sarang Nyamuk / DBD</option>
                                    <option value="jamban_limbah_stbm"     {{ request('kategori_ikl') === 'jamban_limbah_stbm'     ? 'selected' : '' }}>Jamban & Limbah</option>
                                    <option value="tumpukan_sampah"        {{ request('kategori_ikl') === 'tumpukan_sampah'        ? 'selected' : '' }}>Tumpukan Sampah</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-slate-600 mb-1 block">Dari Tanggal IKL</label>
                                <input type="date" name="date_from" value="{{ request('date_from', request('dari_tanggal')) }}"
                                    class="w-full text-sm bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:bg-white focus:ring-2 focus:ring-blue-500/20 outline-none">
                            </div>
                            <div>
                                <label class="text-xs font-medium text-slate-600 mb-1 block">Sampai Tanggal IKL</label>
                                <input type="date" name="date_to" value="{{ request('date_to', request('sampai_tanggal')) }}"
                                    class="w-full text-sm bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:bg-white focus:ring-2 focus:ring-blue-500/20 outline-none">
                            </div>
                            <div>
                                <label class="text-xs font-medium text-slate-600 mb-1 block">Intervensi Fisik</label>
                                <select name="intervensi" class="w-full text-sm bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:bg-white focus:ring-2 focus:ring-blue-500/20 outline-none">
                                    <option value="">Semua</option>
                                    <option value="ada" {{ request('intervensi') === 'ada' ? 'selected' : '' }}>Ada Intervensi</option>
                                    <option value="tidak" {{ request('intervensi') === 'tidak' ? 'selected' : '' }}>Tanpa Intervensi</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            {{-- Active Filter Chips --}}
            @if($hasAnyRekap)
            <div class="flex flex-wrap items-center gap-2 mb-4 text-xs">
                <span class="text-slate-500 font-medium">Filter aktif:</span>
                @if(request('q'))
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-slate-100 border border-slate-200 text-slate-700 font-medium">
                        Cari: "{{ request('q') }}"
                        <a href="{{ request()->fullUrlWithQuery(['q' => null, 'tab' => 'rekap']) }}" class="hover:text-red-600 ml-0.5">×</a>
                    </span>
                @endif
                @if(request('risiko') ?? request('kategori_risiko'))
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-blue-50 border border-blue-200 text-blue-700 font-medium">
                        Risiko: {{ ucwords(str_replace('_', ' ', request('risiko') ?? request('kategori_risiko'))) }}
                        <a href="{{ request()->fullUrlWithQuery(['risiko' => null, 'kategori_risiko' => null, 'page_rekap' => null, 'tab' => 'rekap']) }}" class="hover:text-red-600 ml-0.5">×</a>
                    </span>
                @endif
                @if(request('date_from') || request('date_to') || request('dari_tanggal') || request('sampai_tanggal'))
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-blue-50 border border-blue-200 text-blue-700 font-medium">
                        Periode: {{ request('date_from', request('dari_tanggal')) ?? '...' }} – {{ request('date_to', request('sampai_tanggal')) ?? '...' }}
                        <a href="{{ request()->fullUrlWithQuery(['date_from' => null, 'date_to' => null, 'dari_tanggal' => null, 'sampai_tanggal' => null, 'page_rekap' => null, 'tab' => 'rekap']) }}" class="hover:text-red-600 ml-0.5">×</a>
                    </span>
                @endif
                @if(request('id_desa'))
                    <span class="inline-flex items-center gap-1 rounded-full border border-blue-200 bg-blue-50 px-2.5 py-1 font-medium text-blue-700">Desa: {{ $desaList->firstWhere('id_desa', request('id_desa'))?->nama_desa ?? request('id_desa') }} <a href="{{ request()->fullUrlWithQuery(['id_desa' => null, 'page_rekap' => null, 'tab' => 'rekap']) }}" aria-label="Hapus filter desa">×</a></span>
                @endif
                @if(request('kategori_ikl'))
                    <span class="inline-flex items-center gap-1 rounded-full border border-blue-200 bg-blue-50 px-2.5 py-1 font-medium text-blue-700">Instrumen: {{ ucwords(str_replace('_', ' ', request('kategori_ikl'))) }} <a href="{{ request()->fullUrlWithQuery(['kategori_ikl' => null, 'page_rekap' => null, 'tab' => 'rekap']) }}" aria-label="Hapus filter instrumen">×</a></span>
                @endif
                @if(request('intervensi'))
                    <span class="inline-flex items-center gap-1 rounded-full border border-blue-200 bg-blue-50 px-2.5 py-1 font-medium text-blue-700">Intervensi: {{ request('intervensi') === 'ada' ? 'Ada' : 'Tidak ada' }} <a href="{{ request()->fullUrlWithQuery(['intervensi' => null, 'page_rekap' => null, 'tab' => 'rekap']) }}" aria-label="Hapus filter intervensi">×</a></span>
                @endif
                <span class="ml-auto text-slate-400">Menampilkan {{ $rekapIkl->count() }} dari {{ $rekapIkl->total() }} data</span>
            </div>
            @endif

            <div class="card overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-bold text-gray-800">Rekap Hasil Inspeksi IKL Air</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Data inspeksi kualitas sarana air yang telah selesai diverifikasi</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Tiket / Pelapor</th>
                                <th>Lokasi</th>
                                <th>Petugas</th>
                                <th>Tanggal IKL</th>
                                <th>Sarana Air</th>
                                <th>Skor</th>
                                <th>Risiko</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rekapIkl as $row)
                            @php
                                $risikoMap = [
                                    'aman'          => ['bg-green-100 text-green-700 border-green-200', '✅ Aman'],
                                    'risiko_sedang' => ['bg-yellow-100 text-yellow-700 border-yellow-200', '⚠️ Sedang'],
                                    'risiko_tinggi' => ['bg-red-100 text-red-700 border-red-200', '🔴 Tinggi'],
                                ];
                                [$risikoClass, $risikoLabel] = $risikoMap[$row->kategori_risiko] ?? ['bg-gray-100 text-gray-600 border-gray-200', $row->kategori_risiko];
                            @endphp
                            <tr>
                                {{-- Tiket --}}
                                <td>
                                    <span class="font-mono text-xs font-bold text-gray-600 bg-gray-100 px-2 py-1 rounded block mb-1">
                                        {{ $row->kode_tiket }}
                                    </span>
                                    <p class="text-sm font-medium text-gray-700">{{ $row->nama_pelapor }}</p>
                                </td>

                                {{-- Lokasi --}}
                                <td>
                                    <p class="text-sm text-gray-600">{{ $row->nama_desa ?? '-' }}</p>
                                    <p class="text-xs text-gray-400">RT {{ $row->rt }} / RW {{ $row->rw }}</p>
                                </td>

                                {{-- Petugas --}}
                                <td>
                                    <p class="text-sm font-medium text-gray-700">{{ $row->nama_petugas }}</p>
                                    <p class="text-xs text-gray-400">{{ $row->wilayah_kerja }}</p>
                                </td>

                                {{-- Tanggal IKL --}}
                                <td>
                                    <p class="text-sm text-gray-700">{{ \Carbon\Carbon::parse($row->tanggal_kunjungan)->format('d M Y') }}</p>
                                </td>

                                {{-- Jenis Sarana --}}
                                <td>
                                    <span class="text-xs font-medium text-gray-600 bg-gray-100 px-2 py-1 rounded capitalize">
                                        {{ str_replace('_', ' ', $row->jenis_sarana_air) }}
                                    </span>
                                    <p class="text-xs text-gray-400 mt-1">Jarak: {{ $row->jarak_sumber_pencemar }}m</p>
                                </td>

                                {{-- Skor --}}
                                <td class="text-center">
                                    <span class="text-lg font-bold {{ $row->total_skor_ya >= 3 ? 'text-red-600' : ($row->total_skor_ya >= 1 ? 'text-yellow-600' : 'text-green-600') }}">
                                        {{ $row->total_skor_ya }}
                                    </span>
                                    <span class="text-xs text-gray-400">/5</span>
                                </td>

                                {{-- Risiko --}}
                                <td>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $risikoClass }}">
                                        {{ $risikoLabel }}
                                    </span>
                                </td>

                                {{-- Aksi --}}
                                <td class="text-center">
                                    <a href="{{ route('inspeksi.show', $row->id_inspeksi) }}"
                                        class="btn-secondary btn-sm" title="Lihat Detail Inspeksi">
                                        <i class="fas fa-eye text-gray-500"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-12">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center">
                                            <i class="fas fa-flask text-2xl text-gray-300"></i>
                                        </div>
                                        <p class="text-gray-400 font-medium">Belum ada data inspeksi</p>
                                        <p class="text-xs text-gray-300">Data akan muncul setelah petugas mengisi form IKL</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($rekapIkl->hasPages())
                <div class="flex flex-col gap-3 border-t border-gray-100 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <span class="text-xs font-medium text-slate-500">{{ $rekapIkl->firstItem() ?? 0 }}–{{ $rekapIkl->lastItem() ?? 0 }} dari {{ $rekapIkl->total() }} inspeksi</span>
                    @include('partials.pagination-circles', ['paginator' => $rekapIkl])
                </div>
                @endif
            </div>
        </div>

    </div>{{-- end x-data tab --}}
</div>

{{-- ════════════════════════════════════════ --}}
{{-- MODAL: JADWALKAN KUNJUNGAN              --}}
{{-- ════════════════════════════════════════ --}}
<div id="jadwalModal" class="fixed inset-0 z-50 hidden bg-gray-900/50 backdrop-blur-sm items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl">
        <div class="flex justify-between items-center pb-3 border-b border-gray-100 mb-4">
            <h3 class="font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-calendar-plus text-blue-600"></i> Jadwalkan Kunjungan
            </h3>
            <button type="button" onclick="closeJadwalModal()" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
        </div>

        <p class="text-xs text-gray-500 mb-4">
            Laporan: <span id="jadwal_kode_tiket" class="font-mono font-bold text-gray-700"></span>
        </p>

        <form id="jadwalForm" method="POST" action="" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Petugas Sanitarian <span class="text-red-500">*</span></label>
                <select name="id_operator" required class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-300">
                    <option value="" disabled selected>— Pilih Petugas —</option>
                    @foreach($sanitarians as $san)
                    <option value="{{ $san->id_user }}">
                        {{ $san->nama_lengkap }}{{ $san->wilayah_kerja ? ' — ' . $san->wilayah_kerja : '' }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Tanggal Kunjungan <span class="text-red-500">*</span></label>
                @include('schedules._date-picker')
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Jam Mulai <span class="text-red-500">*</span></label>
                <input type="time" name="jam_mulai" required
                    class="w-full text-sm rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-300 @error('jam_mulai') border border-red-500 @else border border-gray-200 @enderror">
                @error('jam_mulai')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Jenis Kunjungan</label>
                <select name="jenis_kunjungan" class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-300">
                    <option value="ikl_laporan_warga" selected>IKL Laporan Warga</option>
                    <option value="ikl_rutin_rt">IKL Rutin RT</option>
                </select>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeJadwalModal()" class="btn-secondary btn-sm">Batal</button>
                <button type="submit" class="btn-primary btn-sm">
                    <i class="fas fa-calendar-check"></i> Jadwalkan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ════════════════════════════════════════ --}}
{{-- MODAL: TOLAK LAPORAN                    --}}
{{-- ════════════════════════════════════════ --}}
<div id="tolakModal" class="fixed inset-0 z-50 hidden bg-gray-900/50 backdrop-blur-sm items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl">
        <div class="flex justify-between items-center pb-3 border-b border-gray-100 mb-4">
            <h3 class="font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-times-circle text-red-500"></i> Tolak Laporan
            </h3>
            <button type="button" onclick="closeTolakModal()" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
        </div>

        <p class="text-xs text-gray-500 mb-4">
            Laporan: <span id="tolak_kode_tiket" class="font-mono font-bold text-gray-700"></span>
        </p>

        <form id="tolakForm" method="POST" action="" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Alasan Penolakan <span class="text-red-500">*</span></label>
                <textarea name="alasan_penolakan" rows="3" required minlength="5"
                    placeholder="Jelaskan alasan penolakan laporan ini..."
                    class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-300 resize-none"></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeTolakModal()" class="btn-secondary btn-sm">Batal</button>
                <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-red-500 hover:bg-red-600 rounded-lg transition-colors">
                    <i class="fas fa-times"></i> Tolak Laporan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ════════════════════════════════════════ --}}
{{-- MODAL: DETAIL LAPORAN                   --}}
{{-- ════════════════════════════════════════ --}}
<div id="detailModal" class="fixed inset-0 z-50 hidden bg-gray-900/50 backdrop-blur-sm items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center pb-3 border-b border-gray-100 mb-4">
            <h3 class="font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-file-alt text-blue-600"></i>
                Detail Laporan <span id="detail_kode" class="font-mono text-blue-600 text-sm"></span>
            </h3>
            <button type="button" onclick="closeDetailModal()" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
        </div>

        <div class="space-y-3 text-sm">
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                    <p class="text-xs text-gray-400 font-medium">Pelapor</p>
                    <p id="detail_pelapor" class="font-semibold text-gray-700 mt-0.5">-</p>
                </div>
                <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                    <p class="text-xs text-gray-400 font-medium">No. WhatsApp</p>
                    <p id="detail_wa" class="font-semibold text-gray-700 mt-0.5">-</p>
                </div>
            </div>
            <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                <p class="text-xs text-gray-400 font-medium">Lokasi</p>
                <p id="detail_lokasi" class="font-semibold text-gray-700 mt-0.5">-</p>
            </div>
            <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                <p class="text-xs text-gray-400 font-medium mb-1">Deskripsi Masalah</p>
                <p id="detail_deskripsi" class="text-gray-600 text-xs leading-relaxed">-</p>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                    <p class="text-xs text-gray-400 font-medium">Status</p>
                    <span id="detail_status" class="inline-block mt-0.5 text-xs font-semibold px-2 py-1 rounded-full border">-</span>
                </div>
                <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                    <p class="text-xs text-gray-400 font-medium">Tanggal Lapor</p>
                    <p id="detail_tanggal" class="font-semibold text-gray-700 mt-0.5">-</p>
                </div>
            </div>
            <div id="detail_foto_container" class="hidden">
                <p class="text-xs text-gray-400 font-medium mb-2">Foto Bukti</p>
                <button id="detail_foto_trigger" type="button" data-image-viewer-trigger
                    class="relative group cursor-pointer overflow-hidden rounded-xl block focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500"
                    aria-label="Lihat foto bukti">
                    <img id="detail_foto" src="" alt="Foto Bukti" class="rounded-xl max-h-48 object-cover border border-gray-200">
                    <span class="absolute inset-0 flex flex-col items-center justify-center gap-2 bg-black/40 text-white opacity-0 transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100">
                        <span class="rounded-full bg-white/20 p-3 backdrop-blur-sm">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.5-6.75 9.75-6.75S21.75 12 21.75 12s-3.5 6.75-9.75 6.75S2.25 12 2.25 12Z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </span>
                        <span class="text-xs font-semibold">Lihat Foto</span>
                    </span>
                </button>
            </div>
        </div>

        <div class="flex justify-end mt-4">
            <button type="button" onclick="closeDetailModal()" class="btn-secondary btn-sm">Tutup</button>
        </div>
    </div>
</div>
@endsection

@section('modals')
    @include('partials.image-viewer')
@endsection

@section('scripts')
<script>
// ── Helpers ───────────────────────────────────────────────────────
function showModal(id) {
    const el = document.getElementById(id);
    el.classList.remove('hidden');
    el.classList.add('flex');
}
function hideModal(id) {
    const el = document.getElementById(id);
    el.classList.add('hidden');
    el.classList.remove('flex');
}

// ── Modal Jadwalkan ───────────────────────────────────────────────
function openJadwalModal(idLaporan, kodeTiket) {
    document.getElementById('jadwal_kode_tiket').textContent = kodeTiket;
    const jadwalForm = document.getElementById('jadwalForm');
    jadwalForm.reset();
    jadwalForm.action = `/reports/${idLaporan}/jadwalkan`;
    showModal('jadwalModal');
}
function closeJadwalModal() { hideModal('jadwalModal'); }

// ── Modal Tolak ───────────────────────────────────────────────────
function openTolakModal(idLaporan, kodeTiket) {
    document.getElementById('tolak_kode_tiket').textContent = kodeTiket;
    document.getElementById('tolakForm').action = `/reports/${idLaporan}/tolak`;
    showModal('tolakModal');
}
function closeTolakModal() { hideModal('tolakModal'); }

// ── Modal Detail ──────────────────────────────────────────────────
const STATUS_MAP = {
    menunggu:    { label: '⏳ Menunggu',    cls: 'bg-yellow-100 text-yellow-700 border-yellow-200' },
    dibaca:      { label: '👁 Dibaca',      cls: 'bg-blue-100 text-blue-700 border-blue-200' },
    diterima:    { label: '✅ Diterima',    cls: 'bg-green-100 text-green-700 border-green-200' },
    dijadwalkan: { label: '📅 Dijadwalkan', cls: 'bg-indigo-100 text-indigo-700 border-indigo-200' },
    selesai:     { label: '🎉 Selesai',     cls: 'bg-emerald-100 text-emerald-700 border-emerald-200' },
    ditolak:     { label: '❌ Ditolak',     cls: 'bg-red-100 text-red-700 border-red-200' },
};

function openDetailModal(laporan) {
    document.getElementById('detail_kode').textContent = laporan.kode_tiket || '-';
    document.getElementById('detail_pelapor').textContent = laporan.nama_pelapor || '-';
    document.getElementById('detail_wa').textContent = laporan.no_wa || 'Tidak tersedia';
    document.getElementById('detail_lokasi').textContent =
        (laporan.desa || '-') + ' RT ' + (laporan.rt || '-') + ' / RW ' + (laporan.rw || '-');
    document.getElementById('detail_deskripsi').textContent = laporan.deskripsi || 'Tidak ada deskripsi.';
    document.getElementById('detail_tanggal').textContent = laporan.created_at || '-';

    const statusEl = document.getElementById('detail_status');
    const s = STATUS_MAP[laporan.status] || { label: laporan.status, cls: 'bg-gray-100 text-gray-600 border-gray-200' };
    statusEl.textContent = s.label;
    statusEl.className = 'inline-block mt-0.5 text-xs font-semibold px-2 py-1 rounded-full border ' + s.cls;

    const fotoContainer = document.getElementById('detail_foto_container');
    const fotoTrigger = document.getElementById('detail_foto_trigger');
    if (laporan.foto_bukti) {
        const fotoUrl = @json(asset('storage')) + '/' + laporan.foto_bukti;
        document.getElementById('detail_foto').src = fotoUrl;
        fotoTrigger.dataset.imageUrl = fotoUrl;
        fotoTrigger.dataset.imageCaption = 'Foto bukti laporan ' + (laporan.kode_tiket || '');
        fotoContainer.classList.remove('hidden');
    } else {
        fotoContainer.classList.add('hidden');
    }

    showModal('detailModal');
}
function closeDetailModal() { hideModal('detailModal'); }

// ── Tutup modal dengan klik backdrop / Escape ─────────────────────
['jadwalModal', 'tolakModal', 'detailModal'].forEach(id => {
    document.getElementById(id).addEventListener('click', function(e) {
        if (e.target === this) hideModal(id);
    });
});

window.setupScheduleCalendars();

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') ['jadwalModal', 'tolakModal', 'detailModal'].forEach(hideModal);
});

// ── Buka tab rekap jika dari URL ?tab=rekap ───────────────────────
@if(request('tab') === 'rekap')
document.addEventListener('DOMContentLoaded', () => {
    // Alpine init: trigger tab change
    setTimeout(() => {
        document.querySelector('[\\@click="tab = \'rekap\'"]')?.click();
    }, 50);
});
@endif
</script>
@endsection

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
        <div class="card p-4">
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
                    ? 'border-b-2 border-red-500 text-red-600 font-semibold'
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
                    ? 'border-b-2 border-red-500 text-red-600 font-semibold'
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
            <div class="card overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h3 class="font-bold text-gray-800">Laporan Masuk</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Laporan berstatus menunggu verifikasi dan dijadwalkan</p>
                    </div>
                    <form method="GET" action="{{ route('reports.index') }}" class="flex gap-2 flex-wrap">
                        <select name="status_laporan" onchange="this.form.submit()" class="text-xs border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-300">
                            <option value="">Semua Status</option>
                            <option value="menunggu"    {{ request('status_laporan') === 'menunggu'    ? 'selected' : '' }}>Menunggu</option>
                            <option value="dijadwalkan" {{ request('status_laporan') === 'dijadwalkan' ? 'selected' : '' }}>Dijadwalkan</option>
                            <option value="dibaca"      {{ request('status_laporan') === 'dibaca'      ? 'selected' : '' }}>Dibaca</option>
                        </select>
                    </form>
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

                                        {{-- Tombol Form IKL (jika sudah dijadwalkan) --}}
                                        @if($laporan->status_laporan === 'dijadwalkan' && $laporan->jadwal)
                                        <a href="{{ route('inspeksi.form', $laporan->jadwal->id_jadwal) }}"
                                            class="btn-secondary btn-sm text-green-600" title="Isi Form IKL">
                                            <i class="fas fa-clipboard-check"></i>
                                        </a>
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
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $laporanAktif->appends(['page_rekap' => request('page_rekap')])->links() }}
                </div>
                @endif
            </div>
        </div>

        {{-- ════════════════════════════════════ --}}
        {{-- TAB 2: REKAP HASIL INSPEKSI IKL AIR --}}
        {{-- ════════════════════════════════════ --}}
        <div x-show="tab === 'rekap'" x-transition.opacity x-cloak>

            {{-- Filter & Ekspor --}}
            <div class="card p-4 mb-4">
                <form method="GET" action="{{ route('reports.index') }}" class="flex flex-wrap gap-3 items-end">
                    <input type="hidden" name="tab" value="rekap">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Dari Tanggal</label>
                        <input type="date" name="dari_tanggal" value="{{ request('dari_tanggal') }}"
                            class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-300">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Sampai Tanggal</label>
                        <input type="date" name="sampai_tanggal" value="{{ request('sampai_tanggal') }}"
                            class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-300">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Kategori Risiko</label>
                        <select name="kategori_risiko" class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-300">
                            <option value="">Semua</option>
                            <option value="aman"          {{ request('kategori_risiko') === 'aman' ? 'selected' : '' }}>✅ Aman</option>
                            <option value="risiko_sedang" {{ request('kategori_risiko') === 'risiko_sedang' ? 'selected' : '' }}>⚠️ Risiko Sedang</option>
                            <option value="risiko_tinggi" {{ request('kategori_risiko') === 'risiko_tinggi' ? 'selected' : '' }}>🔴 Risiko Tinggi</option>
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="btn-primary btn-sm">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="{{ route('reports.index') }}?tab=rekap" class="btn-secondary btn-sm">Reset</a>
                        <a href="{{ route('reports.export', request()->query()) }}" class="btn-secondary btn-sm text-green-600" title="Ekspor CSV">
                            <i class="fas fa-file-csv"></i> Ekspor
                        </a>
                    </div>
                </form>
            </div>

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
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $rekapIkl->appends(['page_aktif' => request('page_aktif')])->links() }}
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
                <input type="date" name="tanggal_kunjungan" required
                    min="{{ date('Y-m-d') }}"
                    class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-300">
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
                <img id="detail_foto" src="" alt="Foto Bukti" class="rounded-xl max-h-48 object-cover border border-gray-200">
            </div>
        </div>

        <div class="flex justify-end mt-4">
            <button type="button" onclick="closeDetailModal()" class="btn-secondary btn-sm">Tutup</button>
        </div>
    </div>
</div>

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
    document.getElementById('jadwalForm').action = `/reports/${idLaporan}/jadwalkan`;
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
    if (laporan.foto_bukti) {
        document.getElementById('detail_foto').src = '/storage/' + laporan.foto_bukti;
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

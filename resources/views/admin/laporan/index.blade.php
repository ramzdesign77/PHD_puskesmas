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
        $selectedStatus = request('status', request('status_laporan'));
    @endphp
    <form method="GET" action="{{ route('admin.laporan.index') }}" class="mb-4">
        <div class="rounded-2xl border border-slate-200/80 bg-white p-3.5 shadow-sm sm:p-4">
            <div class="flex flex-wrap items-center gap-2">
                <div class="relative min-w-48 flex-1">
                    <i class="fas fa-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari tiket, pelapor, WhatsApp, deskripsi..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-9 pr-3 text-sm outline-none transition focus:border-blue-400 focus:bg-white focus:ring-2 focus:ring-blue-500/20">
                </div>
                <select name="status" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:bg-white focus:ring-2 focus:ring-blue-500/20">
                    <option value="">Semua Status</option>
                    @foreach(['menunggu' => 'Menunggu', 'dibaca' => 'Dibaca', 'dijadwalkan' => 'Dijadwalkan', 'selesai' => 'Selesai', 'ditolak' => 'Ditolak'] as $status => $label)
                        <option value="{{ $status }}" {{ $selectedStatus === $status ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="id_desa" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:bg-white focus:ring-2 focus:ring-blue-500/20">
                    <option value="">Semua Desa</option>
                    @foreach($desaList as $desa)
                        <option value="{{ $desa->id_desa }}" {{ request('id_desa') == $desa->id_desa ? 'selected' : '' }}>{{ $desa->nama_desa }}</option>
                    @endforeach
                </select>
                <button type="button" onclick="document.getElementById('report-advanced-filters').classList.toggle('hidden')"
                    class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-100">
                    <i class="fas fa-sliders mr-1"></i> Filter Lainnya
                </button>
                <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">Terapkan</button>
                @if($hasReportFilters)
                    <a href="{{ route('admin.laporan.index') }}" class="px-2 text-xs font-medium text-slate-500 hover:text-red-600">Reset</a>
                @endif
            </div>
            <div id="report-advanced-filters" class="{{ collect(['kategori', 'date_from', 'date_to', 'sort'])->contains(fn ($filter) => filled(request($filter))) ? '' : 'hidden' }} mt-3 border-t border-slate-100 pt-3">
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <select name="kategori" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:bg-white focus:ring-2 focus:ring-blue-500/20">
                        <option value="">Semua Kategori</option>
                        @foreach(['air_sumur_keruh_berbau' => 'Air Sumur Keruh / Berbau', 'sarang_nyamuk_dbd' => 'Sarang Nyamuk / DBD', 'jamban_limbah_stbm' => 'Jamban & Limbah (STBM)', 'tumpukan_sampah' => 'Tumpukan Sampah'] as $category => $label)
                            <option value="{{ $category }}" {{ request('kategori') === $category ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" aria-label="Dari tanggal lapor" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:bg-white focus:ring-2 focus:ring-blue-500/20">
                    <input type="date" name="date_to" value="{{ request('date_to') }}" aria-label="Sampai tanggal lapor" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:bg-white focus:ring-2 focus:ring-blue-500/20">
                    <select name="sort" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:bg-white focus:ring-2 focus:ring-blue-500/20">
                        <option value="">Terbaru</option>
                        <option value="terlama" {{ request('sort') === 'terlama' ? 'selected' : '' }}>Terlama</option>
                    </select>
                </div>
            </div>
        </div>
    </form>
    <div class="mb-3 flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500">
        <span>{{ $laporan->firstItem() ?? 0 }}–{{ $laporan->lastItem() ?? 0 }} dari {{ $laporan->total() }} laporan</span>
        @if($hasReportFilters)<span class="rounded-full bg-blue-50 px-2.5 py-1 font-medium text-blue-700">Filter aktif</span>@endif
    </div>

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
        @include('partials.pagination-circles', ['paginator' => $laporan])
    </div>
</div>
@endsection

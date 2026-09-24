@extends('layouts.app')

@section('title', 'Rekap Hasil IKL Air')

@section('page-title', 'Hasil Inspeksi IKL Air')
@section('page-subtitle', 'Rekap data inspeksi kualitas air dari seluruh laporan warga')

@section('content')
<div class="space-y-6 fade-in">

    {{-- ── Statistik Ringkasan ──────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="card p-5">
            <p class="text-xs text-gray-400 font-medium uppercase tracking-wider">Total Inspeksi</p>
            <p class="text-3xl font-bold text-gray-800 mt-1">{{ $rekapStatistik->total_inspeksi ?? 0 }}</p>
        </div>
        <div class="card p-5 border-l-4 border-emerald-400">
            <p class="text-xs text-emerald-600 font-medium uppercase tracking-wider">Aman</p>
            <p class="text-3xl font-bold text-emerald-700 mt-1">{{ $rekapStatistik->jumlah_aman ?? 0 }}</p>
        </div>
        <div class="card p-5 border-l-4 border-amber-400">
            <p class="text-xs text-amber-600 font-medium uppercase tracking-wider">Risiko Sedang</p>
            <p class="text-3xl font-bold text-amber-700 mt-1">{{ $rekapStatistik->jumlah_sedang ?? 0 }}</p>
        </div>
        <div class="card p-5 border-l-4 border-red-400">
            <p class="text-xs text-red-600 font-medium uppercase tracking-wider">Risiko Tinggi</p>
            <p class="text-3xl font-bold text-red-700 mt-1">{{ $rekapStatistik->jumlah_tinggi ?? 0 }}</p>
        </div>
    </div>

    {{-- ── Filter ────────────────────────────────────────────────────────── --}}
    <div class="card p-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="text-xs text-gray-500 font-medium block mb-1">Kategori Risiko</label>
                <select name="kategori_risiko" class="form-input text-sm" onchange="this.form.submit()">
                    <option value="">Semua Kategori</option>
                    <option value="aman"          {{ request('kategori_risiko') === 'aman'          ? 'selected' : '' }}>Aman</option>
                    <option value="risiko_sedang"  {{ request('kategori_risiko') === 'risiko_sedang'  ? 'selected' : '' }}>Risiko Sedang</option>
                    <option value="risiko_tinggi"  {{ request('kategori_risiko') === 'risiko_tinggi'  ? 'selected' : '' }}>Risiko Tinggi</option>
                </select>
            </div>
            <div>
                <label class="text-xs text-gray-500 font-medium block mb-1">Petugas</label>
                <select name="id_operator" class="form-input text-sm" onchange="this.form.submit()">
                    <option value="">Semua Petugas</option>
                    @foreach($semuaPetugas as $p)
                        <option value="{{ $p->id_petugas }}" {{ request('id_operator') == $p->id_petugas ? 'selected' : '' }}>
                            {{ $p->nama_petugas }}
                        </option>
                    @endforeach
                </select>
            </div>
            @if(request()->hasAny(['kategori_risiko', 'id_operator']))
            <a href="{{ route('admin.inspeksi-ikl.index') }}" class="btn-secondary btn-sm">
                <i class="fas fa-times mr-1"></i> Reset Filter
            </a>
            @endif
        </form>
    </div>

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
                                <span class="text-xs font-bold text-gray-700">{{ $ikl->total_skor_ya ?? 0 }}/5</span>
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

        @if($jadwalList->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $jadwalList->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

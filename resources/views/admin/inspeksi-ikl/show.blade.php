@extends('layouts.app')

@section('title', 'Detail Hasil IKL')

@section('page-title', 'Detail Hasil Inspeksi IKL')
@section('page-subtitle', 'Hasil pemeriksaan teknis kualitas air oleh sanitarian')

@section('content')
<div class="space-y-6 fade-in">

    <a href="{{ route('admin.inspeksi-ikl.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-800 transition-colors">
        <i class="fas fa-arrow-left text-xs"></i> Kembali ke Rekap IKL
    </a>

    @php
        $ikl      = $jadwal->inspeksiIkl;
        $laporan  = $jadwal->laporan;
        $risikoBg = match($ikl->kategori_risiko) {
            'aman'          => 'bg-emerald-50 border-emerald-200 text-emerald-800',
            'risiko_sedang' => 'bg-amber-50 border-amber-200 text-amber-800',
            'risiko_tinggi' => 'bg-red-50 border-red-200 text-red-800',
            default         => 'bg-gray-50 border-gray-200 text-gray-800',
        };
        $risikoIcon = match($ikl->kategori_risiko) {
            'aman'          => 'fa-check-circle text-emerald-500',
            'risiko_sedang' => 'fa-exclamation-triangle text-amber-500',
            'risiko_tinggi' => 'fa-times-circle text-red-500',
            default         => 'fa-question-circle',
        };
        $risikoLabel = match($ikl->kategori_risiko) {
            'aman'          => 'AMAN',
            'risiko_sedang' => 'RISIKO SEDANG',
            'risiko_tinggi' => 'RISIKO TINGGI',
            default         => strtoupper($ikl->kategori_risiko),
        };
    @endphp

    {{-- ── Banner Kategori Risiko ───────────────────────────────────────── --}}
    <div class="card p-5 border {{ $risikoBg }} flex items-center gap-4">
        <i class="fas {{ $risikoIcon }} text-3xl"></i>
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider opacity-70">Hasil Penilaian IKL</p>
            <p class="text-2xl font-bold">{{ $risikoLabel }}</p>
            <p class="text-sm opacity-80">Skor: {{ $ikl->total_skor_ya }}/5 parameter bermasalah</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- ── Info Laporan & Jadwal ────────────────────────────────────── --}}
        <div class="card p-6 space-y-4">
            <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                <i class="fas fa-file-alt text-medical-red"></i> Informasi Laporan
            </h3>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-400">Kode Tiket</span>
                    <span class="font-mono font-medium">{{ $laporan->kode_tiket ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Pelapor</span>
                    <span class="font-medium">{{ $laporan->nama_pelapor ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Desa / RT RW</span>
                    <span>{{ $laporan->desa->nama_desa ?? '-' }} / RT {{ $laporan->rt }} RW {{ $laporan->rw }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Sanitarian</span>
                    <span class="font-medium">{{ $jadwal->petugas->nama_petugas ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Tanggal Inspeksi</span>
                    <span>{{ \Carbon\Carbon::parse($ikl->tanggal_inspeksi)->translatedFormat('d F Y') }}</span>
                </div>
            </div>
        </div>

        {{-- ── Parameter Teknis ─────────────────────────────────────────── --}}
        <div class="card p-6 space-y-4">
            <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                <i class="fas fa-water text-blue-500"></i> Data Teknis Sarana Air
            </h3>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-400">Jenis Sarana Air</span>
                    <span class="font-medium capitalize">{{ str_replace('_', ' ', $ikl->jenis_sarana_air) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Jarak Sumber Pencemar</span>
                    <span class="font-medium {{ $ikl->jarak_sumber_pencemar < 10 ? 'text-red-600 font-bold' : '' }}">
                        {{ $ikl->jarak_sumber_pencemar }} meter
                        @if($ikl->jarak_sumber_pencemar < 10)
                            <span class="text-xs text-red-500 ml-1">(⚠ &lt;10m)</span>
                        @endif
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Checklist Parameter IKL ──────────────────────────────────────── --}}
    <div class="card p-6">
        <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <i class="fas fa-clipboard-check text-medical-red"></i> Checklist Parameter Sanitasi
        </h3>
        <div class="space-y-3">
            @foreach($labelParameter as $field => $label)
            @php $nilai = $ikl->$field; @endphp
            <div class="flex items-center justify-between p-3 rounded-xl border
                {{ $nilai ? 'bg-red-50 border-red-200' : 'bg-gray-50 border-gray-100' }}">
                <span class="text-sm {{ $nilai ? 'text-red-700 font-medium' : 'text-gray-600' }}">
                    {{ $label }}
                </span>
                <span class="text-xs font-bold px-3 py-1 rounded-full
                    {{ $nilai ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }}">
                    {{ $nilai ? 'YA' : 'TIDAK' }}
                </span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ── Rekomendasi Sanitarian ───────────────────────────────────────── --}}
    <div class="card p-6">
        <h3 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
            <i class="fas fa-notes-medical text-indigo-500"></i> Rekomendasi Sanitarian
        </h3>
        <p class="text-sm text-gray-700 leading-relaxed bg-indigo-50 border border-indigo-100 rounded-xl p-4">
            {{ $ikl->rekomendasi_sanitarian }}
        </p>
    </div>

</div>
@endsection

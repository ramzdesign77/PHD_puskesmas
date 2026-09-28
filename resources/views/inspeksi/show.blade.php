@extends('layouts.app')

@section('title', 'Detail Hasil Inspeksi Kualitas Air')
@section('page-title', 'Detail Hasil IKL Air')
@section('page-subtitle', 'Informasi lengkap hasil inspeksi kualitas lingkungan sarana air')

@section('content')
<div class="max-w-4xl mx-auto space-y-6 fade-in">

    {{-- Aksi Atas --}}
    <div class="flex flex-col sm:flex-row justify-between gap-4">
        <a href="javascript:history.back()" class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-gray-700 bg-white border border-gray-200 px-4 py-2 rounded-xl shadow-sm w-fit transition-colors">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <button onclick="window.print()" class="inline-flex items-center gap-2 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-xl shadow-md w-fit transition-all transform hover:-translate-y-0.5">
            <i class="fas fa-print"></i> Cetak Laporan
        </button>
    </div>

    {{-- Kategori Risiko Banner --}}
    @php
        $risikoMap = [
            'aman'          => ['bg-green-100 border-green-200 text-green-800', '✅ AMAN (Risiko Rendah)', 'Sarana air bersih aman dari risiko pencemaran secara fisik.'],
            'risiko_sedang' => ['bg-yellow-100 border-yellow-200 text-yellow-800', '⚠️ RISIKO SEDANG', 'Terdapat beberapa kondisi fisik yang berisiko mencemari sarana air.'],
            'risiko_tinggi' => ['bg-red-100 border-red-200 text-red-800', '🔴 RISIKO TINGGI', 'Banyak ditemukan kondisi berbahaya! Sarana air sangat rawan pencemaran.'],
        ];
        [$bannerClass, $bannerTitle, $bannerDesc] = $risikoMap[$inspeksi->kategori_risiko] ?? ['bg-gray-100 border-gray-200 text-gray-800', 'TIDAK DIKETAHUI', ''];
    @endphp
    
    <div class="rounded-2xl border p-5 flex flex-col md:flex-row items-center justify-between gap-4 {{ $bannerClass }} print:border-gray-300">
        <div>
            <p class="text-xs font-bold uppercase tracking-widest opacity-80 mb-1">Status Kualitas Lingkungan</p>
            <h2 class="text-2xl md:text-3xl font-black">{{ $bannerTitle }}</h2>
            <p class="text-sm mt-1 opacity-90">{{ $bannerDesc }}</p>
        </div>
        <div class="text-center bg-white/50 rounded-xl p-3 min-w-32">
            <p class="text-xs font-semibold uppercase">Total Skor Risiko</p>
            <p class="text-4xl font-black {{ $inspeksi->total_skor_ya >= 3 ? 'text-red-600' : ($inspeksi->total_skor_ya >= 1 ? 'text-yellow-600' : 'text-green-600') }}">
                {{ $inspeksi->total_skor_ya }} <span class="text-xl text-gray-400 font-medium">/ 5</span>
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        {{-- Kolom Kiri: Info & Rekomendasi --}}
        <div class="md:col-span-1 space-y-6">
            
            {{-- Data Pelaksanaan --}}
            <div class="card p-5">
                <h3 class="font-bold text-gray-800 text-sm border-b border-gray-100 pb-3 mb-4">Informasi Pelaksanaan</h3>
                <ul class="space-y-4">
                    <li>
                        <p class="text-xs text-gray-400 font-semibold mb-1"><i class="far fa-calendar-alt w-4"></i> Tanggal IKL</p>
                        <p class="text-sm font-medium text-gray-800">{{ \Carbon\Carbon::parse($inspeksi->tanggal_inspeksi)->format('d F Y') }}</p>
                    </li>
                    <li>
                        <p class="text-xs text-gray-400 font-semibold mb-1"><i class="fas fa-user-md w-4"></i> Petugas Sanitarian</p>
                        <p class="text-sm font-medium text-gray-800">{{ $inspeksi->jadwal->operator?->nama_lengkap ?? '-' }}</p>
                        <p class="text-xs text-gray-500">{{ $inspeksi->jadwal->operator?->wilayah_kerja ?? '-' }}</p>
                    </li>
                    @if($inspeksi->jadwal->laporan)
                    <li>
                        <p class="text-xs text-gray-400 font-semibold mb-1"><i class="fas fa-ticket-alt w-4"></i> Referensi Laporan</p>
                        <span class="text-xs font-mono font-bold bg-gray-100 px-2 py-0.5 rounded">{{ $inspeksi->jadwal->laporan->kode_tiket }}</span>
                        <p class="text-sm font-medium text-gray-800 mt-1">{{ $inspeksi->jadwal->laporan->nama_pelapor }}</p>
                        <p class="text-xs text-gray-500">
                            {{ $inspeksi->jadwal->laporan->desa?->nama_desa }} RT {{ $inspeksi->jadwal->laporan->rt }}/{{ $inspeksi->jadwal->laporan->rw }}
                        </p>
                    </li>
                    @endif
                </ul>
            </div>

            {{-- Rekomendasi Sanitarian --}}
            <div class="card p-5 bg-blue-50/50 border-blue-100">
                <h3 class="font-bold text-blue-800 text-sm flex items-center gap-2 border-b border-blue-100 pb-3 mb-4">
                    <i class="fas fa-lightbulb"></i> Rekomendasi & Tindak Lanjut
                </h3>
                <p class="text-sm text-gray-700 whitespace-pre-wrap leading-relaxed">{{ $inspeksi->rekomendasi_sanitarian }}</p>
            </div>
            
        </div>

        {{-- Kolom Kanan: Detail Checklist --}}
        <div class="md:col-span-2 space-y-6">
            
            {{-- Spesifikasi Sarana --}}
            <div class="card p-5">
                <h3 class="font-bold text-gray-800 text-sm border-b border-gray-100 pb-3 mb-4">Spesifikasi Sarana Air</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                        <p class="text-xs text-gray-500 mb-1 font-semibold uppercase">Jenis Sarana</p>
                        <p class="text-sm font-bold text-gray-800 capitalize">
                            {{ str_replace('_', ' ', $inspeksi->jenis_sarana_air) }}
                        </p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                        <p class="text-xs text-gray-500 mb-1 font-semibold uppercase">Jarak Sumber Pencemar</p>
                        <p class="text-sm font-bold text-gray-800">
                            {{ $inspeksi->jarak_sumber_pencemar }} <span class="text-xs font-normal text-gray-500">Meter</span>
                        </p>
                        @if($inspeksi->jarak_sumber_pencemar < 10)
                        <p class="text-[10px] text-red-500 font-semibold mt-1"><i class="fas fa-exclamation-triangle"></i> Kurang dari jarak aman (10m)</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Detail Checklist (5 Poin) --}}
            <div class="card p-0 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="font-bold text-gray-800 text-sm">Rincian Hasil Checklist Fisik</h3>
                </div>
                
                <ul class="divide-y divide-gray-100 text-sm">
                    @php
                        $checklists = [
                            ['1. Dinding sumur/bangunan retak atau bocor?', $inspeksi->p1_dinding_sumur_retak],
                            ['2. Penutup sarana air tidak ada atau tidak rapat?', $inspeksi->p2_penutup_tidak_rapat],
                            ['3. Lantai di sekitar sarana becek, retak, atau kotor?', $inspeksi->p3_lantai_becek_retak],
                            ['4. Saluran Pembuangan Air Limbah (SPAL) tersumbat/tidak ada?', $inspeksi->p4_spal_tersumbat],
                            ['5. Secara fisik, air terlihat keruh, berwarna, atau berbau?', $inspeksi->p5_air_keruh_berbau],
                        ];
                    @endphp

                    @foreach($checklists as [$pertanyaan, $jawabanYa])
                    <li class="flex items-start gap-4 p-4 {{ $jawabanYa ? 'bg-red-50/30' : '' }}">
                        <div class="flex-shrink-0 mt-0.5">
                            @if($jawabanYa)
                                <div class="w-6 h-6 rounded-full bg-red-100 text-red-600 flex items-center justify-center">
                                    <i class="fas fa-times text-xs"></i>
                                </div>
                            @else
                                <div class="w-6 h-6 rounded-full bg-green-100 text-green-600 flex items-center justify-center">
                                    <i class="fas fa-check text-xs"></i>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1">
                            <p class="text-gray-800 font-medium">{{ $pertanyaan }}</p>
                        </div>
                        <div class="flex-shrink-0">
                            @if($jawabanYa)
                                <span class="inline-block px-2.5 py-1 bg-red-100 text-red-700 text-xs font-bold rounded">YA (Buruk)</span>
                            @else
                                <span class="inline-block px-2.5 py-1 bg-green-100 text-green-700 text-xs font-bold rounded">TIDAK (Baik)</span>
                            @endif
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
            
        </div>
    </div>
</div>
@endsection

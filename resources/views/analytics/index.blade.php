@extends('layouts.app')

@section('title', 'Laporan & Analitik')
@section('page-title', 'Laporan & Analitik')
@section('page-subtitle', 'Pantau tren dan distribusi risiko kesehatan lingkungan sarana air')

@section('content')
<div class="space-y-6 fade-in">
    {{-- Summary Widget --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
        @foreach([
            ['label' => 'Total Laporan', 'value' => $summary['total'],       'icon' => 'fa-file-alt',       'color' => 'gray'],
            ['label' => 'Menunggu',      'value' => $summary['menunggu'],    'icon' => 'fa-clock',          'color' => 'yellow'],
            ['label' => 'Dijadwalkan',   'value' => $summary['dijadwalkan'], 'icon' => 'fa-calendar-check', 'color' => 'blue'],
            ['label' => 'Selesai IKL',   'value' => $summary['selesai'],     'icon' => 'fa-check-circle',   'color' => 'green'],
            ['label' => 'Ditolak',       'value' => $summary['ditolak'],     'icon' => 'fa-times-circle',   'color' => 'red'],
        ] as $stat)
        <div class="card p-4">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 truncate">{{ $stat['label'] }}</p>
                    <p class="mt-1 text-2xl font-bold text-gray-800">{{ $stat['value'] }}</p>
                </div>
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-{{ $stat['color'] }}-50">
                    <i class="fas {{ $stat['icon'] }} text-{{ $stat['color'] }}-500 text-lg"></i>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Distribusi Risiko IKL --}}
        <div class="card p-6">
            <div class="mb-5 flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-500">
                    <i class="fas fa-chart-pie"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-800">Distribusi Kategori Risiko IKL Air</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Berdasarkan hasil inspeksi yang telah diselesaikan</p>
                </div>
            </div>
            
            <div class="space-y-4">
                @php
                    $totalIkl = $summary['selesai']; // laporan yang sudah IKL
                    $risikoMap = [
                        'aman'          => ['label' => '✅ Aman', 'color' => 'green'],
                        'risiko_sedang' => ['label' => '⚠️ Risiko Sedang', 'color' => 'yellow'],
                        'risiko_tinggi' => ['label' => '🔴 Risiko Tinggi', 'color' => 'red'],
                    ];
                @endphp

                @if($totalIkl > 0)
                    @foreach($risikoMap as $key => $ui)
                    @php
                        $count = $distribusiRisiko->get($key, 0);
                        $percentage = round(($count / $totalIkl) * 100);
                    @endphp
                    <div>
                        <div class="mb-1 flex items-center justify-between text-sm">
                            <span class="font-medium text-gray-600">{{ $ui['label'] }}</span>
                            <span class="font-bold text-gray-800">{{ $count }} <span class="text-xs text-gray-400 font-normal">({{ $percentage }}%)</span></span>
                        </div>
                        <div class="h-3 overflow-hidden rounded-full bg-gray-100">
                            <div class="h-full rounded-full bg-{{ $ui['color'] }}-500" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="text-center py-6 bg-gray-50 rounded-xl border border-gray-100">
                        <i class="fas fa-flask text-gray-300 text-3xl mb-2"></i>
                        <p class="text-sm font-medium text-gray-500">Belum ada data IKL</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Panel Panduan Cepat --}}
        <div class="card overflow-hidden flex flex-col">
            <div class="border-b border-gray-100 px-6 py-5 flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center text-blue-500">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-800">Panduan Modul IKL Air</h3>
                    <p class="mt-0.5 text-xs text-gray-400">Arsitektur & Alur Kerja</p>
                </div>
            </div>
            <div class="p-6 bg-gray-50 flex-1 space-y-4">
                <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
                    <p class="text-sm font-bold text-gray-700 flex items-center gap-2 mb-2">
                        <i class="fas fa-project-diagram text-blue-500 w-4"></i> Alur Kerja Sistem
                    </p>
                    <ol class="list-decimal pl-5 text-xs text-gray-600 space-y-1.5">
                        <li>Warga mengirimkan laporan via Mobile (API) → Status <strong>Menunggu</strong>.</li>
                        <li>Admin menjadwalkan kunjungan → Status <strong>Dijadwalkan</strong>.</li>
                        <li>Petugas Sanitarian melakukan inspeksi & mengisi form IKL.</li>
                        <li>Skor risiko dihitung otomatis (Aman/Sedang/Tinggi) → Status <strong>Selesai</strong>.</li>
                    </ol>
                </div>
                
                <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
                    <p class="text-sm font-bold text-gray-700 flex items-center gap-2 mb-2">
                        <i class="fas fa-database text-green-500 w-4"></i> Relasi Database Terpusat
                    </p>
                    <p class="text-xs text-gray-600 leading-relaxed">
                        Modul ini menggunakan 4 tabel utama: <code class="bg-gray-100 px-1 py-0.5 rounded text-red-500">users</code>, <code class="bg-gray-100 px-1 py-0.5 rounded text-red-500">laporan_warga</code>, <code class="bg-gray-100 px-1 py-0.5 rounded text-red-500">jadwal_inspeksi</code>, dan <code class="bg-gray-100 px-1 py-0.5 rounded text-red-500">inspeksi_ikl</code>. Profil sanitarian kini tergabung langsung ke tabel users (via kolom NIP/NIK).
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-blue-100 bg-blue-50 px-5 py-4 mt-6 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-blue-500 shadow-sm shrink-0">
                <i class="fas fa-file-csv"></i>
            </div>
            <div>
                <p class="font-semibold text-blue-800">Unduh Rekapitulasi Lengkap</p>
                <p class="mt-0.5 text-xs text-blue-600">Ekspor laporan ke format CSV yang berisi JOIN data dari 4 tabel utama.</p>
            </div>
        </div>
        <a href="{{ route('reports.export') }}" class="btn-primary btn-sm bg-blue-600 hover:bg-blue-700 shadow-blue-200">
            <i class="fas fa-download"></i> Unduh CSV Rekap IKL
        </a>
    </div>
</div>
@endsection

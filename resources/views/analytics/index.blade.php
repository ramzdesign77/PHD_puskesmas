@extends('layouts.app')

@section('title', 'Laporan & Analitik')
@section('page-title', 'Laporan & Analitik')
@section('page-subtitle', 'Pantau tren dan distribusi laporan kesehatan lingkungan')

@section('content')
<div class="space-y-6 fade-in">
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        @foreach([
            ['label' => 'Total Laporan', 'value' => $summary['total'], 'icon' => 'fa-file-alt', 'color' => 'red'],
            ['label' => 'Menunggu', 'value' => $summary['pending'], 'icon' => 'fa-clock', 'color' => 'yellow'],
            ['label' => 'Dalam Proses', 'value' => $summary['in_progress'], 'icon' => 'fa-spinner', 'color' => 'blue'],
            ['label' => 'Selesai', 'value' => $summary['resolved'], 'icon' => 'fa-check-circle', 'color' => 'green'],
        ] as $stat)
        <div class="stat-card">
            <div class="flex items-start justify-between gap-2">
                <div><p class="text-xs font-semibold uppercase tracking-wider text-gray-400">{{ $stat['label'] }}</p><p class="mt-2 text-3xl font-bold text-gray-800">{{ $stat['value'] }}</p></div>
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-{{ $stat['color'] }}-50"><i class="fas {{ $stat['icon'] }} text-{{ $stat['color'] }}-500"></i></div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="card p-6">
            <div class="mb-5"><h3 class="font-bold text-gray-800">Laporan Berdasarkan Kategori</h3><p class="mt-1 text-xs text-gray-400">Distribusi laporan yang masuk</p></div>
            <div class="space-y-4">
                @foreach($categories as $category => $total)
                @php($percentage = $summary['total'] > 0 ? round(($total / $summary['total']) * 100) : 0)
                <div>
                    <div class="mb-1 flex items-center justify-between text-sm"><span class="font-medium text-gray-600">{{ $category }}</span><span class="font-bold text-gray-800">{{ $total }}</span></div>
                    <div class="h-2 overflow-hidden rounded-full bg-gray-100"><div class="h-full rounded-full bg-red-500" style="width: {{ $percentage }}%"></div></div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="card overflow-hidden">
            <div class="border-b border-gray-100 px-6 py-5"><h3 class="font-bold text-gray-800">Laporan Terbaru</h3><p class="mt-1 text-xs text-gray-400">Aktivitas pelaporan terakhir</p></div>
            <div class="divide-y divide-gray-100">
                @foreach(array_slice($reports, -4) as $report)
                <div class="flex items-center justify-between gap-4 px-6 py-4">
                    <div class="min-w-0"><p class="truncate text-sm font-semibold text-gray-700">{{ $report['id'] }} · {{ $report['category'] }}</p><p class="mt-1 truncate text-xs text-gray-400">{{ $report['location'] }}</p></div>
                    <span class="badge whitespace-nowrap {{ $report['status'] === 'resolved' ? 'badge-resolved' : ($report['status'] === 'pending' ? 'badge-pending' : 'badge-progress') }}">{{ $report['status'] === 'resolved' ? 'Selesai' : ($report['status'] === 'pending' ? 'Menunggu' : 'Diproses') }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-red-100 bg-red-50 px-5 py-4">
        <div><p class="font-semibold text-red-800">Perlu melihat detail laporan?</p><p class="mt-1 text-xs text-red-600">Buka daftar laporan untuk memeriksa data dan status setiap laporan.</p></div>
        <a href="{{ route('reports.index') }}" class="btn-primary btn-sm"><i class="fas fa-arrow-right"></i> Buka Rekap Laporan</a>
    </div>
</div>
@endsection

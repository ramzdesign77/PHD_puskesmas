@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Ringkasan data kesehatan lingkungan hari ini')

@section('content')
<div class="space-y-6 fade-in p-5 flex flex-col flex-1">

    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Laporan --}}
        <div class="card p-5 bg-white rounded-2xl shadow-sm border border-gray-100">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Total Laporan</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $stats['total_reports'] ?? 0 }}</p>
                    <p class="text-xs text-green-500 font-medium mt-1">
                        <i class="fas fa-arrow-up"></i> +12 bulan ini
                    </p>
                </div>
                <div class="w-12 h-12 bg-red-50 rounded-xl flex items-center justify-center">
                    <i class="fas fa-file-alt text-red-500 text-lg"></i>
                </div>
            </div>
        </div>

        {{-- Menunggu --}}
        <div class="card p-5 bg-white rounded-2xl shadow-sm border border-gray-100">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Menunggu</p>
                    <p class="text-3xl font-bold text-yellow-500">{{ $stats['pending'] ?? 0 }}</p>
                    <p class="text-xs text-gray-400 font-medium mt-1">
                        <i class="fas fa-clock"></i> Perlu tindakan
                    </p>
                </div>
                <div class="w-12 h-12 bg-yellow-50 rounded-xl flex items-center justify-center">
                    <i class="fas fa-hourglass-half text-yellow-500 text-lg"></i>
                </div>
            </div>
        </div>

        {{-- Selesai --}}
        <div class="card p-5 bg-white rounded-2xl shadow-sm border border-gray-100">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Selesai</p>
                    <p class="text-3xl font-bold text-green-500">{{ $stats['resolved'] ?? 0 }}</p>
                    <p class="text-xs text-green-500 font-medium mt-1">
                        <i class="fas fa-check-double"></i> Tertangani
                    </p>
                </div>
                <div class="w-12 h-12 bg-green-50 rounded-xl flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-500 text-lg"></i>
                </div>
            </div>
        </div>

        {{-- Role Card --}}
        @if(session('role') === 'citizen')
        <div class="card p-5 bg-white rounded-2xl shadow-sm border border-gray-100">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Artikel Edukasi</p>
                    <p class="text-3xl font-bold text-blue-500">{{ $stats['articles'] ?? 0 }}</p>
                    <p class="text-xs text-blue-400 font-medium mt-1">
                        <i class="fas fa-book"></i> Tersedia
                    </p>
                </div>
                <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center">
                    <i class="fas fa-book-open text-blue-500 text-lg"></i>
                </div>
            </div>
        </div>
        @elseif(session('role') === 'officer')
        <div class="card p-5 bg-white rounded-2xl shadow-sm border border-gray-100">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Jadwal Saya</p>
                    <p class="text-3xl font-bold text-purple-500">{{ $stats['schedules'] ?? 0 }}</p>
                    <p class="text-xs text-purple-400 font-medium mt-1">
                        <i class="fas fa-calendar"></i> Kunjungan aktif
                    </p>
                </div>
                <div class="w-12 h-12 bg-purple-50 rounded-xl flex items-center justify-center">
                    <i class="fas fa-calendar-check text-purple-500 text-lg"></i>
                </div>
            </div>
        </div>
        @else
        <div class="card p-5 bg-white rounded-2xl shadow-sm border border-gray-100">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Petugas Aktif</p>
                    <p class="text-3xl font-bold text-indigo-500">{{ $stats['active_officers'] ?? 0 }}</p>
                    <p class="text-xs text-indigo-400 font-medium mt-1">
                        <i class="fas fa-users"></i> Di lapangan
                    </p>
                </div>
                <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center">
                    <i class="fas fa-users text-indigo-500 text-lg"></i>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Aktivitas & Aksi Cepat --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 card p-6 bg-white rounded-2xl shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-5">
                <h3 class="font-bold text-gray-800">Aktivitas Terbaru</h3>
                <span class="text-xs text-gray-400">{{ \Carbon\Carbon::now()->format('d M Y') }}</span>
            </div>
            <div class="space-y-4">
                @forelse($recent_activities ?? [] as $activity)
                    <div class="flex items-start gap-4 pb-4 border-b border-slate-50 last:border-0 last:pb-0">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 {{ ['red' => 'bg-red-50', 'green' => 'bg-green-50', 'blue' => 'bg-blue-50', 'purple' => 'bg-purple-50'][$activity['color']] ?? 'bg-yellow-50' }}">
                            <i class="fas {{ ['report' => 'fa-file-circle-exclamation text-red-500', 'check' => 'fa-check-circle text-green-500', 'calendar' => 'fa-calendar-check text-blue-500', 'article' => 'fa-newspaper text-purple-500'][$activity['icon']] ?? 'fa-user-md text-yellow-500' }} text-sm"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-700 font-medium leading-snug">{{ $activity['text'] }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $activity['time'] }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 text-center py-4">Belum ada aktivitas terbaru.</p>
                @endforelse
            </div>
        </div>

        <div class="card p-6 bg-white rounded-2xl shadow-sm border border-gray-100">
            <h3 class="font-bold text-gray-800 mb-5">Aksi Cepat</h3>
            <div class="flex flex-col gap-3">
                @if(session('role') === 'citizen')
                <a href="{{ route('reports.index') }}" class="flex items-center gap-3 p-3 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 transition-colors font-medium">
                    <i class="fas fa-plus-circle text-lg"></i> Buat Laporan Baru
                </a>
                <a href="{{ route('schedules.index') }}" class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 transition-colors font-medium">
                    <i class="fas fa-calendar-plus text-lg"></i> Minta Kunjungan
                </a>
                @elseif(session('role') === 'officer')
                <a href="{{ route('reports.index') }}" class="flex items-center gap-3 p-3 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-700 transition-colors font-medium">
                    <i class="fas fa-tasks text-lg"></i> Verifikasi Laporan
                </a>
                <a href="{{ route('schedules.index') }}" class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 transition-colors font-medium">
                    <i class="fas fa-map-marker-alt text-lg"></i> Jadwal Kunjungan
                </a>
                @else
                <a href="{{ route('reports.index') }}" class="flex items-center gap-3 p-3 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 transition-colors font-medium">
                    <i class="fas fa-chart-bar text-lg"></i> Rekap Laporan
                </a>
                <a href="{{ route('schedules.index') }}" class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 transition-colors font-medium">
                    <i class="fas fa-calendar-check text-lg"></i> Kelola Jadwal
                </a>
                @endif

                <a href="{{ route('education.index') }}" class="flex items-center gap-3 p-3 rounded-xl bg-green-50 hover:bg-green-100 text-green-700 transition-colors font-medium">
                    <i class="fas fa-book-open text-lg"></i> Edukasi Kesehatan
                </a>
            </div>
        </div>
    </div>
</div>

@endsection

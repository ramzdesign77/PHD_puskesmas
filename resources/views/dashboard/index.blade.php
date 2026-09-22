@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Ringkasan data kesehatan lingkungan hari ini')

@section('content')
<div class="space-y-6 fade-in">

    {{-- ─── Welcome Banner ─────────────────────────────── --}}
    <div class="medical-gradient rounded-2xl p-6 md:p-8 text-white relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -translate-y-32 translate-x-32"></div>
        <div class="absolute bottom-0 right-20 w-40 h-40 bg-white/5 rounded-full translate-y-20"></div>
        <div class="relative z-10">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-red-200 text-sm font-medium mb-1">
                        {{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y') }}
                    </p>
                    <h1 class="text-2xl md:text-3xl font-bold mb-2">
                        @php
                            $hour = \Carbon\Carbon::now()->hour;
                            $greeting = $hour < 12 ? 'Selamat Pagi' : ($hour < 15 ? 'Selamat Siang' : ($hour < 18 ? 'Selamat Sore' : 'Selamat Malam'));
                        @endphp
                        {{ $greeting }}, {{ session('user_name') }}! 👋
                    </h1>
                    <p class="text-red-100 text-sm max-w-lg">
                        @if(session('role') === 'citizen')
                            Laporkan masalah kesehatan lingkungan di sekitar Anda. Kami siap membantu.
                        @elseif(session('role') === 'officer')
                            Ada <span class="font-bold text-white">{{ $stats['pending'] }}</span> laporan menunggu tindak lanjut Anda hari ini.
                        @else
                            Pantau seluruh aktivitas program Kesling Puskesmas Sumbersari.
                        @endif
                    </p>
                </div>
                <div class="hidden md:block">
                    <div class="w-20 h-20 bg-white/15 rounded-2xl flex items-center justify-center">
                        @if(session('role') === 'citizen')
                            <i class="fas fa-home text-white text-3xl"></i>
                        @elseif(session('role') === 'officer')
                            <i class="fas fa-user-md text-white text-3xl"></i>
                        @else
                            <i class="fas fa-chart-line text-white text-3xl"></i>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="flex flex-wrap gap-3 mt-5">
                @if(session('role') === 'citizen')
                <a href="{{ route('reports.index') }}" id="quick-report"
                   class="inline-flex items-center gap-2 bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-xl text-sm font-semibold transition-all backdrop-blur">
                    <i class="fas fa-plus-circle"></i> Buat Laporan Baru
                </a>
                <a href="{{ route('schedules.index') }}"
                   class="inline-flex items-center gap-2 bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded-xl text-sm font-medium transition-all">
                    <i class="fas fa-calendar-plus"></i> Minta Kunjungan
                </a>
                @elseif(session('role') === 'officer')
                <a href="{{ route('reports.index') }}" id="quick-verify"
                   class="inline-flex items-center gap-2 bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-xl text-sm font-semibold transition-all backdrop-blur">
                    <i class="fas fa-tasks"></i> Verifikasi Laporan
                </a>
                <a href="{{ route('schedules.index') }}"
                   class="inline-flex items-center gap-2 bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded-xl text-sm font-medium transition-all">
                    <i class="fas fa-map-marker-alt"></i> Jadwal Kunjungan Saya
                </a>
                @else
                <a href="{{ route('reports.index') }}" id="quick-recap"
                   class="inline-flex items-center gap-2 bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-xl text-sm font-semibold transition-all backdrop-blur">
                    <i class="fas fa-chart-bar"></i> Rekap Laporan
                </a>
                <a href="{{ route('schedules.index') }}"
                   class="inline-flex items-center gap-2 bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded-xl text-sm font-medium transition-all">
                    <i class="fas fa-calendar-check"></i> Kelola Jadwal
                </a>
                @endif
                <a href="{{ route('education.index') }}"
                   class="inline-flex items-center gap-2 bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded-xl text-sm font-medium transition-all">
                    <i class="fas fa-book-open"></i> Edukasi Kesehatan
                </a>
            </div>
        </div>
    </div>

    {{-- ─── Stat Cards ──────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Laporan --}}
        <div class="stat-card">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Total Laporan</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $stats['total_reports'] }}</p>
                    <p class="text-xs text-green-500 font-medium mt-1">
                        <i class="fas fa-arrow-up"></i> +12 bulan ini
                    </p>
                </div>
                <div class="w-12 h-12 bg-red-50 rounded-xl flex items-center justify-center">
                    <i class="fas fa-file-alt text-medical-red text-lg"></i>
                </div>
            </div>
        </div>

        {{-- Pending --}}
        <div class="stat-card">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Menunggu</p>
                    <p class="text-3xl font-bold text-yellow-500">{{ $stats['pending'] }}</p>
                    <p class="text-xs text-gray-400 font-medium mt-1">
                        <i class="fas fa-clock"></i> Perlu tindakan
                    </p>
                </div>
                <div class="w-12 h-12 bg-yellow-50 rounded-xl flex items-center justify-center">
                    <i class="fas fa-hourglass-half text-yellow-500 text-lg"></i>
                </div>
            </div>
        </div>

        {{-- Resolved --}}
        <div class="stat-card">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Selesai</p>
                    <p class="text-3xl font-bold text-green-500">{{ $stats['resolved'] }}</p>
                    <p class="text-xs text-green-500 font-medium mt-1">
                        <i class="fas fa-check-double"></i> Tertangani
                    </p>
                </div>
                <div class="w-12 h-12 bg-green-50 rounded-xl flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-500 text-lg"></i>
                </div>
            </div>
        </div>

        {{-- Dynamic 4th card by role --}}
        @if(session('role') === 'citizen')
        <div class="stat-card">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Artikel Edukasi</p>
                    <p class="text-3xl font-bold text-blue-500">{{ $stats['articles'] }}</p>
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
        <div class="stat-card">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Jadwal Saya</p>
                    <p class="text-3xl font-bold text-purple-500">{{ $stats['schedules'] }}</p>
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
        <div class="stat-card">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Petugas Aktif</p>
                    <p class="text-3xl font-bold text-indigo-500">{{ $stats['active_officers'] }}</p>
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

    {{-- ─── Bottom Grid: Activity + Progress ──────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Recent Activity --}}
        <div class="lg:col-span-2 card p-6">
            <div class="flex items-center justify-between mb-5">
                <h3 class="font-bold text-gray-800">Aktivitas Terbaru</h3>
                <span class="text-xs text-gray-400">{{ \Carbon\Carbon::now()->format('d M Y') }}</span>
            </div>
            <div class="space-y-4">
                @foreach($recent_activities as $activity)
                <div class="flex items-start gap-4">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 {{ ['red' => 'bg-red-50', 'green' => 'bg-green-50', 'blue' => 'bg-blue-50', 'purple' => 'bg-purple-50'][$activity['color']] ?? 'bg-yellow-50' }}">
                        <i class="fas {{ ['report' => 'fa-file-circle-exclamation text-red-500', 'check' => 'fa-check-circle text-green-500', 'calendar' => 'fa-calendar-check text-blue-500', 'article' => 'fa-newspaper text-purple-500'][$activity['icon']] ?? 'fa-user-md text-yellow-500' }} text-sm"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-700 font-medium leading-snug">{{ $activity['text'] }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $activity['time'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Status Distribution --}}
        <div class="card p-6">
            <h3 class="font-bold text-gray-800 mb-5">Distribusi Status</h3>
            <div class="space-y-4">
                @php
                    $total = $stats['total_reports'];
                    $pendingPct  = $total > 0 ? round(($stats['pending']/$total)*100) : 0;
                    $progressPct = $total > 0 ? round(($stats['in_progress']/$total)*100) : 0;
                    $resolvedPct = $total > 0 ? round(($stats['resolved']/$total)*100) : 0;
                @endphp

                <div>
                    <div class="flex justify-between mb-1.5">
                        <span class="text-xs font-medium text-gray-600">Menunggu</span>
                        <span class="text-xs font-bold text-yellow-500">{{ $pendingPct }}%</span>
                    </div>
                    <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-yellow-400 rounded-full transition-all duration-1000"
                             style="width: {{ $pendingPct }}%"></div>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">{{ $stats['pending'] }} laporan</p>
                </div>

                <div>
                    <div class="flex justify-between mb-1.5">
                        <span class="text-xs font-medium text-gray-600">Proses</span>
                        <span class="text-xs font-bold text-blue-500">{{ $progressPct }}%</span>
                    </div>
                    <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-blue-400 rounded-full transition-all duration-1000"
                             style="width: {{ $progressPct }}%"></div>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">{{ $stats['in_progress'] }} laporan</p>
                </div>

                <div>
                    <div class="flex justify-between mb-1.5">
                        <span class="text-xs font-medium text-gray-600">Selesai</span>
                        <span class="text-xs font-bold text-green-500">{{ $resolvedPct }}%</span>
                    </div>
                    <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-green-400 rounded-full transition-all duration-1000"
                             style="width: {{ $resolvedPct }}%"></div>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">{{ $stats['resolved'] }} laporan</p>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-gray-100">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-500 font-medium">Tingkat Penyelesaian</span>
                    <span class="text-sm font-bold text-green-600">{{ $resolvedPct }}%</span>
                </div>
                <div class="h-3 bg-gray-100 rounded-full overflow-hidden mt-2">
                    <div class="h-full medical-gradient rounded-full" style="width: {{ $resolvedPct }}%"></div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

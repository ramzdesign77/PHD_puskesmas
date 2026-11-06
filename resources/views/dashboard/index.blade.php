@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Ringkasan data kesehatan lingkungan hari ini')

@section('content')
<div class="flex flex-1 flex-col gap-7 p-4 fade-in sm:p-6">
    <section class="water-hero water-hero--dashboard relative isolate min-h-[19rem] overflow-hidden rounded-3xl bg-gradient-to-br from-sky-600 via-cyan-600 to-cyan-500 px-6 py-7 shadow-xl shadow-sky-900/15 sm:px-8 sm:py-9 lg:min-h-[20rem] lg:px-10" aria-labelledby="dashboard-welcome">
        <div class="pointer-events-none absolute -right-10 -top-16 h-56 w-56 rounded-full border border-white/20 bg-white/10 backdrop-blur-sm" aria-hidden="true"></div>
        <div class="pointer-events-none absolute right-[27%] top-8 h-5 w-5 rounded-full border border-white/40 bg-white/20 backdrop-blur-sm" aria-hidden="true"></div>
        <div class="pointer-events-none absolute bottom-10 right-[42%] h-9 w-9 rounded-full border border-white/30 bg-white/10 backdrop-blur-sm" aria-hidden="true"></div>
        <div class="pointer-events-none absolute bottom-[-3rem] right-[17%] h-28 w-28 rounded-full border border-white/20 bg-white/10 backdrop-blur-sm" aria-hidden="true"></div>

        <div class="relative z-10 flex min-h-[15rem] max-w-[76%] flex-col items-start justify-center sm:max-w-[66%] lg:min-h-[15rem] lg:max-w-[55%]">
            <p class="mb-3 inline-flex items-center gap-2 rounded-full border border-white/25 bg-white/15 px-3 py-1.5 text-[0.65rem] font-semibold uppercase tracking-[0.16em] text-white/90 backdrop-blur-sm">
                <span class="h-1.5 w-1.5 rounded-full bg-cyan-100 shadow-[0_0_10px_rgba(207,250,254,0.9)]"></span>
                Pantauan sanitasi hari ini
            </p>
            <h1 id="dashboard-welcome" class="max-w-2xl text-2xl font-bold leading-tight text-white sm:text-3xl lg:text-4xl">
                Selamat Datang,<br class="sm:hidden">
                <span class="text-cyan-50">{{ session('user_name', 'Administrator Puskesmas') }}</span>
            </h1>
            <p class="mt-3 max-w-xl text-sm leading-relaxed text-cyan-50/90 sm:text-base">
                Pantau laporan warga, jadwal inspeksi, dan tindak lanjut sanitasi lingkungan dalam satu ringkasan.
            </p>
            <div class="mt-5 flex flex-wrap items-center gap-3">
                <a href="{{ route('reports.index') }}" class="inline-flex items-center gap-2 rounded-full bg-white px-5 py-2.5 text-sm font-semibold text-sky-700 shadow-lg shadow-sky-950/10 transition hover:-translate-y-0.5 hover:bg-cyan-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                    Lihat Laporan
                    <i class="fas fa-arrow-right text-xs" aria-hidden="true"></i>
                </a>
                <a href="{{ route('schedules.index') }}" class="inline-flex items-center gap-2 rounded-full border border-white/35 bg-white/10 px-5 py-2.5 text-sm font-semibold text-white backdrop-blur-sm transition hover:bg-white/20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                    <i class="fas fa-calendar-check text-xs" aria-hidden="true"></i>
                    Jadwal Inspeksi
                </a>
            </div>
            <div class="mt-6 flex items-center gap-1.5" aria-label="Slide 1 dari 3">
                <span class="h-1.5 w-7 rounded-full bg-white"></span>
                <span class="h-1.5 w-3 rounded-full bg-white/50"></span>
                <span class="h-1.5 w-3 rounded-full bg-white/50"></span>
            </div>
        </div>

        <img
            src="{{ asset('images/maskot_berangberang.png') }}"
            alt="Maskot berang-berang membawa ikan"
            class="mascot-float pointer-events-none absolute bottom-[-1.5rem] right-[-0.5rem] z-10 h-52 w-auto max-w-[42%] object-contain drop-shadow-[0_22px_20px_rgba(8,47,73,0.28)] sm:bottom-[-2rem] sm:right-[-0.75rem] sm:h-72 lg:bottom-[-3rem] lg:right-[-1rem] lg:h-[24rem] lg:max-w-[54%]"
        >
    </section>

    <section aria-label="Ringkasan laporan" class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="stat-card card p-4 sm:p-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Total Laporan</p>
                    <p class="text-3xl font-bold text-slate-800">{{ $stats['total_reports'] ?? 0 }}</p>
                    <p class="mt-1 text-xs font-medium text-sky-600"><i class="fas fa-chart-line mr-1" aria-hidden="true"></i>Seluruh laporan</p>
                </div>
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-sky-600"><i class="fas fa-file-alt text-lg" aria-hidden="true"></i></span>
            </div>
        </div>

        <div class="stat-card card p-4 sm:p-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Menunggu</p>
                    <p class="text-3xl font-bold text-amber-500">{{ $stats['pending'] ?? 0 }}</p>
                    <p class="mt-1 text-xs font-medium text-slate-500"><i class="fas fa-clock mr-1" aria-hidden="true"></i>Perlu tindakan</p>
                </div>
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-500"><i class="fas fa-hourglass-half text-lg" aria-hidden="true"></i></span>
            </div>
        </div>

        <div class="stat-card card p-4 sm:p-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Selesai</p>
                    <p class="text-3xl font-bold text-emerald-500">{{ $stats['resolved'] ?? 0 }}</p>
                    <p class="mt-1 text-xs font-medium text-emerald-600"><i class="fas fa-check-double mr-1" aria-hidden="true"></i>Tertangani</p>
                </div>
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-500"><i class="fas fa-check-circle text-lg" aria-hidden="true"></i></span>
            </div>
        </div>

        @if(session('role') === 'citizen')
            <div class="stat-card card p-4 sm:p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Artikel Edukasi</p>
                        <p class="text-3xl font-bold text-blue-500">{{ $stats['articles'] ?? 0 }}</p>
                        <p class="mt-1 text-xs font-medium text-blue-500"><i class="fas fa-book mr-1" aria-hidden="true"></i>Tersedia</p>
                    </div>
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-500"><i class="fas fa-book-open text-lg" aria-hidden="true"></i></span>
                </div>
            </div>
        @elseif(session('role') === 'officer')
            <div class="stat-card card p-4 sm:p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Jadwal Saya</p>
                        <p class="text-3xl font-bold text-purple-500">{{ $stats['schedules'] ?? 0 }}</p>
                        <p class="mt-1 text-xs font-medium text-purple-500"><i class="fas fa-calendar mr-1" aria-hidden="true"></i>Kunjungan aktif</p>
                    </div>
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-purple-50 text-purple-500"><i class="fas fa-calendar-check text-lg" aria-hidden="true"></i></span>
                </div>
            </div>
        @else
            <div class="stat-card card p-4 sm:p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Petugas Aktif</p>
                        <p class="text-3xl font-bold text-indigo-500">{{ $stats['active_officers'] ?? 0 }}</p>
                        <p class="mt-1 text-xs font-medium text-indigo-500"><i class="fas fa-users mr-1" aria-hidden="true"></i>Di lapangan</p>
                    </div>
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-500"><i class="fas fa-users text-lg" aria-hidden="true"></i></span>
                </div>
            </div>
        @endif
    </section>

    <section class="grid grid-cols-1 gap-5 lg:grid-cols-3" aria-label="Aktivitas dan aksi cepat">
        <div class="card p-5 sm:p-6 lg:col-span-2">
            <div class="mb-5 flex items-center justify-between gap-3">
                <div>
                    <h2 class="font-bold text-slate-800">Aktivitas Terbaru</h2>
                    <p class="mt-1 text-xs text-slate-400">Pembaruan laporan dan kegiatan sanitasi</p>
                </div>
                <span class="shrink-0 rounded-full bg-sky-50 px-3 py-1.5 text-xs font-medium text-sky-700">{{ \Carbon\Carbon::now()->format('d M Y') }}</span>
            </div>
            <div class="space-y-4">
                @forelse($recent_activities ?? [] as $activity)
                    <div class="flex items-start gap-4 border-b border-slate-100 pb-4 last:border-0 last:pb-0">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ ['red' => 'bg-red-50', 'green' => 'bg-green-50', 'blue' => 'bg-blue-50', 'purple' => 'bg-purple-50', 'gray' => 'bg-slate-100'][$activity['color']] ?? 'bg-amber-50' }}">
                            <i class="fas {{ ['report' => 'fa-file-circle-exclamation text-red-500', 'check' => 'fa-check-circle text-green-500', 'calendar' => 'fa-calendar-check text-blue-500', 'article' => 'fa-newspaper text-purple-500', 'close' => 'fa-circle-xmark text-red-500', 'info' => 'fa-circle-info text-slate-500'][$activity['icon']] ?? 'fa-user-md text-amber-500' }} text-sm" aria-hidden="true"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium leading-snug text-slate-700">{{ $activity['text'] }}</p>
                            <p class="mt-1 text-xs text-slate-400">{{ $activity['time'] }}</p>
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-slate-500">Belum ada aktivitas terbaru.</p>
                @endforelse
            </div>
        </div>

        <div class="card p-5 sm:p-6">
            <div class="mb-5">
                <h2 class="font-bold text-slate-800">Aksi Cepat</h2>
                <p class="mt-1 text-xs text-slate-400">Akses menu yang sering digunakan</p>
            </div>
            <div class="flex flex-col gap-3">
                @if(session('role') === 'citizen')
                    <a href="{{ route('reports.index') }}" class="flex items-center gap-3 rounded-xl bg-sky-50 p-3.5 font-medium text-sky-700 transition hover:bg-sky-100">
                        <i class="fas fa-plus-circle text-lg" aria-hidden="true"></i> Buat Laporan Baru
                    </a>
                    <a href="{{ route('schedules.index') }}" class="flex items-center gap-3 rounded-xl bg-slate-50 p-3.5 font-medium text-slate-700 transition hover:bg-slate-100">
                        <i class="fas fa-calendar-plus text-lg" aria-hidden="true"></i> Minta Kunjungan
                    </a>
                @elseif(session('role') === 'officer')
                    <a href="{{ route('reports.index') }}" class="flex items-center gap-3 rounded-xl bg-purple-50 p-3.5 font-medium text-purple-700 transition hover:bg-purple-100">
                        <i class="fas fa-tasks text-lg" aria-hidden="true"></i> Verifikasi Laporan
                    </a>
                    <a href="{{ route('schedules.index') }}" class="flex items-center gap-3 rounded-xl bg-slate-50 p-3.5 font-medium text-slate-700 transition hover:bg-slate-100">
                        <i class="fas fa-map-marker-alt text-lg" aria-hidden="true"></i> Jadwal Kunjungan
                    </a>
                @else
                    <a href="{{ route('reports.index') }}" class="flex items-center gap-3 rounded-xl bg-indigo-50 p-3.5 font-medium text-indigo-700 transition hover:bg-indigo-100">
                        <i class="fas fa-chart-bar text-lg" aria-hidden="true"></i> Rekap Laporan
                    </a>
                    <a href="{{ route('schedules.index') }}" class="flex items-center gap-3 rounded-xl bg-slate-50 p-3.5 font-medium text-slate-700 transition hover:bg-slate-100">
                        <i class="fas fa-calendar-check text-lg" aria-hidden="true"></i> Kelola Jadwal
                    </a>
                @endif

                <a href="{{ route('education.index') }}" class="flex items-center gap-3 rounded-xl bg-emerald-50 p-3.5 font-medium text-emerald-700 transition hover:bg-emerald-100">
                    <i class="fas fa-book-open text-lg" aria-hidden="true"></i> Edukasi Kesehatan
                </a>
            </div>
        </div>
    </section>
</div>
@endsection

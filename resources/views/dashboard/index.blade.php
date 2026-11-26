@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Ringkasan laporan dan kegiatan kesehatan lingkungan')

@section('content')
<div class="flex flex-1 flex-col gap-6 p-4 fade-in sm:p-6">
    <header class="grid gap-5 xl:grid-cols-12">
        <section class="water-hero water-hero--dashboard relative isolate flex min-h-64 overflow-hidden rounded-3xl bg-gradient-to-br from-sky-700 via-cyan-700 to-cyan-600 px-6 py-7 shadow-lg shadow-sky-900/10 sm:px-8 xl:col-span-8"
            aria-labelledby="dashboard-welcome">
            <div class="pointer-events-none absolute -right-10 -top-16 h-56 w-56 rounded-full border border-white/20 bg-white/10 backdrop-blur-sm" aria-hidden="true"></div>
            <div class="relative z-10 flex max-w-[70%] flex-col justify-center sm:max-w-[62%]">
                <p class="mb-3 inline-flex w-fit items-center gap-2 rounded-full border border-white/25 bg-white/15 px-3 py-1.5 text-[0.65rem] font-semibold uppercase tracking-[0.16em] text-white/90 backdrop-blur-sm">
                    <span class="h-1.5 w-1.5 rounded-full bg-cyan-100 shadow-[0_0_10px_rgba(207,250,254,0.9)]"></span>
                    Pantauan sanitasi
                </p>
                <h1 id="dashboard-welcome" class="font-mont text-2xl font-bold leading-tight text-white sm:text-3xl">
                    Halo, <span class="text-cyan-50">{{ $greetingName }}</span>
                </h1>
                <p class="mt-2 text-sm font-medium text-cyan-50/90">{{ $todayLabel }}</p>
                <p class="mt-4 max-w-xl text-sm leading-relaxed text-cyan-50/85">
                    Pantau laporan warga, inspeksi, dan tindak lanjut sanitasi dalam satu ringkasan.
                </p>
                <div class="mt-5 flex flex-wrap gap-3">
                    <a href="{{ $dashboardLinks['allReports'] }}" class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-semibold text-sky-700 shadow-sm transition hover:-translate-y-0.5 hover:bg-cyan-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                        Lihat Laporan
                        <i class="fas fa-arrow-right text-xs" aria-hidden="true"></i>
                    </a>
                    <a href="{{ $dashboardLinks['todaySchedules'] }}" class="inline-flex items-center gap-2 rounded-full border border-white/35 bg-white/10 px-4 py-2 text-sm font-semibold text-white backdrop-blur-sm transition hover:bg-white/20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                        <i class="fas fa-calendar-check text-xs" aria-hidden="true"></i>
                        Agenda Hari Ini
                    </a>
                </div>
            </div>

            <img src="{{ asset('images/maskot_berangberang.png') }}" alt="" aria-hidden="true"
                class="mascot-float pointer-events-none absolute bottom-[-1.25rem] right-[-0.5rem] z-10 h-48 w-auto max-w-[38%] object-contain drop-shadow-[0_18px_18px_rgba(8,47,73,0.25)] sm:bottom-[-1.75rem] sm:right-0 sm:h-60">
        </section>

        <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 xl:col-span-4"
            aria-labelledby="dashboard-filter-title">
            <div class="mb-4">
                <h2 id="dashboard-filter-title" class="font-mont font-bold text-slate-800">Filter Dashboard</h2>
                <p class="mt-1 text-xs leading-relaxed text-slate-500">Sesuaikan periode dan wilayah analisis.</p>
            </div>
            <form method="GET" action="{{ route('dashboard') }}" class="flex flex-col gap-3">
                <div>
                    <label for="dashboard-period" class="mb-1.5 block text-xs font-semibold text-slate-600">Periode</label>
                    <select id="dashboard-period" name="period" onchange="this.form.submit()"
                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-100">
                        @foreach($periodOptions as $value => $label)
                            <option value="{{ $value }}" @selected($filters['period'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="dashboard-village" class="mb-1.5 block text-xs font-semibold text-slate-600">Wilayah Desa</label>
                    <select id="dashboard-village" name="id_desa" onchange="this.form.submit()"
                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-100">
                        <option value="">Semua Desa</option>
                        @foreach($villages as $village)
                            <option value="{{ $village->id_desa }}" @selected($filters['id_desa'] === $village->id_desa)>
                                {{ $village->nama_desa }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @if($filters['period'] !== 'this_month' || $filters['id_desa'] !== null)
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 px-3 py-2.5 text-sm font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50">
                        <i class="fas fa-rotate-left text-xs" aria-hidden="true"></i>
                        Reset Filter
                    </a>
                @endif
            </form>
        </section>
    </header>

    <section aria-label="Indikator utama" class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <a href="{{ $dashboardLinks['pendingReports'] }}" class="group rounded-2xl border border-amber-200/80 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-1 hover:border-amber-300 hover:shadow-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-500">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Laporan Menunggu Verifikasi</p>
                    <p class="mt-3 text-3xl font-bold text-amber-600">{{ $dashboardMetrics['pendingReports'] }}</p>
                </div>
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600 transition group-hover:bg-amber-100">
                    <i class="fas fa-clipboard-check text-lg" aria-hidden="true"></i>
                </span>
            </div>
            <div class="mt-4 flex min-h-6 items-center justify-between gap-2">
                <span class="text-xs text-slate-500">Perlu ditinjau petugas</span>
                @if($dashboardMetrics['pendingReports'] > 0)
                    <span class="rounded-full bg-amber-100 px-2.5 py-1 text-[10px] font-bold text-amber-700">Perlu Tindakan</span>
                @endif
            </div>
        </a>

        <a href="{{ $dashboardLinks['todaySchedules'] }}" class="group rounded-2xl border border-sky-200/80 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-1 hover:border-sky-300 hover:shadow-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-sky-500">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Jadwal IKL Hari Ini</p>
                    <p class="mt-3 text-3xl font-bold text-sky-700">{{ $dashboardMetrics['todaySchedules'] }}</p>
                </div>
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-sky-600 transition group-hover:bg-sky-100">
                    <i class="fas fa-calendar-day text-lg" aria-hidden="true"></i>
                </span>
            </div>
            <p class="mt-4 text-xs text-slate-500">
                <span class="font-semibold text-sky-700">{{ $dashboardMetrics['scheduledToday'] }} terjadwal</span>
                <span class="mx-1 text-slate-300">/</span>
                <span class="font-semibold text-emerald-700">{{ $dashboardMetrics['completedToday'] }} selesai</span>
            </p>
        </a>

        <a href="{{ $dashboardLinks['inspections'] }}" class="group rounded-2xl border border-emerald-200/80 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-1 hover:border-emerald-300 hover:shadow-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-500">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Air Memenuhi Syarat</p>
                    <p class="mt-3 text-3xl font-bold text-emerald-700">{{ $dashboardMetrics['msPercentage'] }}<span class="text-xl">%</span></p>
                </div>
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 transition group-hover:bg-emerald-100">
                    <i class="fas fa-droplet text-lg" aria-hidden="true"></i>
                </span>
            </div>
            <div class="mt-4">
                <div class="flex h-2 overflow-hidden rounded-full bg-slate-100" role="img"
                    aria-label="{{ $dashboardMetrics['inspectionMsCount'] }} memenuhi syarat dan {{ $dashboardMetrics['inspectionTmsCount'] }} tidak memenuhi syarat">
                    @if($dashboardMetrics['inspectionTotal'] > 0)
                        <span class="h-full bg-emerald-500" style="width: {{ $dashboardMetrics['msPercentage'] }}%"></span>
                        <span class="h-full bg-rose-400" style="width: {{ 100 - $dashboardMetrics['msPercentage'] }}%"></span>
                    @endif
                </div>
                <p class="mt-2 text-xs text-slate-500">
                    <span class="font-semibold text-emerald-700">{{ $dashboardMetrics['inspectionMsCount'] }} MS</span>
                    <span class="mx-1 text-slate-300">/</span>
                    <span class="font-semibold text-rose-600">{{ $dashboardMetrics['inspectionTmsCount'] }} TMS</span>
                </p>
            </div>
        </a>

        <a href="{{ $dashboardLinks['inspections'] }}" class="group rounded-2xl border border-violet-200/80 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-1 hover:border-violet-300 hover:shadow-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-violet-500">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Intervensi Lapangan</p>
                    <p class="mt-3 text-3xl font-bold text-violet-700">{{ $dashboardMetrics['interventions'] }}</p>
                </div>
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600 transition group-hover:bg-violet-100">
                    <i class="fas fa-hand-holding-medical text-lg" aria-hidden="true"></i>
                </span>
            </div>
            <p class="mt-4 text-xs text-slate-500">Distribusi Kaporit, Abate &amp; KIE</p>
        </a>
    </section>

    <section class="grid grid-cols-1 gap-6 lg:grid-cols-12" aria-label="Grafik analitik">
        <article class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 lg:col-span-8">
            <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="font-mont font-bold text-slate-800">Tren Laporan Bulanan</h2>
                    <p class="mt-1 text-xs text-slate-500">Laporan masuk dan selesai selama 6 bulan terakhir</p>
                </div>
                <span class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1.5 text-xs font-medium text-slate-600">
                    <span class="h-2 w-2 rounded-full bg-sky-600"></span>Masuk
                    <span class="ml-1 h-2 w-2 rounded-full bg-emerald-500"></span>Selesai
                </span>
            </div>
            @if($monthlyReportTotal > 0)
                <div class="relative h-64 sm:h-72">
                    <canvas id="monthlyTrendChart" aria-label="Grafik tren laporan bulanan" role="img"></canvas>
                </div>
                <p data-chart-unavailable class="hidden mt-3 rounded-xl bg-amber-50 px-3 py-2 text-xs text-amber-800" role="status">
                    Grafik tidak dapat dimuat saat ini. Silakan muat ulang halaman.
                </p>
            @else
                <div class="flex min-h-64 flex-col items-center justify-center rounded-xl border border-dashed border-slate-200 bg-slate-50/70 px-4 text-center">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-slate-400 shadow-sm">
                        <i class="fas fa-chart-column text-lg" aria-hidden="true"></i>
                    </span>
                    <p class="mt-3 text-sm font-semibold text-slate-700">Belum ada data laporan</p>
                    <p class="mt-1 max-w-xs text-xs leading-relaxed text-slate-500">Tren laporan akan muncul setelah ada laporan pada periode dan wilayah terpilih.</p>
                </div>
            @endif
        </article>

        <article class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 lg:col-span-4">
            <div class="mb-5">
                <h2 class="font-mont font-bold text-slate-800">Proporsi Masalah Sanitasi</h2>
                <p class="mt-1 text-xs text-slate-500">Kategori laporan warga terpilih</p>
            </div>
            @if($categoryReportTotal > 0)
                <div class="mx-auto h-52 max-w-56">
                    <canvas id="sanitationCategoryChart" aria-label="Grafik donat kategori masalah sanitasi" role="img"></canvas>
                </div>
                <ul class="mt-5 space-y-3">
                    @foreach($categoryDistribution as $category)
                        <li class="flex items-center justify-between gap-3 text-xs">
                            <span class="flex min-w-0 items-center gap-2 text-slate-600">
                                <span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background-color: {{ $category['color'] }}"></span>
                                <span class="truncate">{{ $category['label'] }}</span>
                            </span>
                            <span class="shrink-0 font-semibold tabular-nums text-slate-700">
                                {{ $category['count'] }} <span class="font-normal text-slate-400">({{ number_format($category['percentage'], 1) }}%)</span>
                            </span>
                        </li>
                    @endforeach
                </ul>
                <p data-chart-unavailable class="hidden mt-3 rounded-xl bg-amber-50 px-3 py-2 text-xs text-amber-800" role="status">
                    Grafik tidak dapat dimuat saat ini. Silakan muat ulang halaman.
                </p>
            @else
                <div class="flex min-h-64 flex-col items-center justify-center rounded-xl border border-dashed border-slate-200 bg-slate-50/70 px-4 text-center">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-slate-400 shadow-sm">
                        <i class="fas fa-chart-pie text-lg" aria-hidden="true"></i>
                    </span>
                    <p class="mt-3 text-sm font-semibold text-slate-700">Belum ada data kategori</p>
                    <p class="mt-1 max-w-xs text-xs leading-relaxed text-slate-500">Proporsi kategori akan tampil setelah laporan tersedia.</p>
                </div>
            @endif
        </article>
    </section>

    <section class="grid grid-cols-1 gap-6 lg:grid-cols-12" aria-label="Antrean laporan dan agenda kunjungan">
        <article class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm lg:col-span-7">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 sm:px-6">
                <div>
                    <h2 class="font-mont font-bold text-slate-800">Antrean Verifikasi Cepat</h2>
                    <p class="mt-1 text-xs text-slate-500">Lima laporan terbaru yang membutuhkan pemeriksaan</p>
                </div>
                <a href="{{ $dashboardLinks['allReports'] }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-sky-700 transition hover:text-sky-900">
                    Lihat Semua Laporan <span aria-hidden="true">&rarr;</span>
                </a>
            </div>
            @forelse($pendingReports as $report)
                @if($loop->first)
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[700px] text-left text-xs">
                            <thead class="bg-slate-50 text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                                <tr>
                                    <th class="px-5 py-3">Kode Tiket</th>
                                    <th class="px-5 py-3">Pelapor &amp; Lokasi</th>
                                    <th class="px-5 py-3">Kategori</th>
                                    <th class="px-5 py-3">Waktu Masuk</th>
                                    <th class="px-5 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                @endif
                                <tr class="transition hover:bg-slate-50/70">
                                    <td class="whitespace-nowrap px-5 py-4 font-mono font-semibold text-slate-700">{{ $report->kode_tiket }}</td>
                                    <td class="px-5 py-4">
                                        <p class="font-semibold text-slate-700">{{ $report->nama_pelapor }}</p>
                                        <p class="mt-1 text-slate-400">{{ $report->desa?->nama_desa ?? 'Desa tidak tersedia' }} · RT {{ $report->rt }}/RW {{ $report->rw }}</p>
                                    </td>
                                    <td class="max-w-48 px-5 py-4 leading-relaxed text-slate-600">{{ $report->kategori_label }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-slate-500">{{ $report->created_at?->diffForHumans() ?? '-' }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right">
                                        <a href="{{ $canViewAdminReports ? route('admin.laporan.show', $report->id_laporan) : $dashboardLinks['allReports'] }}"
                                            class="inline-flex items-center gap-1.5 rounded-lg bg-sky-50 px-3 py-2 font-semibold text-sky-700 transition hover:bg-sky-100">
                                            Tindak Lanjut / Jadwalkan
                                            <i class="fas fa-arrow-up-right-from-square text-[10px]" aria-hidden="true"></i>
                                        </a>
                                    </td>
                                </tr>
                @if($loop->last)
                            </tbody>
                        </table>
                    </div>
                @endif
            @empty
                <div class="flex min-h-56 flex-col items-center justify-center px-6 py-10 text-center">
                    <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                        <i class="fas fa-circle-check text-2xl" aria-hidden="true"></i>
                    </span>
                    <p class="mt-4 text-sm font-semibold text-slate-700">Semua laporan warga telah diverifikasi.</p>
                    <p class="mt-1 text-xs text-slate-500">Belum ada laporan baru yang menunggu tindakan.</p>
                </div>
            @endforelse
        </article>

        <article class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 lg:col-span-5">
            <div class="mb-5 flex items-start justify-between gap-3">
                <div>
                    <h2 class="font-mont font-bold text-slate-800">Agenda Kunjungan Hari Ini</h2>
                    <p class="mt-1 text-xs text-slate-500">Jadwal dan beban petugas lapangan</p>
                </div>
                <a href="{{ $dashboardLinks['todaySchedules'] }}" class="shrink-0 text-xs font-semibold text-sky-700 transition hover:text-sky-900">
                    Kelola Jadwal <span aria-hidden="true">&rarr;</span>
                </a>
            </div>
            <div class="space-y-3">
                @forelse($todaySchedules as $schedule)
                    <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-800">{{ $schedule->operator?->nama_lengkap ?? $schedule->operator?->name ?? 'Petugas belum ditugaskan' }}</p>
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $schedule->laporan?->desa?->nama_desa ?? 'IKL rutin' }}
                                    @if($schedule->laporan)
                                        · RT {{ $schedule->laporan->rt }}/RW {{ $schedule->laporan->rw }}
                                    @endif
                                </p>
                            </div>
                            @if($schedule->status_kunjungan === 'selesai')
                                <span class="shrink-0 rounded-full bg-emerald-100 px-2.5 py-1 text-[10px] font-semibold text-emerald-700">Selesai</span>
                            @else
                                <span class="shrink-0 rounded-full bg-sky-100 px-2.5 py-1 text-[10px] font-semibold text-sky-700">Terjadwal</span>
                            @endif
                        </div>
                        <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2 border-t border-slate-200/70 pt-3 text-xs text-slate-500">
                            <span class="inline-flex items-center gap-1.5">
                                <i class="fas fa-clock text-sky-600" aria-hidden="true"></i>
                                {{ $schedule->jam_mulai ? \Illuminate\Support\Carbon::parse($schedule->jam_mulai)->format('H:i') : 'Waktu belum ditentukan' }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="flex min-h-48 flex-col items-center justify-center rounded-xl border border-dashed border-slate-200 bg-slate-50/70 px-4 text-center">
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-slate-400 shadow-sm">
                            <i class="fas fa-calendar-check text-lg" aria-hidden="true"></i>
                        </span>
                        <p class="mt-3 text-sm font-semibold text-slate-700">Belum ada kunjungan hari ini</p>
                        <p class="mt-1 text-xs text-slate-500">Agenda akan tampil setelah jadwal dibuat.</p>
                    </div>
                @endforelse
            </div>
            @if($todaySchedules->hasPages())
                <div class="mt-4 border-t border-slate-100 pt-4">
                    @include('partials.pagination-circles', ['paginator' => $todaySchedules])
                </div>
            @endif
        </article>
    </section>
</div>
@endsection

@section('scripts')
@if($monthlyReportTotal > 0 || $categoryReportTotal > 0)
<script defer src="https://cdn.jsdelivr.net/npm/chart.js@4.5.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (typeof Chart === 'undefined') {
        document.querySelectorAll('[data-chart-unavailable]').forEach(element => element.classList.remove('hidden'));
        return;
    }

    const monthlyCanvas = document.getElementById('monthlyTrendChart');
    if (monthlyCanvas) {
        new Chart(monthlyCanvas, {
            type: 'line',
            data: {
                labels: @json($monthlyChartData['labels']),
                datasets: [
                    {
                        label: 'Laporan Masuk',
                        data: @json($monthlyChartData['incoming']),
                        borderColor: '#0284c7',
                        backgroundColor: 'rgba(2, 132, 199, 0.12)',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 3,
                    },
                    {
                        label: 'Laporan Selesai',
                        data: @json($monthlyChartData['resolved']),
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.08)',
                        fill: false,
                        tension: 0.35,
                        pointRadius: 3,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true, ticks: { precision: 0 } },
                },
            },
        });
    }

    const categoryCanvas = document.getElementById('sanitationCategoryChart');
    if (categoryCanvas) {
        new Chart(categoryCanvas, {
            type: 'doughnut',
            data: {
                labels: @json($categoryChartData['labels']),
                datasets: [{
                    data: @json($categoryChartData['counts']),
                    backgroundColor: @json($categoryChartData['colors']),
                    borderWidth: 0,
                    hoverOffset: 5,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: { legend: { display: false } },
            },
        });
    }
});
</script>
@endif
@endsection

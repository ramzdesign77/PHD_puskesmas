<?php

namespace App\Http\Controllers;

use App\Models\Desa;
use App\Models\InspeksiIkl;
use App\Models\JadwalInspeksi;
use App\Models\LaporanWarga;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const PENDING_STATUSES = ['menunggu', 'dikirim', 'diproses'];

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'period' => ['sometimes', 'string', 'in:this_month,last_3_months,this_year,all'],
            'id_desa' => ['sometimes', 'nullable', 'integer', 'exists:ref_desa,id_desa'],
        ]);

        $period = $validated['period'] ?? 'this_month';
        $idDesa = isset($validated['id_desa']) ? (int) $validated['id_desa'] : null;
        $now = now();
        $periodStart = match ($period) {
            'this_month' => $now->copy()->startOfMonth(),
            'last_3_months' => $now->copy()->subMonths(2)->startOfMonth(),
            'this_year' => $now->copy()->startOfYear(),
            default => null,
        };
        $periodEnd = $now->copy()->endOfDay();
        $today = $now->toDateString();

        $applyReportFilters = function (Builder $query) use ($periodStart, $periodEnd, $idDesa): Builder {
            if ($periodStart !== null) {
                $query->whereBetween('created_at', [$periodStart, $periodEnd]);
            }

            if ($idDesa !== null) {
                $query->where('id_desa', $idDesa);
            }

            return $query;
        };

        $applyInspectionFilters = function (Builder $query) use ($periodStart, $periodEnd, $idDesa): Builder {
            if ($periodStart !== null) {
                $query->whereBetween('tanggal_inspeksi', [$periodStart->toDateString(), $periodEnd]);
            }

            if ($idDesa !== null) {
                $query->whereHas('jadwal.laporan', function (Builder $reportQuery) use ($idDesa): void {
                    $reportQuery->where('id_desa', $idDesa);
                });
            }

            return $query;
        };

        $pendingReportsCount = $applyReportFilters(
            LaporanWarga::query()->whereIn('status_laporan', self::PENDING_STATUSES)
        )->count();

        $todayScheduleQuery = JadwalInspeksi::query()
            ->whereDate('tanggal_kunjungan', $today)
            ->whereIn('status_kunjungan', ['terjadwal', 'selesai']);

        if ($idDesa !== null) {
            $todayScheduleQuery->whereHas('laporan', function (Builder $reportQuery) use ($idDesa): void {
                $reportQuery->where('id_desa', $idDesa);
            });
        }

        $todayScheduleAggregate = (clone $todayScheduleQuery)
            ->selectRaw(
                'COUNT(*) as total,
                COALESCE(SUM(CASE WHEN status_kunjungan = ? THEN 1 ELSE 0 END), 0) as scheduled,
                COALESCE(SUM(CASE WHEN status_kunjungan = ? THEN 1 ELSE 0 END), 0) as completed',
                ['terjadwal', 'selesai']
            )
            ->first();

        $inspectionAggregate = $applyInspectionFilters(InspeksiIkl::query())
            ->selectRaw(
                'COUNT(*) as total,
                COALESCE(SUM(CASE WHEN kategori_risiko IN (?, ?) THEN 1 ELSE 0 END), 0) as memenuhi_syarat',
                ['aman', 'rendah']
            )
            ->first();

        $inspectionTotal = (int) ($inspectionAggregate?->total ?? 0);
        $inspectionMsCount = (int) ($inspectionAggregate?->memenuhi_syarat ?? 0);
        $inspectionTmsCount = max(0, $inspectionTotal - $inspectionMsCount);
        $msPercentage = $inspectionTotal > 0
            ? (int) round(($inspectionMsCount / $inspectionTotal) * 100)
            : 0;

        $interventionsCount = $applyInspectionFilters(InspeksiIkl::query())
            ->whereNotNull('intervensi_fisik')
            ->where('intervensi_fisik', '!=', '')
            ->count();

        $categoryGroups = [
            [
                'label' => 'Air Sumur Keruh / Berbau',
                'slugs' => ['air_sumur_keruh_berbau', 'air_masalah'],
                'color' => '#0284c7',
            ],
            [
                'label' => 'Sarang Nyamuk / DBD',
                'slugs' => ['sarang_nyamuk_dbd'],
                'color' => '#f59e0b',
            ],
            [
                'label' => 'Jamban & Limbah (STBM)',
                'slugs' => ['jamban_limbah_stbm', 'sanitasi_lingkungan'],
                'color' => '#8b5cf6',
            ],
            [
                'label' => 'Tumpukan Sampah',
                'slugs' => ['tumpukan_sampah'],
                'color' => '#10b981',
            ],
        ];

        $categorySlugs = array_merge(...array_column($categoryGroups, 'slugs'));
        $categoryCountsBySlug = $applyReportFilters(LaporanWarga::query())
            ->whereIn('kategori_laporan', $categorySlugs)
            ->select('kategori_laporan')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('kategori_laporan')
            ->pluck('total', 'kategori_laporan');

        $categoryCounts = array_map(function (array $category) use ($categoryCountsBySlug): array {
            return [
                'label' => $category['label'],
                'count' => array_sum(array_map(
                    static fn (string $slug): int => (int) ($categoryCountsBySlug[$slug] ?? 0),
                    $category['slugs']
                )),
                'color' => $category['color'],
            ];
        }, $categoryGroups);

        $categoryReportTotal = array_sum(array_column($categoryCounts, 'count'));
        $categoryDistribution = array_map(function (array $category) use ($categoryReportTotal): array {
            return [
                ...$category,
                'percentage' => $categoryReportTotal > 0
                    ? round(($category['count'] / $categoryReportTotal) * 100, 1)
                    : 0,
            ];
        }, $categoryCounts);

        $monthNames = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $firstChartMonth = $now->copy()->startOfMonth()->subMonths(5);
        $monthKeys = [];
        $monthLabels = [];
        $monthlyCounts = [];

        for ($monthOffset = 0; $monthOffset < 6; $monthOffset++) {
            $month = $firstChartMonth->copy()->addMonths($monthOffset);
            $monthKey = $month->format('Y-m');
            $monthKeys[] = $monthKey;
            $monthLabels[] = $monthNames[(int) $month->format('n')].' '.$month->format('Y');
            $monthlyCounts[$monthKey] = ['incoming' => 0, 'resolved' => 0];
        }

        $monthlyReports = $applyReportFilters(
            LaporanWarga::query()->whereBetween('created_at', [$firstChartMonth, $periodEnd])
        )->get(['created_at', 'status_laporan']);

        foreach ($monthlyReports as $report) {
            $monthKey = $report->created_at?->format('Y-m');

            if ($monthKey === null || ! isset($monthlyCounts[$monthKey])) {
                continue;
            }

            $monthlyCounts[$monthKey]['incoming']++;

            if ($report->status_laporan === 'selesai') {
                $monthlyCounts[$monthKey]['resolved']++;
            }
        }

        $monthlyChartData = [
            'labels' => $monthLabels,
            'incoming' => array_map(static fn (string $monthKey): int => $monthlyCounts[$monthKey]['incoming'], $monthKeys),
            'resolved' => array_map(static fn (string $monthKey): int => $monthlyCounts[$monthKey]['resolved'], $monthKeys),
        ];
        $monthlyReportTotal = array_sum($monthlyChartData['incoming']);

        $pendingReports = $applyReportFilters(
            LaporanWarga::with('desa')->whereIn('status_laporan', ['menunggu', 'dikirim'])
        )
            ->latest('created_at')
            ->latest('id_laporan')
            ->limit(5)
            ->get();

        $todaySchedules = (clone $todayScheduleQuery)
            ->with(['operator', 'laporan.desa'])
            ->orderBy('jam_mulai')
            ->orderBy('id_jadwal')
            ->paginate(5, ['*'], 'page_agenda')
            ->withQueryString();

        $pendingReportsParameters = ['status_laporan' => 'menunggu'];
        if ($idDesa !== null) {
            $pendingReportsParameters['id_desa'] = $idDesa;
        }

        $user = $request->user();
        $dashboardMetrics = [
            'pendingReports' => $pendingReportsCount,
            'todaySchedules' => (int) ($todayScheduleAggregate?->total ?? 0),
            'scheduledToday' => (int) ($todayScheduleAggregate?->scheduled ?? 0),
            'completedToday' => (int) ($todayScheduleAggregate?->completed ?? 0),
            'inspectionTotal' => $inspectionTotal,
            'inspectionMsCount' => $inspectionMsCount,
            'inspectionTmsCount' => $inspectionTmsCount,
            'msPercentage' => $msPercentage,
            'interventions' => $interventionsCount,
        ];

        return view('dashboard.index', [
            'role' => session('role'),
            'greetingName' => $user?->nama_lengkap ?: ($user?->name ?: session('user_name', 'Pengguna')),
            'todayLabel' => $now->format('j').' '.$monthNames[(int) $now->format('n')].' '.$now->format('Y'),
            'filters' => ['period' => $period, 'id_desa' => $idDesa],
            'periodOptions' => [
                'this_month' => 'Bulan ini',
                'last_3_months' => '3 bulan terakhir',
                'this_year' => 'Tahun ini',
                'all' => 'Semua periode',
            ],
            'villages' => Desa::orderBy('nama_desa')->get(['id_desa', 'nama_desa']),
            'dashboardMetrics' => $dashboardMetrics,
            'monthlyChartData' => $monthlyChartData,
            'monthlyReportTotal' => $monthlyReportTotal,
            'categoryDistribution' => $categoryDistribution,
            'categoryChartData' => [
                'labels' => array_column($categoryDistribution, 'label'),
                'counts' => array_column($categoryDistribution, 'count'),
                'colors' => array_column($categoryDistribution, 'color'),
            ],
            'categoryReportTotal' => $categoryReportTotal,
            'pendingReports' => $pendingReports,
            'todaySchedules' => $todaySchedules,
            'canViewAdminReports' => $user?->isAdmin() ?? false,
            'dashboardLinks' => [
                'pendingReports' => route('reports.index', $pendingReportsParameters),
                'todaySchedules' => route('schedules.index', [
                    'status_kunjungan' => 'terjadwal',
                    'tanggal' => $today,
                ]),
                'inspections' => route('reports.index', ['tab' => 'rekap']),
                'allReports' => route('reports.index'),
            ],
        ]);
    }
}

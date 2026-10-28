<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InspeksiIkl;
use App\Models\JadwalInspeksi;
use App\Models\LaporanWarga;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Throwable;

class OfficerApiController extends Controller
{
    public function dashboard(Request $request): JsonResponse
    {
        $officer = $this->authorizeOfficer($request);
        $operatorIds = $this->operatorIds($officer);
        $today = now()->toDateString();

        $recentReports = LaporanWarga::query()
            ->with('desa:id_desa,nama_desa')
            ->where('status_laporan', 'menunggu')
            ->latest('created_at')
            ->limit(5)
            ->get([
                'id_laporan',
                'kode_tiket',
                'kategori_laporan',
                'nama_pelapor',
                'id_desa',
                'rt',
                'rw',
                'deskripsi',
                'foto_bukti',
                'created_at',
            ])
            ->map(fn (LaporanWarga $report): array => $this->reportTask($report));

        $todaySchedules = JadwalInspeksi::query()
            ->with([
                'laporan:id_laporan,nama_pelapor,id_desa,rt,rw',
                'laporan.desa:id_desa,nama_desa',
                'paketAlat:id_paket,nama_paket',
            ])
            ->whereIn('id_operator', $operatorIds)
            ->whereDate('tanggal_kunjungan', $today)
            ->orderBy('tanggal_kunjungan')
            ->orderBy('jam_mulai')
            ->limit(5)
            ->get([
                'id_jadwal',
                'id_laporan',
                'id_operator',
                'id_paket',
                'jenis_kunjungan',
                'tanggal_kunjungan',
                'jam_mulai',
                'waktu_kunjungan',
                'status_kunjungan',
            ])
            ->map(fn (JadwalInspeksi $schedule): array => $this->scheduleTask($schedule));

        $recentTasks = $recentReports
            ->concat($todaySchedules)
            ->sortByDesc('_sort_at')
            ->take(10)
            ->map(fn (array $task): array => collect($task)->except('_sort_at')->all())
            ->values();

        $assignedSchedules = JadwalInspeksi::query()->whereIn('id_operator', $operatorIds);

        return response()->json([
            'success' => true,
            'data' => [
                'officer' => $this->officerData($officer),
                'summary' => [
                    'aduan_baru_count' => LaporanWarga::query()
                        ->where('status_laporan', 'menunggu')
                        ->count(),
                    'jadwal_ikl_hari_ini_count' => (clone $assignedSchedules)
                        ->whereDate('tanggal_kunjungan', $today)
                        ->count(),
                    'tugas_aktif_count' => (clone $assignedSchedules)
                        ->where('status_kunjungan', 'terjadwal')
                        ->count(),
                    'selesai_count' => (clone $assignedSchedules)
                        ->where('status_kunjungan', 'selesai')
                        ->count(),
                ],
                'recent_tasks' => $recentTasks,
            ],
        ]);
    }

    public function tasks(Request $request): JsonResponse
    {
        $officer = $this->authorizeOfficer($request);
        $validated = $request->validate([
            'filter' => ['sometimes', 'string', Rule::in(['semua', 'aduan_baru', 'jadwal_ikl', 'selesai'])],
            'search' => ['sometimes', 'nullable', 'string', 'max:100'],
        ]);

        $filter = $validated['filter'] ?? 'semua';
        $search = trim($validated['search'] ?? '');
        $operatorIds = $this->operatorIds($officer);
        $tasks = collect();
        $hasMore = false;

        if (in_array($filter, ['semua', 'aduan_baru'], true)) {
            $reports = LaporanWarga::query()
                ->with('desa:id_desa,nama_desa')
                ->where('status_laporan', 'menunggu')
                ->when($search !== '', fn (Builder $query) => $this->searchReports($query, $search))
                ->latest('created_at')
                ->limit(101)
                ->get([
                    'id_laporan',
                    'kode_tiket',
                    'kategori_laporan',
                    'nama_pelapor',
                    'id_desa',
                    'rt',
                    'rw',
                    'deskripsi',
                    'foto_bukti',
                    'created_at',
                ]);

            $hasMore = $reports->count() > 100;
            $tasks = $tasks->concat($reports->take(100)->map(
                fn (LaporanWarga $report): array => $this->reportTask($report),
            ));
        }

        if (in_array($filter, ['semua', 'jadwal_ikl', 'selesai'], true)) {
            $scheduleStatus = $filter === 'selesai' ? 'selesai' : 'terjadwal';
            $schedules = JadwalInspeksi::query()
                ->with([
                    'laporan:id_laporan,kode_tiket,nama_pelapor,id_desa,rt,rw',
                    'laporan.desa:id_desa,nama_desa',
                    'paketAlat:id_paket,nama_paket',
                ])
                ->whereIn('id_operator', $operatorIds)
                ->where('status_kunjungan', $scheduleStatus)
                ->when($search !== '', fn (Builder $query) => $this->searchSchedules($query, $search))
                ->orderByDesc('tanggal_kunjungan')
                ->orderByDesc('jam_mulai')
                ->limit(101)
                ->get([
                    'id_jadwal',
                    'id_laporan',
                    'id_operator',
                    'id_paket',
                    'jenis_kunjungan',
                    'tanggal_kunjungan',
                    'jam_mulai',
                    'waktu_kunjungan',
                    'status_kunjungan',
                ]);

            $hasMore = $hasMore || $schedules->count() > 100;
            $tasks = $tasks->concat($schedules->take(100)->map(
                fn (JadwalInspeksi $schedule): array => $this->scheduleTask($schedule),
            ));
        }

        $hasMore = $hasMore || $tasks->count() > 100;
        $tasks = $tasks
            ->sortByDesc('_sort_at')
            ->take(100)
            ->map(fn (array $task): array => collect($task)->except('_sort_at')->all())
            ->values();

        return response()->json([
            'success' => true,
            'data' => $tasks,
            'meta' => [
                'filter' => $filter,
                'search' => $search,
                'limit' => 100,
                'returned' => $tasks->count(),
                'has_more' => $hasMore,
            ],
        ]);
    }

    public function verifyReport(Request $request, int $id_laporan): JsonResponse
    {
        $officer = $this->authorizeOfficer($request);
        $request->merge([
            'action' => match ($request->input('action')) {
                'terima' => 'approve',
                'tolak' => 'reject',
                default => $request->input('action'),
            },
        ]);

        $validated = $request->validate([
            'action' => ['required', Rule::in(['approve', 'reject'])],
            'alasan_penolakan' => ['required_if:action,reject', 'nullable', 'string', 'max:2000'],
            'tanggal_kunjungan' => ['nullable', 'date', 'after_or_equal:today'],
            'waktu_kunjungan' => ['nullable', 'date_format:H:i'],
        ]);

        $result = DB::transaction(function () use ($id_laporan, $officer, $validated): array|JsonResponse {
            $report = LaporanWarga::query()->lockForUpdate()->findOrFail($id_laporan);

            if ($report->status_laporan !== 'menunggu') {
                return response()->json([
                    'success' => false,
                    'message' => 'Aduan ini sudah diverifikasi sebelumnya.',
                ], 409);
            }

            if ($validated['action'] === 'reject') {
                $report->update([
                    'status_laporan' => 'ditolak',
                    'alasan_penolakan' => $validated['alasan_penolakan'],
                ]);

                return [
                    'report' => $report->fresh(),
                    'schedule' => null,
                ];
            }

            $schedule = JadwalInspeksi::query()->create([
                'id_laporan' => $report->id_laporan,
                'id_operator' => $officer->id_user,
                'tanggal_kunjungan' => $validated['tanggal_kunjungan'] ?? now()->toDateString(),
                'jam_mulai' => $validated['waktu_kunjungan'] ?? null,
                'jenis_kunjungan' => 'ikl_laporan_warga',
                'status_kunjungan' => 'terjadwal',
            ]);

            $report->update([
                'status_laporan' => 'dijadwalkan',
                'alasan_penolakan' => null,
            ]);

            return [
                'report' => $report->fresh(),
                'schedule' => $schedule,
            ];
        });

        if ($result instanceof JsonResponse) {
            return $result;
        }

        return response()->json([
            'success' => true,
            'message' => $validated['action'] === 'approve'
                ? 'Aduan disetujui dan jadwal IKL berhasil dibuat.'
                : 'Aduan berhasil ditolak.',
            'data' => [
                'id_laporan' => $result['report']->id_laporan,
                'status_laporan' => $result['report']->status_laporan,
                'alasan_penolakan' => $result['report']->alasan_penolakan,
                'jadwal' => $result['schedule'] === null ? null : [
                    'id_jadwal' => $result['schedule']->id_jadwal,
                    'tanggal_kunjungan' => $result['schedule']->tanggal_kunjungan,
                    'waktu_kunjungan' => $result['schedule']->jam_mulai,
                    'status_kunjungan' => $result['schedule']->status_kunjungan,
                ],
            ],
        ]);
    }

    public function storeInspection(Request $request): JsonResponse
    {
        $officer = $this->authorizeOfficer($request);
        $validated = $request->validate([
            'id_jadwal' => [
                'required',
                'integer',
                Rule::exists('trx_jadwal_inspeksi', 'id_jadwal'),
                Rule::unique('trx_inspeksi_ikl', 'id_jadwal'),
            ],
            'jenis_sarana_air' => ['required', Rule::in(['sumur_bor', 'sumur_terlindung', 'pdam', 'mata_air', 'lainnya'])],
            'jarak_sumber_pencemar' => ['required', 'integer', 'min:0', 'max:99999'],
            'p1_dinding_sumur_retak' => ['required', 'boolean'],
            'p2_lantai_sumur_retak' => ['required', 'boolean'],
            'p3_bibir_sumur_tidak_ada' => ['required', 'boolean'],
            'p4_saluran_pembuangan_rusak' => ['required', 'boolean'],
            'p5_air_keruh_berbau' => ['required', 'boolean'],
            'rekomendasi_sanitarian' => ['required', 'string', 'min:5', 'max:2000'],
            'intervensi_fisik' => ['nullable', 'string', 'max:150'],
            'catatan_intervensi' => ['nullable', 'string', 'max:10000'],
            'foto_inspeksi' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $schedule = JadwalInspeksi::query()
            ->with('laporan')
            ->whereIn('id_operator', $this->operatorIds($officer))
            ->where('status_kunjungan', 'terjadwal')
            ->findOrFail($validated['id_jadwal']);

        $checklist = [
            'p1_dinding_sumur_retak',
            'p2_lantai_sumur_retak',
            'p3_bibir_sumur_tidak_ada',
            'p4_saluran_pembuangan_rusak',
            'p5_air_keruh_berbau',
        ];
        $totalSkorYa = collect($checklist)
            ->filter(fn (string $field): bool => (bool) $validated[$field])
            ->count();
        $kategoriRisiko = InspeksiIkl::hitungKategoriRisiko($totalSkorYa);
        $fotoPath = $request->file('foto_inspeksi')?->store('inspeksi-ikl', 'public');

        try {
            $inspection = DB::transaction(function () use (
                $schedule,
                $validated,
                $totalSkorYa,
                $kategoriRisiko,
                $fotoPath,
            ): InspeksiIkl {
                $inspection = InspeksiIkl::query()->create([
                    'id_jadwal' => $schedule->id_jadwal,
                    'jenis_sarana_air' => $validated['jenis_sarana_air'],
                    'jarak_sumber_pencemar' => $validated['jarak_sumber_pencemar'],
                    'p1_dinding_sumur_retak' => (bool) $validated['p1_dinding_sumur_retak'],
                    'p2_penutup_tidak_rapat' => (bool) $validated['p3_bibir_sumur_tidak_ada'],
                    'p3_lantai_becek_retak' => (bool) $validated['p2_lantai_sumur_retak'],
                    'p4_spal_tersumbat' => (bool) $validated['p4_saluran_pembuangan_rusak'],
                    'p5_air_keruh_berbau' => (bool) $validated['p5_air_keruh_berbau'],
                    'total_skor_ya' => $totalSkorYa,
                    'kategori_risiko' => $kategoriRisiko,
                    'rekomendasi_sanitarian' => $validated['rekomendasi_sanitarian'],
                    'tanggal_inspeksi' => now()->toDateString(),
                    'intervensi_fisik' => $validated['intervensi_fisik'] ?? null,
                    'catatan_intervensi' => $validated['catatan_intervensi'] ?? null,
                    'foto_inspeksi' => $fotoPath,
                ]);

                $schedule->update(['status_kunjungan' => 'selesai']);
                $schedule->laporan?->update(['status_laporan' => 'selesai']);

                return $inspection;
            });
        } catch (Throwable $exception) {
            if ($fotoPath !== null) {
                Storage::disk('public')->delete($fotoPath);
            }

            throw $exception;
        }

        return response()->json([
            'success' => true,
            'message' => 'Hasil inspeksi IKL berhasil disimpan.',
            'data' => [
                'id_inspeksi' => $inspection->id_inspeksi,
                'id_jadwal' => $inspection->id_jadwal,
                'total_skor_ya' => $inspection->total_skor_ya,
                'kategori_risiko' => $inspection->kategori_risiko,
                'foto_inspeksi' => $inspection->foto_inspeksi,
                'status_kunjungan' => $schedule->fresh()->status_kunjungan,
                'status_laporan' => $schedule->laporan?->fresh()->status_laporan,
            ],
        ], 201);
    }

    public function stbmDesa(Request $request): JsonResponse
    {
        $this->authorizeOfficer($request);

        $villages = DB::table('ref_desa as desa')
            ->leftJoin('trx_laporan_warga as laporan', 'laporan.id_desa', '=', 'desa.id_desa')
            ->leftJoin('trx_jadwal_inspeksi as jadwal', 'jadwal.id_laporan', '=', 'laporan.id_laporan')
            ->leftJoin('trx_inspeksi_ikl as inspeksi', 'inspeksi.id_jadwal', '=', 'jadwal.id_jadwal')
            ->select('desa.id_desa', 'desa.nama_desa')
            ->selectRaw('COUNT(DISTINCT laporan.id_laporan) AS total_aduan')
            ->selectRaw('COUNT(DISTINCT inspeksi.id_inspeksi) AS jumlah_sumur_diperiksa')
            ->selectRaw(
                'COUNT(DISTINCT CASE WHEN inspeksi.kategori_risiko = ? THEN inspeksi.id_inspeksi END) AS risiko_aman',
                ['aman'],
            )
            ->selectRaw(
                'COUNT(DISTINCT CASE WHEN inspeksi.kategori_risiko = ? THEN inspeksi.id_inspeksi END) AS risiko_sedang',
                ['risiko_sedang'],
            )
            ->selectRaw(
                'COUNT(DISTINCT CASE WHEN inspeksi.kategori_risiko = ? THEN inspeksi.id_inspeksi END) AS risiko_tinggi',
                ['risiko_tinggi'],
            )
            ->groupBy('desa.id_desa', 'desa.nama_desa')
            ->orderBy('desa.nama_desa')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $villages,
        ]);
    }

    private function authorizeOfficer(Request $request): User
    {
        $user = $request->user();

        abort_unless(
            $user instanceof User && ($user->isSanitarian() || $user->hasRole('officer')),
            403,
            'Endpoint ini hanya tersedia untuk akun petugas.',
        );

        return $user;
    }

    /**
     * @return array<int, int|string>
     */
    private function operatorIds(User $user): array
    {
        return array_values(array_unique(array_filter(
            [$user->id_user, $user->getAttribute('id')],
            static fn (mixed $id): bool => $id !== null && $id !== '',
        )));
    }

    /**
     * @return array<string, mixed>
     */
    private function officerData(User $user): array
    {
        $user->loadMissing('petugasProfile');

        return [
            'id' => $user->id_user,
            'nama_lengkap' => $user->nama_lengkap ?: $user->name,
            'nip' => null,
            'jabatan' => $user->petugasProfile?->jabatan ?: 'Petugas Kesling',
            'role' => $user->role,
            'wilayah_kerja' => $user->wilayah_kerja,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function reportTask(LaporanWarga $report): array
    {
        return [
            'type' => 'aduan_baru',
            'id_laporan' => $report->id_laporan,
            'kode_tiket' => $report->kode_tiket,
            'kategori_laporan' => $report->kategori_laporan,
            'nama_pelapor' => $report->nama_pelapor,
            'nama_desa' => $report->desa?->nama_desa,
            'rt' => $report->rt,
            'rw' => $report->rw,
            'deskripsi' => $report->deskripsi,
            'foto_bukti' => $report->foto_bukti,
            'created_at' => $report->created_at?->toIso8601String(),
            '_sort_at' => $report->created_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function scheduleTask(JadwalInspeksi $schedule): array
    {
        $schedule->loadMissing(['laporan.desa', 'paketAlat']);

        return [
            'type' => 'jadwal_ikl',
            'id_jadwal' => $schedule->id_jadwal,
            'jenis_kunjungan' => $schedule->jenis_kunjungan,
            'tanggal_kunjungan' => $schedule->tanggal_kunjungan?->toDateString(),
            'waktu_kunjungan' => $schedule->jam_mulai ?: $schedule->waktu_kunjungan,
            'status_kunjungan' => $schedule->status_kunjungan,
            'nama_pelapor' => $schedule->laporan?->nama_pelapor,
            'nama_desa' => $schedule->laporan?->desa?->nama_desa,
            'rt' => $schedule->laporan?->rt,
            'rw' => $schedule->laporan?->rw,
            'paket_alat' => $schedule->paketAlat?->nama_paket,
            '_sort_at' => $schedule->tanggal_kunjungan?->toDateString().' '.(
                $schedule->jam_mulai ?: $schedule->waktu_kunjungan ?: '00:00:00'
            ),
        ];
    }

    private function searchReports(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $query) use ($search): void {
            $query->where('kode_tiket', 'like', "%{$search}%")
                ->orWhere('nama_pelapor', 'like', "%{$search}%")
                ->orWhere('kategori_laporan', 'like', "%{$search}%")
                ->orWhere('deskripsi', 'like', "%{$search}%")
                ->orWhereHas('desa', fn (Builder $desaQuery) => $desaQuery->where('nama_desa', 'like', "%{$search}%"));
        });
    }

    private function searchSchedules(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $query) use ($search): void {
            $query->whereHas('laporan', function (Builder $reportQuery) use ($search): void {
                $reportQuery->where('kode_tiket', 'like', "%{$search}%")
                    ->orWhere('nama_pelapor', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%")
                    ->orWhereHas('desa', fn (Builder $desaQuery) => $desaQuery->where('nama_desa', 'like', "%{$search}%"));
            })->orWhereHas('paketAlat', fn (Builder $packageQuery) => $packageQuery->where('nama_paket', 'like', "%{$search}%"));
        });
    }
}

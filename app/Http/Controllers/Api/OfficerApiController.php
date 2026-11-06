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
        $newReportStatuses = ['menunggu', 'dikirim', 'diproses'];
        $waitingReportStatuses = ['menunggu', 'dikirim', 'diproses'];
        $activeScheduleStatuses = ['terjadwal', 'dijadwalkan', 'ditindaklanjuti', 'diproses'];

        $recentReports = LaporanWarga::query()
            ->with('desa:id_desa,nama_desa')
            ->whereIn('status_laporan', $waitingReportStatuses)
            ->latest('created_at')
            ->limit(5)
            ->get([
                'id_laporan',
                'kode_tiket',
                'kategori_laporan',
                'nama_pelapor',
                'no_wa',
                'id_desa',
                'rt',
                'rw',
                'alamat_detail',
                'patokan_alamat',
                'deskripsi',
                'foto_bukti',
                'status_laporan',
                'created_at',
            ])
            ->map(fn (LaporanWarga $report): array => $this->reportTask($report));

        $todaySchedules = JadwalInspeksi::query()
            ->with([
                'laporan:id_laporan,kode_tiket,nama_pelapor,no_wa,id_desa,rt,rw,alamat_detail,patokan_alamat,kategori_laporan,deskripsi,foto_bukti,status_laporan,created_at',
                'laporan.desa:id_desa,nama_desa',
                'paketAlat:id_paket,nama_paket',
                'operator:id_user,nama_lengkap,username',
                'inspeksiIkl',
            ])
            ->where(function (Builder $query) use ($operatorIds): void {
                $query->whereIn('id_operator', $operatorIds)
                    ->orWhereNull('id_operator');
            })
            ->whereDate('tanggal_kunjungan', $today)
            ->whereIn('status_kunjungan', $activeScheduleStatuses)
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
                'catatan',
            ])
            ->map(fn (JadwalInspeksi $schedule): array => $this->scheduleTask($schedule));

        $assignedSchedules = JadwalInspeksi::query()->where(function (Builder $query) use ($operatorIds): void {
            $query->whereIn('id_operator', $operatorIds)
                ->orWhereNull('id_operator');
        });

        return response()->json([
            'success' => true,
            'message' => 'Ringkasan dashboard petugas berhasil diambil.',
            'data' => [
                'officer' => $this->officerData($officer),
                'summary' => [
                    'aduan_baru_count' => LaporanWarga::query()
                        ->whereIn('status_laporan', $newReportStatuses)
                        ->count(),
                    'jadwal_ikl_hari_ini_count' => (clone $assignedSchedules)
                        ->whereDate('tanggal_kunjungan', $today)
                        ->count(),
                    'tugas_aktif_count' => (clone $assignedSchedules)
                        ->whereIn('status_kunjungan', $activeScheduleStatuses)
                        ->count()
                        + LaporanWarga::query()->whereIn('status_laporan', ['menunggu', 'dikirim', 'diproses'])->count(),
                    'selesai_count' => (clone $assignedSchedules)
                        ->where('status_kunjungan', 'selesai')
                        ->count(),
                ],
                'recent_reports' => $recentReports
                    ->map(fn (array $task): array => collect($task)->except('_sort_at')->all())
                    ->values(),
                'laporan_menunggu' => $recentReports
                    ->map(fn (array $task): array => collect($task)->except('_sort_at')->all())
                    ->values(),
                'today_schedules' => $todaySchedules
                    ->map(fn (array $task): array => collect($task)->except('_sort_at')->all())
                    ->values(),
                'jadwal_hari_ini' => $todaySchedules
                    ->map(fn (array $task): array => collect($task)->except('_sort_at')->all())
                    ->values(),
                'recent_tasks' => [
                    'laporan_menunggu' => $recentReports
                        ->map(fn (array $task): array => collect($task)->except('_sort_at')->all())
                        ->values(),
                    'jadwal_hari_ini' => $todaySchedules
                        ->map(fn (array $task): array => collect($task)->except('_sort_at')->all())
                        ->values(),
                ],
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
        $newReportStatuses = ['menunggu', 'dikirim', 'diproses'];
        $activeScheduleStatuses = ['terjadwal', 'dijadwalkan', 'ditindaklanjuti', 'diproses'];

        if (in_array($filter, ['semua', 'aduan_baru'], true)) {
            $reports = LaporanWarga::query()
                ->with('desa:id_desa,nama_desa')
                ->whereIn('status_laporan', $newReportStatuses)
                ->when($search !== '', fn (Builder $query) => $this->searchReports($query, $search))
                ->latest('created_at')
                ->limit(101)
                ->get([
                    'id_laporan',
                    'kode_tiket',
                    'kategori_laporan',
                    'nama_pelapor',
                    'no_wa',
                    'id_desa',
                    'rt',
                    'rw',
                    'alamat_detail',
                    'patokan_alamat',
                    'deskripsi',
                    'foto_bukti',
                    'status_laporan',
                    'created_at',
                ]);

            $hasMore = $reports->count() > 100;
            $tasks = $tasks->concat($reports->take(100)->map(
                fn (LaporanWarga $report): array => $this->reportTask($report),
            ));
        }

        if (in_array($filter, ['semua', 'selesai'], true)) {
            $completedReports = LaporanWarga::query()
                ->with('desa:id_desa,nama_desa')
                ->where('status_laporan', 'selesai')
                ->whereDoesntHave('jadwal', function (Builder $query): void {
                    $query->where('status_kunjungan', 'selesai');
                })
                ->when($search !== '', fn (Builder $query) => $this->searchReports($query, $search))
                ->latest('created_at')
                ->limit(101)
                ->get();

            $hasMore = $hasMore || $completedReports->count() > 100;
            $tasks = $tasks->concat($completedReports->take(100)->map(
                fn (LaporanWarga $report): array => $this->reportTask($report, true),
            ));
        }

        if (in_array($filter, ['semua', 'jadwal_ikl', 'selesai'], true)) {
            $schedules = JadwalInspeksi::query()
                ->with([
                    'laporan:id_laporan,kode_tiket,nama_pelapor,no_wa,id_desa,rt,rw,alamat_detail,patokan_alamat,kategori_laporan,deskripsi,foto_bukti,status_laporan,created_at',
                    'laporan.desa:id_desa,nama_desa',
                    'paketAlat:id_paket,nama_paket',
                    'operator:id_user,nama_lengkap,username',
                    'inspeksiIkl',
                ])
                ->where(function (Builder $query) use ($operatorIds): void {
                    $query->whereIn('id_operator', $operatorIds)
                        ->orWhereNull('id_operator');
                })
                ->whereIn(
                    'status_kunjungan',
                    $filter === 'selesai' ? ['selesai'] : (
                        $filter === 'jadwal_ikl' ? $activeScheduleStatuses : array_merge($activeScheduleStatuses, ['selesai'])
                    ),
                )
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
                    'catatan',
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
            'message' => 'Daftar tugas petugas berhasil diambil.',
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
                null => 'approve',
                '' => 'approve',
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

            if (! in_array($report->status_laporan, ['menunggu', 'dikirim', 'dibaca', 'diterima', 'diproses'], true)) {
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

            $schedule = JadwalInspeksi::query()
                ->where('id_laporan', $report->id_laporan)
                ->first();

            if ($schedule === null) {
                $schedule = JadwalInspeksi::query()->create([
                    'id_laporan' => $report->id_laporan,
                    'id_operator' => $officer->id_user,
                    'tanggal_kunjungan' => $validated['tanggal_kunjungan'] ?? now()->toDateString(),
                    'jam_mulai' => $validated['waktu_kunjungan'] ?? null,
                    'jenis_kunjungan' => 'ikl_laporan_warga',
                    'status_kunjungan' => 'terjadwal',
                ]);
            }

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

        $parameterAliases = [
            'p1_dinding_sumur_retak' => ['p1', 'p1_dinding_sumur_retak'],
            'p2_penutup_tidak_rapat' => ['p2', 'p2_lantai_sumur_retak', 'p2_penutup_tidak_rapat'],
            'p3_lantai_becek_retak' => ['p3', 'p3_bibir_sumur_tidak_ada', 'p3_lantai_becek_retak'],
            'p4_spal_tersumbat' => ['p4', 'p4_saluran_pembuangan_rusak', 'p4_spal_tersumbat'],
            'p5_air_keruh_berbau' => ['p5', 'p5_air_keruh_berbau'],
            'p6_indikator_6' => ['p6', 'indikator_6', 'p6_indikator_6'],
            'p7_indikator_7' => ['p7', 'indikator_7', 'p7_indikator_7'],
        ];

        foreach ($parameterAliases as $canonicalName => $aliases) {
            foreach ($aliases as $alias) {
                if ($request->exists($alias)) {
                    $request->merge([$canonicalName => $request->input($alias)]);
                    break;
                }
            }
        }

        if (! $request->exists('kategori_ikl')) {
            $request->merge(['kategori_ikl' => $request->input('jenis_sarana_air') === 'sumur_bor' ? 'sumur_bor' : 'sumur_gali']);
        }

        $validated = $request->validate([
            'id_jadwal' => [
                'required',
                'integer',
                Rule::exists('trx_jadwal_inspeksi', 'id_jadwal'),
            ],
            'jenis_sarana_air' => ['required', Rule::in(['sumur_bor', 'sumur_terlindung', 'pdam', 'mata_air', 'lainnya'])],
            'jarak_sumber_pencemar' => ['required', 'integer', 'min:0', 'max:99999'],
            'p1_dinding_sumur_retak' => ['required', 'boolean'],
            'p2_penutup_tidak_rapat' => ['required', 'boolean'],
            'p3_lantai_becek_retak' => ['required', 'boolean'],
            'p4_spal_tersumbat' => ['required', 'boolean'],
            'p5_air_keruh_berbau' => ['required', 'boolean'],
            'p6_indikator_6' => ['sometimes', 'boolean'],
            'p7_indikator_7' => ['sometimes', 'boolean'],
            'kategori_ikl' => ['required', Rule::in(['sumur_gali', 'sumur_bor', 'sarang_nyamuk_dbd', 'jamban_limbah_stbm', 'tumpukan_sampah'])],
            'tanggal_inspeksi' => ['required', 'date', 'before_or_equal:today'],
            'rekomendasi_sanitarian' => ['required', 'string', 'min:5', 'max:2000'],
            'intervensi_fisik' => ['nullable', 'string', 'max:150'],
            'catatan_intervensi' => ['nullable', 'string', 'max:10000'],
            'catatan_petugas' => ['nullable', 'string', 'max:10000'],
            'foto_bukti' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'foto_inspeksi' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $answers = [
            'p1' => (bool) $validated['p1_dinding_sumur_retak'],
            'p2' => (bool) $validated['p2_penutup_tidak_rapat'],
            'p3' => (bool) $validated['p3_lantai_becek_retak'],
            'p4' => (bool) $validated['p4_spal_tersumbat'],
            'p5' => (bool) $validated['p5_air_keruh_berbau'],
            'p6' => (bool) ($validated['p6_indikator_6'] ?? false),
            'p7' => (bool) ($validated['p7_indikator_7'] ?? false),
        ];
        $totalSkorYa = count(array_filter($answers));
        $risk = InspeksiIkl::calculateRiskCategory($totalSkorYa, $validated['kategori_ikl'], $answers);
        $kategoriRisiko = $risk['code'];
        $detailIndikator = InspeksiIkl::buildIndicatorDetails($validated['kategori_ikl'], $answers);
        $fotoBukti = $request->file('foto_bukti');
        $fotoDisk = $fotoBukti !== null ? 'local' : 'public';
        $fotoPath = $fotoBukti !== null
            ? $fotoBukti->store('inspeksi-ikl', $fotoDisk)
            : $request->file('foto_inspeksi')?->store('inspeksi-ikl', $fotoDisk);

        try {
            $result = DB::transaction(function () use (
                $validated,
                $totalSkorYa,
                $kategoriRisiko,
                $answers,
                $detailIndikator,
                $fotoPath,
                $officer,
            ): array {
                $schedule = JadwalInspeksi::query()
                    ->with('laporan')
                    ->whereKey($validated['id_jadwal'])
                    ->whereIn('id_operator', $this->operatorIds($officer))
                    ->lockForUpdate()
                    ->first();

                abort_if($schedule === null, 404, 'Jadwal inspeksi tidak ditemukan atau tidak ditugaskan kepada akun Anda.');
                abort_if(
                    $schedule->status_kunjungan !== 'terjadwal',
                    409,
                    'Jadwal inspeksi tidak aktif.',
                );
                abort_if(
                    InspeksiIkl::query()->where('id_jadwal', $schedule->id_jadwal)->exists(),
                    409,
                    'Hasil inspeksi untuk jadwal ini sudah pernah dikirim.',
                );

                $inspection = InspeksiIkl::query()->create([
                    'id_jadwal' => $schedule->id_jadwal,
                    'kategori_ikl' => $validated['kategori_ikl'],
                    'jenis_sarana_air' => $validated['jenis_sarana_air'],
                    'jarak_sumber_pencemar' => $validated['jarak_sumber_pencemar'],
                    'p1_dinding_sumur_retak' => (bool) $validated['p1_dinding_sumur_retak'],
                    'p2_penutup_tidak_rapat' => (bool) $validated['p2_penutup_tidak_rapat'],
                    'p3_lantai_becek_retak' => (bool) $validated['p3_lantai_becek_retak'],
                    'p4_spal_tersumbat' => (bool) $validated['p4_spal_tersumbat'],
                    'p5_air_keruh_berbau' => (bool) $validated['p5_air_keruh_berbau'],
                    'p1' => $answers['p1'],
                    'p2' => $answers['p2'],
                    'p3' => $answers['p3'],
                    'p4' => $answers['p4'],
                    'p5' => $answers['p5'],
                    'p6_indikator_6' => $answers['p6'],
                    'p7_indikator_7' => $answers['p7'],
                    'detail_indikator' => $detailIndikator,
                    'total_skor_ya' => $totalSkorYa,
                    'kategori_risiko' => $kategoriRisiko,
                    'rekomendasi_sanitarian' => $validated['rekomendasi_sanitarian'],
                    'tanggal_inspeksi' => $validated['tanggal_inspeksi'],
                    'intervensi_fisik' => $validated['intervensi_fisik'] ?? null,
                    'catatan_intervensi' => $validated['catatan_intervensi'] ?? null,
                    'catatan_petugas' => $validated['catatan_petugas'] ?? null,
                    'foto_inspeksi' => $fotoPath,
                    'foto_bukti' => $fotoPath,
                ]);

                $schedule->update(['status_kunjungan' => 'selesai']);
                $schedule->laporan?->update(['status_laporan' => 'selesai']);

                return [
                    'inspection' => $inspection,
                    'schedule' => $schedule->fresh(),
                ];
            });
        } catch (Throwable $exception) {
            if ($fotoPath !== null) {
                Storage::disk($fotoDisk)->delete($fotoPath);
            }

            throw $exception;
        }

        return response()->json([
            'success' => true,
            'message' => 'Hasil inspeksi IKL berhasil disimpan.',
            'data' => [
                'id_inspeksi' => $result['inspection']->id_inspeksi,
                'id_jadwal' => $result['inspection']->id_jadwal,
                'total_skor_ya' => $result['inspection']->total_skor_ya,
                'detail_indikator' => $result['inspection']->detail_indikator,
                'kategori_ikl' => $result['inspection']->kategori_ikl,
                'kategori_risiko' => $result['inspection']->kategori_risiko,
                'label_kategori_risiko' => match ($result['inspection']->kategori_risiko) {
                    'aman' => 'Risiko Rendah (Aman)',
                    'risiko_sedang' => 'Risiko Sedang',
                    'risiko_tinggi' => 'Risiko Tinggi',
                    'risiko_amat_tinggi' => 'Risiko Amat Tinggi',
                    default => '-',
                },
                'tanggal_inspeksi' => $result['inspection']->tanggal_inspeksi?->toDateString(),
                'intervensi_fisik' => $result['inspection']->intervensi_fisik,
                'foto_inspeksi' => $result['inspection']->foto_inspeksi,
                'status_kunjungan' => $result['schedule']->status_kunjungan,
                'status_laporan' => $result['schedule']->laporan?->fresh()->status_laporan,
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
    private function reportTask(LaporanWarga $report, bool $completed = false): array
    {
        return [
            'type' => 'aduan_baru',
            'task_type' => $completed ? 'jadwal_ikl' : 'aduan_baru',
            'id' => 'laporan-'.$report->id_laporan,
            'id_laporan' => $report->id_laporan,
            'id_jadwal' => null,
            'kode_tiket' => $report->kode_tiket,
            'kategori_laporan' => $report->kategori_laporan,
            'kategori_label' => $report->kategori_label,
            'nama_pelapor' => $report->nama_pelapor,
            'no_wa' => $report->no_wa,
            'id_desa' => $report->id_desa,
            'nama_desa' => $report->desa?->nama_desa,
            'rt' => $report->rt,
            'rw' => $report->rw,
            'alamat_detail' => $report->alamat_detail ?: $report->patokan_alamat,
            'deskripsi' => $report->deskripsi,
            'foto_bukti' => $report->foto_bukti,
            'foto_bukti_url' => $this->fotoBuktiUrl($report->foto_bukti),
            'status_laporan' => $report->status_laporan,
            'status_kunjungan' => $completed ? 'selesai' : 'menunggu_verifikasi',
            'tanggal_kunjungan' => $report->created_at?->toDateString(),
            'created_at' => $report->created_at?->toIso8601String(),
            '_sort_at' => $report->created_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function scheduleTask(JadwalInspeksi $schedule): array
    {
        $schedule->loadMissing(['laporan.desa', 'paketAlat', 'operator', 'inspeksiIkl']);
        $inspection = $schedule->inspeksiIkl;

        return [
            'type' => 'jadwal_ikl',
            'task_type' => 'jadwal_ikl',
            'id' => 'jadwal-'.$schedule->id_jadwal,
            'id_jadwal' => $schedule->id_jadwal,
            'id_laporan' => $schedule->id_laporan,
            'id_operator' => $schedule->id_operator,
            'id_paket' => $schedule->id_paket,
            'jenis_kunjungan' => $schedule->jenis_kunjungan,
            'tanggal_kunjungan' => $schedule->tanggal_kunjungan?->toDateString(),
            'waktu_kunjungan' => $schedule->jam_mulai ?: $schedule->waktu_kunjungan,
            'status_kunjungan' => $schedule->status_kunjungan,
            'catatan' => $schedule->catatan,
            'nama_pelapor' => $schedule->laporan?->nama_pelapor,
            'kode_tiket' => $schedule->laporan?->kode_tiket,
            'no_wa' => $schedule->laporan?->no_wa,
            'kategori_laporan' => $schedule->laporan?->kategori_laporan,
            'kategori_label' => $schedule->laporan?->kategori_label,
            'id_desa' => $schedule->laporan?->id_desa,
            'nama_desa' => $schedule->laporan?->desa?->nama_desa,
            'rt' => $schedule->laporan?->rt,
            'rw' => $schedule->laporan?->rw,
            'alamat_detail' => $schedule->laporan?->alamat_detail ?: $schedule->laporan?->patokan_alamat,
            'deskripsi' => $schedule->laporan?->deskripsi,
            'status_laporan' => $schedule->laporan?->status_laporan,
            'foto_bukti' => $schedule->laporan?->foto_bukti,
            'foto_bukti_url' => $this->fotoBuktiUrl($schedule->laporan?->foto_bukti),
            'paket_alat' => $schedule->paketAlat?->nama_paket,
            'nama_operator' => $schedule->operator?->nama_lengkap ?: $schedule->operator?->name,
            'inspeksi' => $inspection === null ? null : [
                'id_inspeksi' => $inspection->id_inspeksi,
                'kategori_ikl' => $inspection->kategori_ikl,
                'kategori_risiko' => $inspection->kategori_risiko,
                'total_skor_ya' => $inspection->total_skor_ya,
                'detail_indikator' => $inspection->detail_indikator,
                'tanggal_inspeksi' => $inspection->tanggal_inspeksi?->toDateString(),
            ],
            'kategori_risiko' => $inspection?->kategori_risiko,
            'total_skor_ya' => $inspection?->total_skor_ya,
            '_sort_at' => $schedule->tanggal_kunjungan?->toDateString().' '.(
                $schedule->jam_mulai ?: $schedule->waktu_kunjungan ?: '00:00:00'
            ),
        ];
    }

    private function fotoBuktiUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
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

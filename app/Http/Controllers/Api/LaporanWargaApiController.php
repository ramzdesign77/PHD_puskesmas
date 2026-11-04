<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Desa;
use App\Models\LaporanWarga;
use App\Models\MobileAccessToken;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Controller untuk endpoint API Flutter/Mobile.
 * Semua method mengembalikan JSON.
 */
class LaporanWargaApiController extends Controller
{
    public function listDesa(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Desa::query()
                ->orderBy('nama_desa')
                ->get(['id_desa', 'nama_desa']),
        ]);
    }

    /**
     * POST /api/laporan
     *
     * Endpoint untuk warga mengirim laporan masalah air via Flutter app.
     * Tidak memerlukan autentikasi (laporan anonim diperbolehkan).
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $user = $this->resolveAuthenticatedMobileUser($request);

            $validated = $request->validate([
                'nama_pelapor' => [
                    $user === null ? 'required' : 'nullable',
                    'string',
                    'max:150',
                ],
                'nik_pelapor' => 'nullable|string|max:16',
                'no_wa' => [
                    $user === null ? 'required' : 'nullable',
                    'string',
                    'max:20',
                ],
                'id_desa' => 'required|integer|exists:ref_desa,id_desa',
                'rt' => 'required|string|max:10',
                'rw' => 'required|string|max:10',
                'kategori_laporan' => [
                    'required',
                    'string',
                    'max:100',
                    Rule::in([
                        'air_sumur_keruh_berbau',
                        'sarang_nyamuk_dbd',
                        'jamban_limbah_stbm',
                        'tumpukan_sampah',
                        'air_masalah',
                        'sanitasi_lingkungan',
                    ]),
                ],
                'deskripsi' => 'required|string|max:2000',
                'alamat_detail' => 'nullable|string|max:255',
                'patokan_alamat' => 'nullable|string|max:255',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
                'foto_bukti' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            ]);

            if (isset($validated['latitude'], $validated['longitude'])) {
                $distance = $this->calculateDistanceInKilometers(
                    -8.1741,
                    113.7208,
                    (float) $validated['latitude'],
                    (float) $validated['longitude'],
                );

                if ($distance > 5) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Gagal mengirim laporan. Lokasi Anda saat ini berada di luar cakupan wilayah Sumbersari, Jember.',
                    ], 403);
                }
            }

            $fotoBuktiPath = null;
            if ($request->hasFile('foto_bukti')) {
                $fotoBuktiPath = $request->file('foto_bukti')
                    ->store('laporan/foto_bukti', 'public');
            }

            $alamatDetail = $validated['alamat_detail']
                ?? $validated['patokan_alamat']
                ?? null;
            $namaPelapor = $validated['nama_pelapor']
                ?? ($user?->nama_lengkap ?: $user?->name);
            $noWa = $validated['no_wa']
                ?? ($user?->no_telepon ?: $user?->no_wa);

            $laporan = LaporanWarga::create([
                'kode_tiket' => LaporanWarga::generateKodeTiket(),
                'id_user' => $user?->getAttribute('id') ?? $user?->getAttribute('id_user'),
                'nama_pelapor' => $namaPelapor,
                'nik_pelapor' => $validated['nik_pelapor'] ?? null,
                'no_wa' => $noWa,
                'id_desa' => $validated['id_desa'],
                'rt' => $validated['rt'],
                'rw' => $validated['rw'],
                'kategori_laporan' => $validated['kategori_laporan'],
                'deskripsi' => $validated['deskripsi'],
                'foto_bukti' => $fotoBuktiPath,
                'alamat_detail' => $alamatDetail,
                'patokan_alamat' => $alamatDetail,
                'status_laporan' => 'menunggu',
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Laporan berhasil dikirim. Tim Kesling akan segera menindaklanjuti.',
                'kode_tiket' => $laporan->kode_tiket,
                'id_laporan' => $laporan->id_laporan,
                'data' => [
                    'laporan' => $laporan->fresh(['desa', 'pelapor']),
                    'kode_tiket' => $laporan->kode_tiket,
                    'kategori_laporan' => $laporan->kategori_laporan,
                    'kategori_label' => $laporan->kategori_label,
                    'latitude' => $laporan->latitude,
                    'longitude' => $laporan->longitude,
                ],
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('LaporanWargaApi@store error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server. Coba lagi nanti.',
            ], 500);
        }
    }

    private function resolveAuthenticatedMobileUser(Request $request): ?User
    {
        if ($request->user() instanceof User) {
            return $request->user();
        }

        $plainToken = $request->bearerToken();
        if ($plainToken === null || $plainToken === '') {
            return null;
        }

        $mobileToken = MobileAccessToken::query()
            ->where('token_hash', hash('sha256', $plainToken))
            ->first(['id_user', 'expires_at']);

        if ($mobileToken === null) {
            return null;
        }

        if ($mobileToken->expires_at !== null && $mobileToken->expires_at->isPast()) {
            return null;
        }

        $user = User::query()->find($mobileToken->id_user);

        return $user instanceof User && $user->is_active ? $user : null;
    }

    private function calculateDistanceInKilometers(
        float $latitudeFrom,
        float $longitudeFrom,
        float $latitudeTo,
        float $longitudeTo,
    ): float {
        $earthRadiusInKilometers = 6371;
        $latitudeDifference = deg2rad($latitudeTo - $latitudeFrom);
        $longitudeDifference = deg2rad($longitudeTo - $longitudeFrom);

        $haversine = sin($latitudeDifference / 2) ** 2
            + cos(deg2rad($latitudeFrom))
            * cos(deg2rad($latitudeTo))
            * sin($longitudeDifference / 2) ** 2;

        return $earthRadiusInKilometers * 2 * atan2(sqrt($haversine), sqrt(1 - $haversine));
    }

    /**
     * GET /api/laporan/{kode_tiket}
     *
     * Warga dapat mengecek status laporan berdasarkan kode tiket.
     */
    public function statusByTiket(string $kodeTiket): JsonResponse
    {
        $laporan = LaporanWarga::with([
            'desa',
            'jadwal.operator:id_user,nama_lengkap',
            'jadwal.inspeksiIkl',
        ])
            ->where('kode_tiket', $kodeTiket)
            ->first();

        if (! $laporan) {
            return response()->json([
                'success' => false,
                'message' => 'Kode tiket tidak ditemukan.',
            ], 404);
        }

        $jadwal = $laporan->jadwal;
        $inspeksi = $jadwal?->inspeksiIkl;

        return response()->json([
            'success' => true,
            'data' => [
                'kode_tiket' => $laporan->kode_tiket,
                'status_laporan' => $laporan->status_laporan,
                'kategori_laporan' => $laporan->kategori_laporan,
                'deskripsi' => $laporan->deskripsi,
                'desa' => $laporan->desa?->nama_desa,
                'rt' => $laporan->rt,
                'rw' => $laporan->rw,
                'created_at' => $laporan->created_at?->format('d M Y H:i'),
                'jadwal' => $jadwal ? [
                    'tanggal_kunjungan' => $jadwal->tanggal_kunjungan?->format('d M Y'),
                    'petugas' => $jadwal->operator?->nama_lengkap,
                    'status_kunjungan' => $jadwal->status_kunjungan,
                ] : null,
                'hasil_inspeksi' => $inspeksi ? [
                    'id_inspeksi' => $inspeksi->id_inspeksi,
                    'total_skor_ya' => $inspeksi->total_skor_ya,
                    'kategori_risiko' => $inspeksi->kategori_risiko,
                    'label_kategori_risiko' => $inspeksi->label_kategori_risiko,
                    'tanggal_inspeksi' => $inspeksi->tanggal_inspeksi?->toDateString(),
                    'rekomendasi_sanitarian' => $inspeksi->rekomendasi_sanitarian,
                    'intervensi_fisik' => $inspeksi->intervensi_fisik,
                ] : null,
            ],
        ]);
    }

    /**
     * GET /api/v1/laporan/track/{kode_tiket}
     */
    public function track(string $kodeTiket): JsonResponse
    {
        $laporan = LaporanWarga::with([
            'desa:id_desa,nama_desa',
            'jadwal.operator:id_user,nama_lengkap',
            'jadwal.inspeksiIkl',
        ])
            ->where('kode_tiket', $kodeTiket)
            ->first();

        if (! $laporan) {
            return response()->json([
                'success' => false,
                'message' => 'Kode tiket tidak ditemukan.',
            ], 404);
        }

        $jadwal = $laporan->jadwal;
        $hasilIkl = $jadwal?->inspeksiIkl;

        return response()->json([
            'success' => true,
            'data' => [
                'kode_tiket' => $laporan->kode_tiket,
                'status_laporan' => $laporan->status_laporan,
                'status_step' => $this->statusStep($laporan->status_laporan),
                'nama_desa' => $laporan->desa?->nama_desa,
                'rt' => $laporan->rt,
                'rw' => $laporan->rw,
                'kategori_laporan' => $laporan->kategori_laporan,
                'deskripsi' => $laporan->deskripsi,
                'created_at' => $laporan->created_at?->format('d M Y H:i'),
                'jadwal' => $jadwal ? [
                    'tanggal_kunjungan' => $jadwal->tanggal_kunjungan?->toDateString(),
                    'waktu_kunjungan' => $jadwal->jam_mulai
                        ? Carbon::parse($jadwal->jam_mulai)->format('H:i')
                        : null,
                    'status_kunjungan' => $jadwal->status_kunjungan,
                    'nama_petugas' => $jadwal->operator?->nama_lengkap,
                    'petugas' => $jadwal->operator?->nama_lengkap,
                ] : null,
                'hasil_ikl' => $hasilIkl ? [
                    'kategori_risiko' => $hasilIkl->kategori_risiko,
                    'total_skor_ya' => $hasilIkl->total_skor_ya,
                    'rekomendasi_sanitarian' => $hasilIkl->rekomendasi_sanitarian,
                    'intervensi_fisik' => $hasilIkl->intervensi_fisik,
                    'tanggal_inspeksi' => $hasilIkl->tanggal_inspeksi?->toDateString(),
                ] : null,
                'desa' => $laporan->desa?->nama_desa,
            ],
        ]);
    }

    /**
     * GET /api/v1/laporan/history
     * GET /api/laporan/history
     *
     * Mengembalikan laporan milik pengguna login dan laporan lokal yang kode tiketnya dikirim klien.
     */
    public function myReports(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tickets' => ['nullable', 'array'],
            'tickets.*' => ['string', 'max:100'],
        ]);

        $user = $this->resolveAuthenticatedMobileUser($request);
        $localTickets = collect($validated['tickets'] ?? [])
            ->filter(fn (mixed $ticket): bool => is_string($ticket) && trim($ticket) !== '')
            ->map(fn (string $ticket): string => trim($ticket))
            ->unique()
            ->values();

        $query = LaporanWarga::query()->with([
            'desa:id_desa,nama_desa',
            'jadwalInspeksi.operator:id_user,nama_lengkap',
            'jadwalInspeksi.inspeksiIkl',
        ]);

        if ($user !== null && $localTickets->isNotEmpty()) {
            $query->where(function ($builder) use ($user, $localTickets): void {
                $builder->where('id_user', $user->getKey())
                    ->orWhereIn('kode_tiket', $localTickets->all());
            });
        } elseif ($user !== null) {
            $query->where('id_user', $user->getKey());
        } elseif ($localTickets->isNotEmpty()) {
            $query->whereIn('kode_tiket', $localTickets->all());
        } else {
            $query->whereRaw('1 = 0');
        }

        $laporanList = $query
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $laporanList->map(function (LaporanWarga $laporan): array {
                $jadwal = $laporan->jadwalInspeksi;
                $hasilIkl = $jadwal?->inspeksiIkl;

                return [
                    'kode_tiket' => $laporan->kode_tiket,
                    'status_laporan' => $laporan->status_laporan,
                    'status_step' => $this->statusStep($laporan->status_laporan),
                    'nama_desa' => $laporan->desa?->nama_desa,
                    'rt' => $laporan->rt,
                    'rw' => $laporan->rw,
                    'kategori_laporan' => $laporan->kategori_laporan,
                    'deskripsi' => $laporan->deskripsi,
                    'created_at' => $laporan->created_at?->format('d M Y H:i'),
                    'jadwal' => $jadwal ? [
                        'tanggal_kunjungan' => $jadwal->tanggal_kunjungan?->toDateString(),
                        'waktu_kunjungan' => $jadwal->jam_mulai
                            ? Carbon::parse($jadwal->jam_mulai)->format('H:i')
                            : null,
                        'status_kunjungan' => $jadwal->status_kunjungan,
                        'nama_petugas' => $jadwal->operator?->nama_lengkap,
                        'petugas' => $jadwal->operator?->nama_lengkap,
                    ] : null,
                    'hasil_ikl' => $hasilIkl ? [
                        'kategori_risiko' => $hasilIkl->kategori_risiko,
                        'total_skor_ya' => $hasilIkl->total_skor_ya,
                        'rekomendasi_sanitarian' => $hasilIkl->rekomendasi_sanitarian,
                        'intervensi_fisik' => $hasilIkl->intervensi_fisik,
                        'tanggal_inspeksi' => $hasilIkl->tanggal_inspeksi?->toDateString(),
                    ] : null,
                    'desa' => $laporan->desa?->nama_desa,
                ];
            })->values(),
        ]);
    }

    private function statusStep(?string $statusLaporan): string
    {
        return match ($statusLaporan) {
            'dikirim', 'menunggu' => 'dikirim',
            'diproses', 'dibaca', 'diterima' => 'diproses',
            'dijadwalkan' => 'dijadwalkan',
            'ditindaklanjuti' => 'ditindaklanjuti',
            'selesai' => 'selesai',
            'ditolak' => 'ditolak',
            default => 'dikirim',
        };
    }
}

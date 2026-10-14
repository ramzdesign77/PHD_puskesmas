<?php

namespace App\Http\Controllers;

use App\Models\JadwalInspeksi;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Controller untuk halaman Kelola Jadwal (halaman terpisah).
 */
class ScheduleController extends Controller
{
    private const SCHEDULE_CONFLICT_MESSAGE = 'Petugas ini sudah memiliki jadwal pada tanggal dan jam tersebut. Silakan pilih jam lain atau petugas lain.';

    public function index(Request $request)
    {
        $role = session('role');

        $query = JadwalInspeksi::with(['laporan.desa', 'operator', 'inspeksiIkl'])
            ->orderBy('tanggal_kunjungan', 'asc');

        if (in_array($role, ['officer', 'petugas', 'sanitarian', 'staf_backup_kluster4'], true)) {
            $query->where('id_operator', session('user_id'));
        }

        if ($request->filled('status_kunjungan')) {
            $query->where('status_kunjungan', $request->status_kunjungan);
        }
        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal_kunjungan', $request->tanggal);
        }

        $schedules = $query->paginate(20)->withQueryString();

        $officers = User::where('is_active', true)
            ->whereIn('role', ['petugas', 'sanitarian', 'staf_backup_kluster4'])
            ->orderBy('nama_lengkap')
            ->get(['id_user', 'nama_lengkap', 'wilayah_kerja']);

        $summary = [
            'terjadwal' => JadwalInspeksi::where('status_kunjungan', 'terjadwal')->count(),
            'selesai' => JadwalInspeksi::where('status_kunjungan', 'selesai')->count(),
            'batal' => JadwalInspeksi::where('status_kunjungan', 'batal')->count(),
            'total' => JadwalInspeksi::count(),
        ];

        return view('schedules.index', compact('schedules', 'officers', 'role', 'summary'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_operator' => 'required|exists:users,id_user',
            'tanggal_kunjungan' => 'required|date|after_or_equal:today',
            'jam_mulai' => 'required|date_format:H:i',
            'jenis_kunjungan' => 'required|in:ikl_laporan_warga,ikl_rutin_rt',
        ]);

        if ($this->hasScheduleConflict($validated['id_operator'], $validated['tanggal_kunjungan'], $validated['jam_mulai'])) {
            return back()->withErrors([
                'jam_mulai' => self::SCHEDULE_CONFLICT_MESSAGE,
            ])->withInput();
        }

        JadwalInspeksi::create([
            'id_laporan' => null,
            'id_operator' => $validated['id_operator'],
            'tanggal_kunjungan' => $validated['tanggal_kunjungan'],
            'jam_mulai' => $validated['jam_mulai'],
            'jenis_kunjungan' => $validated['jenis_kunjungan'],
            'status_kunjungan' => 'terjadwal',
        ]);

        return redirect()->route('schedules.index')
            ->with('success', 'Jadwal IKL Rutin berhasil ditambahkan.');
    }

    public function assign(Request $request, int $id)
    {
        $validated = $request->validate([
            'id_operator' => 'required|exists:users,id_user',
            'tanggal_kunjungan' => 'required|date|after_or_equal:today',
            'jam_mulai' => 'required|date_format:H:i',
        ]);

        if ($this->hasScheduleConflict($validated['id_operator'], $validated['tanggal_kunjungan'], $validated['jam_mulai'], $id)) {
            return back()->withErrors([
                'jam_mulai' => self::SCHEDULE_CONFLICT_MESSAGE,
            ])->withInput();
        }

        $jadwal = JadwalInspeksi::where('status_kunjungan', 'terjadwal')
            ->findOrFail($id);

        $jadwal->update([
            'id_operator' => $validated['id_operator'],
            'tanggal_kunjungan' => $validated['tanggal_kunjungan'],
            'jam_mulai' => $validated['jam_mulai'],
        ]);

        return redirect()->route('schedules.index')
            ->with('success', 'Jadwal berhasil diperbarui.');
    }

    public function updateHistory(Request $request, int $id): RedirectResponse
    {
        $jadwal = JadwalInspeksi::whereIn('status_kunjungan', ['selesai', 'batal'])
            ->findOrFail($id);

        $validated = $request->validate([
            'id_operator' => 'required|exists:users,id_user',
            'tanggal_kunjungan' => 'required|date',
            'jam_mulai' => 'required|date_format:H:i',
        ]);

        $jadwal->update($validated);

        return redirect()->route('schedules.index')
            ->with('success', 'Informasi riwayat jadwal berhasil diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $jadwal = JadwalInspeksi::with('inspeksiIkl')->findOrFail($id);

        if ($jadwal->status_kunjungan !== 'batal' || $jadwal->inspeksiIkl !== null) {
            return redirect()->route('schedules.index')
                ->with('error', 'Hanya jadwal batal tanpa hasil IKL yang dapat dihapus.');
        }

        $jadwal->delete();

        return redirect()->route('schedules.index')
            ->with('success', 'Riwayat jadwal batal berhasil dihapus.');
    }

    public function unavailableDates(Request $request): JsonResponse
    {
        return response()->json([
            'dates' => [],
        ]);
    }

    public function batal(int $id)
    {
        $jadwal = JadwalInspeksi::with('laporan')
            ->where('status_kunjungan', 'terjadwal')
            ->findOrFail($id);

        $jadwal->update(['status_kunjungan' => 'batal']);

        if ($jadwal->laporan) {
            $jadwal->laporan->update(['status_laporan' => 'menunggu']);
        }

        return redirect()->route('schedules.index')
            ->with('success', 'Jadwal kunjungan dibatalkan. Laporan dikembalikan ke antrean.');
    }

    private function hasScheduleConflict(int|string $idOperator, string $tanggalKunjungan, ?string $jamMulai = null, ?int $exceptId = null): bool
    {
        return JadwalInspeksi::forOperatorOnDate($idOperator, $tanggalKunjungan, $jamMulai, $exceptId)->exists();
    }
}

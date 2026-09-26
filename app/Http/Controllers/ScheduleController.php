<?php

namespace App\Http\Controllers;

use App\Models\JadwalInspeksi;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Controller untuk halaman Kelola Jadwal (halaman terpisah).
 *
 * Menangani:
 *  - Kalender kerja harian
 *  - Reschedule / re-assign petugas
 *  - Pembatalan kunjungan
 *  - Agenda IKL Rutin RT
 */
class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $role = session('role');

        $query = JadwalInspeksi::with(['laporan.desa', 'operator', 'inspeksiIkl'])
            ->orderBy('tanggal_kunjungan', 'asc');

        // Petugas sanitarian hanya lihat jadwal miliknya
        if ($role === 'sanitarian' || $role === 'staf_backup_kluster4') {
            $query->where('id_operator', session('user_id'));
        }

        // Filter opsional
        if ($request->filled('status_kunjungan')) {
            $query->where('status_kunjungan', $request->status_kunjungan);
        }
        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal_kunjungan', $request->tanggal);
        }

        $schedules = $query->paginate(20)->withQueryString();

        $officers = User::where('is_active', true)
            ->whereIn('role', ['sanitarian', 'staf_backup_kluster4'])
            ->orderBy('nama_lengkap')
            ->get(['id_user', 'nama_lengkap', 'wilayah_kerja']);

        // Summary
        $summary = [
            'terjadwal' => JadwalInspeksi::where('status_kunjungan', 'terjadwal')->count(),
            'selesai' => JadwalInspeksi::where('status_kunjungan', 'selesai')->count(),
            'batal' => JadwalInspeksi::where('status_kunjungan', 'batal')->count(),
            'total' => JadwalInspeksi::count(),
        ];

        return view('schedules.index', compact('schedules', 'officers', 'role', 'summary'));
    }

    /**
     * POST /schedules
     *
     * Tambah jadwal IKL Rutin RT (tanpa laporan warga).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_operator' => 'required|exists:users,id_user',
            'tanggal_kunjungan' => 'required|date|after_or_equal:today',
            'jenis_kunjungan' => 'required|in:ikl_laporan_warga,ikl_rutin_rt',
        ]);

        JadwalInspeksi::create([
            'id_laporan' => null, // IKL Rutin tidak terkait laporan warga
            'id_operator' => $validated['id_operator'],
            'tanggal_kunjungan' => $validated['tanggal_kunjungan'],
            'jenis_kunjungan' => $validated['jenis_kunjungan'],
            'status_kunjungan' => 'terjadwal',
        ]);

        return redirect()->route('schedules.index')
            ->with('success', 'Jadwal IKL Rutin berhasil ditambahkan.');
    }

    /**
     * POST /schedules/{id}/assign
     *
     * Re-assign petugas atau reschedule tanggal kunjungan.
     */
    public function assign(Request $request, int $id)
    {
        $validated = $request->validate([
            'id_operator' => 'required|exists:users,id_user',
            'tanggal_kunjungan' => 'required|date|after_or_equal:today',
        ]);

        $jadwal = JadwalInspeksi::where('status_kunjungan', 'terjadwal')
            ->findOrFail($id);

        $jadwal->update([
            'id_operator' => $validated['id_operator'],
            'tanggal_kunjungan' => $validated['tanggal_kunjungan'],
        ]);

        return redirect()->route('schedules.index')
            ->with('success', 'Jadwal berhasil diperbarui.');
    }

    /**
     * POST /schedules/{id}/batal
     *
     * Batalkan jadwal kunjungan. Jika berasal dari laporan warga,
     * status laporan dikembalikan ke 'menunggu' agar bisa dijadwalkan ulang.
     */
    public function batal(int $id)
    {
        $jadwal = JadwalInspeksi::with('laporan')
            ->where('status_kunjungan', 'terjadwal')
            ->findOrFail($id);

        $jadwal->update(['status_kunjungan' => 'batal']);

        // Kembalikan status laporan ke 'menunggu' agar bisa dijadwal ulang
        if ($jadwal->laporan) {
            $jadwal->laporan->update(['status_laporan' => 'menunggu']);
        }

        return redirect()->route('schedules.index')
            ->with('success', 'Jadwal kunjungan dibatalkan. Laporan dikembalikan ke antrean.');
    }
}

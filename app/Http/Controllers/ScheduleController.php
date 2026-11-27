<?php

namespace App\Http\Controllers;

use App\Models\Desa;
use App\Models\JadwalInspeksi;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller untuk halaman Kelola Jadwal (halaman terpisah).
 */
class ScheduleController extends Controller
{
    private const SCHEDULE_CONFLICT_MESSAGE = 'Petugas ini sudah memiliki jadwal pada tanggal dan jam tersebut. Silakan pilih jam lain atau petugas lain.';

    public function index(Request $request): View
    {
        $role = session('role');
        $allowedPerPage = [20, 50, 80, 100];
        $requestedPerPage = (int) $request->query('per_page', 20);
        $perPage = in_array($requestedPerPage, $allowedPerPage, true) ? $requestedPerPage : 20;

        $query = JadwalInspeksi::with(['laporan.desa', 'operator', 'inspeksiIkl'])
            ->orderBy('tanggal_kunjungan', 'asc');

        if (in_array($role, ['officer', 'petugas', 'sanitarian', 'staf_backup_kluster4'], true)) {
            $query->where('id_operator', session('user_id'));
        }

        // Search
        $query->when($request->filled('q'), fn ($q) => $q->where(fn ($sub) => $sub
            ->whereHas('laporan', fn ($l) => $l
                ->where('kode_tiket', 'like', "%{$request->q}%")
                ->orWhere('nama_pelapor', 'like', "%{$request->q}%"))
            ->orWhereHas('operator', fn ($u) => $u->where('nama_lengkap', 'like', "%{$request->q}%"))
            ->orWhere('catatan', 'like', "%{$request->q}%")));

        // Status — mendukung param lama (status_kunjungan) dan baru (status)
        $statusFilter = $request->filled('status_kunjungan') ? $request->status_kunjungan : $request->status;
        $query->when($statusFilter, fn ($q) => $q->where('status_kunjungan', $statusFilter));

        // Petugas
        $query->when($request->filled('id_operator'), fn ($q) => $q->where('id_operator', $request->id_operator));

        // Quick period
        $query->when($request->filled('range'), function ($q) use ($request) {
            match ($request->range) {
                'today' => $q->whereDate('tanggal_kunjungan', today()),
                'this_week' => $q->whereBetween('tanggal_kunjungan', [now()->startOfWeek(), now()->endOfWeek()]),
                'this_month' => $q->whereMonth('tanggal_kunjungan', now()->month)->whereYear('tanggal_kunjungan', now()->year),
                default => null,
            };
        });

        // Filter tanggal tunggal lama (backward compat)
        $query->when($request->filled('tanggal') && ! $request->filled('date_from'),
            fn ($q) => $q->whereDate('tanggal_kunjungan', $request->tanggal));

        // Date range baru
        $query->when($request->filled('date_from'), fn ($q) => $q->whereDate('tanggal_kunjungan', '>=', $request->date_from));
        $query->when($request->filled('date_to'), fn ($q) => $q->whereDate('tanggal_kunjungan', '<=', $request->date_to));

        // Desa (via relasi laporan)
        $query->when($request->filled('id_desa'), fn ($q) => $q->whereHas('laporan', fn ($l) => $l->where('id_desa', $request->id_desa)));

        // Jenis kunjungan
        $query->when($request->filled('jenis'), fn ($q) => $q->where('jenis_kunjungan', $request->jenis));

        $schedules = $query->paginate($perPage)->withQueryString();

        $officers = User::where('is_active', true)
            ->sanitarian()
            ->orderBy('nama_lengkap')
            ->get(['id_user', 'nama_lengkap', 'wilayah_kerja']);

        $desaList = Desa::orderBy('nama_desa')->get(['id_desa', 'nama_desa']);

        $summary = [
            'terjadwal' => JadwalInspeksi::where('status_kunjungan', 'terjadwal')->count(),
            'selesai' => JadwalInspeksi::where('status_kunjungan', 'selesai')->count(),
            'batal' => JadwalInspeksi::where('status_kunjungan', 'batal')->count(),
            'total' => JadwalInspeksi::count(),
        ];

        return view('schedules.index', compact('schedules', 'officers', 'desaList', 'role', 'summary'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id_operator' => 'required|string|max:30|exists:app_users,id_user',
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
            ->with('success', 'Jadwal berhasil ditambahkan.');
    }

    public function assign(Request $request, string $id): RedirectResponse
    {
        $validated = $request->validate([
            'id_operator' => 'required|string|max:30|exists:app_users,id_user',
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

    public function updateHistory(Request $request, string $id): RedirectResponse
    {
        $jadwal = JadwalInspeksi::whereIn('status_kunjungan', ['selesai', 'batal'])
            ->findOrFail($id);

        $validated = $request->validate([
            'id_operator' => 'required|string|max:30|exists:app_users,id_user',
            'tanggal_kunjungan' => 'required|date',
            'jam_mulai' => 'required|date_format:H:i',
        ]);

        $jadwal->update($validated);

        return redirect()->route('schedules.index')
            ->with('success', 'Informasi riwayat jadwal berhasil diperbarui.');
    }

    public function destroy(string $id): RedirectResponse
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

    public function batal(string $id): RedirectResponse
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

    private function hasScheduleConflict(string $idOperator, string $tanggalKunjungan, ?string $jamMulai = null, ?string $exceptId = null): bool
    {
        return JadwalInspeksi::forOperatorOnDate($idOperator, $tanggalKunjungan, $jamMulai, $exceptId)->exists();
    }
}

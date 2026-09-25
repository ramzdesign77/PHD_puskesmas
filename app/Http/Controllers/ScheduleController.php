<?php

namespace App\Http\Controllers;

use App\Models\Desa;
use App\Models\JadwalInspeksi;
use App\Models\LaporanWarga;
use App\Models\Petugas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(): View
    {
        $role = session('role');
        $scheduleQuery = JadwalInspeksi::with(['laporan.desa', 'petugas'])
            ->whereHas('laporan')
            ->latest('id_jadwal');

        if ($role === 'officer') {
            $scheduleQuery->whereHas('petugas', function ($query): void {
                $query->where('nama_petugas', session('user_name'));
            });
        }

        $schedules = $scheduleQuery->get()
            ->map(fn (JadwalInspeksi $schedule): array => $this->scheduleData($schedule))
            ->all();

        $officers = Petugas::query()
            ->where('is_active', true)
            ->orderBy('nama_petugas')
            ->get()
            ->map(fn (Petugas $officer): array => [
                'id' => $officer->getKey(),
                'name' => $officer->nama_petugas,
                'area' => $officer->jabatan ?? 'Petugas Kesling',
            ])
            ->all();

        $villages = Desa::query()->orderBy('nama_desa')->get();

        return view('schedules.index', compact('schedules', 'officers', 'villages', 'role'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(session('role') === 'citizen', 403);

        $validated = $request->validate([
            'report_ref' => ['nullable', 'string', 'max:20'],
            'complaint' => ['required', 'string', 'min:5'],
            'location' => ['required', 'string', 'max:255'],
            'id_desa' => ['required', 'integer', 'exists:desa,id_desa'],
            'rt' => ['required', 'string', 'max:5'],
            'rw' => ['required', 'string', 'max:5'],
            'preferred' => ['required', 'date', 'after_or_equal:now'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated): void {
            $preferred = now()->parse($validated['preferred']);
            $report = LaporanWarga::create([
                'kode_tiket' => $validated['report_ref'] ?: 'TCK-'.strtoupper(Str::random(12)),
                'nama_pelapor' => session('user_name', 'Anonim'),
                'nik_pelapor' => null,
                'no_wa' => null,
                'id_desa' => $validated['id_desa'],
                'rt' => $validated['rt'],
                'rw' => $validated['rw'],
                'kategori_laporan' => 'sanitasi_lingkungan',
                'deskripsi' => $validated['complaint'].' | Alamat: '.$validated['location'],
                'status_laporan' => 'menunggu',
            ]);

            JadwalInspeksi::create([
                'id_laporan' => $report->getKey(),
                'id_operator' => null,
                'tanggal_kunjungan' => $preferred->toDateString(),
                'waktu_kunjungan' => $preferred->format('H:i:s'),
                'jenis_kunjungan' => 'ikl_laporan_warga',
                'status_kunjungan' => 'terjadwal',
                'catatan' => $validated['notes'] ?? null,
            ]);
        });

        return redirect()->route('schedules.index')
            ->with('success', 'Permintaan kunjungan berhasil dikirim! Admin akan menentukan petugas dan waktu kunjungan.');
    }

    public function assign(Request $request, int $id): RedirectResponse
    {
        abort_unless(session('role') === 'admin', 403);

        $validated = $request->validate([
            'officer_id' => ['required', 'integer', 'exists:petugas,id_petugas'],
            'assigned_date' => ['required', 'date'],
            'assigned_time' => ['required', 'date_format:H:i'],
        ]);

        $schedule = JadwalInspeksi::with('laporan')->findOrFail($id);

        DB::transaction(function () use ($schedule, $validated): void {
            $schedule->update([
                'id_operator' => $validated['officer_id'],
                'tanggal_kunjungan' => $validated['assigned_date'],
                'waktu_kunjungan' => $validated['assigned_time'],
                'status_kunjungan' => 'terjadwal',
            ]);

            $schedule->laporan?->update(['status_laporan' => 'dijadwalkan']);
        });

        return redirect()->route('schedules.index')
            ->with('success', "Jadwal {$id} berhasil ditetapkan.");
    }

    /**
     * Keep the view contract independent from the database column names.
     *
     * @return array<string, mixed>
     */
    private function scheduleData(JadwalInspeksi $schedule): array
    {
        $report = $schedule->laporan;
        $location = collect([
            $report?->desa?->nama_desa,
            $report?->rt ? 'RT '.$report->rt : null,
            $report?->rw ? 'RW '.$report->rw : null,
        ])->filter()->implode(', ');

        return [
            'id' => 'SCH-'.str_pad((string) $schedule->getKey(), 3, '0', STR_PAD_LEFT),
            'schedule_id' => $schedule->getKey(),
            'citizen' => $report?->nama_pelapor ?? 'Tanpa nama',
            'report_ref' => $report?->kode_tiket,
            'complaint' => $report?->deskripsi ?? '-',
            'location' => $location ?: 'Alamat belum tersedia',
            'preferred' => $schedule->tanggal_kunjungan.' '.$schedule->waktu_kunjungan,
            'officer' => $schedule->petugas?->nama_petugas,
            'officer_id' => $schedule->id_operator,
            'assigned_date' => $schedule->id_operator ? $schedule->tanggal_kunjungan : null,
            'assigned_time' => $schedule->id_operator ? substr((string) $schedule->waktu_kunjungan, 0, 5) : null,
            'status' => $schedule->id_operator ? 'assigned' : 'pending',
            'notes' => $schedule->catatan,
            'assign_url' => route('schedules.assign', $schedule->getKey()),
        ];
    }
}

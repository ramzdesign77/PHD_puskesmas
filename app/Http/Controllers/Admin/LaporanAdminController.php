<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Desa;
use App\Models\JadwalInspeksi;
use App\Models\LaporanWarga;
use App\Models\User;
use Illuminate\Http\Request;

class LaporanAdminController extends Controller
{
    /**
     * READ: daftar semua laporan warga (bisa difilter status)
     */
    public function index(Request $request)
    {
        $allowedPerPage = [20, 50, 80, 100];
        $requestedPerPage = (int) $request->query('per_page', 20);
        $perPage = in_array($requestedPerPage, $allowedPerPage, true) ? $requestedPerPage : 20;

        $query = LaporanWarga::query()->with('desa')
            ->when($request->filled('q'), fn ($query) => $query->where(fn ($search) => $search
                ->where('kode_tiket', 'like', "%{$request->q}%")
                ->orWhere('nama_pelapor', 'like', "%{$request->q}%")
                ->orWhere('no_wa', 'like', "%{$request->q}%")
                ->orWhere('deskripsi', 'like', "%{$request->q}%")))
            ->when($request->filled('status') || $request->filled('status_laporan'), fn ($query) => $query->where(
                'status_laporan',
                $request->filled('status') ? $request->status : $request->status_laporan
            ))
            ->when($request->filled('kategori'), fn ($query) => $query->where('kategori_laporan', $request->kategori))
            ->when($request->filled('id_desa'), fn ($query) => $query->where('id_desa', $request->id_desa))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('created_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('created_at', '<=', $request->date_to))
            ->when($request->get('sort') === 'terlama',
                fn ($query) => $query->reorder('created_at', 'asc'),
                fn ($query) => $query->orderBy('created_at', 'desc'));

        $laporan = $query->paginate($perPage)->withQueryString();
        $desaList = Desa::orderBy('nama_desa')->get(['id_desa', 'nama_desa']);

        return view('admin.laporan.index', compact('laporan', 'desaList'));
    }

    /**
     * READ detail: menampilkan isi laporan + otomatis ubah status jadi "dibaca"
     * kalau statusnya masih "menunggu"
     */
    public function show($id_laporan)
    {
        $laporan = LaporanWarga::with(['desa', 'jadwal.petugas'])
            ->findOrFail($id_laporan);

        if ($laporan->status_laporan === 'menunggu') {
            $laporan->status_laporan = 'dibaca';
            $laporan->save();
        }

        $petugas = User::query()
            ->sanitarian()
            ->where('is_active', true)
            ->orderBy('nama_lengkap')
            ->get(['id_user', 'nama_lengkap', 'jabatan']);

        return view('admin.laporan.show', compact('laporan', 'petugas'));
    }

    /**
     * ACTION: tolak laporan (wajib isi alasan)
     */
    public function tolak(Request $request, $id_laporan)
    {
        $validated = $request->validate([
            'alasan_penolakan' => 'required|string|min:5',
        ]);

        $laporan = LaporanWarga::findOrFail($id_laporan);
        $laporan->status_laporan = 'ditolak';
        $laporan->alasan_penolakan = $validated['alasan_penolakan'];
        $laporan->save();

        return redirect()->route('admin.laporan.index')
            ->with('success', 'Laporan telah ditolak.');
    }

    /**
     * ACTION: terima laporan + buat jadwal kunjungan sekaligus
     * (pilih petugas, tanggal, dan jenis kunjungan)
     */
    public function terima(Request $request, $id_laporan)
    {
        $validated = $request->validate([
            'id_petugas' => 'required|string|max:30|exists:app_users,id_user',
            'tanggal_kunjungan' => 'required|date|after_or_equal:today',
            'jenis_kunjungan' => 'required|string',
        ]);

        $laporan = LaporanWarga::findOrFail($id_laporan);
        $petugas = User::query()
            ->sanitarian()
            ->where('is_active', true)
            ->whereKey($validated['id_petugas'])
            ->first();

        if ($petugas === null) {
            return back()->withErrors(['id_petugas' => 'Pilih akun sanitarian yang aktif.'])->withInput();
        }

        $laporan->status_laporan = 'diterima';
        $laporan->alasan_penolakan = null;
        $laporan->save();

        JadwalInspeksi::create([
            'id_laporan' => $laporan->id_laporan,
            'id_operator' => $petugas->id_user,
            'tanggal_kunjungan' => $validated['tanggal_kunjungan'],
            'jenis_kunjungan' => $validated['jenis_kunjungan'],
            'status_kunjungan' => 'terjadwal',
        ]);

        return redirect()->route('admin.laporan.index')
            ->with('success', 'Laporan diterima & jadwal kunjungan berhasil dibuat.');
    }
}

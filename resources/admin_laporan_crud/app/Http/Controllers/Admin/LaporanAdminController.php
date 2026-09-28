<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JadwalInspeksi;
use App\Models\LaporanWarga;
use App\Models\PaketAlat;
use App\Models\Petugas;
use Illuminate\Http\Request;

class LaporanAdminController extends Controller
{
    /**
     * READ: daftar semua laporan warga (bisa difilter status)
     */
    public function index(Request $request)
    {
        $query = LaporanWarga::query()->with('desa');

        if ($request->filled('status_laporan')) {
            $query->where('status_laporan', $request->status_laporan);
        }

        $laporan = $query->orderBy('id_laporan', 'desc')->paginate(10);

        return view('admin.laporan.index', compact('laporan'));
    }

    /**
     * READ detail: menampilkan isi laporan + otomatis ubah status jadi "dibaca"
     * kalau statusnya masih "menunggu"
     */
    public function show($id_laporan)
    {
        $laporan = LaporanWarga::with(['desa', 'jadwal.petugas', 'jadwal.paketAlat'])
            ->findOrFail($id_laporan);

        if ($laporan->status_laporan === 'menunggu') {
            $laporan->status_laporan = 'dibaca';
            $laporan->save();
        }

        $petugas = Petugas::where('is_active', 1)->orderBy('nama_petugas')->get();

        $paketAlat = PaketAlat::where('kategori_laporan', $laporan->kategori_laporan)
            ->orWhere('kategori_laporan', 'Lainnya')
            ->orderBy('nama_paket')
            ->get();

        return view('admin.laporan.show', compact('laporan', 'petugas', 'paketAlat'));
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
     * (pilih petugas & paket alat tinggal klik dari dropdown)
     */
    public function terima(Request $request, $id_laporan)
    {
        $validated = $request->validate([
            'id_petugas'        => 'required|exists:petugas,id_petugas',
            'id_paket'          => 'required|exists:paket_alat,id_paket',
            'tanggal_kunjungan' => 'required|date|after_or_equal:today',
            'jenis_kunjungan'   => 'required|string',
        ]);

        $laporan = LaporanWarga::findOrFail($id_laporan);
        $laporan->status_laporan = 'diterima';
        $laporan->alasan_penolakan = null;
        $laporan->save();

        JadwalInspeksi::create([
            'id_laporan'        => $laporan->id_laporan,
            'id_operator'       => $validated['id_petugas'],
            'id_paket'          => $validated['id_paket'],
            'tanggal_kunjungan' => $validated['tanggal_kunjungan'],
            'jenis_kunjungan'   => $validated['jenis_kunjungan'],
            'status_kunjungan'  => 'terjadwal',
        ]);

        return redirect()->route('admin.laporan.index')
            ->with('success', 'Laporan diterima & jadwal kunjungan berhasil dibuat.');
    }
}

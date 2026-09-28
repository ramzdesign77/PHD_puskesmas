namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaporanWarga extends Model
{
    protected $table = 'laporan_warga';
    protected $primaryKey = 'id_laporan';

    // Matikan updated_at karena di tabel hanya ada created_at
    public $timestamps = false;

    protected $fillable = [
        'kode_tiket',
        'nama_pelapor',
        'nik_pelapor',
        'no_wa',
        'id_desa',
        'rt',
        'rw',
        'kategori_laporan',
        'deskripsi',
        'foto_bukti',
        'status_laporan',
        'alasan_penolakan',
    ];

    /**
     * READ: Menampilkan & menyaring (filter) daftar laporan aduan
     */
    public function index(Request $request)
    {
        $query = LaporanWarga::query();

        // 1. Filter berdasarkan status_laporan (menunggu, dijadwalkan, selesai, ditolak)
        if ($request->has('status_laporan')) {
            $query->where('status_laporan', $request->status_laporan);
        }

        // 2. Filter berdasarkan kategori_laporan (air_masalah, sanitasi_lingkungan)
        if ($request->has('kategori_laporan')) {
            $query->where('kategori_laporan', $request->kategori_laporan);
        }

        // 3. Filter berdasarkan desa tertentu jika diperlukan
        if ($request->has('id_desa')) {
            $query->where('id_desa', $request->id_desa);
        }

        // Ambil data diurutkan dari yang terbaru
        $laporan = $query->orderBy('id_laporan', 'desc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar laporan aduan berhasil dimuat',
            'data'    => $laporan
        ], 200);
    }

    /**
     * UPDATE & REJECT: Memperbarui status laporan atau menolak laporan
     */
    public function updateStatus(Request $request, $id_laporan)
    {
        // 1. Cari data berdasarkan id_laporan
        $laporan = LaporanWarga::where('id_laporan', $id_laporan)->first();

        if (!$laporan) {
            return response()->json([
                'success' => false,
                'message' => 'Laporan tidak ditemukan'
            ], 404);
        }

        // 2. Validasi input status baru
        $validated = $request->validate([
            'status_laporan'   => 'required|in:menunggu,dijadwalkan,selesai,ditolak',
            'alasan_penolakan' => 'nullable|string',
        ]);

        // 3. Jika status diubah menjadi 'ditolak' (untuk spam/duplikat/palsu), pastikan alasan diisi
        if ($validated['status_laporan'] === 'ditolak' && empty($validated['alasan_penolakan'])) {
            return response()->json([
                'success' => false,
                'message' => 'Alasan penolakan wajib diisi jika laporan ditolak!'
            ], 422);
        }

        // 4. Update data ke database
        $laporan->status_laporan = $validated['status_laporan'];

        // Jika tidak ditolak, kosongkan kembali alasan penolakannya
        $laporan->alasan_penolakan = ($validated['status_laporan'] === 'ditolak')
            ? $validated['alasan_penolakan']
            : null;

        $laporan->save();

        return response()->json([
            'success' => true,
            'message' => 'Status laporan berhasil diperbarui!',
            'data'    => $laporan
        ], 200);
    }
}



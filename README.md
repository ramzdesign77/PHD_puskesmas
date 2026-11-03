# PHD_puskesmas

Aplikasi Klinik Sanitasi Puskesmas untuk pengelolaan laporan warga, jadwal
kunjungan, inspeksi kesehatan lingkungan (IKL), dan layanan mobile petugas.

## Integrasi inspeksi IKL pada aplikasi mobile

Aturan risiko IKL dihitung di server berdasarkan lima jawaban checklist:

- `0` jawaban Ya: Aman
- `1–2` jawaban Ya: Sedang
- `3–5` jawaban Ya: Tinggi

### Menyiapkan perubahan pada aplikasi yang sudah berjalan

Pastikan migrasi inti sudah diterapkan dan tabel `app_users`, `app_mobile_tokens`,
`trx_laporan_warga`, `trx_jadwal_inspeksi`, serta `trx_inspeksi_ikl` tersedia.
Migrasi tambahan menyimpan catatan petugas dan foto bukti inspeksi.

```sh
php artisan migrate --path=database/migrations/2026_11_02_230000_add_mobile_details_to_inspeksi_ikl_table.php
php artisan optimize:clear
php artisan route:list --path=api/v1/officer
php artisan serve --host=0.0.0.0 --port=8000
```

Hubungkan ponsel dan server pada jaringan lokal yang sama. Login petugas melalui
`POST /api/auth/login`, lalu kirim token yang diterima sebagai
`Authorization: Bearer {token}`. Endpoint inspeksi hanya menerima akun petugas
aktif dan jadwal yang ditugaskan kepada petugas tersebut dengan status
`terjadwal`.

### Mengirim hasil inspeksi

Kirim `POST /api/v1/officer/inspections` dengan semua jawaban checklist:

```json
{
  "id_jadwal": 123,
  "jenis_sarana_air": "sumur_terlindung",
  "jarak_sumber_pencemar": 12,
  "tanggal_inspeksi": "2026-11-02",
  "p1_dinding_sumur_retak": true,
  "p2_penutup_tidak_rapat": false,
  "p3_lantai_becek_retak": true,
  "p4_spal_tersumbat": false,
  "p5_air_keruh_berbau": false,
  "rekomendasi_sanitarian": "Perbaiki lantai dan dinding sumur.",
  "intervensi_fisik": "Tidak Ada",
  "catatan_intervensi": "",
  "catatan_petugas": ""
}
```

Jenis sarana yang diterima: `sumur_bor`, `sumur_terlindung`, `pdam`,
`mata_air`, atau `lainnya`. Jarak sumber pencemar berupa integer 0–99999;
rekomendasi 5–2000 karakter. Tanggal inspeksi tidak boleh melewati hari ini.
Skor dan kategori selalu dihitung dari checklist oleh API, bukan dari nilai
yang dikirim klien.

Foto bersifat opsional. Jika dikirim, gunakan `multipart/form-data` dengan nama
field `foto_bukti`, nilai boolean `1` atau `0`, dan ukuran foto maksimum 5 MB.
Foto disimpan di disk privat `local`. Catatan petugas, catatan intervensi, dan
foto tidak ditampilkan pada endpoint pelacakan laporan publik.

Respons `201` berisi `data.id_inspeksi`, `id_jadwal`, `total_skor_ya`,
`kategori_risiko`, `label_kategori_risiko`, `tanggal_inspeksi`,
`rekomendasi_sanitarian`, `intervensi_fisik`, `status_kunjungan`, dan
`status_laporan`. Status kunjungan menjadi `selesai`; `status_laporan` bernilai
`null` untuk jadwal rutin yang tidak berasal dari laporan warga. Pengiriman
ulang hasil untuk jadwal yang sama ditolak dengan HTTP `409`.

### Melacak laporan dan mengambil jadwal petugas

`GET /api/laporan/{kode_tiket}` menyertakan `data.hasil_inspeksi`: `null` bila
jadwal terbaru belum memiliki hasil, atau ringkasan hasil inspeksi final.
Catatan internal dan foto tidak disertakan. Hasil diambil dari jadwal terbaru
agar jadwal pengganti tidak tertutup oleh jadwal lama yang batal.

`GET /api/v1/officer/tasks` dan `GET /api/v1/officer/dashboard` menyertakan
`id_jadwal` pada data jadwal sehingga aplikasi mobile dapat mengirim hasil ke
jadwal yang benar. Pertahankan route dan implementasi yang sudah ada bila
menggabungkan perubahan ini ke versi proyek yang lebih baru; jangan
mendefinisikan route inspeksi yang sama dua kali.

### Pemeriksaan

Jalankan tes alur inspeksi dengan database pengujian:

```sh
php artisan test --filter=InspectionFlowTest
```

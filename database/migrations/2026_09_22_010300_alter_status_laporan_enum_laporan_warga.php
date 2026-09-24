<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // Menambah pilihan status baru: dibaca & diterima, di samping yang sudah ada.
    // Kalau kolom status_laporan sebelumnya bukan enum (misal varchar), migration
    // ini tetap aman dijalankan (akan mengubahnya jadi enum dengan pilihan lengkap).
    public function up(): void
    {
        DB::statement("ALTER TABLE laporan_warga MODIFY status_laporan
            ENUM('menunggu','dibaca','diterima','ditolak','dijadwalkan','selesai')
            NOT NULL DEFAULT 'menunggu'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE laporan_warga MODIFY status_laporan
            ENUM('menunggu','dijadwalkan','selesai','ditolak')
            NOT NULL DEFAULT 'menunggu'");
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('trx_laporan_warga')) {
            return;
        }

        Schema::table('trx_laporan_warga', function (Blueprint $table): void {
            if (Schema::hasColumn('trx_laporan_warga', 'nik_pelapor')) {
                $table->string('nik_pelapor', 16)->nullable()->change();
            }

            if (Schema::hasColumn('trx_laporan_warga', 'status_laporan')) {
                $table->string('status_laporan', 30)->nullable()->default('menunggu')->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('trx_laporan_warga')) {
            return;
        }

        if (Schema::hasColumn('trx_laporan_warga', 'status_laporan')) {
            DB::table('trx_laporan_warga')
                ->where('status_laporan', 'dikirim')
                ->update(['status_laporan' => 'menunggu']);
            DB::table('trx_laporan_warga')
                ->where('status_laporan', 'diproses')
                ->update(['status_laporan' => 'dibaca']);
            DB::table('trx_laporan_warga')
                ->where('status_laporan', 'ditindaklanjuti')
                ->update(['status_laporan' => 'dijadwalkan']);
        }

        Schema::table('trx_laporan_warga', function (Blueprint $table): void {
            if (Schema::hasColumn('trx_laporan_warga', 'nik_pelapor')) {
                $table->string('nik_pelapor', 16)->nullable()->change();
            }

            if (Schema::hasColumn('trx_laporan_warga', 'status_laporan')) {
                $table->enum('status_laporan', [
                    'menunggu',
                    'dibaca',
                    'diterima',
                    'dijadwalkan',
                    'selesai',
                    'ditolak',
                ])->nullable()->default('menunggu')->change();
            }
        });
    }
};

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
        if (Schema::hasTable('reports') && DB::table('reports')->exists()) {
            throw new RuntimeException(
                'The reports table contains data. Migrate or archive those records before dropping it.'
            );
        }

        $tables = [
            'desa' => 'ref_desa',
            'paket_alat' => 'ref_paket_alat',
            'educations' => 'ref_edukasi',
            'users' => 'app_users',
            'petugas' => 'app_petugas',
            'mobile_access_tokens' => 'app_mobile_tokens',
            'laporan_warga' => 'trx_laporan_warga',
            'jadwal_inspeksi' => 'trx_jadwal_inspeksi',
            'inspeksi_ikl' => 'trx_inspeksi_ikl',
        ];

        foreach ($tables as $oldName => $newName) {
            if (Schema::hasTable($oldName) && Schema::hasTable($newName)) {
                throw new RuntimeException(
                    "Both {$oldName} and {$newName} exist; resolve the table-name conflict before migrating."
                );
            }
        }

        Schema::disableForeignKeyConstraints();

        try {
            Schema::dropIfExists('reports');

            foreach ($tables as $oldName => $newName) {
                if (Schema::hasTable($oldName)) {
                    Schema::rename($oldName, $newName);
                }
            }
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'ref_desa' => 'desa',
            'ref_paket_alat' => 'paket_alat',
            'ref_edukasi' => 'educations',
            'app_users' => 'users',
            'app_petugas' => 'petugas',
            'app_mobile_tokens' => 'mobile_access_tokens',
            'trx_laporan_warga' => 'laporan_warga',
            'trx_jadwal_inspeksi' => 'jadwal_inspeksi',
            'trx_inspeksi_ikl' => 'inspeksi_ikl',
        ];

        foreach ($tables as $newName => $oldName) {
            if (Schema::hasTable($newName) && Schema::hasTable($oldName)) {
                throw new RuntimeException(
                    "Both {$newName} and {$oldName} exist; resolve the table-name conflict before rolling back."
                );
            }
        }

        Schema::disableForeignKeyConstraints();

        try {
            foreach ($tables as $newName => $oldName) {
                if (Schema::hasTable($newName)) {
                    Schema::rename($newName, $oldName);
                }
            }

            if (! Schema::hasTable('reports')) {
                Schema::create('reports', function (Blueprint $table): void {
                    $table->id();
                    $table->string('nama_lengkap');
                    $table->string('nik', 16);
                    $table->string('nomor_telepon');
                    $table->integer('village_id');
                    $table->string('rt');
                    $table->string('rw');
                    $table->enum('kategori_laporan', ['Masalah air', 'Jamban/Sanitasi', 'Lainnya']);
                    $table->text('deskripsi_masalah');
                    $table->string('foto_bukti')->nullable();
                    $table->enum('status', ['Pendataan/Pending', 'Ditinjau', 'Diproses', 'Selesai'])
                        ->default('Pendataan/Pending');
                    $table->timestamps();

                    $table->foreign('village_id')
                        ->references('id_desa')
                        ->on('desa')
                        ->restrictOnDelete();
                });
            }
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }
};

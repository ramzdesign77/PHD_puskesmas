<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Memisahkan kolom nip_nik menjadi dua kolom terpisah: nip & nik.
 * Ini memperjelas profil Petugas Sanitarian sesuai arsitektur baru.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Tambah nip dan nik setelah nama_lengkap (kolom yang pasti ada)
            $table->string('nip', 30)->nullable()->after('nama_lengkap')->comment('Nomor Induk Pegawai');
            $table->char('nik', 16)->nullable()->after('nip')->comment('Nomor Induk Kependudukan');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nip', 'nik']);
        });
    }
};

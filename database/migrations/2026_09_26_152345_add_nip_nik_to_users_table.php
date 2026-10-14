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
        if (! Schema::hasColumn('users', 'nip')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('nip', 30)->nullable()->comment('Nomor Induk Pegawai');
            });
        }

        if (! Schema::hasColumn('users', 'nik')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->char('nik', 16)->nullable()->comment('Nomor Induk Kependudukan');
            });
        }
    }

    public function down(): void
    {
        $columnsToDrop = array_values(array_filter(
            ['nip', 'nik'],
            fn (string $column): bool => Schema::hasColumn('users', $column),
        ));

        if ($columnsToDrop !== []) {
            Schema::table('users', function (Blueprint $table) use ($columnsToDrop): void {
                $table->dropColumn($columnsToDrop);
            });
        }
    }
};

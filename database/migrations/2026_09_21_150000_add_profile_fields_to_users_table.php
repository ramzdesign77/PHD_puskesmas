<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nip_nik', 30)->nullable()->after('nama_lengkap');
            $table->string('email', 150)->nullable()->after('username');
            $table->string('wilayah_kerja', 150)->nullable()->after('role');
            $table->text('alamat')->nullable()->after('wilayah_kerja');
            $table->string('foto')->nullable()->after('alamat');
            $table->softDeletes();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 50)->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nip_nik', 'email', 'wilayah_kerja', 'alamat', 'foto', 'deleted_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['sanitarian', 'staf_backup_kluster4', 'kepala_puskesmas', 'admin'])->change();
        });
    }
};

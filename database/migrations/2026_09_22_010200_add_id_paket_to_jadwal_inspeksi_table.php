<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwal_inspeksi', function (Blueprint $table) {
            // id_operator sudah ada di tabel ini -> kita pakai sebagai referensi ke petugas.id_petugas
            $table->unsignedBigInteger('id_paket')->nullable()->after('id_operator');
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_inspeksi', function (Blueprint $table) {
            $table->dropColumn('id_paket');
        });
    }
};

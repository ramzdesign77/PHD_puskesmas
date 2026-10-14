<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('jadwal_inspeksi', 'id_paket')) {
            Schema::table('jadwal_inspeksi', function (Blueprint $table): void {
                $table->unsignedBigInteger('id_paket')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('jadwal_inspeksi', 'id_paket')) {
            Schema::table('jadwal_inspeksi', function (Blueprint $table): void {
                $table->dropColumn('id_paket');
            });
        }
    }
};

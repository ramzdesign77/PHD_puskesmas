<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasForeignKey('jadwal_inspeksi', ['id_laporan'])) {
            Schema::table('jadwal_inspeksi', function (Blueprint $table): void {
                $table->foreign('id_laporan', 'jadwal_inspeksi_laporan_fk')
                    ->references('id_laporan')
                    ->on('laporan_warga')
                    ->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $foreignKey = collect(Schema::getForeignKeys('jadwal_inspeksi'))
            ->first(fn (array $foreignKey): bool => $foreignKey['columns'] === ['id_laporan']);

        if ($foreignKey !== null) {
            Schema::table('jadwal_inspeksi', function (Blueprint $table) use ($foreignKey): void {
                $table->dropForeign($foreignKey['name'] ?? $foreignKey['columns']);
            });
        }
    }
};

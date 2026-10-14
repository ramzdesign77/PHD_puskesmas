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
        if (! Schema::hasForeignKey('log_notifikasi_wa', ['id_laporan'])) {
            Schema::table('log_notifikasi_wa', function (Blueprint $table): void {
                $table->foreign('id_laporan', 'log_notifikasi_wa_ibfk_1')
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
        $foreignKey = collect(Schema::getForeignKeys('log_notifikasi_wa'))
            ->first(fn (array $foreignKey): bool => $foreignKey['columns'] === ['id_laporan']);

        if ($foreignKey !== null) {
            Schema::table('log_notifikasi_wa', function (Blueprint $table) use ($foreignKey): void {
                $table->dropForeign($foreignKey['name'] ?? $foreignKey['columns']);
            });
        }
    }
};

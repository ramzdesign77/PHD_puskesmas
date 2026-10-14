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
        if (! Schema::hasForeignKey('laporan_warga', ['id_desa'])) {
            Schema::table('laporan_warga', function (Blueprint $table): void {
                $table->foreign('id_desa', 'laporan_warga_ibfk_1')
                    ->references('id_desa')
                    ->on('desa')
                    ->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $foreignKey = collect(Schema::getForeignKeys('laporan_warga'))
            ->first(fn (array $foreignKey): bool => $foreignKey['columns'] === ['id_desa']);

        if ($foreignKey !== null) {
            Schema::table('laporan_warga', function (Blueprint $table) use ($foreignKey): void {
                $table->dropForeign($foreignKey['name'] ?? $foreignKey['columns']);
            });
        }
    }
};

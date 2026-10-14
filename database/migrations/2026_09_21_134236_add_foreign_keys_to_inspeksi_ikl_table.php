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
        if (! Schema::hasForeignKey('inspeksi_ikl', ['id_jadwal'])) {
            Schema::table('inspeksi_ikl', function (Blueprint $table): void {
                $table->foreign('id_jadwal', 'inspeksi_ikl_jadwal_fk')
                    ->references('id_jadwal')
                    ->on('jadwal_inspeksi')
                    ->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $foreignKey = collect(Schema::getForeignKeys('inspeksi_ikl'))
            ->first(fn (array $foreignKey): bool => $foreignKey['columns'] === ['id_jadwal']);

        if ($foreignKey !== null) {
            Schema::table('inspeksi_ikl', function (Blueprint $table) use ($foreignKey): void {
                $table->dropForeign($foreignKey['name'] ?? $foreignKey['columns']);
            });
        }
    }
};

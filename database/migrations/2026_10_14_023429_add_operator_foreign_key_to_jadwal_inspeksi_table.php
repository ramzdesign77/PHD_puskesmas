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
        if (! Schema::hasForeignKey('jadwal_inspeksi', ['id_operator'])) {
            Schema::table('jadwal_inspeksi', function (Blueprint $table): void {
                $table->foreign('id_operator', 'jadwal_inspeksi_user_operator_fk')
                    ->references('id_user')
                    ->on('users')
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $foreignKey = collect(Schema::getForeignKeys('jadwal_inspeksi'))
            ->first(fn (array $foreignKey): bool => (
                $foreignKey['columns'] === ['id_operator']
                && $foreignKey['foreign_table'] === 'users'
            ));

        if ($foreignKey !== null) {
            Schema::table('jadwal_inspeksi', function (Blueprint $table) use ($foreignKey): void {
                $table->dropForeign($foreignKey['name'] ?? $foreignKey['columns']);
            });
        }
    }
};

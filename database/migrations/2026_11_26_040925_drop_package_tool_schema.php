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
        if (Schema::hasTable('trx_jadwal_inspeksi') && Schema::hasColumn('trx_jadwal_inspeksi', 'id_paket')) {
            $foreignKey = collect(Schema::getForeignKeys('trx_jadwal_inspeksi'))
                ->first(fn (array $key): bool => ($key['columns'] ?? []) === ['id_paket']);

            if ($foreignKey !== null) {
                Schema::table('trx_jadwal_inspeksi', function (Blueprint $table) use ($foreignKey): void {
                    $table->dropForeign($foreignKey['name']);
                });
            }

            Schema::table('trx_jadwal_inspeksi', function (Blueprint $table): void {
                $table->dropColumn('id_paket');
            });
        }

        Schema::dropIfExists('ref_paket_alat');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        throw new RuntimeException('The package-tool schema was removed and cannot be restored automatically.');
    }
};

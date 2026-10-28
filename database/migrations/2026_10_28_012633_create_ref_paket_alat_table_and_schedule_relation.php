<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('ref_paket_alat')) {
            Schema::create('ref_paket_alat', function (Blueprint $table): void {
                $table->id('id_paket');
                $table->string('nama_paket', 150);
                $table->string('kategori_laporan', 100);
                $table->text('deskripsi_alat')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('trx_jadwal_inspeksi')) {
            return;
        }

        if (! Schema::hasColumn('trx_jadwal_inspeksi', 'id_paket')) {
            Schema::table('trx_jadwal_inspeksi', function (Blueprint $table): void {
                $table->unsignedBigInteger('id_paket')->nullable();
            });
        } else {
            Schema::table('trx_jadwal_inspeksi', function (Blueprint $table): void {
                $table->unsignedBigInteger('id_paket')->nullable()->change();
            });
        }

        if (
            DB::table('trx_jadwal_inspeksi')
                ->whereNotNull('id_paket')
                ->whereNotIn('id_paket', DB::table('ref_paket_alat')->select('id_paket'))
                ->exists()
        ) {
            throw new RuntimeException('Cannot add the package foreign key while orphaned schedule package references exist.');
        }

        $foreignKeyExists = collect(Schema::getForeignKeys('trx_jadwal_inspeksi'))
            ->contains(fn (array $foreignKey): bool => ($foreignKey['columns'] ?? []) === ['id_paket']
                && ($foreignKey['foreign_table'] ?? null) === 'ref_paket_alat'
                && ($foreignKey['foreign_columns'] ?? []) === ['id_paket']);

        if (! $foreignKeyExists) {
            Schema::table('trx_jadwal_inspeksi', function (Blueprint $table): void {
                $table->foreign('id_paket', 'fk_trx_jadwal_inspeksi_id_paket')
                    ->references('id_paket')
                    ->on('ref_paket_alat')
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (
            Schema::hasTable('ref_paket_alat')
            && DB::table('ref_paket_alat')->exists()
        ) {
            throw new RuntimeException('The package table contains data. Archive or remove package records before rolling back.');
        }

        if (Schema::hasTable('trx_jadwal_inspeksi') && Schema::hasColumn('trx_jadwal_inspeksi', 'id_paket')) {
            $foreignKey = collect(Schema::getForeignKeys('trx_jadwal_inspeksi'))
                ->first(fn (array $key): bool => ($key['columns'] ?? []) === ['id_paket']
                    && ($key['foreign_table'] ?? null) === 'ref_paket_alat');

            if ($foreignKey !== null) {
                Schema::table('trx_jadwal_inspeksi', function (Blueprint $table) use ($foreignKey): void {
                    $table->dropForeign($foreignKey['name'] ?? 'fk_trx_jadwal_inspeksi_id_paket');
                });
            }

            Schema::table('trx_jadwal_inspeksi', function (Blueprint $table): void {
                $table->dropColumn('id_paket');
            });
        }

        Schema::dropIfExists('ref_paket_alat');
    }
};

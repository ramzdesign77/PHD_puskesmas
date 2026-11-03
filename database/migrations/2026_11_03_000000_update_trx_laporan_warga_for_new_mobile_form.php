<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('trx_laporan_warga')) {
            return;
        }

        Schema::table('trx_laporan_warga', function (Blueprint $table): void {
            if (! Schema::hasColumn('trx_laporan_warga', 'id_user')) {
                $table->unsignedBigInteger('id_user')->nullable()->after('id_laporan');
            }

            if (Schema::hasColumn('trx_laporan_warga', 'nik_pelapor')) {
                $table->string('nik_pelapor', 16)->nullable()->change();
            }

            if (Schema::hasColumn('trx_laporan_warga', 'kategori_laporan')) {
                $table->string('kategori_laporan', 100)->nullable(false)->change();
            }

            if (! Schema::hasColumn('trx_laporan_warga', 'alamat_detail')) {
                $table->string('alamat_detail', 255)->nullable();
            }

            if (! Schema::hasColumn('trx_laporan_warga', 'patokan_alamat')) {
                $table->string('patokan_alamat', 255)->nullable();
            }

            if (! Schema::hasColumn('trx_laporan_warga', 'latitude')) {
                $table->decimal('latitude', 10, 8)->nullable();
            }

            if (! Schema::hasColumn('trx_laporan_warga', 'longitude')) {
                $table->decimal('longitude', 11, 8)->nullable();
            }
        });

        if (
            Schema::hasTable('app_users')
            && Schema::hasColumn('trx_laporan_warga', 'id_user')
            && Schema::hasColumn('app_users', 'id')
        ) {
            $hasForeignKey = collect(Schema::getForeignKeys('trx_laporan_warga'))
                ->contains(fn (array $foreignKey): bool => $foreignKey['columns'] === ['id_user']);

            if (! $hasForeignKey) {
                Schema::table('trx_laporan_warga', function (Blueprint $table): void {
                    $table->foreign('id_user', 'fk_trx_laporan_warga_id_user')
                        ->references('id')
                        ->on('app_users')
                        ->nullOnDelete();
                });
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('trx_laporan_warga')) {
            return;
        }

        $foreignKey = collect(Schema::getForeignKeys('trx_laporan_warga'))
            ->first(fn (array $foreignKey): bool => $foreignKey['columns'] === ['id_user']);

        if ($foreignKey !== null) {
            Schema::table('trx_laporan_warga', function (Blueprint $table) use ($foreignKey): void {
                $table->dropForeign($foreignKey['name'] ?? 'fk_trx_laporan_warga_id_user');
            });
        }

        Schema::table('trx_laporan_warga', function (Blueprint $table): void {
            foreach (['id_user', 'alamat_detail', 'patokan_alamat'] as $column) {
                if (Schema::hasColumn('trx_laporan_warga', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

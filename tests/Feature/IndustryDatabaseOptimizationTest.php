<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class IndustryDatabaseOptimizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_migration_backfills_compatibility_fields_and_adds_schema_constraints(): void
    {
        $migration = require base_path(
            'database/migrations/2026_10_28_000000_optimize_database_to_industry_standards.php'
        );

        DB::table('app_users')->insert([
            'id' => 510,
            'id_user' => null,
            'nama_lengkap' => 'Admin Test',
            'name' => null,
            'username' => 'admin-test',
            'password' => 'not-a-real-password',
            'role' => 'admin',
        ]);

        $migration->up();
        $migration->up();

        $this->assertDatabaseHas('app_users', [
            'id' => 510,
            'id_user' => 510,
            'name' => 'Admin Test',
            'nama_lengkap' => 'Admin Test',
        ]);

        foreach ([
            ['app_users', 'idx_app_users_role'],
            ['app_users', 'idx_app_users_is_active'],
            ['app_users', 'idx_app_users_id_desa'],
            ['app_petugas', 'idx_app_petugas_is_active'],
            ['ref_edukasi', 'idx_ref_edukasi_category'],
            ['ref_edukasi', 'idx_ref_edukasi_is_published'],
            ['trx_laporan_warga', 'idx_trx_laporan_warga_status_created'],
            ['trx_laporan_warga', 'idx_trx_laporan_warga_desa_status'],
            ['trx_jadwal_inspeksi', 'idx_trx_jadwal_inspeksi_operator_date_status'],
            ['trx_inspeksi_ikl', 'idx_trx_inspeksi_ikl_risiko_tanggal'],
            ['log_notifikasi_wa', 'idx_log_notifikasi_wa_status_sentat'],
        ] as [$table, $index]) {
            $this->assertTrue(Schema::hasIndex($table, $index), "Expected {$index} on {$table}.");
        }

        foreach ([
            ['app_mobile_tokens', 'last_used_at'],
            ['app_mobile_tokens', 'expires_at'],
            ['app_mobile_tokens', 'updated_at'],
            ['ref_desa', 'latitude'],
            ['ref_desa', 'longitude'],
            ['ref_desa', 'updated_at'],
            ['ref_edukasi', 'id_author'],
            ['ref_edukasi', 'is_published'],
            ['ref_paket_alat', 'updated_at'],
            ['trx_laporan_warga', 'latitude'],
            ['trx_laporan_warga', 'longitude'],
            ['trx_laporan_warga', 'updated_at'],
            ['trx_jadwal_inspeksi', 'updated_at'],
            ['trx_inspeksi_ikl', 'intervensi_fisik'],
            ['trx_inspeksi_ikl', 'catatan_intervensi'],
            ['trx_inspeksi_ikl', 'foto_inspeksi'],
            ['trx_inspeksi_ikl', 'created_at'],
            ['trx_inspeksi_ikl', 'updated_at'],
            ['log_notifikasi_wa', 'response_gateway'],
        ] as [$table, $column]) {
            $this->assertTrue(Schema::hasColumn($table, $column), "Expected {$column} on {$table}.");
        }

        foreach ([
            ['app_users', 'id_desa', 'ref_desa', 'id_desa'],
            ['ref_edukasi', 'id_author', 'app_users', 'id'],
            ['trx_jadwal_inspeksi', 'id_paket', 'ref_paket_alat', 'id_paket'],
        ] as [$table, $column, $referencedTable, $referencedColumn]) {
            $foreignKeys = collect(Schema::getForeignKeys($table));
            $this->assertTrue($foreignKeys->contains(
                fn (array $foreignKey): bool => $foreignKey['columns'] === [$column]
                    && $foreignKey['foreign_table'] === $referencedTable
                    && $foreignKey['foreign_columns'] === [$referencedColumn]
                    && strtolower($foreignKey['on_delete']) === 'set null',
            ));
        }

    }
}

<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class IndustryDatabaseOptimizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_domain_tables_use_named_string_primary_keys_and_preserve_framework_tables(): void
    {
        $primaryKeys = [
            'app_users' => 'id_user',
            'app_mobile_tokens' => 'id_token',
            'ref_desa' => 'id_desa',
            'ref_edukasi' => 'id_edukasi',
            'trx_laporan_warga' => 'id_laporan',
            'trx_jadwal_inspeksi' => 'id_jadwal',
            'trx_inspeksi_ikl' => 'id_inspeksi',
        ];

        foreach ($primaryKeys as $table => $column) {
            $this->assertTrue(Schema::hasColumn($table, $column), "Expected {$column} on {$table}.");
            $this->assertSame('varchar', Schema::getColumnType($table, $column), "Expected {$column} to be a string.");
        }

        $this->assertFalse(Schema::hasColumn('app_users', 'id'));
        $this->assertFalse(Schema::hasColumn('app_mobile_tokens', 'id'));
        $this->assertFalse(Schema::hasColumn('ref_edukasi', 'id'));
        $this->assertFalse(Schema::hasTable('ref_paket_alat'));
        $this->assertFalse(Schema::hasTable('log_notifikasi_wa'));
        $this->assertFalse(Schema::hasColumn('trx_jadwal_inspeksi', 'id_paket'));
        $this->assertFalse(Schema::hasTable('app_petugas'));
        $this->assertTrue(Schema::hasColumn('app_users', 'jabatan'));

        foreach (['migrations', 'sessions', 'cache', 'cache_locks', 'password_reset_tokens'] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Expected framework table {$table} to remain.");
        }
    }

    public function test_domain_foreign_keys_reference_the_custom_primary_keys(): void
    {
        $expected = [
            ['app_users', 'id_desa', 'ref_desa', 'id_desa', 'set null'],
            ['app_mobile_tokens', 'id_user', 'app_users', 'id_user', 'cascade'],
            ['ref_edukasi', 'id_author', 'app_users', 'id_user', 'set null'],
            ['trx_laporan_warga', 'id_desa', 'ref_desa', 'id_desa', 'set null'],
            ['trx_laporan_warga', 'id_user', 'app_users', 'id_user', 'set null'],
            ['trx_jadwal_inspeksi', 'id_laporan', 'trx_laporan_warga', 'id_laporan', 'cascade'],
            ['trx_jadwal_inspeksi', 'id_operator', 'app_users', 'id_user', 'set null'],
            ['trx_inspeksi_ikl', 'id_jadwal', 'trx_jadwal_inspeksi', 'id_jadwal', 'cascade'],
        ];

        foreach ($expected as [$table, $column, $parent, $parentColumn, $onDelete]) {
            $this->assertTrue(collect(Schema::getForeignKeys($table))->contains(
                fn (array $foreignKey): bool => $foreignKey['columns'] === [$column]
                    && $foreignKey['foreign_table'] === $parent
                    && $foreignKey['foreign_columns'] === [$parentColumn]
                    && strtolower($foreignKey['on_delete']) === $onDelete,
            ), "Expected {$table}.{$column} to reference {$parent}.{$parentColumn} with {$onDelete} delete behavior.");
        }
    }
}

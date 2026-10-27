<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseTableStandardizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_migration_renames_tables_and_rolls_them_back_without_renaming_logs(): void
    {
        $migration = require base_path(
            'database/migrations/2026_10_27_000000_standardize_db_tables.php'
        );

        $migration->up();

        foreach ([
            'ref_desa',
            'ref_edukasi',
            'app_users',
            'app_petugas',
            'app_mobile_tokens',
            'trx_laporan_warga',
            'trx_jadwal_inspeksi',
            'trx_inspeksi_ikl',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Expected {$table} to exist after migration.");
        }

        $migration->down();

        foreach ([
            'desa',
            'educations',
            'users',
            'petugas',
            'mobile_access_tokens',
            'laporan_warga',
            'jadwal_inspeksi',
            'inspeksi_ikl',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Expected {$table} to exist after rollback.");
        }
    }
}

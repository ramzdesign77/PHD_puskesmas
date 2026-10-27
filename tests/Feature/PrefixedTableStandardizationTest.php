<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PrefixedTableStandardizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_prefixed_tables_are_renamed_without_losing_data_and_can_be_rolled_back(): void
    {
        $tables = [
            'tm_desa' => 'ref_desa',
            'tm_edukasi' => 'ref_edukasi',
            'tm_paket_alat' => 'ref_paket_alat',
            'tm_petugas' => 'app_petugas',
            'tm_users' => 'app_users',
            'tr_mobile_access_tokens' => 'app_mobile_tokens',
            'tr_laporan_warga' => 'trx_laporan_warga',
            'tr_jadwal_inspeksi' => 'trx_jadwal_inspeksi',
            'tr_inspeksi_ikl' => 'trx_inspeksi_ikl',
            'tr_log_notifikasi_wa' => 'log_notifikasi_wa',
        ];

        Schema::disableForeignKeyConstraints();

        try {
            foreach ($tables as $sourceTable => $targetTable) {
                Schema::rename($targetTable, $sourceTable);
            }
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        DB::table('tm_users')->insert([
            'id' => 1,
            'id_user' => 1,
            'nama_lengkap' => 'Administrator',
            'username' => 'admin',
            'password' => 'hashed-password',
            'role' => 'admin',
        ]);

        $migration = require base_path(
            'database/migrations/2026_10_27_064413_standardize_prefixed_table_names.php'
        );
        $migration->up();

        foreach ($tables as $sourceTable => $targetTable) {
            $this->assertTrue(Schema::hasTable($targetTable), "Expected {$targetTable} to exist.");
            $this->assertFalse(Schema::hasTable($sourceTable), "Expected {$sourceTable} to be renamed.");
        }

        $this->assertDatabaseHas('app_users', [
            'id_user' => 1,
            'username' => 'admin',
        ]);

        $migration->down();

        foreach ($tables as $sourceTable => $targetTable) {
            $this->assertTrue(Schema::hasTable($sourceTable), "Expected {$sourceTable} after rollback.");
            $this->assertFalse(Schema::hasTable($targetTable), "Expected {$targetTable} to be renamed back.");
        }

        $this->assertDatabaseHas('tm_users', [
            'id_user' => 1,
            'username' => 'admin',
        ]);
    }

    public function test_migration_refuses_conflicting_table_names_without_renaming_data(): void
    {
        Schema::rename('app_users', 'tm_users');
        Schema::create('app_users', function (Blueprint $table): void {
            $table->id();
        });

        $migration = require base_path(
            'database/migrations/2026_10_27_064413_standardize_prefixed_table_names.php'
        );

        try {
            $migration->up();
            $this->fail('The migration should reject conflicting table names.');
        } catch (\RuntimeException $exception) {
            $this->assertSame(
                'Both tm_users and app_users exist; resolve the table-name conflict before migrating.',
                $exception->getMessage(),
            );
        }

        $this->assertTrue(Schema::hasTable('tm_users'));
        $this->assertTrue(Schema::hasTable('app_users'));
    }
}

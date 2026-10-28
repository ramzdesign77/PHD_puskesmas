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
        if (! Schema::hasTable('log_notifikasi_wa') && ! Schema::hasTable('trx_laporan_warga')) {
            throw new RuntimeException('The trx_laporan_warga table must exist before creating log_notifikasi_wa.');
        }

        if (Schema::hasTable('app_users')) {
            $columnsToDrop = array_values(array_filter(
                ['nip', 'nik', 'nip_nik'],
                static fn (string $column): bool => Schema::hasColumn('app_users', $column),
            ));

            if ($columnsToDrop !== []) {
                Schema::table('app_users', function (Blueprint $table) use ($columnsToDrop): void {
                    $table->dropColumn($columnsToDrop);
                });
            }
        }

        if (! Schema::hasTable('log_notifikasi_wa')) {
            Schema::create('log_notifikasi_wa', function (Blueprint $table): void {
                $table->increments('id_log');
                $table->integer('id_laporan');
                $table->string('no_wa_tujuan', 20);
                $table->text('pesan_terkirim');
                $table->enum('status_kirim', ['pending', 'terkirim', 'gagal'])->default('pending');
                $table->text('response_gateway')->nullable();
                $table->timestamp('sent_at')->nullable()->useCurrent();

                $table->foreign('id_laporan')
                    ->references('id_laporan')
                    ->on('trx_laporan_warga')
                    ->cascadeOnDelete();
                $table->index(['status_kirim', 'sent_at']);
            });
        }

        if (Schema::hasTable('ref_edukasi')) {
            DB::table('ref_edukasi')->where('slug', 'langsungj')->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('app_users')) {
            return;
        }

        Schema::table('app_users', function (Blueprint $table): void {
            if (! Schema::hasColumn('app_users', 'nip')) {
                $table->string('nip', 30)->nullable();
            }

            if (! Schema::hasColumn('app_users', 'nik')) {
                $table->char('nik', 16)->nullable();
            }

            if (! Schema::hasColumn('app_users', 'nip_nik')) {
                $table->string('nip_nik', 30)->nullable();
            }
        });
    }
};

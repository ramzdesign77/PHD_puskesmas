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
        if (Schema::hasTable('app_users') && ! Schema::hasColumn('app_users', 'jabatan')) {
            Schema::table('app_users', function (Blueprint $table): void {
                $table->string('jabatan')->nullable();
            });
        }

        if (Schema::hasTable('app_users') && Schema::hasTable('app_petugas')) {
            $accounts = DB::table('app_users')->get(['id_user', 'id_petugas', 'no_telepon']);

            foreach (DB::table('app_petugas')->get() as $profile) {
                $account = $accounts->first(
                    static fn (object $candidate): bool => (string) $candidate->id_petugas === (string) $profile->id_petugas,
                );

                if ($account === null) {
                    continue;
                }

                $attributes = ['jabatan' => $profile->jabatan];

                if (blank($account->no_telepon) && filled($profile->no_telepon)) {
                    $attributes['no_telepon'] = $profile->no_telepon;
                }

                DB::table('app_users')
                    ->where('id_user', $account->id_user)
                    ->update($attributes);
            }
        }

        Schema::dropIfExists('app_petugas');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        throw new RuntimeException('Deleted app_petugas records cannot be restored automatically.');
    }
};

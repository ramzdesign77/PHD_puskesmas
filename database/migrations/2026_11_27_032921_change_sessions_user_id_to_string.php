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
        if (Schema::hasTable('sessions') && Schema::getColumnType('sessions', 'user_id') !== 'string') {
            Schema::table('sessions', function (Blueprint $table): void {
                $table->string('user_id', 30)->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sessions') && Schema::getColumnType('sessions', 'user_id') !== 'integer') {
            DB::table('sessions')->update(['user_id' => null]);

            Schema::table('sessions', function (Blueprint $table): void {
                $table->unsignedBigInteger('user_id')->nullable()->change();
            });
        }
    }
};

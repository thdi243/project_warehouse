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
        if (!Schema::hasColumn('wrm_soh', 'loc_id')) {
            Schema::table('wrm_soh', function (Blueprint $table) {
                $table->foreignId('loc_id')
                    ->nullable()
                    ->after('pallet')
                    ->constrained('wrm_master_bin')
                    ->onDelete('restrict');
            });
        }

        if (!Schema::hasColumn('wrm_so_summaries', 'loc_id')) {
            Schema::table('wrm_so_summaries', function (Blueprint $table) {
                $table->foreignId('loc_id')
                    ->nullable()
                    ->after('pallet')
                    ->constrained('wrm_master_bin')
                    ->onDelete('restrict');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wrm_so_summaries', function (Blueprint $table) {
            if (Schema::hasColumn('wrm_so_summaries', 'loc_id')) {
                $table->dropForeign(['loc_id']);
                $table->dropColumn('loc_id');
            }
        });

        // We only drop if it wasn't there before, but down() drops it anyway
        Schema::table('wrm_soh', function (Blueprint $table) {
            if (Schema::hasColumn('wrm_soh', 'loc_id')) {
                $table->dropForeign(['loc_id']);
                $table->dropColumn('loc_id');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = ['wsp_so_detail', 'wsp_so_temp', 'wsp_so_temp_note'];

        foreach ($tables as $table) {
            if (!Schema::hasColumn($table, 'loc_id')) {
                Schema::table($table, function (Blueprint $tableObj) {
                    $tableObj->unsignedBigInteger('loc_id')->nullable()->after('barang_id');
                    $tableObj->foreign('loc_id')
                        ->references('id')
                        ->on('wsp_stock_location')
                        ->onDelete('set null');
                });
            }
        }

        if (!Schema::hasColumn('wsp_so_summaries', 'loc_id')) {
            Schema::table('wsp_so_summaries', function (Blueprint $tableObj) {
                $tableObj->unsignedBigInteger('loc_id')->nullable()->after('barang_id');
                $tableObj->foreign('loc_id')
                    ->references('id')
                    ->on('wsp_stock_location')
                    ->onDelete('set null');
            });
        }

        Schema::table('wsp_so_summaries', function (Blueprint $tableObj) {
            // Check if unique index exists before dropping
            $indices = DB::select("SHOW INDEX FROM wsp_so_summaries WHERE Key_name = 'wsp_so_summaries_so_barang_unique'");
            if (count($indices) > 0) {
                // Drop foreign key first because the unique index is used by it
                $tableObj->dropForeign('wsp_so_summaries_so_id_foreign');
                $tableObj->dropUnique('wsp_so_summaries_so_barang_unique');

                // Re-add foreign key constraint
                $tableObj->foreign('so_id')
                    ->references('id')
                    ->on('wsp_so')
                    ->onDelete('cascade');
            }

            // Check if new unique index does not exist before adding
            $newIndices = DB::select("SHOW INDEX FROM wsp_so_summaries WHERE Key_name = 'wsp_so_summaries_loc_unique'");
            if (count($newIndices) === 0) {
                // Drop foreign key first to let us alter indexes
                $tableObj->dropForeign('wsp_so_summaries_so_id_foreign');
                $tableObj->unique(
                    ['so_id', 'barang_id', 'loc_id'],
                    'wsp_so_summaries_loc_unique'
                );
                $tableObj->foreign('so_id')
                    ->references('id')
                    ->on('wsp_so')
                    ->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wsp_so_summaries', function (Blueprint $tableObj) {
            // Check if new index exists before dropping
            $newIndices = DB::select("SHOW INDEX FROM wsp_so_summaries WHERE Key_name = 'wsp_so_summaries_loc_unique'");
            if (count($newIndices) > 0) {
                $tableObj->dropForeign('wsp_so_summaries_so_id_foreign');
                $tableObj->dropUnique('wsp_so_summaries_loc_unique');
                $tableObj->foreign('so_id')
                    ->references('id')
                    ->on('wsp_so')
                    ->onDelete('cascade');
            }

            if (Schema::hasColumn('wsp_so_summaries', 'loc_id')) {
                $tableObj->dropForeign(['loc_id']);
                $tableObj->dropColumn('loc_id');
            }

            // Check if old unique index does not exist before re-creating it
            $oldIndices = DB::select("SHOW INDEX FROM wsp_so_summaries WHERE Key_name = 'wsp_so_summaries_so_barang_unique'");
            if (count($oldIndices) === 0) {
                $tableObj->dropForeign('wsp_so_summaries_so_id_foreign');
                $tableObj->unique(['so_id', 'barang_id'], 'wsp_so_summaries_so_barang_unique');
                $tableObj->foreign('so_id')
                    ->references('id')
                    ->on('wsp_so')
                    ->onDelete('cascade');
            }
        });

        $tables = ['wsp_so_detail', 'wsp_so_temp', 'wsp_so_temp_note'];
        foreach ($tables as $table) {
            if (Schema::hasColumn($table, 'loc_id')) {
                Schema::table($table, function (Blueprint $tableObj) {
                    $tableObj->dropForeign(['loc_id']);
                    $tableObj->dropColumn('loc_id');
                });
            }
        }
    }
};

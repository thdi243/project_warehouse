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
        $tables = ['wsp_soh', 'wsp_so_detail', 'wsp_so_temp', 'wsp_so_temp_note'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $tableObj) {
                $tableObj->unsignedBigInteger('loc_id')->nullable()->after('barang_id');
                $tableObj->foreign('loc_id')
                    ->references('id')
                    ->on('wsp_stock_location')
                    ->onDelete('set null');
            });
        }

        Schema::table('wsp_so_summaries', function (Blueprint $tableObj) {
            $tableObj->unsignedBigInteger('loc_id')->nullable()->after('barang_id');
            $tableObj->foreign('loc_id')
                ->references('id')
                ->on('wsp_stock_location')
                ->onDelete('set null');

            // Drop old unique constraint if it exists
            $indices = DB::select("SHOW INDEX FROM wsp_so_summaries WHERE Key_name = 'wsp_so_summaries_so_barang_unique'");
            if (count($indices) > 0) {
                $tableObj->dropUnique('wsp_so_summaries_so_barang_unique');
            }

            // Add new unique constraint
            $tableObj->unique(
                ['so_id', 'barang_id', 'loc_id'],
                'wsp_so_summaries_loc_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wsp_so_summaries', function (Blueprint $tableObj) {
            $tableObj->dropUnique('wsp_so_summaries_loc_unique');
            $tableObj->dropForeign(['loc_id']);
            $tableObj->dropColumn('loc_id');
            $tableObj->unique(['so_id', 'barang_id'], 'wsp_so_summaries_so_barang_unique');
        });

        $tables = ['wsp_soh', 'wsp_so_detail', 'wsp_so_temp', 'wsp_so_temp_note'];
        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $tableObj) {
                $tableObj->dropForeign(['loc_id']);
                $tableObj->dropColumn('loc_id');
            });
        }
    }
};

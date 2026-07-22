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
        $tables = [
            'wcp_soh' => 'barang_id',
            'wcp_so' => 'tgl_opname',
            'wcp_so_status' => 'tgl_opname',

            'wpm_soh' => 'barang_id',
            'wpm_so' => 'tgl_opname',
            'wpm_so_status' => 'tgl_opname',

            'wrm_soh' => 'barang_id',
            'wrm_so' => 'tgl_opname',
            'wrm_so_status' => 'tgl_opname',

            'wsp_soh' => 'barang_id',
            'wsp_so' => 'tgl_opname',
            'wsp_so_status' => 'tgl_opname',

            'wfg_soh' => 'barang_id',
            'wfg_sop' => 'tgl_opname',
            'wfg_sop_status' => 'tgl_opname',
        ];

        foreach ($tables as $table => $afterColumn) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'jenis_so')) {
                Schema::table($table, function (Blueprint $tableObj) use ($afterColumn) {
                    $tableObj->string('jenis_so')->default('cycle_count')->after($afterColumn);
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'wcp_soh', 'wcp_so', 'wcp_so_status',
            'wpm_soh', 'wpm_so', 'wpm_so_status',
            'wrm_soh', 'wrm_so', 'wrm_so_status',
            'wsp_soh', 'wsp_so', 'wsp_so_status',
            'wfg_soh', 'wfg_sop', 'wfg_sop_status'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'jenis_so')) {
                Schema::table($table, function (Blueprint $tableObj) {
                    $tableObj->dropColumn('jenis_so');
                });
            }
        }
    }
};

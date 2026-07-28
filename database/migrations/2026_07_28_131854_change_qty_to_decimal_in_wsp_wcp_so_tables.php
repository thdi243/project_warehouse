<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $wspTables = ['wsp_soh', 'wsp_so_detail', 'wsp_so_temp', 'wsp_so_summaries'];
    private array $wcpTables = ['wcp_soh', 'wcp_so_detail', 'wcp_so_temp', 'wcp_so_summaries'];

    private array $columnMap = [
        'wsp_soh'           => ['qty_soh', 'qty_unrest', 'qty_qi', 'qty_block'],
        'wsp_so_detail'     => ['qty_full', 'qty_receh'],
        'wsp_so_temp'       => ['qty_full', 'qty_receh', 'summary'],
        'wsp_so_summaries'  => ['qty_fisik', 'qty_sistem', 'selisih'],
        'wcp_soh'           => ['qty_soh', 'qty_unrest', 'qty_qi', 'qty_block'],
        'wcp_so_detail'     => ['qty_full', 'qty_receh'],
        'wcp_so_temp'       => ['qty_full', 'qty_receh', 'summary'],
        'wcp_so_summaries'  => ['qty_fisik', 'qty_sistem', 'selisih'],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $allTables = array_merge($this->wspTables, $this->wcpTables);

        foreach ($allTables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            $columns = $this->columnMap[$table] ?? [];

            Schema::table($table, function (Blueprint $tableObj) use ($table, $columns) {
                foreach ($columns as $col) {
                    if (Schema::hasColumn($table, $col)) {
                        $tableObj->decimal($col, 10, 2)->default(0)->change();
                    }
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $allTables = array_merge($this->wspTables, $this->wcpTables);

        foreach ($allTables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            $columns = $this->columnMap[$table] ?? [];

            Schema::table($table, function (Blueprint $tableObj) use ($table, $columns) {
                foreach ($columns as $col) {
                    if (Schema::hasColumn($table, $col)) {
                        $tableObj->integer($col)->default(0)->change();
                    }
                }
            });
        }
    }
};

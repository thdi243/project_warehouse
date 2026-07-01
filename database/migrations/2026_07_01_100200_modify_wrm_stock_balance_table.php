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
        if (Schema::hasColumn('wrm_stock_balance', 'loc_id')) {
            try {
                Schema::table('wrm_stock_balance', function (Blueprint $table) {
                    $table->dropForeign('wrm_stock_balance_loc_id_foreign');
                });
            } catch (\Exception $e) {}

            // 1. Retrieve and group the data
            $balances = DB::table('wrm_stock_balance')
                ->select('barang_id', DB::raw('SUM(qty) as total_qty'), DB::raw('MIN(created_by) as created_by'))
                ->groupBy('barang_id')
                ->get();

            // 2. Clear the table
            DB::table('wrm_stock_balance')->delete();

            // 3. Drop the loc_id column
            Schema::table('wrm_stock_balance', function (Blueprint $table) {
                $table->dropColumn('loc_id');
            });

            // 4. Insert the consolidated data
            foreach ($balances as $bal) {
                DB::table('wrm_stock_balance')->insert([
                    'barang_id'  => $bal->barang_id,
                    'qty'        => $bal->total_qty,
                    'created_by' => $bal->created_by ?? 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 5. Add unique index to barang_id
        try {
            Schema::table('wrm_stock_balance', function (Blueprint $table) {
                $table->unique('barang_id');
            });
        } catch (\Exception $e) {}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wrm_stock_balance', function (Blueprint $table) {
            $table->dropUnique(['barang_id']);
            $table->foreignId('loc_id')->nullable()->after('barang_id')->constrained('wrm_master_location')->onDelete('restrict');
        });
    }
};

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
        Schema::table('wrm_stock_on_hand', function (Blueprint $table) {
            $table->index(['barang_id', 'status'], 'wrm_soh_summary_item_idx');
            $table->index(['no_spb', 'status', 'barang_id'], 'wrm_soh_summary_spb_idx');
        });

        Schema::table('wrm_master_barang', function (Blueprint $table) {
            $table->index('mid', 'wrm_master_barang_mid_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wrm_master_barang', function (Blueprint $table) {
            $table->dropIndex('wrm_master_barang_mid_idx');
        });

        Schema::table('wrm_stock_on_hand', function (Blueprint $table) {
            $table->dropIndex('wrm_soh_summary_spb_idx');
            $table->dropIndex('wrm_soh_summary_item_idx');
        });
    }
};

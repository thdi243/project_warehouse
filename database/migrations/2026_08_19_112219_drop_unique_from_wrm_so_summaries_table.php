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
        Schema::table('wrm_so_summaries', function (Blueprint $table) {
            $table->dropUnique('wrm_so_summaries_so_barang_spb_pallet_loc_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wrm_so_summaries', function (Blueprint $table) {
            $table->unique(['so_id', 'barang_id', 'no_spb', 'pallet', 'loc_id'], 'wrm_so_summaries_so_barang_spb_pallet_loc_unique');
        });
    }
};

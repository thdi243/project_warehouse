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
        Schema::table('wsp_barang', function (Blueprint $table) {
            $table->dropColumn(['plant', 's_loc']);

            $table->index('mid_barang', 'idx_wsp_barang_mid_barang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wsp_barang', function (Blueprint $table) {
            $table->string('plant', 50)->nullable()->after('qty_pallet');
            $table->string('s_loc', 50)->nullable()->after('plant');

            $table->dropIndex('idx_wsp_barang_mid_barang');
        });
    }
};

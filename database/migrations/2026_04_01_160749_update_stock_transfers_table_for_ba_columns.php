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
        Schema::table('wrm_stock_transfers', function (Blueprint $table) {
            $table->string('no_ba', 50)->nullable()->after('id');
            $table->date('tgl_ba')->nullable()->after('no_ba');
            $table->date('tgl_gi')->nullable()->after('tgl_reservasi');
            $table->string('matdoc_gi')->nullable()->after('tgl_gi');
        });

        Schema::table('wrm_stock_transfer_details', function (Blueprint $table) {
            $table->string('matdoc_scrup', 50)->nullable()->after('transfer_id');
            $table->string('matdoc_year', 50)->nullable()->after('matdoc_scrup');
            $table->dropColumn(['tgl_gi', 'matdoc_gi']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wrm_stock_transfer_details', function (Blueprint $table) {
            $table->dropColumn(['matdoc_scrup', 'matdoc_year']);
            $table->date('tgl_gi')->nullable();
            $table->string('matdoc_gi')->nullable();
        });

        Schema::table('wrm_stock_transfers', function (Blueprint $table) {
            $table->dropColumn(['no_ba', 'tgl_ba', 'tgl_gi', 'matdoc_gi']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('p2h_forklift', function (Blueprint $table) {
            $table->string('foto_kondisi_accu')->nullable()->after('fungsi_rem');
        });

        Schema::table('p2h_pallet_mover', function (Blueprint $table) {
            $table->string('foto_kondisi_accu')->nullable()->after('check_hydraulic');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('p2h_forklift', function (Blueprint $table) {
            $table->dropColumn('foto_kondisi_accu');
        });

        Schema::table('p2h_pallet_mover', function (Blueprint $table) {
            $table->dropColumn('foto_kondisi_accu');
        });
    }
};

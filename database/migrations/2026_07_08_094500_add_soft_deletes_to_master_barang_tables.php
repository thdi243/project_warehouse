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
        Schema::table('wrm_master_barang', function (Blueprint $table) {
            if (!Schema::hasColumn('wrm_master_barang', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('wsp_barang', function (Blueprint $table) {
            if (!Schema::hasColumn('wsp_barang', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('wpm_master_barang', function (Blueprint $table) {
            if (!Schema::hasColumn('wpm_master_barang', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('wcp_master_barang', function (Blueprint $table) {
            if (!Schema::hasColumn('wcp_master_barang', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('wfg_master_destinasi', function (Blueprint $table) {
            if (!Schema::hasColumn('wfg_master_destinasi', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('wrm_master_suppliers', function (Blueprint $table) {
            if (!Schema::hasColumn('wrm_master_suppliers', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('wrm_master_pallet', function (Blueprint $table) {
            if (!Schema::hasColumn('wrm_master_pallet', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('wrm_master_bin', function (Blueprint $table) {
            if (!Schema::hasColumn('wrm_master_bin', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('wrm_master_location', function (Blueprint $table) {
            if (!Schema::hasColumn('wrm_master_location', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wrm_master_barang', function (Blueprint $table) {
            if (Schema::hasColumn('wrm_master_barang', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });

        Schema::table('wsp_barang', function (Blueprint $table) {
            if (Schema::hasColumn('wsp_barang', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });

        Schema::table('wpm_master_barang', function (Blueprint $table) {
            if (Schema::hasColumn('wpm_master_barang', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });

        Schema::table('wcp_master_barang', function (Blueprint $table) {
            if (Schema::hasColumn('wcp_master_barang', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });

        Schema::table('wfg_master_destinasi', function (Blueprint $table) {
            if (Schema::hasColumn('wfg_master_destinasi', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });

        Schema::table('wrm_master_pallet', function (Blueprint $table) {
            if (Schema::hasColumn('wrm_master_pallet', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });

        Schema::table('wrm_master_suppliers', function (Blueprint $table) {
            if (Schema::hasColumn('wrm_master_suppliers', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });

        Schema::table('wrm_master_bin', function (Blueprint $table) {
            if (Schema::hasColumn('wrm_master_bin', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });

        Schema::table('wrm_master_location', function (Blueprint $table) {
            if (Schema::hasColumn('wrm_master_location', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};

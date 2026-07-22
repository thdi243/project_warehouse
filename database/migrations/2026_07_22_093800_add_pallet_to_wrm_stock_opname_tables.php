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
        if (!Schema::hasColumn('wrm_soh', 'pallet')) {
            Schema::table('wrm_soh', function (Blueprint $table) {
                $table->string('jenis_data')->nullable()->after('user_id');
                $table->string('pallet')->nullable()->after('no_spb');
            });
        }

        if (!Schema::hasColumn('wrm_so_detail', 'pallet')) {
            Schema::table('wrm_so_detail', function (Blueprint $table) {
                $table->string('pallet')->nullable()->after('no_spb');
            });
        }

        if (!Schema::hasColumn('wrm_so_temp', 'pallet')) {
            Schema::table('wrm_so_temp', function (Blueprint $table) {
                $table->string('pallet')->nullable()->after('no_spb');
            });
        }

        if (!Schema::hasColumn('wrm_so_temp_note', 'pallet')) {
            Schema::table('wrm_so_temp_note', function (Blueprint $table) {
                $table->string('pallet')->nullable()->after('no_spb');
            });
        }

        Schema::table('wrm_so_summaries', function (Blueprint $table) {
            if (!Schema::hasColumn('wrm_so_summaries', 'pallet')) {
                $table->string('pallet')->nullable()->after('no_spb');
            }
            // Drop foreign keys first to allow index drop
            $table->dropForeign('wrm_so_summaries_so_id_foreign');
            $table->dropForeign('wrm_so_summaries_barang_id_foreign');

            // Drop old unique constraint
            $table->dropUnique('wrm_so_summaries_so_barang_spb_unique');

            // Add new unique constraint including pallet
            $table->unique(['so_id', 'barang_id', 'no_spb', 'pallet'], 'wrm_so_summaries_so_barang_spb_pallet_unique');

            // Re-add foreign keys
            $table->foreign('so_id')->references('id')->on('wrm_so')->onDelete('cascade');
            $table->foreign('barang_id')->references('id')->on('wrm_master_barang')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wrm_so_summaries', function (Blueprint $table) {
            $table->dropForeign('wrm_so_summaries_so_id_foreign');
            $table->dropForeign('wrm_so_summaries_barang_id_foreign');

            $table->dropUnique('wrm_so_summaries_so_barang_spb_pallet_unique');
            $table->unique(['so_id', 'barang_id', 'no_spb'], 'wrm_so_summaries_so_barang_spb_unique');

            $table->foreign('so_id')->references('id')->on('wrm_so')->onDelete('cascade');
            $table->foreign('barang_id')->references('id')->on('wrm_master_barang')->onDelete('cascade');

            if (Schema::hasColumn('wrm_so_summaries', 'pallet')) {
                $table->dropColumn('pallet');
            }
        });

        if (Schema::hasColumn('wrm_so_temp_note', 'pallet')) {
            Schema::table('wrm_so_temp_note', function (Blueprint $table) {
                $table->dropColumn('pallet');
            });
        }

        if (Schema::hasColumn('wrm_so_temp', 'pallet')) {
            Schema::table('wrm_so_temp', function (Blueprint $table) {
                $table->dropColumn('pallet');
            });
        }

        if (Schema::hasColumn('wrm_so_detail', 'pallet')) {
            Schema::table('wrm_so_detail', function (Blueprint $table) {
                $table->dropColumn('pallet');
            });
        }

        if (Schema::hasColumn('wrm_soh', 'pallet')) {
            Schema::table('wrm_soh', function (Blueprint $table) {
                $table->dropColumn('pallet');
                $table->dropColumn('jenis_data');
            });
        }
    }
};

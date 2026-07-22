<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('wrm_so_detail', 'jenis_data')) {
            Schema::table('wrm_so_detail', function (Blueprint $table) {
                $table->string('jenis_data')->nullable()->after('pallet');
            });
        }

        if (!Schema::hasColumn('wrm_so_summaries', 'jenis_data')) {
            Schema::table('wrm_so_summaries', function (Blueprint $table) {
                $table->string('jenis_data')->nullable()->after('pallet');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('wrm_so_detail', 'jenis_data')) {
            Schema::table('wrm_so_detail', function (Blueprint $table) {
                $table->dropColumn('jenis_data');
            });
        }

        if (Schema::hasColumn('wrm_so_summaries', 'jenis_data')) {
            Schema::table('wrm_so_summaries', function (Blueprint $table) {
                $table->dropColumn('jenis_data');
            });
        }
    }
};

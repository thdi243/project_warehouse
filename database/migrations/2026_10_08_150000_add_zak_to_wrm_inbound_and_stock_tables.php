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
        if (Schema::hasTable('wrm_stock_inbound_temp_upload') && !Schema::hasColumn('wrm_stock_inbound_temp_upload', 'zak')) {
            Schema::table('wrm_stock_inbound_temp_upload', function (Blueprint $table) {
                $table->decimal('zak', 15, 2)->nullable()->after('qty');
            });
        }

        if (Schema::hasTable('wrm_stock_inbound_details') && !Schema::hasColumn('wrm_stock_inbound_details', 'zak')) {
            Schema::table('wrm_stock_inbound_details', function (Blueprint $table) {
                $table->decimal('zak', 15, 2)->nullable()->after('qty');
            });
        }

        if (Schema::hasTable('wrm_stock_on_hand') && !Schema::hasColumn('wrm_stock_on_hand', 'zak')) {
            Schema::table('wrm_stock_on_hand', function (Blueprint $table) {
                $table->decimal('zak', 15, 2)->nullable()->after('qty');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('wrm_stock_inbound_temp_upload') && Schema::hasColumn('wrm_stock_inbound_temp_upload', 'zak')) {
            Schema::table('wrm_stock_inbound_temp_upload', function (Blueprint $table) {
                $table->dropColumn('zak');
            });
        }

        if (Schema::hasTable('wrm_stock_inbound_details') && Schema::hasColumn('wrm_stock_inbound_details', 'zak')) {
            Schema::table('wrm_stock_inbound_details', function (Blueprint $table) {
                $table->dropColumn('zak');
            });
        }

        if (Schema::hasTable('wrm_stock_on_hand') && Schema::hasColumn('wrm_stock_on_hand', 'zak')) {
            Schema::table('wrm_stock_on_hand', function (Blueprint $table) {
                $table->dropColumn('zak');
            });
        }
    }
};

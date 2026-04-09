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
        Schema::table('wrm_stock_inbound_details', function (Blueprint $table) {
            $table->decimal('qty', 15, 2)->change();
        });

        Schema::table('wrm_stock_inbound_temp_upload', function (Blueprint $table) {
            $table->decimal('qty', 15, 2)->change();
        });

        Schema::table('wrm_stock_draft_outbound_details', function (Blueprint $table) {
            $table->decimal('qty', 15, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wrm_stock_inbound_details', function (Blueprint $table) {
            $table->integer('qty')->change();
        });

        Schema::table('wrm_stock_inbound_temp_upload', function (Blueprint $table) {
            $table->integer('qty')->change();
        });

        Schema::table('wrm_stock_draft_outbound_details', function (Blueprint $table) {
            $table->integer('qty')->change();
        });
    }
};

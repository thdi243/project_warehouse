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
        Schema::table('wrm_stock_draft_outbound_details', function (Blueprint $table) {
            $table->decimal('qty_request', 15, 2)->nullable()->after('qty');
            $table->string('batch_id', 50)->nullable()->after('qty_request');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wrm_stock_draft_outbound_details', function (Blueprint $table) {
            $table->dropColumn(['qty_request', 'batch_id']);
        });
    }
};

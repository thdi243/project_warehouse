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
        Schema::table('wrm_stock_draft_outbound', function (Blueprint $table) {
            $table->unsignedBigInteger('driver_id')->nullable()->after('checklist_kondisi');
            $table->string('status_transfer')->default('PENDING')->after('driver_id'); // 'PENDING', 'ASSIGNED', 'COMPLETED'

            $table->foreign('driver_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wrm_stock_draft_outbound', function (Blueprint $table) {
            $table->dropForeign(['driver_id']);
            $table->dropColumn(['driver_id', 'status_transfer']);
        });
    }
};

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
        Schema::table('vehicle_transactions', function (Blueprint $table) {
            $table->dateTime('follow_up_time')->nullable()->after('timbangan_out_by');
            $table->string('follow_up_target')->nullable()->after('follow_up_time');
            $table->text('follow_up_notes')->nullable()->after('follow_up_target');
            $table->unsignedBigInteger('follow_up_by')->nullable()->after('follow_up_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_transactions', function (Blueprint $table) {
            $table->dropColumn(['follow_up_time', 'follow_up_target', 'follow_up_notes', 'follow_up_by']);
        });
    }
};

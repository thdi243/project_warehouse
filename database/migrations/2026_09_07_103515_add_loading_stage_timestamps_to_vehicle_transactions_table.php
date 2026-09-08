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
            $table->dateTime('queue_taken_time')->nullable()->after('no_antrian');
            $table->dateTime('start_loading_time')->nullable()->after('unloading_status');
            $table->dateTime('finish_loading_time')->nullable()->after('start_loading_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_transactions', function (Blueprint $table) {
            $table->dropColumn(['queue_taken_time', 'start_loading_time', 'finish_loading_time']);
        });
    }
};

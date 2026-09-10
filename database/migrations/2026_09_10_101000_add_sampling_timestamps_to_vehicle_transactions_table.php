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
            $table->dateTime('start_sampling_time')->nullable()->after('qc_status');
            $table->dateTime('finish_sampling_time')->nullable()->after('start_sampling_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_transactions', function (Blueprint $table) {
            $table->dropColumn(['start_sampling_time', 'finish_sampling_time']);
        });
    }
};

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
            $table->timestamp('last_loading_alert_at')->nullable()->after('follow_up_by');
            $table->timestamp('last_duration_alert_at')->nullable()->after('last_loading_alert_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_transactions', function (Blueprint $table) {
            $table->dropColumn(['last_loading_alert_at', 'last_duration_alert_at']);
        });
    }
};

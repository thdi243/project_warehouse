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
            $table->timestamp('notif_qc_doc_at')->nullable()->after('last_duration_alert_at');
            $table->timestamp('notif_qc_dock_at')->nullable()->after('notif_qc_doc_at');
            $table->timestamp('notif_qc_release_at')->nullable()->after('notif_qc_dock_at');
            $table->timestamp('notif_queue_taken_at')->nullable()->after('notif_qc_release_at');
            $table->timestamp('notif_prepare_at')->nullable()->after('notif_queue_taken_at');
            $table->timestamp('notif_completed_at')->nullable()->after('notif_prepare_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_transactions', function (Blueprint $table) {
            $table->dropColumn([
                'notif_qc_doc_at',
                'notif_qc_dock_at',
                'notif_qc_release_at',
                'notif_queue_taken_at',
                'notif_prepare_at',
                'notif_completed_at',
            ]);
        });
    }
};

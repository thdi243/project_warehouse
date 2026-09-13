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
            $table->foreignId('queue_taken_by')->nullable()->after('queue_taken_time')->constrained('users')->nullOnDelete();
            $table->foreignId('start_sampling_by')->nullable()->after('start_sampling_time')->constrained('users')->nullOnDelete();
            $table->foreignId('finish_sampling_by')->nullable()->after('finish_sampling_time')->constrained('users')->nullOnDelete();
            $table->foreignId('start_loading_by')->nullable()->after('start_loading_time')->constrained('users')->nullOnDelete();
            $table->foreignId('finish_loading_by')->nullable()->after('finish_loading_time')->constrained('users')->nullOnDelete();
            $table->foreignId('timbangan_out_by')->nullable()->after('timbangan_out_time')->constrained('users')->nullOnDelete();
            $table->foreignId('check_out_by')->nullable()->after('check_out_time')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_transactions', function (Blueprint $table) {
            $table->dropForeign(['queue_taken_by']);
            $table->dropForeign(['start_sampling_by']);
            $table->dropForeign(['finish_sampling_by']);
            $table->dropForeign(['start_loading_by']);
            $table->dropForeign(['finish_loading_by']);
            $table->dropForeign(['timbangan_out_by']);
            $table->dropForeign(['check_out_by']);

            $table->dropColumn([
                'queue_taken_by',
                'start_sampling_by',
                'finish_sampling_by',
                'start_loading_by',
                'finish_loading_by',
                'timbangan_out_by',
                'check_out_by',
            ]);
        });
    }
};

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
        Schema::create('kempu_main', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kempu_master_id')->constrained('kempu_master')->cascadeOnDelete()->unique();
            $table->string('id_kempu', 100)->index();
            $table->string('current_location', 50)->default('WPM');
            $table->string('current_status', 50)->default('QC_PM_PENDING');
            $table->unsignedInteger('reused_count')->default(0);
            $table->unsignedInteger('max_reused')->default(21);
            $table->string('condition', 50)->default('OK');
            $table->timestamp('last_scanned_at')->nullable();
            $table->string('last_action', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kempu_main');
    }
};

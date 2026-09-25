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
        Schema::create('kempu_tracking_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kempu_master_id')->nullable()->constrained('kempu_master')->cascadeOnDelete();
            $table->string('id_kempu', 100)->index();
            $table->string('stage', 50);
            $table->string('action', 50);
            $table->string('action_result', 50)->nullable();
            $table->string('from_location', 50)->nullable();
            $table->string('to_location', 50)->nullable();
            $table->unsignedInteger('reused_count')->default(0);
            $table->string('condition', 50)->default('OK');
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kempu_tracking_history');
    }
};

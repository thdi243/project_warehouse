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
        Schema::create('wfg_sop_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sop_id')->constrained('wfg_sop')->onDelete('cascade');
            $table->foreignId('approver_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['pending', 'read', 'approved', 'rejected'])->default('pending');
            $table->dateTime('action_at')->nullable();
            $table->foreignId('action_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['sop_id', 'approver_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wfg_sop_approvals');
    }
};

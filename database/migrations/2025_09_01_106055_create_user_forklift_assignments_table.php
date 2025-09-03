<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_forklift_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('forklift_id')->constrained()->onDelete('cascade');
            $table->boolean('is_primary')->default(false);
            $table->date('assigned_date')->default(now());
            $table->foreignId('assigned_by')->nullable()->constrained('users');
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'forklift_id'], 'unique_user_forklift');

            // bikin index supaya query lebih cepat
            $table->index(['forklift_id', 'is_primary'], 'idx_forklift_primary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_forklift_assignments');
    }
};

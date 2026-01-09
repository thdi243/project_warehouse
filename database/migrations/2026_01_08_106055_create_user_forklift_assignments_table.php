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

            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');

            $table->foreignId('forklift_id')
                ->constrained()
                ->onDelete('cascade');

            // operator level: 1, 2, 3
            $table->unsignedTinyInteger('operator_type');

            $table->date('assigned_date')->useCurrent();

            $table->foreignId('assigned_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // 1 forklift tidak boleh punya operator level sama
            // $table->unique(['forklift_id', 'operator_type'], 'unique_forklift_operator');

            // index bantu query
            $table->index(['forklift_id', 'is_active']);
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

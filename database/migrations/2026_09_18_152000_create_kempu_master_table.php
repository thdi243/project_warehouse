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
        Schema::create('kempu_master', function (Blueprint $table) {
            $table->id();
            $table->string('id_kempu', 100)->unique();
            $table->string('rfid', 100)->nullable();
            $table->string('status', 50)->default('active');    // active, in_use, maintenance, damaged
            $table->text('keterangan')->nullable();
            $table->date('gr_date')->nullable();
            $table->string('no_spb')->nullable();
            $table->unsignedInteger('print_count')->default(0);
            $table->timestamp('printed_at')->nullable();
            $table->foreignId('printed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kempu_master');
    }
};

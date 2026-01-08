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
        Schema::create('pallet_movers', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_unit', 10);
            $table->string('departemen')->nullable();
            $table->string('section')->nullable();
            $table->enum('status', ['active', 'maintenance', 'inactive'])->default('active');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pallet_movers');
    }
};

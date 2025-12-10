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
        Schema::create('wsp_rak', function (Blueprint $table) {
            $table->id();
            $table->string('area_rak', 20);
            $table->string('nama_rak', 20);
            $table->integer('kolom_rak');
            $table->integer('level_rak');
            $table->string('box_rak', 20)->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['area_rak', 'nama_rak', 'kolom_rak', 'level_rak', 'box_rak']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wsp_rak');
    }
};

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
        Schema::create('rak', function (Blueprint $table) {
            $table->id();
            $table->string('kode_rak', 10);
            $table->string('nama_rak', 10);
            $table->integer('kolom_rak');
            $table->integer('level_rak');
            $table->string('box_rak', 10)->nullable();
            $table->timestamps();

            $table->unique(['kode_rak', 'nama_rak', 'kolom_rak', 'level_rak', 'box_rak']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rak');
    }
};

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
        Schema::create('wfg_soh', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')->constrained('wfg_barang')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('qty_soh')->default(0);
            $table->integer('qty_unrest')->default(0);
            $table->integer('qty_qi')->default(0);
            $table->integer('qty_block')->default(0);
            $table->datetime('last_updated')->nullable();
            $table->string('principal')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wfg_soh');
    }
};

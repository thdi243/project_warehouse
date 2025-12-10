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
        Schema::create('wsp_stock_on_hand', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')->constrained('wsp_barang')->onDelete('cascade');
            $table->integer('qty_soh')->default(0);
            $table->integer('unrest')->default(0);
            $table->integer('qual_insp')->default(0);
            $table->integer('blocked')->default(0);
            $table->integer('transf')->default(0);
            $table->datetime('last_update');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wsp_stock_on_hand');
    }
};

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
        Schema::create('wrm_stock_by_date', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')->constrained('wrm_master_barang')->onDelete('cascade');
            $table->date('tanggal');
            $table->decimal('qty', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['barang_id', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wrm_stock_by_date');
    }
};

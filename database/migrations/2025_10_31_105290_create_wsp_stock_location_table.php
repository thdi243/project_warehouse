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
        Schema::create('wsp_stock_location', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')->constrained('wsp_barang')->onDelete('cascade');
            $table->foreignId('rak_id')->constrained('wsp_rak')->onDelete('cascade');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_location');
    }
};

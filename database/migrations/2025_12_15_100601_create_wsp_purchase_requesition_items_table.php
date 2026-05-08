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
        Schema::create('wsp_purchase_requesition_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pr_id')->constrained('wsp_purchase_requesition')->onDelete('cascade');
            $table->foreignId('barang_id')->constrained('wsp_barang')->onDelete('restrict');
            $table->integer('qty');
            $table->integer('qty_book_soh')->nullable();
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wsp_purchase_requesition_items');
    }
};

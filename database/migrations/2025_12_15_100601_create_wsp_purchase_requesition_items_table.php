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
            $table->enum('jenis', ['pr', 'blocked'])->default('pr');
            $table->integer('qty')->default(0);
            $table->string('keterangan')->nullable();
            $table->string('alasan')->nullable();
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

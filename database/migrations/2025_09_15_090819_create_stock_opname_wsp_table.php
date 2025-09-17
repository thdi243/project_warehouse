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
        Schema::create('stock_opname_wsp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rak_id')->constrained('rak')->onDelete('cascade');
            $table->foreignId('barang_id')->constrained('barang')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('stock_sistem')->default(0); // dari stock_barang_rak
            $table->integer('stock_fisik')->default(0);  // hasil input user
            $table->integer('selisih')->default(0);      // stock_fisik - stock_sistem
            $table->text('keterangan')->nullable();
            $table->date('tgl_opname');
            $table->timestamps();

            // Relasi
            // $table->foreign('rak_id')->references('id')->on('rak')->onDelete('cascade');
            // $table->foreign('barang_id')->references('id')->on('barang')->onDelete('cascade');
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_opname_wsp');
    }
};

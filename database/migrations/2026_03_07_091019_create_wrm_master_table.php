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
        Schema::create('wrm_master_location', function (Blueprint $table) {
            $table->id();
            $table->string('gudang');
            $table->string('bin');
            $table->string('s_loc');
            $table->string('plant');
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });

        Schema::create('wrm_master_barang', function (Blueprint $table) {
            $table->id();
            $table->integer('mid');
            $table->string('nama_barang');
            $table->string('uom');
            $table->foreignId('loc_id')->constrained('wrm_master_location')->onDelete('restrict');
            $table->string('qty_kg')->nullable(); // Kg/Pallet
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wrm_master_barang');
        Schema::dropIfExists('wrm_master_location');
    }
};

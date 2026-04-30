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
        Schema::create('wrm_stock_on_hand', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('no_spb')->nullable();
            $table->dateTime('incoming_date');
            $table->dateTime('expired_date')->nullable();
            $table->string('supplier')->nullable();
            $table->foreignId('barang_id')->constrained('wrm_master_barang')->onDelete('cascade');
            $table->bigInteger('barcode')->nullable();
            $table->integer('pallet_id')->nullable();
            $table->string('group')->nullable();
            $table->integer('qty');
            $table->string('status')->nullable();
            $table->foreignId('loc_id')->constrained('wrm_master_bin')->onDelete('restrict');
            $table->text('catatan')->nullable();
            $table->string('pallet')->nullable()->nullable();
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
        Schema::dropIfExists('wrm_stock_on_hand');
    }
};

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
        Schema::create('wrm_stock_gula', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')->constrained('wrm_master_barang')->onDelete('cascade');
            $table->bigInteger('no_spb');
            $table->integer('pallet_id');
            $table->date('incoming_date');
            $table->string('group');
            $table->integer('qty');
            $table->string('supplier');
            $table->string('status');
            $table->string('gudang');
            $table->string('loc');
            $table->text('catatan')->nullable();
            $table->text('transaksi')->nullable();
            $table->date('expired_date')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wrm_stock_gula');
    }
};

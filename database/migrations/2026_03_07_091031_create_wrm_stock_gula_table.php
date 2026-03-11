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
            $table->string('supplier')->nullable();
            $table->string('status');
            $table->foreignId('loc_id')->constrained('wrm_master_location')->onDelete('restrict');
            $table->text('catatan')->nullable();
            $table->string('pallet')->nullable();
            $table->date('issued_date')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });

        // Schema::create('wrm_stock_gula_transaksi', function (Blueprint $table) {
        //     $table->id();
        //     $table->foreignId('stock_gula_id')->constrained('wrm_stock_gula')->onDelete('cascade');
        //     $table->enum('jenis', ['inbound', 'outbound', 'adjustment']); // inbound atau outbound
        //     $table->integer('qty');
        //     $table->date('tanggal');
        //     $table->text('catatan')->nullable();
        //     $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
        //     $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('cascade');
        //     $table->timestamps();
        // });

        Schema::create('wrm_stock_gula_temp_upload', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('barcode');
            $table->bigInteger('no_spb');
            $table->bigInteger('mid');
            $table->integer('pallet_id');
            $table->integer('qty');
            $table->string('group');
            $table->string('status');
            $table->date('incoming_date');
            $table->string('supplier')->nullable();
            $table->string('pallet')->nullable();
            $table->string('catatan')->nullable();
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
        Schema::dropIfExists('wrm_stock_gula_temp_upload');
        Schema::dropIfExists('wrm_stock_gula');
    }
};

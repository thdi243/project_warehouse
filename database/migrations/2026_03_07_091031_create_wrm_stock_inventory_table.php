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
        Schema::create('wrm_stock_inbound', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('no_spb');
            $table->dateTime('incoming_date');
            $table->dateTime('expired_date')->nullable();
            $table->string('supplier')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });

        Schema::create('wrm_stock_inbound_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inbound_id')->constrained('wrm_stock_inbound')->onDelete('cascade');
            $table->foreignId('barang_id')->constrained('wrm_master_barang')->onDelete('cascade');
            $table->bigInteger('barcode')->nullable();
            $table->integer('pallet_id');
            $table->string('group')->nullable();
            $table->integer('qty');
            $table->string('status');
            $table->foreignId('loc_id')->constrained('wrm_master_bin')->onDelete('restrict');
            $table->text('catatan')->nullable();
            $table->string('pallet')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });

        Schema::create('wrm_stock_draft_outbound', function (Blueprint $table) {
            $table->id();
            $table->string('no_reservasi')->nullable();
            $table->string('shift')->nullable();
            $table->dateTime('reservasi_date')->nullable();
            $table->bigInteger('qty_request')->nullable();
            $table->text('checklist_kondisi')->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });

        Schema::create('wrm_stock_draft_outbound_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outbound_id')->constrained('wrm_stock_draft_outbound')->onDelete('cascade');
            $table->foreignId('barang_id')->constrained('wrm_master_barang')->onDelete('cascade');
            $table->bigInteger('barcode');
            $table->bigInteger('no_spb');
            $table->string('supplier')->nullable();
            $table->dateTime('incoming_date');
            $table->integer('pallet_id');
            $table->string('group');
            $table->integer('qty');
            $table->string('status');
            $table->dateTime('expired_date')->nullable();
            $table->foreignId('loc_id')->constrained('wrm_master_bin')->onDelete('restrict');
            $table->text('catatan')->nullable();
            $table->string('pallet')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });

        Schema::create('wrm_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')->constrained('wrm_master_barang')->onDelete('cascade');
            $table->foreignId('loc_id')->constrained('wrm_master_location')->onDelete('restrict');
            $table->dateTime('tanggal');
            $table->decimal('qty', 15, 2);
            $table->enum('jenis', ['in', 'out', 'transfer']);
            $table->string('ref_type');
            $table->unsignedBigInteger('ref_id');
            $table->text('catatan')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });

        Schema::create('wrm_stock_balance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')->constrained('wrm_master_barang')->onDelete('cascade');
            $table->foreignId('loc_id')->constrained('wrm_master_location')->onDelete('restrict');
            $table->decimal('qty', 15, 2);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });

        Schema::create('wrm_stock_inbound_temp_upload', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('barcode');
            $table->bigInteger('no_spb');
            $table->bigInteger('mid');
            $table->integer('pallet_id');
            $table->integer('qty');
            $table->string('group');
            // $table->string('status');
            $table->dateTime('incoming_date');
            $table->dateTime('expired_date')->nullable();
            // $table->string('supplier')->nullable();
            // $table->string('pallet')->nullable();
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
        Schema::dropIfExists('wrm_stock_inbound_temp_upload');
        Schema::dropIfExists('wrm_stock_balance');
        Schema::dropIfExists('wrm_stock_movements');
        Schema::dropIfExists('wrm_stock_draft_outbound_details');
        Schema::dropIfExists('wrm_stock_draft_outbound');
        Schema::dropIfExists('wrm_stock_inbound_details');
        Schema::dropIfExists('wrm_stock_inbound');
    }
};

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
        Schema::create('wrm_stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->date('tgl_gr')->nullable();
            $table->string('no_reservasi')->nullable();
            $table->date('tgl_reservasi')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });

        // 2. Create wrm_stock_transfer_details
        Schema::create('wrm_stock_transfer_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transfer_id')->constrained('wrm_stock_transfers')->onDelete('cascade');
            $table->bigInteger('no_barcode')->nullable();
            $table->bigInteger('no_spb')->nullable();
            $table->date('tgl_gi')->nullable();
            $table->string('matdoc_gi')->nullable();
            $table->string('plant', 10)->nullable();
            $table->string('sloc', 10)->nullable();
            $table->foreignId('barang_id')->constrained('wrm_master_barang')->onDelete('cascade');
            $table->string('grade', 10)->nullable();
            $table->decimal('qty_barcode', 15, 2)->default(0);
            $table->decimal('qty_actual', 15, 2)->default(0);
            $table->decimal('qty_susut_simpan', 15, 2)->default(0);
            $table->string('uom', 10)->nullable();
            $table->integer('lama_simpan')->default(0);
            $table->decimal('persen_susut', 10, 2)->default(0);
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wrm_stock_transfer_details');
        Schema::dropIfExists('wrm_stock_transfers');
    }
};

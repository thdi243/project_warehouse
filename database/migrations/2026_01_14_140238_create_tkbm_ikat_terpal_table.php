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
        Schema::create('tkbm_ikat_terpal', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->foreignId('produk_id')->constrained('tkbm_produk_ikat_terpal')->onDelete('restrict');
            $table->foreignId('fee_id')->constrained('tkbm_fee_ikat_terpal')->onDelete('restrict')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->integer('qty_pallet');
            $table->integer('jml_buruh')->nullable();
            $table->decimal('subtotal_barang', 14, 2);
            $table->decimal('total_fee', 14, 2);
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index('tanggal');
            $table->index('produk_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tkbm_ikat_terpal');
    }
};

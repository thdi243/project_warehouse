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
        Schema::create('tkbm_produk_ikat_terpal', function (Blueprint $table) {
            $table->id();
            $table->decimal('harga_pallet', 12, 2);
            $table->string('satuan', 20)->default('pallet');
            $table->text('keterangan')->nullable();
            $table->boolean('aktif')->default(true);
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tkbm_produk_ikat_terpal');
    }
};

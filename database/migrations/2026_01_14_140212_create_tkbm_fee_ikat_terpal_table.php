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
        Schema::create('tkbm_fee_ikat_terpal', function (Blueprint $table) {
            $table->id();
            $table->decimal('fee', 12, 2);
            $table->decimal('ppn', 12, 2)->default(0);
            $table->decimal('pph', 12, 2)->default(0);
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
        Schema::dropIfExists('tkbm_fee_ikat_terpal');
    }
};

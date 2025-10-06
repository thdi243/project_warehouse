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
        Schema::create('totals_tkbm', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('year');
            $table->integer('total_terpal')->default(0);
            $table->integer('total_slipsheet')->default(0);
            $table->integer('total_pallet')->default(0);
            $table->decimal('total_produk', 15, 2)->default(0);
            $table->decimal('total_fee', 15, 2)->default(0);
            $table->decimal('total_ppn', 15, 2)->default(0);
            $table->decimal('total_pph', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['month', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('totals_tkbm');
    }
};

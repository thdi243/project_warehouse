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
        Schema::create('tkbm', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('petugas');
            $table->string('shift');
            $table->bigInteger('qty_terpal')->nullable();
            $table->bigInteger('qty_slipsheet')->nullable();
            $table->bigInteger('qty_pallet')->nullable();
            $table->bigInteger('jml_tkbm');
            $table->string('keterangan')->nullable();
            $table->bigInteger('total_qty')->nullable();
            $table->bigInteger('total_fee')->nullable();
            $table->float('fee_id')
                ->nullable()
                ->constrained('tkbm_fee')
                ->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tkbm');
    }
};

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
        Schema::create('wsp_stock_reservations', function (Blueprint $table) {
            $table->id();
            $table->string('mid_barang');
            $table->integer('qty');
            $table->string('session_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('status', ['booked', 'confirmed', 'released', 'cancelled'])->default('booked');
            $table->dateTime('reserved_at');
            $table->dateTime('expired_at');
            $table->dateTime('confirmed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wsp_stock_reservations');
    }
};

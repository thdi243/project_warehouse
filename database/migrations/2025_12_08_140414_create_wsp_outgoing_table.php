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
        Schema::create('wsp_outgoing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('mid');
            $table->string('nama_barang');
            $table->string('s_loc');
            $table->string('unit');
            $table->bigInteger('material_doc');
            $table->date('posting_date');
            $table->integer('qty');
            $table->integer('mvt');
            $table->string('vendor')->nullable();
            $table->integer('batch')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wsp_outgoing');
    }
};

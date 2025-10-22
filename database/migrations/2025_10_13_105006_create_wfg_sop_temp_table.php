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
        Schema::create('wfg_sop_temp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('soh_id')->nullable()->constrained('wfg_soh')->onDelete('cascade');
            $table->foreignId('barang_id')->constrained('wfg_barang')->onDelete('cascade');
            $table->integer('qty_full');
            $table->integer('qty_receh');
            $table->integer('summary');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade'); // siapa yang input
            $table->date('tgl_opname')->nullable();
            $table->string('principal')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wfg_sop_temp');
    }
};

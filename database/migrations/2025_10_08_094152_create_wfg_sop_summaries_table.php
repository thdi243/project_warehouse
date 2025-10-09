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
        Schema::create('wfg_sop_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sop_id')->constrained('wfg_sop')->onDelete('cascade');
            $table->foreignId('barang_id')->constrained('wfg_barang')->onDelete('cascade');
            $table->decimal('qty_fisik', 10, 2);
            $table->decimal('qty_sistem', 10, 2);
            $table->decimal('selisih', 10, 2)->default(0);
            $table->string('status');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['sop_id', 'barang_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wfg_sop_summaries');
    }
};

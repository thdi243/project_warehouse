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
        Schema::create('wfg_sop_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sop_id')->constrained('wfg_sop')->onDelete('cascade');
            $table->foreignId('barang_id')->constrained('wfg_barang')->onDelete('cascade');
            $table->decimal('qty_full', 10, 2)->default(0);
            $table->decimal('qty_receh', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wfg_sop_detail');
    }
};

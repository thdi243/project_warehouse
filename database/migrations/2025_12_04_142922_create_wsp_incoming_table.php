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
        Schema::create('wsp_incoming', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('request_date');
            $table->bigInteger('pr_number');
            $table->integer('mid');
            $table->string('nama_barang');
            $table->string('text');
            $table->string('requisitio');
            $table->string('recipient');
            $table->string('cc_email');
            $table->bigInteger('po_number');
            $table->date('po_date');
            $table->integer('gr_qty');
            $table->date('gr_date');
            $table->bigInteger('material_doc');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wsp_incoming');
    }
};

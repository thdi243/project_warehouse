<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('wfg_loading_order_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loading_order_id')->constrained('wfg_loading_orders')->cascadeOnDelete();
            $table->foreignId('material_id')->constrained('wfg_barang')->cascadeOnDelete();
            $table->string('batch_number')->nullable();
            $table->enum('jenis', ['P', 'R'])->default('P')->comment('P = Pallet, R = Receh');
            $table->integer('qty')->default(0);

            // Flags
            $table->string('to_dummy')->nullable();
            $table->string('to_sap')->nullable();
            $table->boolean('double_po')->default(false);
            $table->boolean('cancel_to')->default(false);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('wfg_loading_order_details');
    }
};

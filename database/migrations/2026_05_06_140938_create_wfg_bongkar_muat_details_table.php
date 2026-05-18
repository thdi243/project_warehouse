<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('wfg_bongkar_muat_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bongkar_muat_id')->nullable()->constrained('wfg_bongkar_muats')->cascadeOnDelete();
            $table->foreignId('material_id')->nullable()->constrained('wfg_barang')->cascadeOnDelete();
            $table->string('batch_number')->nullable();
            $table->enum('jenis', ['P', 'R'])->nullable()->default('P')->comment('P = Pallet, R = Receh');
            $table->integer('qty')->nullable()->default(0);

            // Flags
            $table->string('to_dummy')->nullable();
            $table->string('to_sap')->nullable();
            $table->boolean('double_po')->nullable()->default(false);
            $table->boolean('cancel_to')->nullable()->default(false);
            $table->boolean('manual_picking')->nullable()->default(false);
            $table->string('no_to')->nullable();
            $table->integer('qty_to')->nullable();


            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('wfg_bongkar_muat_details');
    }
};

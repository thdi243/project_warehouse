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
        Schema::create('kempu_cycle_fillings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kempu_master_id')->index();
            $table->string('id_kempu', 50)->index();
            $table->unsignedSmallInteger('reused_count')->default(0)->index();
            $table->boolean('has_barcode')->default(true);
            $table->boolean('has_rfid')->default(true);
            $table->boolean('has_nti')->default(true);
            $table->string('no_po', 100)->nullable();
            $table->string('foto_1', 255)->nullable();
            $table->string('foto_2', 255)->nullable();
            $table->string('foto_3', 255)->nullable();
            $table->string('foto_4', 255)->nullable();
            $table->timestamp('filled_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->unique(['kempu_master_id', 'reused_count'], 'uniq_kempu_reused_cycle');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kempu_cycle_fillings');
    }
};

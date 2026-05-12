<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('wfg_loading_orders', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('no_dokumen')->unique();
            $table->string('shipment_smu')->nullable();
            $table->string('wavepick_smu')->nullable();
            $table->string('shipment_bas')->nullable();
            $table->string('wavepick_bas')->nullable();
            $table->foreignId('forklift_driver_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('destinasi_id')->constrained('wfg_master_destinasi')->cascadeOnDelete();
            $table->string('no_mobil')->nullable();
            $table->string('gate')->nullable();
            $table->string('no_kontainer')->nullable();
            $table->string('no_segel_bas')->nullable();
            $table->string('no_segel_vendor')->nullable();
            $table->integer('jumlah_slipsheet')->default(0);
            $table->time('jam_muat')->nullable();

            // Workflow fields
            $table->enum('status', ['draft', 'submitted', 'approved', 'loaded', 'verified', 'rejected'])->default('draft');

            // Approval fields
            $table->foreignId('checker_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->longText('checker_signature')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->string('driver_name')->nullable();
            $table->longText('driver_signature')->nullable();
            $table->dateTime('driver_approved_at')->nullable();

            // Validation fields
            $table->foreignId('verified_by')->nullable()->constrained('users')->cascadeOnDelete();
            $table->dateTime('verified_at')->nullable();
            $table->text('verified_note')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('wfg_loading_orders');
    }
};

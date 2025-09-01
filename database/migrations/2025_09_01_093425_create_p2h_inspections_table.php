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
        Schema::create('p2h_forklift', function (Blueprint $table) {
            $table->id(); // id INT AUTO_INCREMENT PRIMARY KEY
            $table->date('tanggal');
            $table->string('nomor_unit', 100);
            $table->string('dept', 100);
            $table->boolean('cek_baterai')->default(0);
            $table->boolean('cek_fork')->default(0);
            $table->boolean('kondisi_body_kebersihan')->default(0);
            $table->boolean('lampu_kiri')->default(0);
            $table->boolean('lampu_kanan')->default(0);
            $table->boolean('lampu_sorot')->default(0);
            $table->boolean('lampu_sign_depan_kanan')->default(0);
            $table->boolean('lampu_sign_depan_kiri')->default(0);
            $table->boolean('kipas_belakang')->default(0);
            $table->boolean('rantai_lift')->default(0);
            $table->boolean('sistem_hidrolik')->default(0);
            $table->boolean('kondisi_axle')->default(0);
            $table->boolean('sistem_kemudi')->default(0);
            $table->boolean('panel_display')->default(0);
            $table->integer('jam_operasional')->nullable();
            $table->boolean('air_aki')->default(0);
            $table->boolean('klakson')->default(0);
            $table->boolean('buzzer_mundur')->default(0);
            $table->boolean('kaca_spion')->default(0);
            $table->boolean('kondisi_ban')->default(0);
            $table->boolean('fungsi_rem')->default(0);
            $table->integer('shift')->nullable();
            $table->string('operator_name', 150)->nullable();
            $table->string('catatan')->nullable();
            $table->string('jenis_p2h', 50)->nullable();
            $table->float('persentase');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('p2h_inspections');
    }
};

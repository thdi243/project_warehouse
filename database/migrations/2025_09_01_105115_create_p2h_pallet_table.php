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
        Schema::create('p2h_pallet', function (Blueprint $table) {
            $table->id(); // AUTO_INCREMENT PRIMARY KEY
            $table->date('tanggal');
            $table->string('nomor_unit', 50);
            $table->string('dept', 50);
            $table->string('jenis_p2h', 50);

            // Checklist fields (default 0 = false)
            $table->integer('check_air_accu')->default(0);
            $table->integer('check_battery')->default(0);
            $table->integer('check_body_unit')->default(0);
            $table->integer('check_klakson')->default(0);
            $table->integer('check_roda')->default(0);
            $table->integer('check_sistem_kemudi')->default(0);
            $table->integer('check_kebersihan_unit')->default(0);
            $table->integer('check_kunci_pm')->default(0);
            $table->integer('check_hydraulic')->default(0);

            $table->string('shift', 20)->nullable();
            $table->string('operator_name', 100)->nullable();
            $table->string('catatan', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('p2h_pallet');
    }
};

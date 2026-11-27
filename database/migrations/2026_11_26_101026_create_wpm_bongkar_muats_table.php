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
        Schema::create('wpm_bongkar_muats', function (Blueprint $table) {
            $table->id();
            $table->string('tipe')->default('bongkar');
            $table->date('tanggal');
            $table->string('no_dokumen')->nullable()->unique();
            $table->string('no_spkb')->nullable();
            $table->string('no_ecp')->nullable();
            $table->string('sloc')->default('C001');
            $table->string('supplier_vendor')->nullable();
            $table->string('no_segel_bas')->nullable();
            $table->string('jumlah_tkbm')->nullable();
            $table->string('no_mobil')->nullable();
            $table->string('driver_name')->nullable();
            $table->string('gate')->nullable();
            $table->string('jenis_pengiriman')->default('Lokal');
            $table->time('jam_serah_terima_qc')->nullable();
            $table->time('jam_release_qc')->nullable();
            $table->time('jam_muat')->nullable(); // Jam mulai bongkar
            $table->time('jam_selesai')->nullable();
            $table->string('no_kontainer')->nullable();
            $table->foreignId('forklift_driver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('checker_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('draft'); // draft, submitted, approved, finished, verified, rejected
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('driver_approved_at')->nullable();
            $table->foreignId('qc_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('qc_approved_at')->nullable();
            $table->string('qc_signature')->nullable();
            $table->string('checker_signature')->nullable();
            $table->string('driver_signature')->nullable();
            $table->string('admin_signature')->nullable();
            $table->string('foreman_signature')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('verified_note')->nullable();
            $table->string('verified_signature')->nullable();
            $table->json('inspection_data')->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('wpm_bongkar_muat_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wpm_bongkar_muat_id')->constrained('wpm_bongkar_muats')->cascadeOnDelete();
            $table->foreignId('material_id')->nullable()->constrained('wpm_master_barang')->nullOnDelete();
            $table->string('mid')->nullable();
            $table->string('nama_barang')->nullable();
            $table->string('uom')->nullable()->default('BOX');
            $table->decimal('qty', 12, 2)->default(0);
            $table->string('batch_number')->nullable();
            $table->string('jenis', 10)->default('P');
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wpm_bongkar_muat_details');
        Schema::dropIfExists('wpm_bongkar_muats');
    }
};

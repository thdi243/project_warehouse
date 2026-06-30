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
        // 1. wpm_master_barang
        Schema::create('wpm_master_barang', function (Blueprint $table) {
            $table->id();
            $table->string('mid')->unique();
            $table->string('nama_barang');
            $table->string('uom');
            $table->decimal('qty_pallet', 12, 2)->default(0);
            $table->timestamps();
        });

        // 2. wpm_soh
        Schema::create('wpm_soh', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')->constrained('wpm_master_barang')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('qty_soh')->default(0);
            $table->integer('qty_unrest')->default(0);
            $table->integer('qty_qi')->default(0);
            $table->integer('qty_block')->default(0);
            $table->datetime('last_updated')->nullable();
            $table->timestamps();
        });

        // 3. wpm_so
        Schema::create('wpm_so', function (Blueprint $table) {
            $table->id();
            $table->date('tgl_opname');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('status')->default('draft');
            $table->string('no_doc')->nullable();
            $table->timestamps();
        });

        // 4. wpm_so_detail
        Schema::create('wpm_so_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('so_id')->constrained('wpm_so')->onDelete('cascade');
            $table->foreignId('barang_id')->constrained('wpm_master_barang')->onDelete('cascade');
            $table->integer('qty_full')->default(0);
            $table->integer('qty_receh')->default(0);
            $table->timestamps();
        });

        // 5. wpm_so_summaries
        Schema::create('wpm_so_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('so_id')->constrained('wpm_so')->onDelete('cascade');
            $table->foreignId('barang_id')->constrained('wpm_master_barang')->onDelete('cascade');
            $table->integer('qty_fisik')->default(0);
            $table->integer('qty_sistem')->default(0);
            $table->integer('selisih')->default(0);
            $table->string('status')->nullable(); // 'lebih', 'kurang', 'match'
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['so_id', 'barang_id'], 'wpm_so_summaries_so_barang_unique');
        });

        // 6. wpm_so_temp
        Schema::create('wpm_so_temp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('soh_id')->nullable()->constrained('wpm_soh')->onDelete('cascade');
            $table->foreignId('barang_id')->constrained('wpm_master_barang')->onDelete('cascade');
            $table->integer('qty_full')->default(0);
            $table->integer('qty_receh')->default(0);
            $table->integer('summary')->default(0);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->date('tgl_opname');
            $table->timestamps();
        });

        // 7. wpm_so_temp_note
        Schema::create('wpm_so_temp_note', function (Blueprint $table) {
            $table->id();
            $table->foreignId('soh_id')->nullable()->constrained('wpm_soh')->onDelete('cascade');
            $table->foreignId('barang_id')->constrained('wpm_master_barang')->onDelete('cascade');
            $table->text('catatan');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->date('tgl_opname');
            $table->timestamps();
        });

        // 8. wpm_so_status
        Schema::create('wpm_so_status', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('tgl_opname');
            $table->string('status')->default('idle'); // 'started', 'finished'
            $table->timestamps();
        });

        // 9. wpm_so_approvals
        Schema::create('wpm_so_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('so_id')->constrained('wpm_so')->onDelete('cascade');
            $table->foreignId('approver_id')->constrained('users')->onDelete('cascade');
            $table->string('status')->default('pending');
            $table->datetime('action_at')->nullable();
            $table->foreignId('action_by')->nullable()->constrained('users')->onDelete('cascade');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wpm_so_approvals');
        Schema::dropIfExists('wpm_so_status');
        Schema::dropIfExists('wpm_so_temp_note');
        Schema::dropIfExists('wpm_so_temp');
        Schema::dropIfExists('wpm_so_summaries');
        Schema::dropIfExists('wpm_so_detail');
        Schema::dropIfExists('wpm_so');
        Schema::dropIfExists('wpm_soh');
        Schema::dropIfExists('wpm_master_barang');
    }
};

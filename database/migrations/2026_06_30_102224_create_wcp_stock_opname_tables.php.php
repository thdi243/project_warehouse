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
        // 1. wcp_master_barang
        Schema::create('wcp_master_barang', function (Blueprint $table) {
            $table->id();
            $table->string('mid')->unique();
            $table->string('nama_barang');
            $table->string('uom');
            $table->decimal('qty_pallet', 12, 2)->default(0);
            $table->timestamps();
        });

        // 2. wcp_soh
        Schema::create('wcp_soh', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')->constrained('wcp_master_barang')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('qty_soh')->default(0);
            $table->integer('qty_unrest')->default(0);
            $table->integer('qty_qi')->default(0);
            $table->integer('qty_block')->default(0);
            $table->datetime('last_updated')->nullable();
            $table->timestamps();
        });

        // 3. wcp_so
        Schema::create('wcp_so', function (Blueprint $table) {
            $table->id();
            $table->date('tgl_opname');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('status')->default('draft');
            $table->string('no_doc')->nullable();
            $table->timestamps();
        });

        // 4. wcp_so_detail
        Schema::create('wcp_so_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('so_id')->constrained('wcp_so')->onDelete('cascade');
            $table->foreignId('barang_id')->constrained('wcp_master_barang')->onDelete('cascade');
            $table->integer('qty_full')->default(0);
            $table->integer('qty_receh')->default(0);
            $table->timestamps();
        });

        // 5. wcp_so_summaries
        Schema::create('wcp_so_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('so_id')->constrained('wcp_so')->onDelete('cascade');
            $table->foreignId('barang_id')->constrained('wcp_master_barang')->onDelete('cascade');
            $table->integer('qty_fisik')->default(0);
            $table->integer('qty_sistem')->default(0);
            $table->integer('selisih')->default(0);
            $table->string('status')->nullable(); // 'lebih', 'kurang', 'match'
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['so_id', 'barang_id'], 'wcp_so_summaries_so_barang_unique');
        });

        // 6. wcp_so_temp
        Schema::create('wcp_so_temp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('soh_id')->nullable()->constrained('wcp_soh')->onDelete('cascade');
            $table->foreignId('barang_id')->constrained('wcp_master_barang')->onDelete('cascade');
            $table->integer('qty_full')->default(0);
            $table->integer('qty_receh')->default(0);
            $table->integer('summary')->default(0);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->date('tgl_opname');
            $table->timestamps();
        });

        // 7. wcp_so_temp_note
        Schema::create('wcp_so_temp_note', function (Blueprint $table) {
            $table->id();
            $table->foreignId('soh_id')->nullable()->constrained('wcp_soh')->onDelete('cascade');
            $table->foreignId('barang_id')->constrained('wcp_master_barang')->onDelete('cascade');
            $table->text('catatan');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->date('tgl_opname');
            $table->timestamps();
        });

        // 8. wcp_so_status
        Schema::create('wcp_so_status', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('tgl_opname');
            $table->string('status')->default('idle'); // 'started', 'finished'
            $table->timestamps();
        });

        // 9. wcp_so_approvals
        Schema::create('wcp_so_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('so_id')->constrained('wcp_so')->onDelete('cascade');
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
        Schema::dropIfExists('wcp_so_approvals');
        Schema::dropIfExists('wcp_so_status');
        Schema::dropIfExists('wcp_so_temp_note');
        Schema::dropIfExists('wcp_so_temp');
        Schema::dropIfExists('wcp_so_summaries');
        Schema::dropIfExists('wcp_so_detail');
        Schema::dropIfExists('wcp_so');
        Schema::dropIfExists('wcp_soh');
        Schema::dropIfExists('wcp_master_barang');
    }
};

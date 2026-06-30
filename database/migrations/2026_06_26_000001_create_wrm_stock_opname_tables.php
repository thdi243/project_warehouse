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
        // 1. wrm_soh
        Schema::create('wrm_soh', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')->constrained('wrm_master_barang')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->bigInteger('no_spb')->nullable();
            $table->integer('qty_soh')->default(0);
            $table->integer('qty_unrest')->default(0);
            $table->integer('qty_qi')->default(0);
            $table->integer('qty_block')->default(0);
            $table->datetime('last_updated')->nullable();
            $table->timestamps();
        });

        // 2. wrm_so
        Schema::create('wrm_so', function (Blueprint $table) {
            $table->id();
            $table->date('tgl_opname');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('status')->default('draft');
            $table->timestamps();
        });

        // 3. wrm_so_detail
        Schema::create('wrm_so_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('so_id')->constrained('wrm_so')->onDelete('cascade');
            $table->foreignId('barang_id')->constrained('wrm_master_barang')->onDelete('cascade');
            $table->bigInteger('no_spb')->nullable();
            $table->integer('qty_full')->default(0);
            $table->integer('qty_receh')->default(0);
            $table->timestamps();
        });

        // 4. wrm_so_summaries
        Schema::create('wrm_so_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('so_id')->constrained('wrm_so')->onDelete('cascade');
            $table->foreignId('barang_id')->constrained('wrm_master_barang')->onDelete('cascade');
            $table->bigInteger('no_spb')->nullable();
            $table->integer('qty_fisik')->default(0);
            $table->integer('qty_sistem')->default(0);
            $table->integer('selisih')->default(0);
            $table->string('status')->nullable(); // 'lebih', 'kurang', 'match'
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['so_id', 'barang_id', 'no_spb'], 'wrm_so_summaries_so_barang_spb_unique');
        });

        // 5. wrm_so_temp
        Schema::create('wrm_so_temp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('soh_id')->nullable()->constrained('wrm_soh')->onDelete('cascade');
            $table->foreignId('barang_id')->constrained('wrm_master_barang')->onDelete('cascade');
            $table->bigInteger('no_spb')->nullable();
            $table->integer('qty_full')->default(0);
            $table->integer('qty_receh')->default(0);
            $table->integer('summary')->default(0);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->date('tgl_opname');
            $table->timestamps();
        });

        // 6. wrm_so_temp_note
        Schema::create('wrm_so_temp_note', function (Blueprint $table) {
            $table->id();
            $table->foreignId('soh_id')->nullable()->constrained('wrm_soh')->onDelete('cascade');
            $table->foreignId('barang_id')->constrained('wrm_master_barang')->onDelete('cascade');
            $table->bigInteger('no_spb')->nullable();
            $table->text('catatan');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->date('tgl_opname');
            $table->timestamps();
        });

        // 7. wrm_so_status
        Schema::create('wrm_so_status', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('tgl_opname');
            $table->string('status')->default('idle'); // 'started', 'finished'
            $table->timestamps();
        });

        Schema::create('wrm_so_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('so_id')->constrained('wrm_so')->onDelete('cascade');
            $table->foreignId('approver_id')->constrained('users')->onDelete('cascade');
            $table->string('status')->default('pending'); // 'pending', 'approved', 'rejected'
            $table->dateTime('action_at')->nullable();
            $table->foreignId('action_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['so_id', 'approver_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wrm_so_approvals');
        Schema::dropIfExists('wrm_so_status');
        Schema::dropIfExists('wrm_so_temp_note');
        Schema::dropIfExists('wrm_so_temp');
        Schema::dropIfExists('wrm_so_summaries');
        Schema::dropIfExists('wrm_so_detail');
        Schema::dropIfExists('wrm_so');
        Schema::dropIfExists('wrm_soh');
    }
};

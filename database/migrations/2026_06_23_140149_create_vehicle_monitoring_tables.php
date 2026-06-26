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
        // 1. locations
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('s_loc')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. vehicles
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('no_pol')->unique();
            $table->string('vendor')->nullable();
            $table->timestamps();
        });

        // 3. vehicle_items (Master Item/SKU)
        Schema::create('vehicle_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // 4. vehicle_transactions
        Schema::create('vehicle_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('no_transaction')->unique();
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('restrict');
            $table->string('jenis'); // bongkaran, slipsheet, curah
            $table->string('vendor')->nullable();
            $table->foreignId('item_id')->nullable()->constrained('vehicle_items')->onDelete('restrict');
            $table->string('no_spb')->nullable();
            $table->decimal('qty_spb', 12, 2)->nullable();
            $table->foreignId('target_location_id')->constrained('locations')->onDelete('restrict');
            $table->foreignId('current_location_id')->constrained('locations')->onDelete('restrict');
            $table->string('status'); // timbangan_in, wpm_qc, wrm_bongkar, wfg_muat, smu, timbangan_out, completed
            $table->string('qc_status')->default('pending'); // pending, released, rejected
            $table->string('unloading_status')->default('pending'); // pending, unloading, completed
            $table->string('no_antrian')->nullable();
            $table->dateTime('check_in_time');
            $table->dateTime('check_out_time')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });

        // 5. vehicle_tracking
        Schema::create('vehicle_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_transaction_id')->constrained('vehicle_transactions')->onDelete('cascade');
            $table->foreignId('location_id')->constrained('locations')->onDelete('restrict');
            $table->dateTime('arrival_time');
            $table->dateTime('departure_time')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->string('status_notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_tracking');
        Schema::dropIfExists('vehicle_transactions');
        Schema::dropIfExists('vehicle_items');
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('locations');
    }
};

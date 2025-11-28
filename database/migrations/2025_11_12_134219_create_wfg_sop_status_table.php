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
        Schema::create('wfg_sop_status', function (Blueprint $table) {
            $table->id();
            $table->date('tgl_opname');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('status')->default('draft');
            $table->string('mode')->default('normal');
            $table->string('principal')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'tgl_opname']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wfg_sop_status');
    }
};

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
        Schema::create('wsp_purchase_requesition', function (Blueprint $table) {
            $table->id();
            $table->string('pr_number')->nullable();
            $table->date('pr_date');
            $table->string('hal')->nullable();
            $table->string('no_doc')->nullable();
            $table->string('requested_by')->nullable();
            $table->string('department');
            $table->string('jenis')->nullable();
            $table->string('detail_jenis')->nullable();
            $table->string('no_io')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'finished'])->default('pending');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wsp_purchase_requesition');
    }
};

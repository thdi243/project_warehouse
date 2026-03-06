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
        Schema::create('wsp_purchase_requesition_item_approval', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pr_item_id')
                ->constrained('wsp_purchase_requesition_items')
                ->cascadeOnDelete();

            $table->foreignId('approval_id')
                ->constrained('wsp_purchase_requesition_approval')
                ->cascadeOnDelete();

            $table->enum('status', ['pending', 'approved', 'rejected'])
                ->default('pending');

            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wsp_purchase_requesition_item_approval');
    }
};

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
        Schema::table('wsp_stock_reservations', function (Blueprint $table) {
            $table->index(['status', 'expired_at']);
            $table->index(['status', 'type', 'confirmed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wsp_stock_reservations', function (Blueprint $table) {
            $table->dropIndex(['status', 'expired_at']);
            $table->dropIndex(['status', 'type', 'confirmed_at']);
        });
    }
};

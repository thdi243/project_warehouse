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
        Schema::table('kempu_main', function (Blueprint $table) {
            $table->boolean('has_barcode')->default(true)->after('condition');
            $table->boolean('has_rfid')->default(true)->after('has_barcode');
            $table->boolean('has_nti')->default(true)->after('has_rfid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kempu_main', function (Blueprint $table) {
            $table->dropColumn(['has_barcode', 'has_rfid', 'has_kitir']);
        });
    }
};

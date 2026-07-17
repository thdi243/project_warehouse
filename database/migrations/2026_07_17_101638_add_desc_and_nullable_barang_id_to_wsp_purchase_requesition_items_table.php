<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wsp_purchase_requesition_items', function (Blueprint $table) {
            $table->unsignedBigInteger('barang_id')->nullable()->change();
            $table->text('desc')->nullable()->after('qty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wsp_purchase_requesition_items', function (Blueprint $table) {
            $table->unsignedBigInteger('barang_id')->nullable(false)->change();
            $table->dropColumn('desc');
        });
    }
};

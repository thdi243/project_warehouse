<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wsp_barang', function (Blueprint $table) {
            $table->decimal('qty_pallet', 12, 2)->default(1)->after('uom');
        });
    }

    public function down(): void
    {
        Schema::table('wsp_barang', function (Blueprint $table) {
            $table->dropColumn('qty_pallet');
        });
    }
};

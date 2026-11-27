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
        Schema::table('wfg_bongkar_muats', function (Blueprint $table) {
            $table->string('no_ecp')->nullable()->after('no_spkb');
            $table->string('jumlah_tkbm')->nullable()->after('no_ecp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wfg_bongkar_muats', function (Blueprint $table) {
            $table->dropColumn(['no_ecp', 'jumlah_tkbm']);
        });
    }
};

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
            $table->string('no_po', 100)->nullable()->after('condition');
            $table->string('foto_1', 255)->nullable()->after('no_po');
            $table->string('foto_2', 255)->nullable()->after('foto_1');
            $table->string('foto_3', 255)->nullable()->after('foto_2');
            $table->string('foto_4', 255)->nullable()->after('foto_3');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kempu_main', function (Blueprint $table) {
            $table->dropColumn(['no_po', 'foto_1', 'foto_2', 'foto_3', 'foto_4']);
        });
    }
};

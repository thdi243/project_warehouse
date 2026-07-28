<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add plant and s_loc columns to wsp_rak
        Schema::table('wsp_rak', function (Blueprint $table) {
            $table->string('plant', 50)->nullable()->after('id');
            $table->string('s_loc', 50)->nullable()->after('plant');
        });

        // 2. Set default values for existing records
        DB::table('wsp_rak')->update([
            'plant' => '1006',
            's_loc' => 'G001',
        ]);

        // 3. Make plant and s_loc not nullable
        Schema::table('wsp_rak', function (Blueprint $table) {
            $table->string('plant', 50)->nullable(false)->change();
            $table->string('s_loc', 50)->nullable(false)->change();
        });

        // 4. Update the unique key constraint
        Schema::table('wsp_rak', function (Blueprint $table) {
            // Drop old coordinate unique constraint
            $table->dropUnique(['area_rak', 'nama_rak', 'kolom_rak', 'level_rak', 'box_rak']);

            // Create new unique constraint with plant and s_loc
            $table->unique(['plant', 's_loc', 'area_rak', 'nama_rak', 'kolom_rak', 'level_rak', 'box_rak'], 'wsp_rak_full_coordinate_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wsp_rak', function (Blueprint $table) {
            // Drop new unique index
            $table->dropUnique('wsp_rak_full_coordinate_unique');

            // Re-create old unique index
            $table->unique(['area_rak', 'nama_rak', 'kolom_rak', 'level_rak', 'box_rak']);

            // Drop plant and s_loc columns
            $table->dropColumn(['plant', 's_loc']);
        });
    }
};

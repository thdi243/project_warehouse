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
        // 1. Add detail_loc column
        Schema::table('wsp_rak', function (Blueprint $table) {
            $table->string('detail_loc', 100)->nullable()->after('s_loc');
        });

        // 2. Populate detail_loc from existing columns
        $existingRows = DB::table('wsp_rak')->get();
        foreach ($existingRows as $row) {
            $parts = array_filter([
                $row->area_rak ?? '',
                $row->nama_rak ?? '',
                isset($row->kolom_rak) && isset($row->level_rak)
                    ? ($row->kolom_rak . '.' . $row->level_rak . '.' . ($row->box_rak ?? '000'))
                    : ($row->box_rak ?? '')
            ]);
            $detailLoc = !empty($parts) ? implode('-', $parts) : 'LOC-' . $row->id;

            DB::table('wsp_rak')->where('id', $row->id)->update([
                'detail_loc' => $detailLoc,
            ]);
        }

        // 3. Make detail_loc not nullable
        Schema::table('wsp_rak', function (Blueprint $table) {
            $table->string('detail_loc', 100)->nullable(false)->change();
        });

        // 4. Update unique key constraint
        Schema::table('wsp_rak', function (Blueprint $table) {
            // Drop old unique index
            $table->dropUnique('wsp_rak_full_coordinate_unique');

            // Drop old columns
            $table->dropColumn(['area_rak', 'nama_rak', 'kolom_rak', 'level_rak', 'box_rak']);

            // Create new unique index
            $table->unique(['plant', 's_loc', 'detail_loc'], 'wsp_rak_plant_sloc_detail_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wsp_rak', function (Blueprint $table) {
            // Drop new unique index
            $table->dropUnique('wsp_rak_plant_sloc_detail_unique');

            // Re-create old columns
            $table->string('area_rak', 20)->nullable()->after('s_loc');
            $table->string('nama_rak', 20)->nullable()->after('area_rak');
            $table->integer('kolom_rak')->nullable()->after('nama_rak');
            $table->integer('level_rak')->nullable()->after('kolom_rak');
            $table->string('box_rak', 20)->nullable()->after('level_rak');

            // Drop detail_loc
            $table->dropColumn('detail_loc');

            // Re-create old unique index
            $table->unique(['plant', 's_loc', 'area_rak', 'nama_rak', 'kolom_rak', 'level_rak', 'box_rak'], 'wsp_rak_full_coordinate_unique');
        });
    }
};

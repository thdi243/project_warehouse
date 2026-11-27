<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wfg_bongkar_muats', function (Blueprint $table) {
            if (!Schema::hasColumn('wfg_bongkar_muats', 'tipe')) {
                $table->string('tipe', 20)->default('muat')->after('id')->index();
            }
            if (!Schema::hasColumn('wfg_bongkar_muats', 'no_spkb')) {
                $table->string('no_spkb')->nullable()->after('no_dokumen');
            }
            if (!Schema::hasColumn('wfg_bongkar_muats', 'grup')) {
                $table->string('grup', 100)->nullable()->after('no_spkb');
            }
            if (!Schema::hasColumn('wfg_bongkar_muats', 'sloc')) {
                $table->string('sloc', 50)->nullable()->after('grup');
            }
            if (!Schema::hasColumn('wfg_bongkar_muats', 'supplier_vendor')) {
                $table->string('supplier_vendor')->nullable()->after('sloc');
            }
            if (!Schema::hasColumn('wfg_bongkar_muats', 'tujuan_bongkar')) {
                $table->string('tujuan_bongkar')->nullable()->after('destinasi_id');
            }
            if (!Schema::hasColumn('wfg_bongkar_muats', 'slipsheet_in')) {
                $table->string('slipsheet_in', 50)->nullable()->after('jumlah_slipsheet');
            }
            if (!Schema::hasColumn('wfg_bongkar_muats', 'slipsheet_out')) {
                $table->string('slipsheet_out', 50)->nullable()->after('slipsheet_in');
            }
            if (!Schema::hasColumn('wfg_bongkar_muats', 'no_kartu')) {
                $table->string('no_kartu', 100)->nullable()->after('slipsheet_out');
            }
            if (!Schema::hasColumn('wfg_bongkar_muats', 'jenis_pengiriman')) {
                $table->string('jenis_pengiriman', 30)->nullable()->after('no_kartu');
            }
            if (!Schema::hasColumn('wfg_bongkar_muats', 'mobil_in')) {
                $table->string('mobil_in', 30)->nullable()->after('no_mobil');
            }
            if (!Schema::hasColumn('wfg_bongkar_muats', 'mobil_out')) {
                $table->string('mobil_out', 30)->nullable()->after('mobil_in');
            }
            if (!Schema::hasColumn('wfg_bongkar_muats', 'jam_serah_terima_qc')) {
                $table->string('jam_serah_terima_qc', 30)->nullable()->after('mobil_out');
            }
            if (!Schema::hasColumn('wfg_bongkar_muats', 'jam_release_qc')) {
                $table->string('jam_release_qc', 30)->nullable()->after('jam_serah_terima_qc');
            }
            if (!Schema::hasColumn('wfg_bongkar_muats', 'inspection_data')) {
                $table->json('inspection_data')->nullable()->after('verified_note');
            }
        });

        Schema::table('wfg_bongkar_muat_details', function (Blueprint $table) {
            if (!Schema::hasColumn('wfg_bongkar_muat_details', 'uom')) {
                $table->string('uom', 30)->nullable()->after('jenis');
            }
            if (!Schema::hasColumn('wfg_bongkar_muat_details', 'keterangan')) {
                $table->string('keterangan')->nullable()->after('qty_to');
            }
        });
    }

    public function down(): void
    {
        Schema::table('wfg_bongkar_muats', function (Blueprint $table) {
            $cols = [
                'tipe',
                'no_spkb',
                'grup',
                'sloc',
                'supplier_vendor',
                'tujuan_bongkar',
                'slipsheet_in',
                'slipsheet_out',
                'no_kartu',
                'jenis_pengiriman',
                'mobil_in',
                'mobil_out',
                'jam_serah_terima_qc',
                'jam_release_qc',
                'inspection_data',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('wfg_bongkar_muats', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('wfg_bongkar_muat_details', function (Blueprint $table) {
            foreach (['uom', 'keterangan'] as $col) {
                if (Schema::hasColumn('wfg_bongkar_muat_details', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

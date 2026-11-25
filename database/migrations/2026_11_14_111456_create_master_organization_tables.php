<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Master Departemen
        Schema::create('master_departemens', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique();
            $table->string('nama', 100);
            $table->text('deskripsi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Master Bagian
        Schema::create('master_bagians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('departemen_id')->constrained('master_departemens')->cascadeOnDelete();
            $table->string('kode', 30)->nullable();
            $table->string('nama', 100);
            $table->text('deskripsi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Master Jabatan
        Schema::create('master_jabatans', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique();
            $table->string('nama', 100);
            $table->text('deskripsi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seeding Data Awal Departemen Berdasarkan Data di Users
        $initialDepts = [
            ['kode' => 'WRH', 'nama' => 'Warehouse', 'deskripsi' => 'Gudang Utama & Logistik'],
            ['kode' => 'ENG', 'nama' => 'Engineering', 'deskripsi' => 'Departemen Pemeliharaan & Teknik'],
            ['kode' => 'PRD', 'nama' => 'Produksi', 'deskripsi' => 'Departemen Operasional Produksi'],
            ['kode' => 'QCB', 'nama' => 'Quality Control', 'deskripsi' => 'Departemen Penjaminan Mutu'],
            ['kode' => 'XPE', 'nama' => 'Expedisi', 'deskripsi' => 'Departemen Pengiriman & Ekspedisi'],
            ['kode' => 'PPC', 'nama' => 'PPIC', 'deskripsi' => 'Production Planning & Inventory Control'],
            ['kode' => 'HRD', 'nama' => 'HRD / GA', 'deskripsi' => 'Human Resources & General Affairs'],
            ['kode' => 'FAT', 'nama' => 'Finance & Accounting', 'deskripsi' => 'Keuangan dan Akuntansi'],
            ['kode' => 'ITF', 'nama' => 'IT & Digitalization', 'deskripsi' => 'Information Technology & Facility'],
        ];

        foreach ($initialDepts as $d) {
            DB::table('master_departemens')->insert(array_merge($d, [
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Seeding Data Awal Bagian
        $deptMap = DB::table('master_departemens')->pluck('id', 'kode')->toArray();

        $initialBagians = [
            // Warehouse
            ['kode' => 'WSP', 'nama' => 'Warehouse Sparepart', 'dept_kode' => 'WRH'],
            ['kode' => 'WFG', 'nama' => 'Warehouse Finish Goods', 'dept_kode' => 'WRH'],
            ['kode' => 'WRM', 'nama' => 'Warehouse Raw Material', 'dept_kode' => 'WRH'],
            ['kode' => 'WCP', 'nama' => 'Warehouse Packaging', 'dept_kode' => 'WRH'],
            // Engineering
            ['kode' => 'UTL', 'nama' => 'Engineering Utility', 'dept_kode' => 'ENG'],
            ['kode' => 'MEC', 'nama' => 'Engineering Production', 'dept_kode' => 'ENG'],
            ['kode' => 'WSH', 'nama' => 'Engineering Workshop', 'dept_kode' => 'ENG'],
            // Quality Control
            ['kode' => 'QCP', 'nama' => 'Quality Control Proses', 'dept_kode' => 'QCB'],
            ['kode' => 'QCR', 'nama' => 'Quality Control RMPM', 'dept_kode' => 'QCB'],
            // Produksi
            ['kode' => 'PCK', 'nama' => 'Produksi Packing', 'dept_kode' => 'PRD'],
            ['kode' => 'PRC', 'nama' => 'Produksi Processing', 'dept_kode' => 'PRD'],
            // Expedisi
            ['kode' => 'XPD', 'nama' => 'Expedisi Distribusi', 'dept_kode' => 'XPE'],
            // PPIC
            ['kode' => 'INV', 'nama' => 'PPIC Inventory', 'dept_kode' => 'PPC'],
        ];

        foreach ($initialBagians as $b) {
            if (isset($deptMap[$b['dept_kode']])) {
                DB::table('master_bagians')->insert([
                    'departemen_id' => $deptMap[$b['dept_kode']],
                    'kode' => $b['kode'],
                    'nama' => $b['nama'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Seeding Data Awal Jabatan
        $initialJabatans = [
            ['kode' => 'OPR', 'nama' => 'Operator', 'deskripsi' => 'Pelaksana teknis lapangan'],
            ['kode' => 'STF', 'nama' => 'Staff / Admin', 'deskripsi' => 'Administrasi dan staf operasional'],
            ['kode' => 'FRM', 'nama' => 'Foreman', 'deskripsi' => 'Pengawas lini kerja'],
            ['kode' => 'SPV', 'nama' => 'Supervisor', 'deskripsi' => 'Penyelia bagian / shift'],
            ['kode' => 'AST', 'nama' => 'Asisten Manager', 'deskripsi' => 'Asisten kepala departemen'],
            ['kode' => 'DH', 'nama' => 'Dept Head', 'deskripsi' => 'Kepala departemen'],
            ['kode' => 'MGR', 'nama' => 'Manager', 'deskripsi' => 'Manajer divisi / departemen'],
            ['kode' => 'ADM', 'nama' => 'Administrator', 'deskripsi' => 'Administrator sistem'],
        ];

        foreach ($initialJabatans as $j) {
            DB::table('master_jabatans')->insert(array_merge($j, [
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_bagians');
        Schema::dropIfExists('master_departemens');
        Schema::dropIfExists('master_jabatans');
    }
};

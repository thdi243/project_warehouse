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
        // 1. Tambah kolom pr_number di wsp_purchase_requesition_items
        if (Schema::hasTable('wsp_purchase_requesition_items')) {
            Schema::table('wsp_purchase_requesition_items', function (Blueprint $table) {
                if (!Schema::hasColumn('wsp_purchase_requesition_items', 'pr_number')) {
                    $table->string('pr_number')->nullable()->after('status');
                }
            });
        }

        // 2. Update enum status di wsp_purchase_requesition agar menyertakan 'partial'
        if (Schema::hasTable('wsp_purchase_requesition')) {
            DB::statement("ALTER TABLE wsp_purchase_requesition MODIFY COLUMN status ENUM('pending', 'approved', 'partial', 'rejected', 'finished') NOT NULL DEFAULT 'pending'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('wsp_purchase_requesition_items')) {
            Schema::table('wsp_purchase_requesition_items', function (Blueprint $table) {
                if (Schema::hasColumn('wsp_purchase_requesition_items', 'pr_number')) {
                    $table->dropColumn('pr_number');
                }
            });
        }

        if (Schema::hasTable('wsp_purchase_requesition')) {
            DB::statement("ALTER TABLE wsp_purchase_requesition MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'finished') NOT NULL DEFAULT 'pending'");
        }
    }
};

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
        if (Schema::hasTable('wsp_purchase_requesition_approval')) {
            Schema::table('wsp_purchase_requesition_approval', function (Blueprint $table) {
                if (!Schema::hasColumn('wsp_purchase_requesition_approval', 'last_wa_follow_up_at')) {
                    $table->dateTime('last_wa_follow_up_at')->nullable()->after('ttd');
                }
            });
        }

        if (!Schema::hasTable('wsp_pr_wa_logs')) {
            Schema::create('wsp_pr_wa_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pr_id')->constrained('wsp_purchase_requesition')->onDelete('cascade');
                $table->foreignId('approval_id')->nullable()->constrained('wsp_purchase_requesition_approval')->onDelete('cascade');
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('phone_number', 30)->index();
                $table->string('status', 30)->default('success');
                $table->text('message')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wsp_pr_wa_logs');

        if (Schema::hasTable('wsp_purchase_requesition_approval')) {
            Schema::table('wsp_purchase_requesition_approval', function (Blueprint $table) {
                if (Schema::hasColumn('wsp_purchase_requesition_approval', 'last_wa_follow_up_at')) {
                    $table->dropColumn('last_wa_follow_up_at');
                }
            });
        }
    }
};

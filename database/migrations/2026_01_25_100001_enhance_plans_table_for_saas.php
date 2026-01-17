<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the database migrations.
     */
    public function up(): void
    {
        // Enhance plans table with SaaS features
        Schema::table('plans', function (Blueprint $table) {
            if (!Schema::hasColumn('plans', 'max_invoices_per_month')) {
                $table->integer('max_invoices_per_month')->default(-1)->after('duration'); // -1 = unlimited
                $table->integer('max_users')->default(1)->after('max_invoices_per_month');
                $table->boolean('pos_enabled')->default(0)->after('max_users');
                $table->boolean('gst_reports_enabled')->default(0)->after('pos_enabled');
                $table->boolean('whatsapp_integration_enabled')->default(0)->after('gst_reports_enabled');
                $table->boolean('mobile_app_access')->default(1)->after('whatsapp_integration_enabled');
                $table->boolean('multi_branch_enabled')->default(0)->after('mobile_app_access');
                $table->json('additional_features')->nullable()->after('features');
            }
        });
    }

    /**
     * Reverse the database migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumnIfExists('max_invoices_per_month');
            $table->dropColumnIfExists('max_users');
            $table->dropColumnIfExists('pos_enabled');
            $table->dropColumnIfExists('gst_reports_enabled');
            $table->dropColumnIfExists('whatsapp_integration_enabled');
            $table->dropColumnIfExists('mobile_app_access');
            $table->dropColumnIfExists('multi_branch_enabled');
            $table->dropColumnIfExists('additional_features');
        });
    }
};

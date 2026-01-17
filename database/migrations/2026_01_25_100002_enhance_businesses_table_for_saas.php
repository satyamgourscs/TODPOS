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
        // Enhance businesses table for SaaS multi-store
        Schema::table('businesses', function (Blueprint $table) {
            if (!Schema::hasColumn('businesses', 'store_slug')) {
                $table->string('store_slug')->unique()->nullable()->after('companyName'); // URL slug
                $table->string('website_title')->nullable()->after('store_slug');
                $table->text('website_description')->nullable()->after('website_title');
                $table->string('website_logo')->nullable()->after('website_description');
                $table->string('website_banner')->nullable()->after('website_logo');
                $table->boolean('website_enabled')->default(1)->after('website_banner');
                $table->string('store_type')->default('billing')->after('website_enabled'); // billing, retail, services
                $table->boolean('status')->default(1)->after('store_type');
                $table->integer('invoice_count')->default(0)->after('status');
            }
        });
    }

    /**
     * Reverse the database migrations.
     */
    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumnIfExists('store_slug');
            $table->dropColumnIfExists('website_title');
            $table->dropColumnIfExists('website_description');
            $table->dropColumnIfExists('website_logo');
            $table->dropColumnIfExists('website_banner');
            $table->dropColumnIfExists('website_enabled');
            $table->dropColumnIfExists('store_type');
            $table->dropColumnIfExists('status');
            $table->dropColumnIfExists('invoice_count');
        });
    }
};

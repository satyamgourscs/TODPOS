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
        // Enhance users table for role management
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role_type')) {
                $table->enum('role_type', ['super_admin', 'store_owner', 'staff'])->default('staff')->after('role');
                $table->boolean('is_active')->default(1)->after('role_type');
                $table->timestamp('last_login_at')->nullable()->after('is_active');
            }
        });
    }

    /**
     * Reverse the database migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumnIfExists('role_type');
            $table->dropColumnIfExists('is_active');
            $table->dropColumnIfExists('last_login_at');
        });
    }
};

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
        Schema::create('store_website_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('theme_color')->default('#007bff');
            $table->string('primary_color')->default('#007bff');
            $table->string('secondary_color')->default('#6c757d');
            $table->boolean('show_products')->default(0);
            $table->boolean('show_inventory')->default(0);
            $table->boolean('enable_contact_form')->default(1);
            $table->boolean('show_payment_methods')->default(1);
            $table->string('contact_email')->nullable();
            $table->string('contact_whatsapp')->nullable();
            $table->text('custom_html')->nullable();
            $table->text('custom_css')->nullable();
            $table->json('social_links')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the database migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_website_settings');
    }
};

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StoreWebsiteSetting extends Model
{
    use HasFactory;

    protected $table = 'store_website_settings';

    protected $fillable = [
        'business_id',
        'theme_color',
        'primary_color',
        'secondary_color',
        'show_products',
        'show_inventory',
        'enable_contact_form',
        'show_payment_methods',
        'contact_email',
        'contact_whatsapp',
        'custom_html',
        'custom_css',
        'social_links',
    ];

    protected $casts = [
        'show_products' => 'boolean',
        'show_inventory' => 'boolean',
        'enable_contact_form' => 'boolean',
        'show_payment_methods' => 'boolean',
        'social_links' => 'array',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}

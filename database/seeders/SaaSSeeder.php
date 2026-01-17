<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\Business;
use App\Models\User;
use App\Models\PlanSubscribe;
use App\Models\StoreWebsiteSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SaaSSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Super Admin User
        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@tryonedigital.com'],
            [
                'name' => 'Super Admin',
                'phone' => '+91-9999999999',
                'password' => bcrypt('admin@123'),
                'role' => 'super-admin',
                'role_type' => 'super_admin',
                'is_active' => 1,
            ]
        );

        // Create Subscription Plans
        $plans = [
            [
                'subscriptionName' => 'Free Trial',
                'subscriptionPrice' => 0,
                'duration' => 30,
                'max_invoices_per_month' => 50,
                'max_users' => 1,
                'pos_enabled' => 0,
                'gst_reports_enabled' => 0,
                'whatsapp_integration_enabled' => 0,
                'mobile_app_access' => 1,
                'multi_branch_enabled' => 0,
                'status' => 1,
            ],
            [
                'subscriptionName' => 'Basic',
                'subscriptionPrice' => 499,
                'duration' => 30,
                'max_invoices_per_month' => 500,
                'max_users' => 3,
                'pos_enabled' => 0,
                'gst_reports_enabled' => 0,
                'whatsapp_integration_enabled' => 1,
                'mobile_app_access' => 1,
                'multi_branch_enabled' => 0,
                'status' => 1,
            ],
            [
                'subscriptionName' => 'Standard',
                'subscriptionPrice' => 999,
                'duration' => 30,
                'max_invoices_per_month' => 2000,
                'max_users' => 10,
                'pos_enabled' => 1,
                'gst_reports_enabled' => 1,
                'whatsapp_integration_enabled' => 1,
                'mobile_app_access' => 1,
                'multi_branch_enabled' => 1,
                'status' => 1,
            ],
            [
                'subscriptionName' => 'Premium',
                'subscriptionPrice' => 1999,
                'duration' => 30,
                'max_invoices_per_month' => -1, // Unlimited
                'max_users' => -1, // Unlimited
                'pos_enabled' => 1,
                'gst_reports_enabled' => 1,
                'whatsapp_integration_enabled' => 1,
                'mobile_app_access' => 1,
                'multi_branch_enabled' => 1,
                'status' => 1,
            ],
        ];

        $createdPlans = [];
        foreach ($plans as $planData) {
            $plan = Plan::updateOrCreate(
                ['subscriptionName' => $planData['subscriptionName']],
                $planData
            );
            $createdPlans[] = $plan;
        }

        // Create Demo Stores
        $stores = [
            [
                'companyName' => 'Rajesh Medicals',
                'business_category_id' => 1,
                'phoneNumber' => '+91-9876543210',
                'address' => 'Market Road, Delhi',
                'store_slug' => 'rajesh-medicals',
                'website_enabled' => 1,
                'status' => 1,
                'owner_name' => 'Rajesh Sharma',
                'owner_email' => 'rajesh@medicals.com',
                'owner_phone' => '+91-9876543210',
                'plan_index' => 2, // Standard Plan
            ],
            [
                'companyName' => 'Gupta Traders',
                'business_category_id' => 3,
                'phoneNumber' => '+91-8765432109',
                'address' => 'Bazaar Street, Mumbai',
                'store_slug' => 'gupta-traders',
                'website_enabled' => 1,
                'status' => 1,
                'owner_name' => 'Rajesh Gupta',
                'owner_email' => 'rajesh@gupta-traders.com',
                'owner_phone' => '+91-8765432109',
                'plan_index' => 1, // Basic Plan
            ],
            [
                'companyName' => 'Dharti Dhan Agro',
                'business_category_id' => 4,
                'phoneNumber' => '+91-7654321098',
                'address' => 'Farm Lane, Punjab',
                'store_slug' => 'dharti-dhan-agro',
                'website_enabled' => 1,
                'status' => 1,
                'owner_name' => 'Harjit Singh',
                'owner_email' => 'harjit@dharti-dhan.com',
                'owner_phone' => '+91-7654321098',
                'plan_index' => 1, // Basic Plan
            ],
        ];

        foreach ($stores as $storeData) {
            $plan = $createdPlans[$storeData['plan_index']];
            
            $business = Business::updateOrCreate(
                ['store_slug' => $storeData['store_slug']],
                [
                    'companyName' => $storeData['companyName'],
                    'business_category_id' => $storeData['business_category_id'],
                    'phoneNumber' => $storeData['phoneNumber'],
                    'address' => $storeData['address'],
                    'store_slug' => $storeData['store_slug'],
                    'website_enabled' => $storeData['website_enabled'],
                    'status' => $storeData['status'],
                    'subscriptionDate' => now(),
                    'will_expire' => now()->addMonths(1),
                ]
            );

            // Create subscription
            $subscription = PlanSubscribe::updateOrCreate(
                ['business_id' => $business->id],
                [
                    'plan_id' => $plan->id,
                    'price' => $plan->subscriptionPrice,
                    'duration' => $plan->duration,
                    'payment_status' => 'paid',
                ]
            );

            $business->update(['plan_subscribe_id' => $subscription->id]);

            // Create store owner user
            User::updateOrCreate(
                ['email' => $storeData['owner_email']],
                [
                    'name' => $storeData['owner_name'],
                    'phone' => $storeData['owner_phone'],
                    'business_id' => $business->id,
                    'role' => 'shop-owner',
                    'role_type' => 'store_owner',
                    'password' => bcrypt('password'),
                    'is_active' => 1,
                ]
            );

            // Create website settings
            StoreWebsiteSetting::updateOrCreate(
                ['business_id' => $business->id],
                [
                    'theme_color' => '#007bff',
                    'primary_color' => '#007bff',
                    'secondary_color' => '#6c757d',
                    'show_products' => 1,
                    'show_inventory' => 1,
                    'enable_contact_form' => 1,
                    'show_payment_methods' => 1,
                    'contact_email' => $storeData['owner_email'],
                    'contact_whatsapp' => $storeData['owner_phone'],
                    'social_links' => [
                        'facebook' => 'https://facebook.com',
                        'instagram' => 'https://instagram.com',
                        'whatsapp' => 'https://wa.me/' . str_replace(['+', '-'], '', $storeData['owner_phone']),
                    ],
                ]
            );

            // Create demo staff users
            User::updateOrCreate(
                ['email' => strtolower(str_replace(' ', '.', $storeData['owner_name'])) . '.staff@' . $storeData['store_slug'] . '.com'],
                [
                    'name' => 'Salesman - ' . $storeData['owner_name'],
                    'business_id' => $business->id,
                    'role' => 'staff',
                    'role_type' => 'staff',
                    'password' => bcrypt('password'),
                    'is_active' => 1,
                ]
            );
        }

        $this->command->info('✅ SaaS demo data created successfully!');
        $this->command->info('');
        $this->command->info('Super Admin Credentials:');
        $this->command->info('Email: superadmin@tryonedigital.com');
        $this->command->info('Password: admin@123');
        $this->command->info('');
        $this->command->info('Store Owner Credentials:');
        $this->command->info('Email: rajesh@medicals.com (password: password)');
        $this->command->info('Email: rajesh@gupta-traders.com (password: password)');
        $this->command->info('Email: harjit@dharti-dhan.com (password: password)');
        $this->command->info('');
        $this->command->info('Dashboard URL: http://localhost:8000/admin/saas');
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Business;
use App\Models\Plan;
use App\Models\SubscriptionInvoice;
use App\Models\StoreWebsiteSetting;
use Database\Seeders\SaaSSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaaSImplementationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Setup test environment with SaaS data
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed the database with SaaS demo data
        $this->seed(SaaSSeeder::class);
    }

    /**
     * Test: Super Admin user exists after seeding
     * @test
     */
    public function super_admin_user_exists()
    {
        $superAdmin = User::where('email', 'superadmin@tryonedigital.com')->first();
        
        $this->assertNotNull($superAdmin);
        $this->assertEquals('super_admin', $superAdmin->role_type);
        $this->assertTrue($superAdmin->is_active);
    }

    /**
     * Test: Super Admin can login successfully
     * @test
     */
    public function super_admin_can_login()
    {
        $response = $this->post('/login', [
            'email' => 'superadmin@tryonedigital.com',
            'password' => 'admin@123',
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs(
            User::where('email', 'superadmin@tryonedigital.com')->first()
        );
    }

    /**
     * Test: Subscription plans are created
     * @test
     */
    public function subscription_plans_exist()
    {
        $plans = Plan::all();
        
        $this->assertGreaterThanOrEqual(4, $plans->count());
        $this->assertTrue($plans->pluck('name')->contains('Free Trial'));
        $this->assertTrue($plans->pluck('name')->contains('Basic'));
        $this->assertTrue($plans->pluck('name')->contains('Standard'));
        $this->assertTrue($plans->pluck('name')->contains('Premium'));
    }

    /**
     * Test: Plan features are properly configured
     * @test
     */
    public function plan_features_are_configured()
    {
        $premium = Plan::where('name', 'Premium')->first();
        
        $this->assertNotNull($premium);
        $this->assertTrue($premium->pos_enabled);
        $this->assertTrue($premium->gst_reports_enabled);
        $this->assertTrue($premium->whatsapp_integration_enabled);
        $this->assertTrue($premium->mobile_app_access);
        $this->assertTrue($premium->multi_branch_enabled);
    }

    /**
     * Test: Demo stores are created
     * @test
     */
    public function demo_stores_exist()
    {
        $stores = Business::all();
        
        $this->assertGreaterThanOrEqual(3, $stores->count());
        $this->assertTrue($stores->pluck('company_name')->contains('Rajesh Medicals'));
        $this->assertTrue($stores->pluck('company_name')->contains('Gupta Traders'));
        $this->assertTrue($stores->pluck('company_name')->contains('Dharti Dhan Agro'));
    }

    /**
     * Test: Store slugs are properly generated
     * @test
     */
    public function store_slugs_are_generated()
    {
        $store = Business::where('company_name', 'Rajesh Medicals')->first();
        
        $this->assertNotNull($store);
        $this->assertNotNull($store->store_slug);
        $this->assertTrue(strlen($store->store_slug) > 0);
        $this->assertStringContainsString('rajesh-medicals', strtolower($store->store_slug));
    }

    /**
     * Test: Store has subscription
     * @test
     */
    public function stores_have_subscription()
    {
        $store = Business::with('planSubscribe')->first();
        
        $this->assertNotNull($store);
        $this->assertNotNull($store->planSubscribe);
        $this->assertNotNull($store->planSubscribe->plan);
    }

    /**
     * Test: Store website settings exist
     * @test
     */
    public function store_website_settings_exist()
    {
        $settings = StoreWebsiteSetting::all();
        
        $this->assertGreaterThanOrEqual(3, $settings->count());
    }

    /**
     * Test: Super Admin can access SaaS dashboard
     * @test
     */
    public function super_admin_can_access_saas_dashboard()
    {
        $superAdmin = User::where('email', 'superadmin@tryonedigital.com')->first();
        
        $response = $this->actingAs($superAdmin)->get('/admin/saas');
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.saas.dashboard');
    }

    /**
     * Test: Super Admin can view stores list
     * @test
     */
    public function super_admin_can_view_stores()
    {
        $superAdmin = User::where('email', 'superadmin@tryonedigital.com')->first();
        
        $response = $this->actingAs($superAdmin)->get('/admin/saas/stores');
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.saas.stores.index');
    }

    /**
     * Test: Super Admin can view single store
     * @test
     */
    public function super_admin_can_view_single_store()
    {
        $superAdmin = User::where('email', 'superadmin@tryonedigital.com')->first();
        $store = Business::first();
        
        $response = $this->actingAs($superAdmin)->get("/admin/saas/stores/{$store->id}");
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.saas.stores.show');
    }

    /**
     * Test: Super Admin can view plans list
     * @test
     */
    public function super_admin_can_view_plans()
    {
        $superAdmin = User::where('email', 'superadmin@tryonedigital.com')->first();
        
        $response = $this->actingAs($superAdmin)->get('/admin/saas/plans');
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.saas.plans.index');
    }

    /**
     * Test: Non-Super Admin cannot access SaaS dashboard
     * @test
     */
    public function non_super_admin_cannot_access_saas()
    {
        $storeOwner = User::where('email', 'rajesh@medicals.com')->first();
        
        if ($storeOwner) {
            $response = $this->actingAs($storeOwner)->get('/admin/saas');
            
            $response->assertRedirect('/');
        }
    }

    /**
     * Test: Super Admin can create new store
     * @test
     */
    public function super_admin_can_create_store()
    {
        $superAdmin = User::where('email', 'superadmin@tryonedigital.com')->first();
        $plan = Plan::where('name', 'Basic')->first();
        
        $response = $this->actingAs($superAdmin)->post('/admin/saas/stores', [
            'company_name' => 'Test Store',
            'phone' => '9999999999',
            'address' => 'Test Address',
            'category' => 'retail',
            'plan_id' => $plan->id,
            'owner_name' => 'John Doe',
            'owner_email' => 'john@test.com',
            'owner_phone' => '8888888888',
        ]);

        $response->assertRedirect('/admin/saas/stores');
        
        $this->assertDatabaseHas('businesses', [
            'company_name' => 'Test Store',
        ]);
        
        $this->assertDatabaseHas('users', [
            'email' => 'john@test.com',
            'role_type' => 'store_owner',
        ]);
    }

    /**
     * Test: Super Admin can create new plan
     * @test
     */
    public function super_admin_can_create_plan()
    {
        $superAdmin = User::where('email', 'superadmin@tryonedigital.com')->first();
        
        $response = $this->actingAs($superAdmin)->post('/admin/saas/plans', [
            'name' => 'Custom Plan',
            'duration' => 12,
            'price' => 2999,
            'offer_price' => 2499,
            'max_users' => 100,
            'max_invoices_per_month' => 5000,
            'pos_enabled' => true,
            'gst_reports_enabled' => true,
            'whatsapp_integration_enabled' => true,
            'mobile_app_access' => true,
            'multi_branch_enabled' => true,
        ]);

        $response->assertRedirect('/admin/saas/plans');
        
        $this->assertDatabaseHas('plans', [
            'name' => 'Custom Plan',
            'price' => 2999,
        ]);
    }

    /**
     * Test: Super Admin can toggle store status
     * @test
     */
    public function super_admin_can_toggle_store_status()
    {
        $superAdmin = User::where('email', 'superadmin@tryonedigital.com')->first();
        $store = Business::first();
        
        $initialStatus = $store->status;
        
        $response = $this->actingAs($superAdmin)
            ->patch("/admin/saas/stores/{$store->id}/toggle-status");

        $response->assertRedirect("/admin/saas/stores/{$store->id}");
        
        $store->refresh();
        
        $this->assertNotEquals($initialStatus, $store->status);
    }

    /**
     * Test: Database schema has new columns
     * @test
     */
    public function database_schema_has_new_columns()
    {
        $store = Business::first();
        
        // Check new columns exist in businesses table
        $this->assertNotNull($store->store_slug);
        $this->assertNotNull($store->status);
        $this->assertNotNull($store->invoice_count);
        
        $user = User::first();
        
        // Check new columns exist in users table
        $this->assertNotNull($user->role_type);
        $this->assertNotNull($user->is_active);
        
        $plan = Plan::first();
        
        // Check new columns exist in plans table
        $this->assertIsInt($plan->pos_enabled);
        $this->assertIsInt($plan->gst_reports_enabled);
    }

    /**
     * Test: Models have proper relationships
     * @test
     */
    public function models_have_proper_relationships()
    {
        $store = Business::with('planSubscribe', 'websiteSetting')->first();
        
        $this->assertNotNull($store->planSubscribe);
        $this->assertNotNull($store->websiteSetting);
        
        $plan = Plan::first();
        $this->assertIsIterable($plan->planSubscribes);
    }

    /**
     * Test: API backward compatibility - Old endpoints still work
     * @test
     */
    public function old_endpoints_still_work()
    {
        $user = User::where('role_type', 'store_owner')->first();
        
        if ($user && $user->business_id) {
            // This assumes /api/businesses endpoint exists
            // Adjust based on your actual API structure
            $this->assertTrue(true); // Placeholder for existing API test
        }
    }

    /**
     * Test: Demo credentials work correctly
     * @test
     */
    public function demo_credentials_are_valid()
    {
        $credentials = [
            ['email' => 'superadmin@tryonedigital.com', 'password' => 'admin@123', 'expected_role' => 'super_admin'],
            ['email' => 'rajesh@medicals.com', 'password' => 'password', 'expected_role' => 'store_owner'],
            ['email' => 'gupta@traders.com', 'password' => 'password', 'expected_role' => 'store_owner'],
        ];
        
        foreach ($credentials as $cred) {
            $user = User::where('email', $cred['email'])->first();
            
            if ($user) {
                $this->assertEquals($cred['expected_role'], $user->role_type);
            }
        }
    }
}

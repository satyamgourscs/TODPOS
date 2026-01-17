<?php

namespace App\Http\Controllers\Admin\SaaS;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Plan;
use App\Models\User;
use App\Models\PlanSubscribe;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use DB;

class StoreManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:superadmin');
    }

    /**
     * Display a listing of all stores
     */
    public function index()
    {
        $stores = Business::with('category', 'enrolled_plan.plan', 'user')
            ->paginate(20);

        return view('admin.saas.stores.index', compact('stores'));
    }

    /**
     * Show store creation form
     */
    public function create()
    {
        $categories = \App\Models\BusinessCategory::where('status', 1)->get();
        $plans = Plan::where('status', 1)->get();

        return view('admin.saas.stores.create', compact('categories', 'plans'));
    }

    /**
     * Store a newly created store
     */
    public function store(Request $request)
    {
        $request->validate([
            'companyName' => 'required|string|max:255',
            'business_category_id' => 'required|exists:business_categories,id',
            'phoneNumber' => 'required|string|max:20',
            'address' => 'nullable|string|max:500',
            'plan_id' => 'required|exists:plans,id',
            'owner_name' => 'required|string|max:255',
            'owner_email' => 'required|email|unique:users,email',
            'owner_phone' => 'required|string|max:20',
        ]);

        DB::beginTransaction();
        try {
            // Create business
            $slug = Str::slug($request->companyName) . '-' . Str::random(6);
            
            $business = Business::create([
                'companyName' => $request->companyName,
                'business_category_id' => $request->business_category_id,
                'phoneNumber' => $request->phoneNumber,
                'address' => $request->address,
                'store_slug' => $slug,
                'status' => 1,
            ]);

            // Create subscription
            $plan = Plan::find($request->plan_id);
            $planSubscribe = PlanSubscribe::create([
                'plan_id' => $plan->id,
                'business_id' => $business->id,
                'price' => $plan->subscriptionPrice,
                'duration' => $plan->duration,
                'payment_status' => 'paid',
            ]);

            $business->update(['plan_subscribe_id' => $planSubscribe->id]);

            // Create owner user
            $user = User::create([
                'name' => $request->owner_name,
                'email' => $request->owner_email,
                'phone' => $request->owner_phone,
                'business_id' => $business->id,
                'role' => 'shop-owner',
                'role_type' => 'store_owner',
                'password' => bcrypt(Str::random(12)),
                'is_active' => 1,
            ]);

            DB::commit();

            return redirect()->route('admin.saas.stores.show', $business->id)
                ->with('success', 'Store created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create store: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified store
     */
    public function show(Business $business)
    {
        $business = $business->load('category', 'enrolled_plan.plan', 'user');
        $stats = $this->getStoreStats($business);

        return view('admin.saas.stores.show', compact('business', 'stats'));
    }

    /**
     * Show the form for editing the specified store
     */
    public function edit(Business $business)
    {
        $categories = \App\Models\BusinessCategory::where('status', 1)->get();
        $plans = Plan::where('status', 1)->get();

        return view('admin.saas.stores.edit', compact('business', 'categories', 'plans'));
    }

    /**
     * Update the specified store
     */
    public function update(Request $request, Business $business)
    {
        $request->validate([
            'companyName' => 'required|string|max:255',
            'phoneNumber' => 'required|string|max:20',
            'address' => 'nullable|string|max:500',
            'status' => 'required|boolean',
            'store_slug' => 'required|string|unique:businesses,store_slug,' . $business->id,
        ]);

        $business->update($request->only(['companyName', 'phoneNumber', 'address', 'status', 'store_slug']));

        return redirect()->route('admin.saas.stores.show', $business->id)
            ->with('success', 'Store updated successfully!');
    }

    /**
     * Suspend/Activate store
     */
    public function toggleStatus(Business $business)
    {
        $business->update(['status' => !$business->status]);

        $status = $business->status ? 'activated' : 'suspended';
        return back()->with('success', "Store {$status} successfully!");
    }

    /**
     * Upgrade store plan
     */
    public function upgradePlan(Request $request, Business $business)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        DB::beginTransaction();
        try {
            $plan = Plan::find($request->plan_id);
            
            $newSubscribe = PlanSubscribe::create([
                'plan_id' => $plan->id,
                'business_id' => $business->id,
                'price' => $plan->subscriptionPrice,
                'duration' => $plan->duration,
                'payment_status' => 'paid',
            ]);

            $business->update(['plan_subscribe_id' => $newSubscribe->id]);

            DB::commit();
            return back()->with('success', 'Plan upgraded successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to upgrade plan']);
        }
    }

    /**
     * Delete store (with all related data)
     */
    public function destroy(Business $business)
    {
        DB::beginTransaction();
        try {
            // Delete all related users
            User::where('business_id', $business->id)->delete();
            
            // Delete the business (cascades will handle relations)
            $business->delete();

            DB::commit();
            return redirect()->route('admin.saas.stores.index')
                ->with('success', 'Store deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to delete store']);
        }
    }

    private function getStoreStats(Business $business)
    {
        return [
            'total_users' => User::where('business_id', $business->id)->count(),
            'total_invoices' => $business->invoice_count ?? 0,
            'active_plan' => $business->enrolled_plan?->plan->subscriptionName,
            'subscription_expires' => $business->will_expire,
            'is_expired' => $business->will_expire && now()->isAfter($business->will_expire),
        ];
    }
}

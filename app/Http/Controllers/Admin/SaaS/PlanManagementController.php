<?php

namespace App\Http\Controllers\Admin\SaaS;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:superadmin');
    }

    /**
     * Display all plans
     */
    public function index()
    {
        $plans = Plan::paginate(20);
        return view('admin.saas.plans.index', compact('plans'));
    }

    /**
     * Show create plan form
     */
    public function create()
    {
        return view('admin.saas.plans.create');
    }

    /**
     * Store new plan
     */
    public function store(Request $request)
    {
        $request->validate([
            'subscriptionName' => 'required|string|max:255|unique:plans',
            'subscriptionPrice' => 'required|numeric|min:0',
            'offerPrice' => 'nullable|numeric|min:0',
            'duration' => 'required|integer|min:1',
            'max_invoices_per_month' => 'required|integer|min:-1',
            'max_users' => 'required|integer|min:1',
            'pos_enabled' => 'boolean',
            'gst_reports_enabled' => 'boolean',
            'whatsapp_integration_enabled' => 'boolean',
            'mobile_app_access' => 'boolean',
            'multi_branch_enabled' => 'boolean',
            'features' => 'nullable|string',
        ]);

        Plan::create([
            'subscriptionName' => $request->subscriptionName,
            'subscriptionPrice' => $request->subscriptionPrice,
            'offerPrice' => $request->offerPrice,
            'duration' => $request->duration,
            'max_invoices_per_month' => $request->max_invoices_per_month,
            'max_users' => $request->max_users,
            'pos_enabled' => $request->boolean('pos_enabled'),
            'gst_reports_enabled' => $request->boolean('gst_reports_enabled'),
            'whatsapp_integration_enabled' => $request->boolean('whatsapp_integration_enabled'),
            'mobile_app_access' => $request->boolean('mobile_app_access'),
            'multi_branch_enabled' => $request->boolean('multi_branch_enabled'),
            'features' => $request->features,
            'status' => 1,
        ]);

        return redirect()->route('admin.saas.plans.index')
            ->with('success', 'Plan created successfully!');
    }

    /**
     * Show edit plan form
     */
    public function edit(Plan $plan)
    {
        return view('admin.saas.plans.edit', compact('plan'));
    }

    /**
     * Update plan
     */
    public function update(Request $request, Plan $plan)
    {
        $request->validate([
            'subscriptionName' => 'required|string|max:255|unique:plans,subscriptionName,' . $plan->id,
            'subscriptionPrice' => 'required|numeric|min:0',
            'offerPrice' => 'nullable|numeric|min:0',
            'duration' => 'required|integer|min:1',
            'max_invoices_per_month' => 'required|integer|min:-1',
            'max_users' => 'required|integer|min:1',
            'pos_enabled' => 'boolean',
            'gst_reports_enabled' => 'boolean',
            'whatsapp_integration_enabled' => 'boolean',
            'mobile_app_access' => 'boolean',
            'multi_branch_enabled' => 'boolean',
        ]);

        $plan->update($request->all());

        return redirect()->route('admin.saas.plans.index')
            ->with('success', 'Plan updated successfully!');
    }

    /**
     * Toggle plan status
     */
    public function toggleStatus(Plan $plan)
    {
        $plan->update(['status' => !$plan->status]);
        return back()->with('success', 'Plan status updated!');
    }

    /**
     * Delete plan
     */
    public function destroy(Plan $plan)
    {
        $plan->delete();
        return redirect()->route('admin.saas.plans.index')
            ->with('success', 'Plan deleted successfully!');
    }
}

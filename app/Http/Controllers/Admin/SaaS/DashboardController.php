<?php

namespace App\Http\Controllers\Admin\SaaS;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Plan;
use App\Models\User;
use App\Models\SubscriptionInvoice;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:superadmin')->only(['index']);
    }

    public function index()
    {
        $stats = [
            'total_stores' => Business::count(),
            'active_subscriptions' => Business::whereNotNull('plan_subscribe_id')->count(),
            'total_users' => User::count(),
            'monthly_revenue' => $this->calculateMonthlyRevenue(),
            'expiring_soon' => Business::where('will_expire', '<=', now()->addDays(30))
                ->where('will_expire', '>', now())
                ->count(),
            'active_stores_count' => Business::where('status', 1)->count(),
        ];

        $recentStores = Business::with('category', 'enrolled_plan.plan')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $expiringSubscriptions = Business::with('enrolled_plan.plan')
            ->where('will_expire', '<=', now()->addDays(30))
            ->where('will_expire', '>', now())
            ->orderBy('will_expire')
            ->limit(10)
            ->get();

        $revenueData = $this->getMonthlyRevenueChart();

        return view('admin.saas.dashboard', compact('stats', 'recentStores', 'expiringSubscriptions', 'revenueData'));
    }

    private function calculateMonthlyRevenue()
    {
        return SubscriptionInvoice::whereMonth('invoice_date', now()->month)
            ->whereYear('invoice_date', now()->year)
            ->where('status', 'paid')
            ->sum('amount');
    }

    private function getMonthlyRevenueChart()
    {
        $months = [];
        $revenues = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('M Y');
            
            $revenue = SubscriptionInvoice::whereMonth('invoice_date', $date->month)
                ->whereYear('invoice_date', $date->year)
                ->where('status', 'paid')
                ->sum('amount');
            
            $revenues[] = $revenue;
        }

        return [
            'months' => $months,
            'revenues' => $revenues,
        ];
    }
}

<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\PlanSubscribe;
use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;

class SubscriptionReport extends Controller
{
    public function index(Request $request)
    {
        $subscribers = PlanSubscribe::with(['plan:id,subscriptionName', 'business:id,companyName,business_category_id,pictureUrl', 'business.category:id,name', 'gateway:id,name'])->whereDate('created_at', Carbon::today()->format('Y-m-d'))->latest()->paginate(20);
        return view('admin.subscribers.index', compact('subscribers'));
    }

    public function acnooFilter(Request $request)
    {
        $subscriberQuery = PlanSubscribe::with(['plan:id,subscriptionName', 'business:id,companyName,business_category_id', 'business.category:id,name']);

        $startDate = Carbon::today()->format('Y-m-d');
        $endDate = Carbon::today()->format('Y-m-d');

        if ($request->custom_days === 'yesterday') {
            $startDate = Carbon::yesterday()->format('Y-m-d');
            $endDate = Carbon::yesterday()->format('Y-m-d');
        } elseif ($request->custom_days === 'last_seven_days') {
            $startDate = Carbon::today()->subDays(6)->format('Y-m-d');
        } elseif ($request->custom_days === 'last_thirty_days') {
            $startDate = Carbon::today()->subDays(29)->format('Y-m-d');
        } elseif ($request->custom_days === 'current_month') {
            $startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        } elseif ($request->custom_days === 'last_month') {
            $startDate = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');
        } elseif ($request->custom_days === 'current_year') {
            $startDate = Carbon::now()->startOfYear()->format('Y-m-d');
            $endDate = Carbon::now()->endOfYear()->format('Y-m-d');
        } elseif ($request->custom_days === 'custom_date' && $request->from_date && $request->to_date) {
            $startDate = Carbon::parse($request->from_date)->format('Y-m-d');
            $endDate = Carbon::parse($request->to_date)->format('Y-m-d');
        }

        $subscriberQuery->whereDate('created_at', '>=', $startDate)->whereDate('created_at', '<=', $endDate);

        // Search Filter
        if ($request->filled('search')) {
            $search = $request->search;
            $subscriberQuery->where(function ($query) use ($search) {
                $query->where('duration', 'like', '%' . $search . '%')
                    ->orWhereHas('plan', function ($q) use ($search) {
                        $q->where('subscriptionName', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('gateway', function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('business', function ($q) use ($search) {
                        $q->where('companyName', 'like', '%' . $search . '%')
                            ->orWhereHas('category', function ($q) use ($search) {
                                $q->where('name', 'like', '%' . $search . '%');
                            });
                    });
            });
        }

        $perPage = $request->input('per_page', 10);
        $subscribers = $subscriberQuery->latest()->paginate($perPage);

        if ($request->ajax()) {
            return response()->json([
                'data' => view('admin.subscribers.datas', compact('subscribers'))->render()
            ]);
        }

        return redirect(url()->previous());
    }
}

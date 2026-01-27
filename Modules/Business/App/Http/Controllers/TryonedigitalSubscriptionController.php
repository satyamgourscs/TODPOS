<?php

namespace Modules\Business\App\Http\Controllers;

use App\Models\Plan;
use App\Http\Controllers\Controller;

class TryonedigitalSubscriptionController extends Controller
{
    public function __construct()
    {
        $this->middleware('check.permission:subscriptions.read')->only(['index']);
    }

    public function index()
    {
        $plans = Plan::where('status', 1)->latest()->get();
        return view('business::subscriptions.index', compact('plans'));
    }
}

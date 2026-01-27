<?php

namespace Modules\MultiBranchAddon\App\Http\Controllers;

use App\Models\Sale;
use App\Models\User;
use App\Models\Branch;
use App\Models\Income;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class TryonedigitalBranchController extends Controller
{
    public function __construct()
    {
        $this->middleware('check.permission:branches.read')->only(['index']);
        $this->middleware('check.permission:branches.create')->only(['store']);
        $this->middleware('check.permission:branches.update')->only(['update']);
        $this->middleware('check.permission:branches.delete')->only(['destroy', 'deleteAll']);
    }

    public function index()
    {
        $branches = Branch::where('business_id', auth()->user()->business_id)->paginate(10);
        return view('multibranchaddon::branches.index', compact('branches'));
    }

    public function tryonedigitalFilter(Request $request)
    {
        $branches = Branch::where('business_id', auth()->user()->business_id)
            ->when(request('search'), function ($q) use ($request) {
                $q->where(function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('phone', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%')
                        ->orWhere('address', 'like', '%' . $request->search . '%');
                });
            })
            ->paginate($request->per_page ?? 10);

        if ($request->ajax()) {
            return response()->json([
                'data' => view('multibranchaddon::branches.datas', compact('branches'))->render()
            ]);
        }
        return redirect(url()->previous());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
            'branchOpeningBalance' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $business_id = auth()->user()->business_id;
            $opening_balance = $request->branchOpeningBalance ?? 0;
            $has_main_branch = Branch::where('business_id', $business_id)->where('is_main', 1)->exists();

            if (!branch_count() || !$has_main_branch) {
                manipulateBranchData($business_id);
            }

            Branch::create($request->except('branchOpeningBalance', 'branchRemainingBalance') + [
                'branchRemainingBalance' => $opening_balance,
                'branchOpeningBalance' => $opening_balance,
            ]);

            Cache::forget('branch-count-' . $business_id);

            DB::commit();
            return response()->json([
                'message' => __('Branch saved successfully.'),
                'redirect' => route('multibranch.branches.index')
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
            'branchOpeningBalance' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:1000',
        ]);

        $branch = Branch::findOrFail($id);

        $updateData = $request->except('branchRemainingBalance');

        $requestedOpeningBalance = $request->input('branchOpeningBalance');

        if ($requestedOpeningBalance != $branch->branchOpeningBalance) {
            if ($branch->branchRemainingBalance === $branch->branchOpeningBalance) {
                $updateData['branchRemainingBalance'] = $requestedOpeningBalance;
            } else {
                return response()->json([
                    'message' => __('You cannot update opening balance because it differs from remaining balance.')
                ], 422);
            }
        }

        $branch->update($updateData);

        return response()->json([
            'message' => __('Branch updated successfully.'),
            'redirect' => route('multibranch.branches.index')
        ]);
    }

    public function destroy($id)
    {
        $business_id = auth()->user()->business_id;
        $branch = Branch::where('business_id', $business_id)->findOrFail($id);

        if ($branch->is_main) {
            return response()->json([
                'message' => __('You can not delete main branch.')
            ], 406);
        }

        User::where('branch_id', $branch->id)->delete();
        $branch->delete();

        Cache::forget('branch-count-' . $business_id);

        return response()->json([
            'message' => __('Branch deleted successfully'),
            'redirect' => route('multibranch.branches.index'),
        ]);
    }

    public function deleteAll(Request $request)
    {
        $business_id = auth()->user()->business_id;

        User::whereIn('branch_id', $request->ids)->delete();
        Branch::where('business_id', $business_id)->where('is_main', 0)->whereIn('id', $request->ids)->delete();

        Cache::forget('branch-count-' . $business_id);

        return response()->json([
            'message' => __('Selected branch deleted successfully'),
            'redirect' => route('multibranch.branches.index'),
        ]);
    }

    public function overview()
    {
        $branches = Branch::where('business_id', auth()->user()->business_id)->withCount('employees')->latest()->take(5)->get();
        $branches_expired_products  = Branch::where('business_id', auth()->user()->business_id)->whereHas('expiredStocks')->withCount('expiredStocks')->latest()->take(5)->get();

        return view('multibranchaddon::branches.overview', compact('branches', 'branches_expired_products'));
    }

    public function branchWiseSales(Request $request)
    {
        $year = $request->get('year', date('Y'));

        $branches_sales = Branch::where('business_id', auth()->user()->business_id)
            ->whereHas('sales', function ($q) use ($year) {
                $q->whereYear('saleDate', $year);
            })
            ->withSum(['sales as totalAmount' => function ($q) use ($year) {
                $q->whereYear('saleDate', $year);
            }], 'totalAmount')
            ->withSum(['sales as paidAmount' => function ($q) use ($year) {
                $q->whereYear('saleDate', $year);
            }], 'paidAmount')
            ->withSum(['sales as dueAmount' => function ($q) use ($year) {
                $q->whereYear('saleDate', $year);
            }], 'dueAmount')
            ->latest()
            ->take(5)
            ->get();

        $branches_sales->transform(function ($branch) {
            $branch->sales_sum_total_amount_formatted = currency_format($branch->totalAmount, currency: business_currency());
            $branch->sales_sum_paid_amount_formatted = currency_format($branch->paidAmount, currency: business_currency());
            $branch->sales_sum_due_amount_formatted = currency_format($branch->dueAmount, currency: business_currency());
            return $branch;
        });

        return response()->json($branches_sales);
    }

    public function branchWisePurchases(Request $request)
    {
        $year = $request->get('year', date('Y'));

        $branches_purchases = Branch::where('business_id', auth()->user()->business_id)
            ->whereHas('purchases', function ($q) use ($year) {
                $q->whereYear('purchaseDate', $year);
            })
            ->withSum(['purchases as totalAmount' => function ($q) use ($year) {
                $q->whereYear('purchaseDate', $year);
            }], 'totalAmount')
            ->withSum(['purchases as paidAmount' => function ($q) use ($year) {
                $q->whereYear('purchaseDate', $year);
            }], 'paidAmount')
            ->withSum(['purchases as dueAmount' => function ($q) use ($year) {
                $q->whereYear('purchaseDate', $year);
            }], 'dueAmount')
            ->latest()
            ->take(5)
            ->get();

        $branches_purchases->transform(function ($branch) {
            $branch->purchases_sum_total_amount_formatted = currency_format($branch->totalAmount, currency: business_currency());
            $branch->purchases_sum_paid_amount_formatted = currency_format($branch->paidAmount, currency: business_currency());
            $branch->purchases_sum_due_amount_formatted = currency_format($branch->dueAmount, currency: business_currency());
            return $branch;
        });

        return response()->json($branches_purchases);
    }

    public function incomeExpense(Request $request)
    {
        $year = $request->year ?? date('Y');

        $data['incomes'] = Income::where('business_id', auth()->user()->business_id)
            ->whereYear('incomeDate', $year)
            ->selectRaw('MONTH(incomeDate) as month_number, MONTHNAME(incomeDate) as month, SUM(ABS(amount)) as total')
            ->groupBy('month_number', 'month')
            ->orderBy('month_number')
            ->get();

        $data['expenses'] = Expense::where('business_id', auth()->user()->business_id)
            ->whereYear('expenseDate', $year)
            ->selectRaw('MONTH(expenseDate) as month_number, MONTHNAME(expenseDate) as month, SUM(ABS(amount)) as total')
            ->groupBy('month_number', 'month')
            ->orderBy('month_number')
            ->get();

        return response()->json($data);
    }

    public function earningData(Request $request)
    {
        $year = $request->year ?? date('Y');

        $businessId = auth()->user()->business_id;

        $profit = Income::where('business_id', $businessId)
            ->whereYear('incomeDate', $year)
            ->sum('amount');

        $loss = Expense::where('business_id', $businessId)
            ->whereYear('expenseDate', $year)
            ->sum('amount');

        // Get the total loss/profit for the month
        $sale_loss_profit = Sale::where('business_id', $businessId)
            ->whereYear('saleDate', $year)
            ->sum('lossProfit');

        // Update income and expense based on lossProfit value
        $profit += $sale_loss_profit > 0 ? $sale_loss_profit : 0;
        $loss += $sale_loss_profit < 0 ? abs($sale_loss_profit) : 0;

        $data = [
            'profit' => $profit,
            'loss' => $loss,
        ];

        return response()->json($data);
    }

    public function status(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);
        $branch->update(['status' => $request->status]);
        return response()->json(['message' => __('Branch')]);
    }

    public function switchBranch($id)
    {
        if (!auth()->user()->branch_id) {

            $branch = Branch::where('business_id', auth()->user()->business_id)->findOrFail($id);
            auth()->user()->update([
                'active_branch_id' => $branch->id
            ]);

            auth()->user()->tokens()->delete();

            return redirect(route('business.dashboard.index'))->with('message', "You've successfully login to " . $branch->name);
        } else {
            return redirect(route('business.dashboard.index'))->with('warning', "You're not permitted to login on this branch.");
        }
    }

    public function exitBranch($id)
    {
        if (auth()->user()->active_branch_id) {

            $branch = Branch::where('business_id', auth()->user()->business_id)->findOrFail($id);
            auth()->user()->update([
                'active_branch_id' => null
            ]);

            auth()->user()->tokens()->delete();

            return redirect(route('business.dashboard.index'))->with('message', "You've successfully exit from " . $branch->name);
        } else {
            return redirect(route('business.dashboard.index'))->with('warning', "You're not permitted to exit from this branch.");
        }
    }
}

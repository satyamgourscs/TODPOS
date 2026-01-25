<?php

namespace Modules\Business\App\Http\Controllers;

use App\Models\Business;
use Carbon\Carbon;
use App\Models\Sale;
use App\Models\Party;
use App\Models\Income;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\SaleReturn;
use App\Models\PurchaseReturn;
use App\Models\SaleReturnDetails;
use App\Http\Controllers\Controller;
use App\Models\PurchaseReturnDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $businessId = auth()->user()->business_id;

        $allProducts = Product::with('stocks')
            ->withSum('stocks', 'productStock')
            ->where('business_id', $businessId)
            ->latest()
            ->get();

        $stocks = $allProducts->filter(function ($product) {
            return $product->stocks->sum('productStock') <= $product->alert_qty;
        })->take(5);

        // Latest sales
        $sales = Sale::with('party:id,name', 'details')
            ->where('business_id', $businessId)
            ->latest()
            ->limit(5)
            ->get();

        // Latest purchases
        $purchases = Purchase::with('details', 'party:id,name')
            ->where('business_id', $businessId)
            ->latest()
            ->limit(5)
            ->get();

        return view('business::dashboard.index', compact('stocks', 'purchases', 'sales'));
    }

    public function getDashboardData()
    {
        $businessId = auth()->user()->business_id;

        $data['total_sales'] = currency_format(Sale::where('business_id', $businessId)->sum('totalAmount'), currency: business_currency(), abbreviate: true);
        $data['this_month_total_sales'] = currency_format(Sale::where('business_id', $businessId)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('totalAmount'), currency: business_currency(), abbreviate: true);

        $data['total_purchase'] = currency_format(Purchase::where('business_id', $businessId)->sum('totalAmount'), currency: business_currency(), abbreviate: true);
        $data['this_month_total_purchase'] = currency_format(Purchase::where('business_id', $businessId)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('totalAmount'), currency: business_currency(), abbreviate: true);

        // Get total and monthly lossProfit
        $sale_loss_profit = Sale::where('business_id', auth()->user()->business_id)->sum('lossProfit');
        $this_month_loss_profit = Sale::where('business_id', auth()->user()->business_id)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('lossProfit');

        // Get total income and expense
        $total_income = Income::where('business_id', $businessId)->sum('amount');
        $this_month_total_income = Income::where('business_id', $businessId)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('amount');

        $total_expense = Expense::where('business_id', $businessId)->sum('amount');
        $this_month_total_expense = Expense::where('business_id', $businessId)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('amount');

        // Update income and expense based on lossProfit value
        $total_income += $sale_loss_profit > 0 ? $sale_loss_profit : 0;
        $total_expense += $sale_loss_profit < 0 ? abs($sale_loss_profit) : 0;

        $this_month_total_income += $this_month_loss_profit > 0 ? $this_month_loss_profit : 0;
        $this_month_total_expense += $this_month_loss_profit < 0 ? abs($this_month_loss_profit) : 0;

        // Format data for display
        $data['total_income'] = currency_format($total_income, currency: business_currency(), abbreviate: true);
        $data['this_month_total_income'] = currency_format($this_month_total_income, currency: business_currency(), abbreviate: true);

        $data['total_expense'] = currency_format($total_expense, currency: business_currency(), abbreviate: true);
        $data['this_month_total_expense'] = currency_format($this_month_total_expense, currency: business_currency(), abbreviate: true);


        $data['total_customer'] = Party::where('business_id', $businessId)->where('type', '!=', 'Supplier')->count();
        $data['this_month_total_customer'] = Party::where('business_id', $businessId)
            ->where('type', '!=', 'Supplier')
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        $data['total_supplier'] = Party::where('business_id', $businessId)->whereType('Supplier')->count();
        $data['this_month_total_supplier'] = Party::where('business_id', $businessId)
            ->whereType('Supplier')
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();


        $sale_return_id = SaleReturn::where('business_id', $businessId)
            ->pluck('id');
        $data['total_sales_return'] = currency_format(SaleReturnDetails::whereIn('sale_return_id', $sale_return_id)
            ->sum('return_amount'), currency: business_currency(), abbreviate: true);


        $saleReturns = SaleReturn::where('business_id', $businessId)
            ->whereYear('return_date', now()->year)
            ->whereMonth('return_date', now()->month)
            ->pluck('id');

        $data['this_month_total_sale_return'] = currency_format(SaleReturnDetails::whereIn('sale_return_id', $saleReturns)
            ->sum('return_amount'), currency: business_currency(), abbreviate: true);



        $purchase_return_id = PurchaseReturn::where('business_id', $businessId)
            ->pluck('id');
        $data['total_purchase_return'] = currency_format(PurchaseReturnDetail::whereIn('purchase_return_id', $purchase_return_id)
            ->sum('return_amount'), currency: business_currency(), abbreviate: true);


        $purchaseReturns = PurchaseReturn::where('business_id', $businessId)
            ->whereYear('return_date', now()->year)
            ->whereMonth('return_date', now()->month)
            ->pluck('id');

        $data['this_month_total_purchase_return'] = currency_format(PurchaseReturnDetail::whereIn('purchase_return_id', $purchaseReturns)
            ->sum('return_amount'), currency: business_currency(), abbreviate: true);

        return response()->json($data);
    }

    public function overall_report()
    {
        $businessId = auth()->user()->business_id;

        // Calculate overall values
        $overall_purchase = Purchase::where('business_id', $businessId)
            ->whereYear('created_at', request('year') ?? date('Y'))
            ->sum('totalAmount');

        $overall_sale = Sale::where('business_id', $businessId)
            ->whereYear('created_at', request('year') ?? date('Y'))
            ->sum('totalAmount');

        $overall_income = Income::where('business_id', $businessId)
            ->whereYear('created_at', request('year') ?? date('Y'))
            ->sum('amount');

        $overall_expense = Expense::where('business_id', $businessId)
            ->whereYear('created_at', request('year') ?? date('Y'))
            ->sum('amount');

        // Get the total loss/profit for the month
        $sale_loss_profit = Sale::where('business_id', $businessId)
            ->whereYear('created_at', request('year') ?? date('Y'))
            ->sum('lossProfit');

        // Update income and expense based on lossProfit value
        $overall_income += $sale_loss_profit > 0 ? $sale_loss_profit : 0;
        $overall_expense += $sale_loss_profit < 0 ? abs($sale_loss_profit) : 0;

        $data = [
            'overall_purchase' => $overall_purchase,
            'overall_sale' => $overall_sale,
            'overall_income' => $overall_income,
            'overall_expense' => $overall_expense,
        ];

        return response()->json($data);
    }


    public function revenue()
    {
        $data['loss'] = Sale::where('business_id', auth()->user()->business_id)
            ->whereYear('created_at', request('year') ?? date('Y'))
            ->where('lossProfit', '<', 0)
            ->selectRaw('MONTHNAME(created_at) as month, SUM(ABS(lossProfit)) as total')
            ->orderBy('created_at')
            ->groupBy('created_at')
            ->get();

        $data['profit'] = Sale::where('business_id', auth()->user()->business_id)
            ->whereYear('created_at', request('year') ?? date('Y'))
            ->where('lossProfit', '>=', 0)
            ->selectRaw('MONTHNAME(created_at) as month, SUM(ABS(lossProfit)) as total')
            ->orderBy('created_at')
            ->groupBy('created_at')
            ->get();

        return response()->json($data);
    }

    public function updateExpireDate(Request $request)
    {
        $days = $request->query('days', 0);
        $operation = $request->query('operation');
        $business = Business::where('id', auth()->user()->business_id)->first();
        if (!$business) {
            return response()->json([
                'message' => 'Business not found.',
            ], 404);
        }
        if ($operation === 'add') {
            $business->will_expire = now()->addDays($days);
        } elseif ($operation === 'sub') {
            $business->will_expire = now()->subDays($days);
        } else {
            return response()->json([
                'message' => 'Invalid operation. Use "add" or "sub".',
            ], 400);
        }
        $business->save();

        Cache::forget("plan-data-{$business->id}");


        return response()->json([
            'message' => 'Expiry date updated successfully.',
            'will_expire' => $business->will_expire,
        ]);
    }
}

<?php

namespace Modules\Business\App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Sale;
use App\Models\Stock;
use App\Models\Product;
use App\Models\Business;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Business\App\Exports\ExportLossProfit;

class AcnooLossProfitReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('check.permission:loss-profit-reports.read')->only(['index']);
    }

    public function index()
    {
        $businessId = auth()->user()->business_id;
        $today = Carbon::today()->format('Y-m-d');

        $loss = Sale::where('business_id', $businessId)
            ->whereDate('created_at', $today)
            ->where('lossProfit', '<=', 0)
            ->selectRaw('SUM(ABS(lossProfit)) as total_loss')
            ->value('total_loss');

        $profit = Sale::where('business_id', $businessId)
            ->whereDate('created_at', $today)
            ->where('lossProfit', '>', 0)
            ->sum('lossProfit');

        $total_sale_count = Sale::where('business_id', $businessId)
            ->whereDate('created_at', $today)
            ->count();

        $loss_profits = Sale::with('party:id,name')
            ->where('business_id', $businessId)
            ->whereDate('created_at', $today)
            ->latest()
            ->paginate(20);

        return view('business::reports.loss-profits.loss-profit-reports', compact('loss_profits', 'profit', 'loss', 'total_sale_count'));
    }

    public function acnooFilter(Request $request)
    {
        $salesQuery = Sale::with('party:id,name')
            ->where('business_id', auth()->user()->business_id)
            ->when($request->custom_days, function ($query) use ($request) {
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

                $query->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate);
            })
            ->when($request->search, function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('lossProfit', 'like', '%' . $request->search . '%')
                        ->orWhere('totalAmount', 'like', '%' . $request->search . '%')
                        ->orWhere('invoiceNumber', 'like', '%' . $request->search . '%')
                        ->orWhereHas('party', function ($q) use ($request) {
                            $q->where('name', 'like', '%' . $request->search . '%');
                        });
                });
            });

        $loss_profits = $salesQuery->latest()->paginate($request->per_page ?? 10);

        $loss = (clone $salesQuery)->where('lossProfit', '<=', 0)->get()
            ->sum(function ($sale) {
                return abs($sale->lossProfit);
            });

        $profit = (clone $salesQuery)->where('lossProfit', '>', 0)->sum('lossProfit');
        
        $total_sale_count = (clone $salesQuery)->count();


        if ($request->ajax()) {
            return response()->json([
                'data' => view('business::reports.loss-profits.datas', compact('loss_profits'))->render(),
                'total_loss' => currency_format($loss, currency: business_currency()),
                'total_profit' => currency_format($profit, currency: business_currency()),
                'total_sale_count' => $total_sale_count,
            ]);
        }

        return redirect(url()->previous());
    }

    public function generatePDF(Request $request)
    {
        $loss_profits = Sale::with('party:id,name')->where('business_id', auth()->user()->business_id)->whereYear('created_at', Carbon::now()->year)->latest()->get();
        $pdf = Pdf::loadView('business::reports.loss-profits.pdf', compact('loss_profits'));
        return $pdf->download('loss-profits.pdf');
    }

    public function exportExcel()
    {
        return Excel::download(new ExportLossProfit, 'loss-profit.xlsx');
    }

    public function exportCsv()
    {
        return Excel::download(new ExportLossProfit, 'loss-profit.csv');
    }

    public function lossProfitDetails()
    {
        $businessId = auth()->user()->business_id;
        $today = Carbon::today()->format('Y-m-d');

        $salesQuery = Sale::where('business_id', $businessId)->whereDate('created_at', $today);
        $purchaseQuery = Purchase::where('business_id', $businessId)->whereDate('created_at', $today);
        $productQuery = Product::where('business_id', $businessId);

        // Opening stock (before today) from stocks table
        $opening_stock_by_purchase = Stock::whereHas('product', function ($query) use ($businessId) {
            $query->where('business_id', $businessId);
        })
            ->whereDate('created_at', '<', $today)
            ->sum(DB::raw('productPurchasePrice * productStock'));

        // Closing stock (up to today) from stocks table
        $closing_stock_by_purchase = Stock::whereHas('product', function ($query) use ($businessId) {
            $query->where('business_id', $businessId);
        })
            ->whereDate('created_at', '<=', $today)
            ->sum(DB::raw('productPurchasePrice * productStock'));

        $total_purchase_price = (clone $purchaseQuery)->sum('totalAmount');
        $total_purchase_shipping_charge = (clone $purchaseQuery)->sum('shipping_charge');
        $total_purchase_discount = (clone $purchaseQuery)->sum('discountAmount');
        $all_purchase_return = (clone $purchaseQuery)->with([
            'purchaseReturns' => function ($query) {
                $query->withSum('details as total_return_amount', 'return_amount');
            }
        ])
            ->get()
            ->flatMap
            ->purchaseReturns
            ->sum('total_return_amount');

        $opening_stock_by_sale = Stock::whereHas('product', function ($query) use ($businessId) {
            $query->where('business_id', $businessId);
        })
            ->whereDate('created_at', '<', $today)
            ->sum(DB::raw('productSalePrice * productStock'));

        $closing_stock_by_sale = Stock::whereHas('product', function ($query) use ($businessId) {
            $query->where('business_id', $businessId);
        })
            ->whereDate('created_at', '<=', $today)
            ->sum(DB::raw('productSalePrice * productStock'));

        $total_sale_price = (clone $salesQuery)->sum('totalAmount');
        $total_sale_shipping_charge = (clone $salesQuery)->sum('shipping_charge');
        $total_sale_discount = (clone $salesQuery)->sum('discountAmount');
        $total_sale_rounding_off = (clone $salesQuery)->sum('rounding_amount');

        $all_sale_return = (clone $salesQuery)->with([
            'saleReturns' => function ($query) {
                $query->withSum('details as total_return_amount', 'return_amount');
            }
        ])
            ->get()
            ->flatMap
            ->saleReturns
            ->sum('total_return_amount');

        return view('business::reports.loss-profits-details.index', compact('opening_stock_by_purchase', 'closing_stock_by_purchase', 'total_purchase_price', 'total_purchase_shipping_charge', 'total_purchase_discount', 'all_purchase_return', 'all_sale_return', 'opening_stock_by_sale', 'closing_stock_by_sale', 'total_sale_price', 'total_sale_shipping_charge', 'total_sale_discount', 'total_sale_rounding_off'));
    }

    public function lossProfitFilter(Request $request)
    {
        $businessId = auth()->user()->business_id;

        $startDate = Carbon::today();
        $endDate = Carbon::today();

        switch ($request->custom_days) {
            case 'yesterday':
                $startDate = $endDate = Carbon::yesterday();
                break;
            case 'last_seven_days':
                $startDate = Carbon::today()->subDays(6);
                break;
            case 'last_thirty_days':
                $startDate = Carbon::today()->subDays(29);
                break;
            case 'current_month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                break;
            case 'last_month':
                $startDate = Carbon::now()->subMonth()->startOfMonth();
                $endDate = Carbon::now()->subMonth()->endOfMonth();
                break;
            case 'current_year':
                $startDate = Carbon::now()->startOfYear();
                $endDate = Carbon::now()->endOfYear();
                break;
            case 'custom_date':
                if ($request->from_date && $request->to_date) {
                    $startDate = Carbon::parse($request->from_date);
                    $endDate = Carbon::parse($request->to_date);
                }
                break;
        }

        $salesQuery = Sale::where('business_id', $businessId)
            ->whereBetween('created_at', [$startDate, $endDate]);

        $purchaseQuery = Purchase::where('business_id', $businessId)
            ->whereBetween('created_at', [$startDate, $endDate]);

        $productQuery = Product::where('business_id', $businessId);

        // Opening stock by purchase (before start date)
        $opening_stock_by_purchase = Stock::whereHas('product', function ($q) use ($businessId) {
            $q->where('business_id', $businessId);
        })
            ->whereDate('created_at', '<', $startDate)
            ->sum(DB::raw('productPurchasePrice * productStock'));

        // Closing stock by purchase (up to end date)
        $closing_stock_by_purchase = Stock::whereHas('product', function ($q) use ($businessId) {
            $q->where('business_id', $businessId);
        })
            ->whereDate('created_at', '<=', $endDate)
            ->sum(DB::raw('productPurchasePrice * productStock'));

        $total_purchase_price = (clone $purchaseQuery)->sum('totalAmount');
        $total_purchase_shipping_charge = (clone $purchaseQuery)->sum('shipping_charge');
        $total_purchase_discount = (clone $purchaseQuery)->sum('discountAmount');

        $all_purchase_return = (clone $purchaseQuery)->with([
            'purchaseReturns' => function ($query) {
                $query->withSum('details as total_return_amount', 'return_amount');
            }
        ])->get()->flatMap->purchaseReturns->sum('total_return_amount');

        // Opening stock by sale
        $opening_stock_by_sale = Stock::whereHas('product', function ($q) use ($businessId) {
            $q->where('business_id', $businessId);
        })
            ->whereDate('created_at', '<', $startDate)
            ->sum(DB::raw('productSalePrice * productStock'));

        // Closing stock by sale
        $closing_stock_by_sale = Stock::whereHas('product', function ($q) use ($businessId) {
            $q->where('business_id', $businessId);
        })
            ->whereDate('created_at', '<=', $endDate)
            ->sum(DB::raw('productSalePrice * productStock'));

        $total_sale_price = (clone $salesQuery)->sum('totalAmount');
        $total_sale_shipping_charge = (clone $salesQuery)->sum('shipping_charge');
        $total_sale_discount = (clone $salesQuery)->sum('discountAmount');
        $total_sale_rounding_off = (clone $salesQuery)->sum('rounding_amount') ?? 5;
        $total_sale_rounding_off =  5;

        $all_sale_return = (clone $salesQuery)->with([
            'saleReturns' => function ($query) {
                $query->withSum('details as total_return_amount', 'return_amount');
            }
        ])->get()->flatMap->saleReturns->sum('total_return_amount');


        if ($request->ajax()) {
            return response()->json([
                'opening_stock_by_purchase' => currency_format($opening_stock_by_purchase, currency: business_currency()),
                'closing_stock_by_purchase' => currency_format($closing_stock_by_purchase, currency: business_currency()),
                'total_purchase_price' => currency_format($total_purchase_price, currency: business_currency()),
                'total_purchase_shipping_charge' => currency_format($total_purchase_shipping_charge, currency: business_currency()),
                'total_purchase_discount' => currency_format($total_purchase_discount, currency: business_currency()),
                'all_purchase_return' => currency_format($all_purchase_return, currency: business_currency()),
                'all_sale_return' => currency_format($all_sale_return, currency: business_currency()),
                'opening_stock_by_sale' => currency_format($opening_stock_by_sale, currency: business_currency()),
                'closing_stock_by_sale' => currency_format($closing_stock_by_sale, currency: business_currency()),
                'total_sale_price' => currency_format($total_sale_price, currency: business_currency()),
                'total_sale_shipping_charge' => currency_format($total_sale_shipping_charge, currency: business_currency()),
                'total_sale_discount' => currency_format($total_sale_discount, currency: business_currency()),
                'total_sale_rounding_off' => currency_format($total_sale_rounding_off, currency: business_currency()),
            ]);
        }
    }
}

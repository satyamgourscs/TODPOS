<?php

namespace Modules\Business\App\Http\Controllers;

use App\Models\Stock;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Business\App\Exports\ExportCurrentStock;

class AcnooStockController extends Controller
{
    public function __construct()
    {
        $this->middleware('check.permission:stocks.read')->only(['index']);
    }

    public function index()
    {
        $businessId = auth()->user()->business_id;
        $alert_qty_filter = request('alert_qty');

        $query = Product::with('stocks')->where('business_id', $businessId);

        $products = $query->latest()->get();

        if ($alert_qty_filter) {
            $products = $products->filter(function ($product) {
                $totalStock = $product->stocks->sum('productStock');
                return $totalStock <= $product->alert_qty;
            });
        }

        // Calculate total stock value & quantity
        if ($alert_qty_filter) {
            // Use only low-stock products
            $total_stock_value = $products->sum(function ($product) {
                return $product->stocks->sum(function ($stock) {
                    return $stock->productStock * $stock->productPurchasePrice;
                });
            });

            $total_qty = $products->sum(function ($product) {
                return $product->stocks->sum('productStock');
            });
        } else {
            // Use all stock entries
            $total_stock_value = Stock::whereHas('product', function ($q) use ($businessId) {
                $q->where('business_id', $businessId);
            })->sum(DB::raw('productPurchasePrice * productStock'));

            $total_qty = Stock::whereHas('product', function ($q) use ($businessId) {
                $q->where('business_id', $businessId);
            })->sum('productStock');
        }

        // Paginate manually (after filtering)
        $perPage = 20;
        $currentPage = request('page', 1);
        $paginated = new LengthAwarePaginator(
            $products->forPage($currentPage, $perPage),
            $products->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );
        return view('business::stocks.index', [
            'products' => $paginated,
            'total_stock_value' => $total_stock_value,
            'total_qty' => $total_qty,
        ]);
    }

    public function acnooFilter(Request $request)
    {
        $businessId = auth()->user()->business_id;

        // Load products with stocks
        $query = Product::with('stocks')->where('business_id', $businessId);

        // Apply search filter
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('productName', 'like', '%' . $request->search . '%')
                    ->orWhere('productPurchasePrice', 'like', '%' . $request->search . '%')
                    ->orWhere('productSalePrice', 'like', '%' . $request->search . '%');
            });
        }

        // Fetch the products first (unpaginated, because we need to filter by total stock)
        $products = $query->get();

        // Apply alert_qty logic and stock calculations
        $total_stock_value = 0;
        $total_qty = 0;

        if ($request->alert_qty) {
            // Filter low stock products
            $products = $products->filter(function ($product) {
                return $product->stocks->sum('productStock') <= $product->alert_qty;
            });
        }

        // Calculate totals only for the resulting (filtered or all) products
        $total_stock_value = $products->sum(function ($product) {
            return $product->stocks->sum(function ($stock) {
                return $stock->productStock * $stock->productPurchasePrice;
            });
        });

        $total_qty = $products->sum(function ($product) {
            return $product->stocks->sum('productStock');
        });

        // Paginate manually
        $perPage = $request->per_page ?? 10;
        $currentPage = $request->page ?? 1;

        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $products->forPage($currentPage, $perPage),
            $products->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        if ($request->ajax()) {
            return response()->json([
                'data' => view('business::stocks.datas', [
                    'products' => $paginated,
                    'total_stock_value' => $total_stock_value,
                    'total_qty' => $total_qty
                ])->render()
            ]);
        }

        return redirect()->back();
    }

    public function exportExcel()
    {
        return Excel::download(new ExportCurrentStock, 'current-stock.xlsx');
    }

    public function exportCsv()
    {
        return Excel::download(new ExportCurrentStock, 'current-stock.csv');
    }
}

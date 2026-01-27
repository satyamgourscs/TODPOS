<?php

namespace Modules\Business\App\Http\Controllers;

use App\Models\Stock;
use App\Models\Product;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Business\App\Exports\ExportStock;

class TryonedigitalStockReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('check.permission:stock-reports.read')->only(['index']);
    }

    public function index()
    {
        $businessId = auth()->user()->business_id;

        $total_stock_value = Stock::whereHas('product', function ($q) use ($businessId) {
            $q->where('business_id', $businessId);
        })->sum(DB::raw('productPurchasePrice * productStock'));

        $total_qty = Stock::whereHas('product', function ($q) use ($businessId) {
            $q->where('business_id', $businessId);
        })->sum('productStock');

        $stocks = Product::with(['category:id,categoryName'])
            ->withSum('stocks', 'productStock')
            ->where('business_id', $businessId)
            ->latest()
            ->paginate(20);

        return view('business::reports.stocks.stock-reports', compact('stocks', 'total_stock_value', 'total_qty'));
    }

    public function tryonedigitalFilter(Request $request)
    {
        $stocks = Product::where('business_id', auth()->user()->business_id)
            ->when($request->search, function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('productName', 'like', '%' . $request->search . '%')
                        ->orwhere('productSalePrice', 'like', '%' . $request->search . '%')
                        ->orwhere('productPurchasePrice', 'like', '%' . $request->search . '%');
                });
            })
            ->withSum('stocks', 'productStock')
            ->latest()
            ->paginate($request->per_page ?? 10);

        if ($request->ajax()) {
            return response()->json([
                'data' => view('business::reports.stocks.datas', compact('stocks'))->render()
            ]);
        }
        return redirect(url()->previous());
    }

    public function exportExcel()
    {
        return Excel::download(new ExportStock, 'stock.xlsx');
    }

    public function exportCsv()
    {
        return Excel::download(new ExportStock, 'stock.csv');
    }
}

<?php

namespace Modules\Business\App\Http\Controllers;

use App\Models\Sale;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\SaleReturnDetails;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Business\App\Exports\ExportSalesReturn;

class AcnooSaleReturnReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('check.permission:sale-return-reports.read')->only(['index']);
    }

    public function index()
    {
        $total_sale_return = SaleReturnDetails::whereHas('saleReturn', function ($query) {
            $query->whereHas('sale', function ($q) {
                $q->where('business_id', auth()->user()->business_id);
            });
        })->sum('return_amount');

        $sales = Sale::with([
            'user:id,name',
            'party:id,name',
            'details',
            'branch:id,name',
            'details.product:id,productName,category_id',
            'details.product.category:id,categoryName',
            'saleReturns' => function ($query) {
                $query->withSum('details as total_return_amount', 'return_amount')
                    ->with('branch:id,name');
            }
        ])
            ->where('business_id', auth()->user()->business_id)
            ->whereHas('saleReturns')
            ->latest()
            ->paginate(20);

        $branches = Branch::withTrashed()->where('business_id', auth()->user()->business_id)->latest()->get();

        return view('business::reports.sales-return.sale-reports', compact('sales', 'total_sale_return', 'branches'));
    }

    public function acnooFilter(Request $request)
    {
        $businessId = auth()->user()->business_id;

        $salesQuery = Sale::with([
            'user:id,name',
            'party:id,name',
            'branch:id,name',
            'details',
            'details.product:id,productName,category_id',
            'details.product.category:id,categoryName',
            'saleReturns' => function ($query) {
                $query->withSum('details as total_return_amount', 'return_amount')
                    ->with('branch:id,name');
            }
        ])->where('business_id', $businessId)
          ->whereHas('saleReturns')

            ->when($request->branch_id, function ($q) use ($request) {
                $q->where('branch_id', $request->branch_id);
            });


        // Search Filter
        if ($request->filled('search')) {
            $salesQuery->where(function ($query) use ($request) {
                $query->where('invoiceNumber', 'like', '%' . $request->search . '%')
                    ->orWhereHas('party', function ($q) use ($request) {
                        $q->where('name', 'like', '%' . $request->search . '%');
                    })
                    ->orWhereHas('branch', function ($q) use ($request) {
                        $q->where('name', 'like', '%' . $request->search . '%');
                    });
            });
        }

        $total_sale_return = SaleReturnDetails::whereHas('saleReturn', function ($query) use ($businessId) {
            $query->whereHas('sale', function ($q) use ($businessId) {
                $q->where('business_id', $businessId);
            });
        })->sum('return_amount');

        $perPage = $request->input('per_page', 10);
        $sales = $salesQuery->latest()->paginate($perPage);

        if ($request->ajax()) {
            return response()->json([
                'data' => view('business::reports.sales-return.datas', compact('sales'))->render(),
                'total_sale_return' => currency_format($total_sale_return, currency: business_currency())
            ]);
        }

        return redirect(url()->previous());
    }

    public function generatePDF(Request $request)
    {
        $sales =  Sale::with([
            'user:id,name',
            'party:id,name',
            'details',
            'details.product:id,productName,category_id',
            'details.product.category:id,categoryName',
            'saleReturns' => function ($query) {
                $query->withSum('details as total_return_amount', 'return_amount');
            }
        ])
            ->where('business_id', auth()->user()->business_id)
            ->whereHas('saleReturns')
            ->get();
        $pdf = Pdf::loadView('business::reports.sales-return.pdf', compact('sales'));
        return $pdf->download('sales-return-report.pdf');
    }

    public function exportExcel()
    {
        return Excel::download(new ExportSalesReturn, 'sales-return.xlsx');
    }

    public function exportCsv()
    {
        return Excel::download(new ExportSalesReturn, 'sales-return.csv');
    }
}

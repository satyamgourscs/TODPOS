<?php

namespace Modules\Business\App\Http\Controllers;

use App\Models\Branch;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use App\Models\PurchaseReturnDetail;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Business\App\Exports\ExportPurchaseReturn;

class TryonedigitalPurchaseReturnReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('check.permission:purchase-return-reports.read')->only(['index']);
    }

    public function index()
    {
        $businessId = auth()->user()->business_id;
        $today = Carbon::today()->format('Y-m-d');

        $total_purchase_return = PurchaseReturnDetail::whereHas('purchaseReturn', function ($query) use ($businessId, $today) {
            $query->whereHas('purchase', function ($q) use ($businessId) {
                $q->where('business_id', $businessId);
            });
        })->sum('return_amount');

        $purchases = Purchase::with([
            'user:id,name',
            'party:id,name,email,phone,type',
            'branch:id,name',
            'details:id,purchase_id,product_id,productPurchasePrice,quantities',
            'details.product:id,productName,category_id',
            'details.product.category:id,categoryName',
            'purchaseReturns' => function ($query) {
                $query->withSum('details as total_return_amount', 'return_amount');
            }
        ])
            ->where('business_id', $businessId)
            ->whereHas('purchaseReturns')
            ->latest()
            ->paginate(20);


        $branches = Branch::withTrashed()->where('business_id', auth()->user()->business_id)->latest()->get();

        return view('business::reports.purchase-return.purchase-reports', compact('purchases', 'total_purchase_return', 'branches'));
    }

    public function tryonedigitalFilter(Request $request)
    {
        $businessId = auth()->user()->business_id;

        // Query for Purchases with Purchase Returns in the Selected Date Range
        $purchasesQuery = Purchase::with([
            'user:id,name',
            'branch:id,name',
            'party:id,name,email,phone,type',
            'details:id,purchase_id,product_id,productPurchasePrice,quantities',
            'details.product:id,productName,category_id',
            'details.product.category:id,categoryName',
            'purchaseReturns' => function ($query) {
                $query->withSum('details as total_return_amount', 'return_amount');
            }
        ])->where('business_id', $businessId)
          ->whereHas('purchaseReturns');

        $purchasesQuery->when($request->branch_id, function ($q) use ($request) {
            $q->where('branch_id', $request->branch_id);
        });

        // Search Filter
        if ($request->filled('search')) {
            $purchasesQuery->where(function ($query) use ($request) {
                $query->where('invoiceNumber', 'like', '%' . $request->search . '%')
                    ->orWhereHas('party', function ($q) use ($request) {
                        $q->where('name', 'like', '%' . $request->search . '%');
                    })
                    ->orWhereHas('branch', function ($q) use ($request) {
                        $q->where('name', 'like', '%' . $request->search . '%');
                    });
            });
        }

        // Calculate Total Purchase Return Amount in the Selected Date Range
        $total_purchase_return = PurchaseReturnDetail::whereHas('purchaseReturn', function ($query) use ($businessId) {
            $query->whereHas('purchase', function ($q) use ($businessId) {
                $q->where('business_id', $businessId);
            });
        })->sum('return_amount');

        // Pagination
        $perPage = $request->input('per_page', 10);
        $purchases = $purchasesQuery->latest()->paginate($perPage);

        // Handle AJAX Request
        if ($request->ajax()) {
            return response()->json([
                'data' => view('business::reports.purchase-return.datas', compact('purchases'))->render(),
                'total_purchase_return' => currency_format($total_purchase_return, currency: business_currency())
            ]);
        }

        return redirect(url()->previous());
    }

    public function generatePDF(Request $request)
    {
        $purchases = Purchase::with([
            'user:id,name',
            'party:id,name,email,phone,type',
            'details:id,purchase_id,product_id,productPurchasePrice,quantities',
            'details.product:id,productName,category_id',
            'details.product.category:id,categoryName',
            'purchaseReturns' => function ($query) {
                $query->withSum('details as total_return_amount', 'return_amount');
            }
        ])
            ->where('business_id', auth()->user()->business_id)
            ->whereHas('purchaseReturns')
            ->get();
        $pdf = Pdf::loadView('business::reports.purchase-return.pdf', compact('purchases'));
        return $pdf->download('purchase.report.pdf');
    }

    public function exportExcel()
    {
        return Excel::download(new ExportPurchaseReturn, 'purchase-return.xlsx');
    }

    public function exportCsv()
    {
        return Excel::download(new ExportPurchaseReturn, 'purchase-return.csv');
    }
}

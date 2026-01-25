<?php

namespace Modules\Business\App\Http\Controllers;

use App\Models\Branch;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Business\App\Exports\purchaseExport;

class AcnooPurchaseReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('check.permission:purchase-reports.read')->only(['index']);
    }

    public function index()
    {
        $businessId = auth()->user()->business_id;

        $total_purchase = Purchase::where('business_id', $businessId)
            ->whereDate('purchaseDate', Carbon::today())
            ->sum('totalAmount');

        $purchases = Purchase::with('details', 'party', 'details.product', 'details.product.category', 'payment_type:id,name', 'branch:id,name')
            ->where('business_id', $businessId)
            ->whereDate('purchaseDate', Carbon::today())
            ->latest()
            ->paginate(20);

        $branches = Branch::withTrashed()->where('business_id', auth()->user()->business_id)->latest()->get();

        return view('business::reports.purchase.purchase-reports', compact('purchases', 'total_purchase', 'branches'));
    }

    public function acnooFilter(Request $request)
    {
        $businessId = auth()->user()->business_id;
        $purchasesQuery = Purchase::with('user:id,name', 'party:id,name,email,phone,type', 'payment_type:id,name', 'branch:id,name')
            ->where('business_id', $businessId);

        $purchasesQuery->when($request->branch_id, function ($q) use ($request) {
            $q->where('branch_id', $request->branch_id);
        });

        // Default to today
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

        $purchasesQuery->whereDate('purchaseDate', '>=', $startDate)
            ->whereDate('purchaseDate', '<=', $endDate);

        // Search Filter
        if ($request->filled('search')) {
            $purchasesQuery->where(function ($query) use ($request) {
                $query->where('paymentType', 'like', '%' . $request->search . '%')
                    ->orWhere('invoiceNumber', 'like', '%' . $request->search . '%')
                    ->orWhereHas('party', function ($q) use ($request) {
                        $q->where('name', 'like', '%' . $request->search . '%');
                    })
                    ->orWhereHas('payment_type', function ($q) use ($request) {
                        $q->where('name', 'like', '%' . $request->search . '%');
                    })
                    ->orWhereHas('branch', function ($q) use ($request) {
                        $q->where('name', 'like', '%' . $request->search . '%');
                    });
            });
        }

        $perPage = $request->input('per_page', 10);
        $purchases = $purchasesQuery->latest()->paginate($perPage);

        $total_purchase = $purchasesQuery->sum('totalAmount');

        if ($request->ajax()) {
            return response()->json([
                'data' => view('business::reports.purchase.datas', compact('purchases'))->render(),
                'total_purchase' => currency_format($total_purchase, currency: business_currency())
            ]);
        }

        return redirect(url()->previous());
    }

    public function generatePDF(Request $request)
    {
        $purchases = Purchase::with('details', 'party', 'details.product', 'details.product.category', 'payment_type:id,name')->where('business_id', auth()->user()->business_id)->latest()->get();
        $pdf = Pdf::loadView('business::reports.purchase.pdf', compact('purchases'));
        return $pdf->download('purchase.report.pdf');
    }

    public function exportExcel()
    {
        return Excel::download(new purchaseExport, 'purchase.xlsx');
    }

    public function exportCsv()
    {
        return Excel::download(new purchaseExport, 'purchase.csv');
    }
}

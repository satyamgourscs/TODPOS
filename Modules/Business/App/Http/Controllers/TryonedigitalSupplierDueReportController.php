<?php

namespace Modules\Business\App\Http\Controllers;

use App\Models\Party;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Business\App\Exports\ExportSupplierDue;

class TryonedigitalSupplierDueReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('check.permission:supplier-due-reports.read')->only(['index']);
    }

    public function index()
    {
        $user = auth()->user();
        $businessId = $user->business_id;
        $activeBranch = $user->active_branch;

        $query = Party::where('business_id', $businessId)
            ->where('type', 'Supplier')
            ->with('purchases_dues')
            ->latest();

        if ($activeBranch) {
            // Filter parties that have branch-specific due > 0
            $query->whereHas('purchases_dues', function ($q) use ($activeBranch) {
                $q->where('branch_id', $activeBranch->id)
                    ->where('dueAmount', '>', 0);
            });
        } else {
            $query->where('due', '>', 0);
        }

        $parties = $query->paginate(20);

        // Calculate total due
        $total_due = $parties->sum(function ($supplier) use ($activeBranch) {
            if ($activeBranch) {
                return $supplier->purchases_dues
                    ->where('branch_id', $activeBranch->id)
                    ->sum('dueAmount');
            }
            return $supplier->due;
        });

        // Replace $supplier->due with branch-specific due if active branch exists
        if ($activeBranch) {
            $parties->setCollection(
                $parties->getCollection()
                    ->transform(function ($supplier) use ($activeBranch) {
                        $supplier->due = $supplier->purchases_dues
                            ->where('branch_id', $activeBranch->id)
                            ->sum('dueAmount');
                        return $supplier;
                    })
                    ->filter(fn($supplier) => $supplier->due > 0)
                    ->values()
            );
        }

        return view('business::reports.supplier-due.due-reports', compact('parties', 'total_due'));
    }

    public function tryonedigitalFilter(Request $request)
    {
        $user = auth()->user();
        $businessId = $user->business_id;
        $activeBranch = $user->active_branch;

        $query = Party::where('business_id', $businessId)
            ->where('type', 'Supplier')
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($q2) use ($request) {
                    $q2->where('type', 'like', '%' . $request->search . '%')
                        ->orWhere('name', 'like', '%' . $request->search . '%')
                        ->orWhere('phone', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%');
                });
            })
            ->with('purchases_dues')
            ->latest();

        // Branch-aware due filter
        if ($activeBranch) {
            $query->whereHas('purchases_dues', function ($q) use ($activeBranch) {
                $q->where('branch_id', $activeBranch->id)
                    ->where('dueAmount', '>', 0);
            });
        } else {
            $query->where('due', '>', 0);
        }

        $parties = $query->paginate($request->per_page ?? 10);

        // Replace $supplier->due with branch-specific due if active branch exists
        if ($activeBranch) {
            $parties->setCollection(
                $parties->getCollection()
                    ->transform(function ($supplier) use ($activeBranch) {
                        $supplier->due = $supplier->purchases_dues
                            ->where('branch_id', $activeBranch->id)
                            ->sum('dueAmount');
                        return $supplier;
                    })
                    ->filter(fn($supplier) => $supplier->due > 0)
                    ->values()
            );
        }

        // Calculate total_due
        $total_due = $parties->sum(function ($supplier) {
            return $supplier->due;
        });

        if ($request->ajax()) {
            return response()->json([
                'data' => view('business::reports.supplier-due.datas', compact('parties', 'total_due'))->render()
            ]);
        }

        return redirect()->back();
    }

    public function generatePDF(Request $request)
    {
        $due_lists = Party::where('business_id', auth()->user()->business_id)->where('type','Supplier')->latest()->get();
        $pdf = Pdf::loadView('business::reports.supplier-due.pdf', compact('due_lists'));
        return $pdf->download('supplier.due.pdf');
    }

    public function exportExcel()
    {
        return Excel::download(new ExportSupplierDue, 'supplier-due.xlsx');
    }

    public function exportCsv()
    {
        return Excel::download(new ExportSupplierDue, 'supplier-due.csv');
    }
}

<?php

namespace Modules\Business\App\Http\Controllers;

use App\Models\Party;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Business\App\Exports\ExportDue;

class TryonedigitalDueReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('check.permission:due-reports.read')->only(['index']);
    }

    public function index()
    {
        $user = auth()->user();
        $activeBranch = $user->active_branch;
        $business_id = $user->business_id;

        $query = Party::where('business_id', $business_id)
            ->where('type', '!=', 'Supplier')
            ->with('sales_dues')
            ->latest();

        if ($activeBranch) {
            // Filter customers that have branch-specific due > 0
            $query->whereHas('sales_dues', function ($q) use ($activeBranch) {
                $q->where('branch_id', $activeBranch->id)
                    ->where('dueAmount', '>', 0);
            });
        } else {
            // global due > 0
            $query->where('due', '>', 0);
        }

        $parties = $query->paginate(20);

        // Replace customer due with branch-specific due if active branch exists
        if ($activeBranch) {
            $parties->setCollection(
                $parties->getCollection()
                    ->transform(function ($customer) use ($activeBranch) {
                        $customer->due = $customer->sales_dues
                            ->where('branch_id', $activeBranch->id)
                            ->sum('dueAmount');
                        return $customer;
                    })
                    ->filter(fn($customer) => $customer->due > 0)
                    ->values()
            );
        }

        // Calculate total_due
        $total_due = $parties->sum(fn($customer) => $customer->due);

        return view('business::reports.due.due-reports', compact('parties', 'total_due'));
    }

    public function tryonedigitalFilter(Request $request)
    {
        $user = auth()->user();
        $businessId = $user->business_id;
        $activeBranch = $user->active_branch;

        $query = Party::where('business_id', $businessId)
            ->where('type', '!=', 'Supplier')
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($q2) use ($request) {
                    $q2->where('type', 'like', '%' . $request->search . '%')
                        ->orWhere('name', 'like', '%' . $request->search . '%')
                        ->orWhere('phone', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%');
                });
            })
            ->with('sales_dues')
            ->latest();

        if ($activeBranch) {
            $query->whereHas('sales_dues', function ($q) use ($activeBranch) {
                $q->where('branch_id', $activeBranch->id)
                    ->where('dueAmount', '>', 0);
            });
        } else {
            $query->where('due', '>', 0);
        }

        $parties = $query->paginate($request->per_page ?? 10);

        if ($activeBranch) {
            $parties->setCollection(
                $parties->getCollection()
                    ->transform(function ($customer) use ($activeBranch) {
                        $customer->due = $customer->sales_dues
                            ->where('branch_id', $activeBranch->id)
                            ->sum('dueAmount');
                        return $customer;
                    })
                    ->filter(fn($customer) => $customer->due > 0)
                    ->values()
            );
        }

        $total_due = $parties->sum(fn($customer) => $customer->due);

        if ($request->ajax()) {
            return response()->json([
                'data' => view('business::reports.due.datas', compact('parties', 'total_due'))->render()
            ]);
        }

        return redirect()->back();
    }

    public function generatePDF(Request $request)
    {
        $due_lists = Party::where('business_id', auth()->user()->business_id)->where('type', '!=', 'Supplier')->latest()->get();
        return view('business::reports.due.pdf', compact('due_lists'));
        $pdf = Pdf::loadView('business::reports.due.pdf', compact('due_lists'));
        return $pdf->download('customer.due.pdf');
    }

    public function exportExcel()
    {
        return Excel::download(new ExportDue, 'customer-due.xlsx');
    }

    public function exportCsv()
    {
        return Excel::download(new ExportDue, 'customer-due.csv');
    }
}

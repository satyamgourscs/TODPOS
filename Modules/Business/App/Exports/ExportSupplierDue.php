<?php

namespace Modules\Business\App\Exports;

use App\Models\Party;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ExportSupplierDue implements FromView
{
    public function view(): View
    {
        $user = auth()->user();
        $businessId = $user->business_id;
        $activeBranch = $user->active_branch;

        $query = Party::where('business_id', $businessId)
            ->where('type', 'Supplier')
            ->with('purchases_dues')
            ->latest();

        if ($activeBranch) {
            $query->whereHas('purchases_dues', function ($q) use ($activeBranch) {
                $q->where('branch_id', $activeBranch->id)
                    ->where('dueAmount', '>', 0);
            });
        } else {
            $query->where('due', '>', 0);
        }

        $parties = $query->get();

        if ($activeBranch) {
            $parties->transform(function ($supplier) use ($activeBranch) {
                $supplier->due = $supplier->purchases_dues
                    ->where('branch_id', $activeBranch->id)
                    ->sum('dueAmount');
                return $supplier;
            })->filter(fn($supplier) => $supplier->due > 0);
        }

        return view('business::reports.supplier-due.excel-csv', [
            'parties' => $parties
        ]);
    }
}

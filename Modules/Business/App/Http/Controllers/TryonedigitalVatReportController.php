<?php

namespace Modules\Business\App\Http\Controllers;

use App\Models\Vat;
use App\Models\Sale;
use App\Models\Purchase;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Business\App\Exports\ExportVatReport;

class TryonedigitalVatReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('check.permission:vat-reports.read')->only(['index']);
    }

    public function index()
    {
        $businessId = auth()->user()->business_id;

        $sales = Sale::with('user:id,name', 'party:id,name,email,phone,type', 'business:id,companyName', 'payment_type:id,name')
            ->where('business_id', $businessId)
            ->where('vat_amount', '>', 0)
            ->latest()
            ->paginate(20);

        $purchases = Purchase::with('details', 'party', 'details.product', 'details.product.category', 'payment_type:id,name')
            ->where('business_id', $businessId)
            ->where('vat_amount', '>', 0)
            ->latest()
            ->paginate(20);

        $vats = Vat::where('business_id', auth()->user()->business_id)->whereStatus(1)->get();

        return view('business::reports.vats.index', compact('sales', 'purchases', 'vats'));
    }

    public function exportExcel($type = 'all')
    {
        return $this->exportFile($type, 'vat-report.xlsx');
    }

    public function exportCsv($type = 'all')
    {
        return $this->exportFile($type, 'vat-report.csv');
    }

    private function exportFile($type, $filename, $format = null)
    {
        $businessId = auth()->user()->business_id;

        $sales = collect();
        $purchases = collect();

        if ($type === 'sales' || $type === 'all') {
            $sales = Sale::with('user:id,name', 'party:id,name,email,phone,type', 'payment_type:id,name')
                ->where('business_id', $businessId)
                ->where('vat_amount', '>', 0)
                ->latest()
                ->get();
        }

        if ($type === 'purchases' || $type === 'all') {
            $purchases = Purchase::with('details', 'party', 'details.product', 'details.product.category', 'payment_type:id,name')
                ->where('business_id', $businessId)
                ->where('vat_amount', '>', 0)
                ->latest()
                ->get();
        }

        $vats = Vat::where('business_id', $businessId)->get();

        $export = new ExportVatReport($sales, $purchases, $vats);

        return Excel::download($export, $filename, $format);
    }
}

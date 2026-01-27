<?php

namespace Modules\Business\App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Sale;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Business\App\Exports\ExportCurrentLossProfit;

class TryonedigitalLossProfitController extends Controller
{
    public function __construct()
    {
        $this->middleware('check.permission:loss-profits.read')->only(['index', 'show']);
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

        return view('business::loss-profits.index', compact('loss_profits', 'profit', 'loss', 'total_sale_count'));
    }

    public function tryonedigitalFilter(Request $request)
    {
        $baseQuery = Sale::with('party:id,name')
            ->where('business_id', auth()->user()->business_id)
            ->when($request->custom_days, function ($query) use ($request) {
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

        $loss_profits = $baseQuery->latest()->paginate($request->per_page ?? 10);

        $allMatching = collect($loss_profits->items());

        $loss = $allMatching->where('lossProfit', '<=', 0)->sum(fn($item) => abs($item->lossProfit));
        $profit = $allMatching->where('lossProfit', '>', 0)->sum('lossProfit');
        $total_sale_count = $loss_profits->total();

        if ($request->ajax()) {
            return response()->json([
                'data' => view('business::loss-profits.datas', compact('loss_profits'))->render(),
                'total_loss' => currency_format($loss, currency: business_currency()),
                'total_profit' => currency_format($profit, currency: business_currency()),
                'total_sale_count' => $total_sale_count,
            ]);
        }

        return redirect(url()->previous());
    }

    public function show(string $id)
    {
        $lossProfit = Sale::select('id', 'invoiceNumber', 'party_id')
            ->where('business_id', auth()->user()->business_id)
            ->with('party:id,name', 'details', 'details.product:id,productName')
            ->findOrFail($id);

        return response()->json($lossProfit);
    }

    public function generatePDF(Request $request)
    {
        $loss_profits = Sale::with('party:id,name')->where('business_id', auth()->user()->business_id)->whereYear('created_at', Carbon::now()->year)->latest()->get();
        $pdf = Pdf::loadView('business::loss-profits.pdf', compact('loss_profits'));
        return $pdf->download('loss-profits.pdf');
    }

    public function exportExcel()
    {
        return Excel::download(new ExportCurrentLossProfit, 'loss-profits.xlsx');
    }

    public function exportCsv()
    {
        return Excel::download(new ExportCurrentLossProfit, 'loss-profits.csv');
    }
}

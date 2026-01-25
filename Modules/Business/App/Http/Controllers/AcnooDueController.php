<?php

namespace Modules\Business\App\Http\Controllers;

use App\Models\Sale;
use App\Models\Party;
use App\Models\Purchase;
use App\Models\DueCollect;
use App\Models\PaymentType;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;

class AcnooDueController extends Controller
{
    public function __construct()
    {
        $this->middleware('check.permission:dues.read')->only(['index', 'collectDue']);
    }

    public function index()
    {
        $businessId = auth()->user()->business_id;
        $activeBranch = auth()->user()->active_branch;

        if ($activeBranch) {
            $total_supplier_due = Party::where('business_id', $businessId)
                ->where('type', 'Supplier')
                ->with('purchases_dues')
                ->get()
                ->sum(fn($party) => $party->purchases_dues->sum('dueAmount'));

            $total_customer_due = Party::where('business_id', $businessId)
                ->where('type', '!=', 'Supplier')
                ->with('sales_dues')
                ->get()
                ->sum(fn($party) => $party->sales_dues->sum('dueAmount'));

            $parties = Party::where('business_id', $businessId)
                ->latest()
                ->paginate(20);

            // Replace due with branch-wise due and remove zero-due parties from display
            $parties->getCollection()->transform(function ($party) {
                $party->due = $party->type === 'Supplier'
                    ? $party->purchases_dues->sum('dueAmount')
                    : $party->sales_dues->sum('dueAmount');

                return $party;
            });

            $parties->setCollection(
                $parties->getCollection()->filter(fn($party) => $party->due > 0)
            );

        } else {
            $total_supplier_due = Party::where('business_id', $businessId)
                ->where('type', 'Supplier')
                ->sum('due');

            $total_customer_due = Party::where('business_id', $businessId)
                ->where('type', '!=', 'Supplier')
                ->sum('due');

            $parties = Party::where('business_id', $businessId)
                ->where('due', '>', 0)
                ->latest()
                ->paginate(20);
        }

        return view('business::dues.index', compact('parties', 'total_supplier_due', 'total_customer_due'));
    }

    public function acnooFilter(Request $request)
    {
        $businessId = auth()->user()->business_id;
        $activeBranch = auth()->user()->active_branch;

        $parties = Party::where('business_id', $businessId)
            ->when($request->search, function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('type', 'like', '%' . $request->search . '%')
                        ->orWhere('name', 'like', '%' . $request->search . '%')
                        ->orWhere('phone', 'like', '%' . $request->search . '%')
                        ->orWhere('due', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%');
                });
            })
            ->latest()
            ->paginate($request->per_page ?? 10);

        // Calculate branch-wise due if active branch and remove zero-due parties
        if ($activeBranch) {
            $parties->setCollection(
                $parties->getCollection()->filter(function ($party) {
                    $party->due = $party->type === 'Supplier'
                        ? $party->purchases_dues->sum('dueAmount')
                        : $party->sales_dues->sum('dueAmount');

                    return $party->due > 0;
                })->values()
            );
        }

        if ($request->ajax()) {
            return response()->json([
                'data' => view('business::dues.datas', compact('parties'))->render()
            ]);
        }

        return redirect(url()->previous());
    }

    public function collectDue($id)
    {
        $party = Party::where('business_id', auth()->user()->business_id)->with(['sales_dues', 'purchases_dues'])->findOrFail($id);
        $payment_types = PaymentType::where('business_id', auth()->user()->business_id)->whereStatus(1)->latest()->get();

        $due_amount = 0;
        if ($party->type == 'Supplier') {
            foreach ($party->purchases_dues as $sales_due) {
                $due_amount += $sales_due->dueAmount;
            }
        } else {
            foreach ($party->sales_dues as $sales_due) {
                $due_amount += $sales_due->dueAmount;
            }
        }

        if (auth()->user()->active_branch) {
            $party_opening_due = 0;
        } else {
            $party_opening_due = $party->due - $due_amount;
        }

        return view('business::dues.collect-due', compact('party', 'party_opening_due', 'payment_types'));
    }

    public function collectDueStore(Request $request)
    {
        $party = Party::find($request->party_id);

        $request->validate([
            'payment_type_id' => 'required|exists:payment_types,id',
            'paymentDate' => 'required|string',
            'payDueAmount' => 'required|numeric',
            'party_id' => 'required|exists:parties,id',
            'invoiceNumber' => 'nullable|exists:' . ($party->type == 'Supplier' ? 'purchases' : 'sales') . ',invoiceNumber',
        ]);

        $business_id = auth()->user()->business_id;

        DB::beginTransaction();
        try {
            if (auth()->user()->active_branch && !$request->invoiceNumber) {
                return response()->json([
                    'message' => __('You must select an invoice when login any branch.')
                ], 400);
            }

            $branch_id = null;
            if ($request->invoiceNumber) {
                if ($party->type == 'Supplier') {
                    $invoice = Purchase::where('invoiceNumber', $request->invoiceNumber)->where('party_id', $request->party_id)->first();
                } else {
                    $invoice = Sale::where('invoiceNumber', $request->invoiceNumber)->where('party_id', $request->party_id)->first();
                }

                if (!isset($invoice)) {
                    return response()->json([
                        'message' => 'Invoice Not Found.'
                    ], 404);
                }

                if (!auth()->user()->active_branch) {
                    if (isset($invoice) && isset($invoice->branch_id)) {
                        $branch_id = $invoice->branch_id;
                    }
                }

                if ($invoice->dueAmount < $request->payDueAmount) {
                    return response()->json([
                        'message' => 'Invoice due is ' . $invoice->dueAmount . '. You can not pay more then the invoice due amount.'
                    ], 400);
                }
            }

            if (!$request->invoiceNumber) {
                if ($party->type == 'Supplier') {
                    $all_invoice_due = Purchase::where('party_id', $request->party_id)->sum('dueAmount');
                } else {
                    $all_invoice_due = Sale::where('party_id', $request->party_id)->sum('dueAmount');
                }

                if (($all_invoice_due + $request->payDueAmount) > $party->due) {
                    return response()->json([
                        'message' => __('You can pay only ' . $party->due - $all_invoice_due . ', without selecting an invoice.')
                    ], 400);
                }
            }

            $data = DueCollect::create($request->all() + [
                'user_id' => auth()->id(),
                'business_id' => auth()->user()->business_id,
                'sale_id' => $party->type != 'Supplier' && isset($invoice) ? $invoice->id : NULL,
                'purchase_id' => $party->type == 'Supplier' && isset($invoice) ? $invoice->id : NULL,
                'totalDue' => isset($invoice) ? $invoice->dueAmount : $party->due,
                'dueAmountAfterPay' => isset($invoice) ? ($invoice->dueAmount - $request->payDueAmount) : ($party->due - $request->payDueAmount),
            ]);

            if (isset($invoice)) {
                $invoice->update([
                    'dueAmount' => $invoice->dueAmount - $request->payDueAmount
                ]);
            }

            $party->type == 'Supplier' ? updateBalance($request->payDueAmount, 'decrement', $branch_id) : updateBalance($request->payDueAmount, 'increment', $branch_id);

            $party->update([
                'due' => $party->due - $request->payDueAmount
            ]);

            sendNotifyToUser($data->id, route('business.dues.index', ['id' => $data->id]), __('Due Collection has been created.'), $business_id);

            DB::commit();

            return response()->json([
                'message' => __('Collect Due saved successfully'),
                'redirect' => route('business.dues.index'),
                'secondary_redirect_url' => route('business.collect.dues.invoice', $party->id),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Something went wrong!'], 404);
        }
    }

    public function getInvoice($id)
    {
        $due_collect = DueCollect::with('user:id,name,role', 'party:id,name,email,phone,type', 'payment_type:id,name')
            ->where('business_id', auth()->user()->business_id)
            ->where('party_id', $id)
            ->latest()
            ->first();

        $party = Party::with('dueCollect.business')->find($id);

        return view('business::dues.invoice', compact('due_collect', 'party'));
    }

    public function generatePDF(Request $request, $id)
    {

        $party = Party::with('dueCollect.business')->find($id);

        $due_collects = DueCollect::with('user:id,name', 'party:id,name,email,phone,type', 'payment_type:id,name')
            ->where('business_id', auth()->user()->business_id)
            ->where('party_id', $id)
            ->latest()
            ->get();

        $pdf = Pdf::loadView('business::dues.pdf', compact('due_collects', 'party'));
        return $pdf->download('dues.pdf');
    }

    public function sendMail(Request $request, $id)
    {
        $party = Party::with('dueCollect.business')->find($id);

        $due_collects = DueCollect::with('user:id,name', 'party:id,name,email,phone,type', 'payment_type:id,name')
            ->where('business_id', auth()->user()->business_id)
            ->where('party_id', $id)
            ->latest()
            ->get();
        $pdf = Pdf::loadView('business::dues.pdf', compact('due_collects', 'party'));

        // Send email with PDF attachment
        Mail::raw('Please find attached your Due Collext invoice.', function ($message) use ($pdf) {
            $message->to(auth()->user()->email)
                ->subject('Sales Invoice')
                ->attachData($pdf->output(), 'collect-due.pdf', [
                    'mime' => 'application/pdf',
                ]);
        });

        return response()->json([
            'message' => __('Email Sent Successfully.'),
            'redirect' => route('business.dues.index'),
        ]);
    }

    public function partyDue()
    {
        $user = auth()->user();
        $business_id = $user->business_id;
        $activeBranch = $user->active_branch;
        $party_type = request('type');

        $query = Party::where('business_id', $business_id);

        // Filter by party type
        if ($party_type === 'Customer') {
            $query->whereIn('type', ['Retailer', 'Dealer', 'Wholesaler']);
        } elseif ($party_type === 'Supplier') {
            $query->where('type', 'Supplier');
        }

        $parties = $query->latest()->paginate(20);

        if ($activeBranch) {
            // Calculate branch-wise due and replace $party->due
            $parties->setCollection(
                $parties->getCollection()
                    ->transform(function ($party) {
                        $party->due = $party->type === 'Supplier'
                            ? $party->purchases_dues->sum('dueAmount')
                            : $party->sales_dues->sum('dueAmount');
                        return $party;
                    })
                    ->filter(fn($party) => $party->due > 0)
                    ->values()
            );
        } else {
            // For non-active branch, filter parties with DB due > 0
            $parties->setCollection(
                $parties->getCollection()
                    ->filter(fn($party) => $party->due > 0)
                    ->values()
            );
        }

        return view('business::dues.party.index', compact('parties', 'party_type'));
    }

    public function partyDueFilter(Request $request)
    {
        $user = auth()->user();
        $business_id = $user->business_id;
        $activeBranch = $user->active_branch;
        $party_type = $request->type;

        $query = Party::where('business_id', $business_id);

        // Filter by party type
        if ($party_type === 'Customer') {
            $query->whereIn('type', ['Retailer', 'Dealer', 'Wholesaler']);
        } elseif ($party_type === 'Supplier') {
            $query->where('type', 'Supplier');
        }

        // Apply search
        $query->when($request->search, function ($q) use ($request) {
            $q->where(function ($q) use ($request) {
                $q->where('email', 'like', '%' . $request->search . '%')
                    ->orWhere('name', 'like', '%' . $request->search . '%')
                    ->orWhere('phone', 'like', '%' . $request->search . '%')
                    ->orWhere('due', 'like', '%' . $request->search . '%');
            });
        });

        // Paginate parties
        $parties = $query->latest()->paginate($request->per_page ?? 10);

        if ($activeBranch) {
            // Calculate branch-wise due and replace $party->due, remove zero-due parties
            $parties->setCollection(
                $parties->getCollection()
                    ->transform(function ($party) {
                        $party->due = $party->type === 'Supplier'
                            ? $party->purchases_dues->sum('dueAmount')
                            : $party->sales_dues->sum('dueAmount');
                        return $party;
                    })
                    ->filter(fn($party) => $party->due > 0)
                    ->values()
            );
        } else {
            // Non-active branch: ensure only parties with due > 0
            $parties->setCollection(
                $parties->getCollection()
                    ->filter(fn($party) => $party->due > 0)
                    ->values()
            );
        }

        if ($request->ajax()) {
            return response()->json([
                'data' => view('business::dues.party.datas', compact('parties'))->render()
            ]);
        }

        return redirect()->back();
    }
}

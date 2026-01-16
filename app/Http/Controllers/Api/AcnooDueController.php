<?php

namespace App\Http\Controllers\Api;

use App\Models\Sale;
use App\Models\Party;
use App\Models\Business;
use App\Models\Purchase;
use App\Models\DueCollect;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AcnooDueController extends Controller
{
    public function index()
    {
        $data = DueCollect::with('user:id,name,role', 'party:id,name,email,phone,type', 'payment_type:id,name', 'branch:id,name,phone,address')
                ->where('business_id', auth()->user()->business_id)
                ->latest()
                ->get();

        return response()->json([
            'message' => __('Data fetched successfully.'),
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $party = Party::find($request->party_id);

        $request->validate([
            'payment_type_id' => 'required|exists:payment_types,id',
            'paymentDate' => 'required|string',
            'payDueAmount' => 'required|numeric',
            'party_id' => 'required|exists:parties,id',
            'invoiceNumber' => 'nullable|exists:' . ($party->type == 'Supplier' ? 'purchases' : 'sales') . ',invoiceNumber',
        ]);

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
                    'message' => __('You can pay only '. $party->due - $all_invoice_due .', without selecting an invoice.')
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

        $business = Business::findOrFail(auth()->user()->business_id);
        $business_name = $business->companyName;

        $party->type == 'Supplier' ? updateBalance($request->payDueAmount, 'decrement', $branch_id) : updateBalance($request->payDueAmount, 'increment', $branch_id);

        $party->update([
            'due' => $party->due - $request->payDueAmount
        ]);

        if (env('MESSAGE_ENABLED')) {
            sendMessage($party->phone, dueCollectMessage($data, $party, $business_name, $request->invoiceNumber));
        }

        return response()->json([
            'message' => __('Data fetched successfully.'),
            'data' => $data->load('user:id,name,role', 'party:id,name,email,phone,type','payment_type:id,name', 'branch:id,name,phone,address'),
        ]);
    }
}


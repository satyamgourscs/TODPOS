<?php

namespace Modules\Business\App\Http\Controllers;

use App\Models\PaymentType;
use Carbon\Carbon;
use App\Models\Party;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\PaymentIn;

class PaymentInController extends Controller
{
    public function __construct()
    {
        $this->middleware('check.permission:sales.create')->only(['create', 'store']);
        $this->middleware('check.permission:sales.read')->only(['index', 'show']);
        $this->middleware('check.permission:sales.update')->only(['edit', 'update']);
        $this->middleware('check.permission:sales.delete')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = PaymentIn::with('user:id,name', 'party:id,name,email,phone,type', 'payment_type:id,name')
            ->where('business_id', auth()->user()->business_id)
            ->latest();

        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('payment_date', '>=', $request->from_date);
        }
        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('payment_date', '<=', $request->to_date);
        }

        $payments = $query->paginate(20);
        $customers = Party::where('type', '!=', 'supplier')
            ->where('business_id', auth()->user()->business_id)
            ->latest()
            ->get();

        return view('business::payment-in.index', compact('payments', 'customers'));
    }

    public function create()
    {
        $business_id = auth()->user()->business_id;
        $customers = Party::where('type', '!=', 'supplier')
            ->where('business_id', $business_id)
            ->where('due', '>', 0)
            ->latest()
            ->get();

        $payment_types = PaymentType::where('business_id', $business_id)->whereStatus(1)->latest()->get();

        $payment_id = (PaymentIn::max('id') ?? 0) + 1;
        $payment_no = 'PI-' . str_pad($payment_id, 5, '0', STR_PAD_LEFT);

        return view('business::payment-in.create', compact('customers', 'payment_types', 'payment_no'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'payment_number' => 'required|string',
            'party_id' => 'required|exists:parties,id',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'payment_type_id' => 'required|exists:payment_types,id',
            'reference_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $business_id = auth()->user()->business_id;
            $party = Party::findOrFail($request->party_id);

            if ($request->amount > $party->due) {
                return response()->json(['message' => __('Payment amount cannot exceed due amount.')], 400);
            }

            $payment = PaymentIn::create([
                'user_id' => auth()->id(),
                'business_id' => $business_id,
                'party_id' => $request->party_id,
                'payment_number' => $request->payment_number,
                'payment_date' => $request->payment_date,
                'amount' => $request->amount,
                'payment_type_id' => $request->payment_type_id,
                'reference_number' => $request->reference_number,
                'notes' => $request->notes,
            ]);

            $party->decrement('due', $request->amount);

            DB::commit();

            return response()->json([
                'message' => __('Payment received successfully.'),
                'redirect' => route('business.payment-in.index'),
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function show($id)
    {
        $payment = PaymentIn::with('user', 'party', 'payment_type')
            ->where('business_id', auth()->user()->business_id)
            ->findOrFail($id);

        return view('business::payment-in.show', compact('payment'));
    }
}

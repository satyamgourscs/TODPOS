<?php

namespace Modules\Business\App\Http\Controllers;

use App\Models\PaymentType;
use App\Models\Vat;
use Carbon\Carbon;
use App\Models\Brand;
use App\Models\Party;
use App\Models\Product;
use App\Models\Category;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\CreditNote;

class CreditNoteController extends Controller
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
        $query = CreditNote::with('user:id,name', 'party:id,name,email,phone,type', 'sale:id,invoiceNumber')
            ->where('business_id', auth()->user()->business_id)
            ->latest();

        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('credit_note_date', '>=', $request->from_date);
        }
        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('credit_note_date', '<=', $request->to_date);
        }

        $creditNotes = $query->paginate(20);

        return view('business::credit-notes.index', compact('creditNotes'));
    }

    public function create()
    {
        $business_id = auth()->user()->business_id;
        $customers = Party::where('type', '!=', 'supplier')
            ->where('business_id', $business_id)
            ->latest()
            ->get();

        $sales = Sale::where('business_id', $business_id)
            ->where('party_id', '!=', null)
            ->latest()
            ->get();

        $products = Product::with('category:id,categoryName', 'unit:id,unitName')
            ->where('business_id', $business_id)
            ->latest()
            ->get();

        $credit_note_id = (CreditNote::max('id') ?? 0) + 1;
        $credit_note_no = 'CN-' . str_pad($credit_note_id, 5, '0', STR_PAD_LEFT);

        return view('business::credit-notes.create', compact('customers', 'sales', 'products', 'credit_note_no'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'credit_note_number' => 'required|string',
            'party_id' => 'required|exists:parties,id',
            'sale_id' => 'nullable|exists:sales,id',
            'credit_note_date' => 'required|date',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|numeric|min:1',
            'products.*.price' => 'required|numeric|min:0',
            'reason' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $business_id = auth()->user()->business_id;
            $subtotal = collect($request->products)->sum(fn($p) => $p['quantity'] * $p['price']);
            
            $vat = $request->vat_id ? Vat::find($request->vat_id) : null;
            $vatAmount = $vat ? ($subtotal * $vat->rate) / 100 : 0;
            
            $discountAmount = $request->discountAmount ?? 0;
            $totalAmount = $subtotal + $vatAmount - $discountAmount;

            $creditNote = CreditNote::create([
                'user_id' => auth()->id(),
                'business_id' => $business_id,
                'party_id' => $request->party_id,
                'sale_id' => $request->sale_id,
                'credit_note_number' => $request->credit_note_number,
                'credit_note_date' => $request->credit_note_date,
                'vat_id' => $request->vat_id,
                'vat_amount' => $vatAmount,
                'discountAmount' => $discountAmount,
                'totalAmount' => $totalAmount,
                'reason' => $request->reason,
                'notes' => $request->notes,
            ]);

            foreach ($request->products as $productData) {
                $creditNote->items()->create([
                    'product_id' => $productData['product_id'],
                    'quantity' => $productData['quantity'],
                    'price' => $productData['price'],
                    'total' => $productData['quantity'] * $productData['price'],
                ]);
            }

            if ($request->party_id) {
                $party = Party::find($request->party_id);
                $party->decrement('due', $totalAmount);
            }

            DB::commit();

            return response()->json([
                'message' => __('Credit Note created successfully.'),
                'redirect' => route('business.credit-notes.index'),
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function show($id)
    {
        $creditNote = CreditNote::with('user', 'party', 'sale', 'items.product')
            ->where('business_id', auth()->user()->business_id)
            ->findOrFail($id);

        return view('business::credit-notes.show', compact('creditNote'));
    }
}

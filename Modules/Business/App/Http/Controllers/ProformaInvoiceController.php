<?php

namespace Modules\Business\App\Http\Controllers;

use App\Models\PaymentType;
use App\Models\Vat;
use Carbon\Carbon;
use App\Models\Brand;
use App\Models\Party;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\ProformaInvoice;

class ProformaInvoiceController extends Controller
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
        $query = ProformaInvoice::with('user:id,name', 'party:id,name,email,phone,type')
            ->where('business_id', auth()->user()->business_id)
            ->latest();

        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('invoice_date', '>=', $request->from_date);
        }
        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('invoice_date', '<=', $request->to_date);
        }

        $invoices = $query->paginate(20);

        return view('business::proforma-invoices.index', compact('invoices'));
    }

    public function create()
    {
        $business_id = auth()->user()->business_id;
        $customers = Party::where('type', '!=', 'supplier')
            ->where('business_id', $business_id)
            ->latest()
            ->get();

        $products = Product::with('category:id,categoryName', 'unit:id,unitName', 'stocks')
            ->where('business_id', $business_id)
            ->latest()
            ->get();

        $categories = Category::where('business_id', $business_id)->latest()->get();
        $brands = Brand::where('business_id', $business_id)->latest()->get();
        $vats = Vat::where('business_id', $business_id)->whereStatus(1)->latest()->get();
        $payment_types = PaymentType::where('business_id', $business_id)->whereStatus(1)->latest()->get();

        $invoice_id = (ProformaInvoice::max('id') ?? 0) + 1;
        $invoice_no = 'PF-' . str_pad($invoice_id, 5, '0', STR_PAD_LEFT);

        return view('business::proforma-invoices.create', compact('customers', 'products', 'invoice_no', 'categories', 'brands', 'vats', 'payment_types'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'invoice_number' => 'required|string',
            'party_id' => 'nullable|exists:parties,id',
            'invoice_date' => 'required|date',
            'valid_until' => 'nullable|date',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|numeric|min:1',
            'products.*.price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $business_id = auth()->user()->business_id;
            $subtotal = collect($request->products)->sum(fn($p) => $p['quantity'] * $p['price']);
            
            $vat = $request->vat_id ? Vat::find($request->vat_id) : null;
            $vatAmount = $vat ? ($subtotal * $vat->rate) / 100 : 0;
            
            $discountAmount = $request->discountAmount ?? 0;
            $shippingCharge = $request->shipping_charge ?? 0;
            $totalAmount = $subtotal + $vatAmount - $discountAmount + $shippingCharge;

            $invoice = ProformaInvoice::create([
                'user_id' => auth()->id(),
                'business_id' => $business_id,
                'party_id' => $request->party_id,
                'invoice_number' => $request->invoice_number,
                'invoice_date' => $request->invoice_date,
                'valid_until' => $request->valid_until,
                'vat_id' => $request->vat_id,
                'vat_amount' => $vatAmount,
                'discountAmount' => $discountAmount,
                'shipping_charge' => $shippingCharge,
                'totalAmount' => $totalAmount,
                'notes' => $request->notes,
                'status' => 'pending',
            ]);

            foreach ($request->products as $productData) {
                $invoice->items()->create([
                    'product_id' => $productData['product_id'],
                    'quantity' => $productData['quantity'],
                    'price' => $productData['price'],
                    'total' => $productData['quantity'] * $productData['price'],
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => __('Proforma Invoice created successfully.'),
                'redirect' => route('business.proforma-invoices.index'),
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function show($id)
    {
        $invoice = ProformaInvoice::with('user', 'party', 'items.product')
            ->where('business_id', auth()->user()->business_id)
            ->findOrFail($id);

        return view('business::proforma-invoices.show', compact('invoice'));
    }
}

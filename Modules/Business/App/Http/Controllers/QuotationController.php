<?php

namespace Modules\Business\App\Http\Controllers;

use App\Helpers\HasUploader;
use App\Models\PaymentType;
use App\Models\Vat;
use Carbon\Carbon;
use App\Models\Brand;
use App\Models\Party;
use App\Models\Product;
use App\Models\Business;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Quotation;

class QuotationController extends Controller
{
    use HasUploader;

    public function __construct()
    {
        $this->middleware('check.permission:sales.create')->only(['create', 'store']);
        $this->middleware('check.permission:sales.read')->only(['index', 'show']);
        $this->middleware('check.permission:sales.update')->only(['edit', 'update']);
        $this->middleware('check.permission:sales.delete')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = Quotation::with('user:id,name', 'party:id,name,email,phone,type')
            ->where('business_id', auth()->user()->business_id)
            ->latest();

        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('quotation_date', '>=', $request->from_date);
        }
        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('quotation_date', '<=', $request->to_date);
        }

        $quotations = $query->paginate(20);

        return view('business::quotations.index', compact('quotations'));
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

        $quotation_id = (Quotation::max('id') ?? 0) + 1;
        $quotation_no = 'QT-' . str_pad($quotation_id, 5, '0', STR_PAD_LEFT);

        return view('business::quotations.create', compact('customers', 'products', 'quotation_no', 'categories', 'brands', 'vats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'quotation_number' => 'required|string',
            'party_id' => 'nullable|exists:parties,id',
            'quotation_date' => 'required|date',
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
            $totalAmount = $subtotal + $vatAmount - $discountAmount;

            $quotation = Quotation::create([
                'user_id' => auth()->id(),
                'business_id' => $business_id,
                'party_id' => $request->party_id,
                'quotation_number' => $request->quotation_number,
                'quotation_date' => $request->quotation_date,
                'valid_until' => $request->valid_until,
                'vat_id' => $request->vat_id,
                'vat_amount' => $vatAmount,
                'discountAmount' => $discountAmount,
                'totalAmount' => $totalAmount,
                'notes' => $request->notes,
                'status' => 'pending',
            ]);

            foreach ($request->products as $productData) {
                $quotation->items()->create([
                    'product_id' => $productData['product_id'],
                    'quantity' => $productData['quantity'],
                    'price' => $productData['price'],
                    'total' => $productData['quantity'] * $productData['price'],
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => __('Quotation created successfully.'),
                'redirect' => route('business.quotations.index'),
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function show($id)
    {
        $quotation = Quotation::with('user', 'party', 'items.product')
            ->where('business_id', auth()->user()->business_id)
            ->findOrFail($id);

        return view('business::quotations.show', compact('quotation'));
    }
}

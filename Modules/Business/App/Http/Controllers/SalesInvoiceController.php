<?php

namespace Modules\Business\App\Http\Controllers;

use App\Helpers\HasUploader;
use App\Models\PaymentType;
use App\Models\Stock;
use App\Models\Vat;
use Carbon\Carbon;
use App\Models\Sale;
use App\Models\Brand;
use App\Models\Party;
use App\Models\Product;
use App\Models\Business;
use App\Models\Category;
use App\Models\SaleDetails;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Models\Branch;

class SalesInvoiceController extends Controller
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
        $query = Sale::with('user:id,name', 'branch:id,name', 'party:id,name,email,phone,type', 'details', 'details.product:id,productName,category_id', 'details.product.category:id,categoryName', 'payment_type:id,name')
            ->where('business_id', auth()->user()->business_id)
            ->latest();

        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('saleDate', '>=', $request->from_date);
        }
        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('saleDate', '<=', $request->to_date);
        }

        $sales = $query->paginate(20);
        $branches = Branch::withTrashed()->where('business_id', auth()->user()->business_id)->latest()->get();

        return view('business::sales-invoices.index', compact('sales', 'branches'));
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
            ->withSum('stocks as total_stock', 'productStock')
            ->having('total_stock', '>', 0)
            ->latest()
            ->get();

        $categories = Category::where('business_id', $business_id)->latest()->get();
        $brands = Brand::where('business_id', $business_id)->latest()->get();
        $vats = Vat::where('business_id', $business_id)->whereStatus(1)->latest()->get();
        $payment_types = PaymentType::where('business_id', $business_id)->whereStatus(1)->latest()->get();

        $sale_id = (Sale::max('id') ?? 0) + 1;
        $invoice_no = 'SI-' . str_pad($sale_id, 5, '0', STR_PAD_LEFT);

        return view('business::sales-invoices.create', compact('customers', 'products', 'invoice_no', 'categories', 'brands', 'vats', 'payment_types'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'invoiceNumber' => 'required|string',
            'party_id' => 'nullable|exists:parties,id',
            'saleDate' => 'required|date',
            'payment_type_id' => 'required|exists:payment_types,id',
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
            
            $paidAmount = $request->paidAmount ?? 0;
            $dueAmount = max(0, $totalAmount - $paidAmount);

            $sale = Sale::create([
                'user_id' => auth()->id(),
                'business_id' => $business_id,
                'branch_id' => auth()->user()->branch_id ?? session('branch_id'),
                'type' => 'sale',
                'party_id' => $request->party_id,
                'invoiceNumber' => $request->invoiceNumber,
                'saleDate' => $request->saleDate,
                'vat_id' => $request->vat_id,
                'vat_amount' => $vatAmount,
                'discountAmount' => $discountAmount,
                'totalAmount' => $totalAmount,
                'paidAmount' => $paidAmount,
                'dueAmount' => $dueAmount,
                'payment_type_id' => $request->payment_type_id,
                'shipping_charge' => $shippingCharge,
                'isPaid' => $dueAmount <= 0,
            ]);

            foreach ($request->products as $productData) {
                $stock = Stock::where('product_id', $productData['product_id'])
                    ->where('productStock', '>=', $productData['quantity'])
                    ->first();

                if (!$stock) {
                    throw new \Exception(__('Insufficient stock for product.'));
                }

                SaleDetails::create([
                    'sale_id' => $sale->id,
                    'product_id' => $productData['product_id'],
                    'stock_id' => $stock->id,
                    'quantities' => $productData['quantity'],
                    'price' => $productData['price'],
                    'total' => $productData['quantity'] * $productData['price'],
                ]);

                $stock->decrement('productStock', $productData['quantity']);
            }

            if ($request->party_id && $dueAmount > 0) {
                $party = Party::find($request->party_id);
                $party->increment('due', $dueAmount);
            }

            DB::commit();

            return response()->json([
                'message' => __('Sales Invoice created successfully.'),
                'redirect' => route('business.sales-invoices.index'),
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function show($id)
    {
        $sale = Sale::with('user', 'party', 'details.product', 'payment_type', 'branch')
            ->where('business_id', auth()->user()->business_id)
            ->findOrFail($id);

        return view('business::sales-invoices.show', compact('sale'));
    }

    public function createCustomer(Request $request)
    {
        // Validate required fields
        $rules = [
            'name' => 'required|string|max:255',
            'phone' => 'required|max:20|' . Rule::unique('parties')->where('business_id', auth()->user()->business_id),
            'type' => 'nullable|string|in:Retailer,Dealer,Wholesaler,Supplier',
            'email' => 'nullable|email',
            'image' => 'nullable|image',
            'address' => 'nullable|string|max:255',
            'due' => 'nullable|numeric|min:0',
            'have_gst' => 'nullable|boolean',
            'gst' => 'nullable|string|max:50',
            'state' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'house_no' => 'nullable|string|max:50',
        ];

        // If have_gst is checked, GST is required
        if ($request->has('have_gst') && $request->have_gst) {
            $rules['gst'] = 'required|string|max:50';
        }

        $request->validate($rules);

        // Prepare data
        $data = $request->except('image', 'due', 'have_gst');
        
        // Handle GST - only save if have_gst is checked
        if (!$request->has('have_gst') || !$request->have_gst) {
            $data['gst'] = null;
        }

        $party = Party::create($data + [
            'due' => $request->due ?? 0,
            'type' => $request->type ?? 'Retailer',
            'image' => $request->image ? $this->upload($request, 'image') : NULL,
            'business_id' => auth()->user()->business_id
        ]);

        return response()->json([
            'message'   => __('Customer created successfully'),
            'customer'  => [
                'id' => $party->id,
                'name' => $party->name,
                'type' => $party->type,
                'phone' => $party->phone,
                'due' => $party->due ?? 0,
            ]
        ]);
    }
}

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
use App\Models\DeliveryChallan;

class DeliveryChallanController extends Controller
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
        $query = DeliveryChallan::with('user:id,name', 'party:id,name,email,phone,type', 'sale:id,invoiceNumber')
            ->where('business_id', auth()->user()->business_id)
            ->latest();

        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('challan_date', '>=', $request->from_date);
        }
        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('challan_date', '<=', $request->to_date);
        }

        $challans = $query->paginate(20);

        return view('business::delivery-challans.index', compact('challans'));
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

        $products = Product::with('category:id,categoryName', 'unit:id,unitName', 'stocks')
            ->where('business_id', $business_id)
            ->latest()
            ->get();

        $challan_id = (DeliveryChallan::max('id') ?? 0) + 1;
        $challan_no = 'DC-' . str_pad($challan_id, 5, '0', STR_PAD_LEFT);

        return view('business::delivery-challans.create', compact('customers', 'sales', 'products', 'challan_no'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'challan_number' => 'required|string',
            'party_id' => 'required|exists:parties,id',
            'sale_id' => 'nullable|exists:sales,id',
            'challan_date' => 'required|date',
            'delivery_address' => 'required|string',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|numeric|min:1',
        ]);

        DB::beginTransaction();
        try {
            $business_id = auth()->user()->business_id;

            $challan = DeliveryChallan::create([
                'user_id' => auth()->id(),
                'business_id' => $business_id,
                'party_id' => $request->party_id,
                'sale_id' => $request->sale_id,
                'challan_number' => $request->challan_number,
                'challan_date' => $request->challan_date,
                'delivery_address' => $request->delivery_address,
                'vehicle_number' => $request->vehicle_number,
                'driver_name' => $request->driver_name,
                'notes' => $request->notes,
                'status' => 'pending',
            ]);

            foreach ($request->products as $productData) {
                $challan->items()->create([
                    'product_id' => $productData['product_id'],
                    'quantity' => $productData['quantity'],
                    'description' => $productData['description'] ?? null,
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => __('Delivery Challan created successfully.'),
                'redirect' => route('business.delivery-challans.index'),
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function show($id)
    {
        $challan = DeliveryChallan::with('user', 'party', 'sale', 'items.product')
            ->where('business_id', auth()->user()->business_id)
            ->findOrFail($id);

        return view('business::delivery-challans.show', compact('challan'));
    }
}

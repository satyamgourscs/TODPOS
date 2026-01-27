<?php

namespace Modules\Business\App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Vat;
use App\Models\Brand;
use App\Models\Party;
use App\Models\Stock;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Business;
use App\Models\Category;
use App\Models\Purchase;
use App\Models\PaymentType;
use App\Helpers\HasUploader;
use Illuminate\Http\Request;
use App\Models\PurchaseReturn;
use App\Models\PurchaseDetails;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PurchaseProductImport;
use Gloudemans\Shoppingcart\Facades\Cart;

class TryonedigitalPurchaseController extends Controller
{
    use HasUploader;

    public function __construct()
    {
        $this->middleware('check.permission:purchases.read')->only(['index']);
        $this->middleware('check.permission:purchases.create')->only(['create', 'store']);
        $this->middleware('check.permission:purchases.update')->only(['edit', 'update']);
        $this->middleware('check.permission:purchases.delete')->only(['destroy', 'deleteAll']);
    }

    public function index(Request $request)
    {
        $purchasesWithReturns = PurchaseReturn::where('business_id', auth()->user()->business_id)
            ->pluck('purchase_id')
            ->toArray();

        $query = Purchase::with('details', 'branch:id,name', 'party', 'details.product', 'details.product.category', 'payment_type:id,name')
            ->where('business_id', auth()->user()->business_id)
            ->whereDate('purchaseDate', Carbon::today());

        if ($request->today) {
            $query->whereDate('purchaseDate', Carbon::today());
        }

        $purchases = $query->latest()->paginate(20);

        $branches = Branch::withTrashed()->where('business_id', auth()->user()->business_id)->latest()->get();

        return view('business::purchases.index', compact('purchases', 'purchasesWithReturns', 'branches'));
    }

    public function tryonedigitalFilter(Request $request)
    {
        $business_id = auth()->user()->business_id;

        $purchasesWithReturns = PurchaseReturn::where('business_id', $business_id)
            ->pluck('purchase_id')
            ->toArray();

        $purchasesQuery = Purchase::with('user:id,name', 'party:id,name,email,phone,type', 'payment_type:id,name', 'branch:id,name')
            ->where('business_id', $business_id);

        $purchasesQuery->when($request->branch_id, function ($q) use ($request) {
            $q->where('branch_id', $request->branch_id);
        });

        // Default to today
        $startDate = Carbon::today()->format('Y-m-d');
        $endDate = Carbon::today()->format('Y-m-d');

        if ($request->custom_days === 'yesterday') {
            $startDate = Carbon::yesterday()->format('Y-m-d');
            $endDate = Carbon::yesterday()->format('Y-m-d');
        } elseif ($request->custom_days === 'last_seven_days') {
            $startDate = Carbon::today()->subDays(6)->format('Y-m-d');
        } elseif ($request->custom_days === 'last_thirty_days') {
            $startDate = Carbon::today()->subDays(29)->format('Y-m-d');
        } elseif ($request->custom_days === 'current_month') {
            $startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        } elseif ($request->custom_days === 'last_month') {
            $startDate = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');
        } elseif ($request->custom_days === 'current_year') {
            $startDate = Carbon::now()->startOfYear()->format('Y-m-d');
            $endDate = Carbon::now()->endOfYear()->format('Y-m-d');
        } elseif ($request->custom_days === 'custom_date' && $request->from_date && $request->to_date) {
            $startDate = Carbon::parse($request->from_date)->format('Y-m-d');
            $endDate = Carbon::parse($request->to_date)->format('Y-m-d');
        }

        $purchasesQuery->whereDate('purchaseDate', '>=', $startDate)
            ->whereDate('purchaseDate', '<=', $endDate);

        // Search Filter
        if ($request->filled('search')) {
            $purchasesQuery->where(function ($query) use ($request) {
                $query->where('paymentType', 'like', '%' . $request->search . '%')
                    ->orWhere('invoiceNumber', 'like', '%' . $request->search . '%')
                    ->orWhereHas('party', function ($q) use ($request) {
                        $q->where('name', 'like', '%' . $request->search . '%');
                    })
                    ->orWhereHas('payment_type', function ($q) use ($request) {
                        $q->where('name', 'like', '%' . $request->search . '%');
                    })
                    ->orWhereHas('branch', function ($q) use ($request) {
                        $q->where('name', 'like', '%' . $request->search . '%');
                    });
            });
        }

        $perPage = $request->input('per_page', 10);
        $purchases = $purchasesQuery->latest()->paginate($perPage);

        if ($request->ajax()) {
            return response()->json([
                'data' => view('business::purchases.datas', compact('purchases', 'purchasesWithReturns'))->render()
            ]);
        }

        return redirect(url()->previous());
    }

    public function productFilter(Request $request)
    {
        $business_id = auth()->user()->business_id;
        $products = Product::where('business_id', $business_id)
            ->when($request->search, function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('productName', 'like', '%' . $request->search . '%')
                        ->orWhere('productCode', 'like', '%' . $request->search . '%');
                });
            })
            ->when($request->category_id, function ($query) use ($request) {
                $query->where('category_id', $request->category_id);
            })
            ->when($request->brand_id, function ($query) use ($request) {
                $query->where('brand_id', $request->brand_id);
            })
            ->withSum('stocks as total_stock', 'productStock')
            ->latest()
            ->get();

        // Query categories for search options
        $categories = Category::where('business_id', $business_id)
            ->when($request->search, function ($query) use ($request) {
                $query->where('categoryName', 'like', '%' . $request->search . '%');
            })
            ->get();

        // Query brands for search options
        $brands = Brand::where('business_id', $business_id)
            ->when($request->search, function ($query) use ($request) {
                $query->where('brandName', 'like', '%' . $request->search . '%');
            })
            ->get();

        $total_products = $products->count();

        if ($request->ajax()) {
            return response()->json([
                'total_products' => $total_products,
                'product_id' => $total_products == 1 ? $products->first()->id : null,
                'data' => view('business::purchases.product-list', compact('products'))->render(),
                'categories' => view('business::purchases.category-list', compact('categories'))->render(),
                'brands' => view('business::purchases.brand-list', compact('brands'))->render(),
            ]);
        }

        return redirect(url()->previous());
    }

    // Category search Filter
    public function categoryFilter(Request $request)
    {
        $search = $request->search;
        $categories = Category::where('business_id', auth()->user()->business_id)
            ->when($search, function ($query) use ($search) {
                $query->where('categoryName', 'like', '%' . $search . '%');
            })
            ->get();

        return response()->json([
            'categories' => view('business::purchases.category-list', compact('categories'))->render(),
        ]);
    }

    // Brand search Filter
    public function brandFilter(Request $request)
    {
        $search = $request->search;
        $brands = Brand::where('business_id', auth()->user()->business_id)
            ->when($search, function ($query) use ($search) {
                $query->where('brandName', 'like', '%' . $search . '%');
            })
            ->get();

        return response()->json([
            'brands' => view('business::purchases.brand-list', compact('brands'))->render(),
        ]);
    }

    public function getInvoice($purchase_id)
    {
        $purchase = Purchase::with('user:id,name,role', 'party:id,name,phone', 'business:id,phoneNumber,companyName,vat_name,vat_no,address,email', 'details:id,productPurchasePrice,quantities,product_id,purchase_id', 'details.stock:id,batch_no', 'details.product:id,productName', 'payment_type:id,name')
            ->findOrFail($purchase_id);

        $purchase_returns = PurchaseReturn::with('purchase:id,party_id,isPaid,totalAmount,dueAmount,paidAmount,invoiceNumber', 'purchase.party:id,name', 'details', 'details.purchaseDetail.product:id,productName', 'details.purchaseDetail.stock:id,batch_no')
            ->where('business_id', auth()->user()->business_id)
            ->where('purchase_id', $purchase_id)
            ->latest()
            ->get();

        // sum of  return_qty
        $purchase->details = $purchase->details->map(function ($detail) use ($purchase_returns) {
            $return_qty_sum = $purchase_returns->flatMap(function ($return) use ($detail) {
                return $return->details->where('purchaseDetail.id', $detail->id)->pluck('return_qty');
            })->sum();

            $detail->quantities = $detail->quantities + $return_qty_sum;

            return $detail;
        });

        // Calculate total discount based on return quantities and amounts
        $total_discount = 0;
        $product_discounts = [];

        foreach ($purchase_returns as $return) {
            foreach ($return->details as $detail) {
                // Initialize discount tracking for the first occurrence of each purchase_detail_id
                if (!isset($product_discounts[$detail->purchase_detail_id])) {
                    $product_discounts[$detail->purchase_detail_id] = [
                        'return_qty' => 0,
                        'return_amount' => 0,
                        'price' => $detail->purchaseDetail->productPurchasePrice,
                    ];
                }

                // Accumulate return quantities and return amounts
                $product_discounts[$detail->purchase_detail_id]['return_qty'] += $detail->return_qty;
                $product_discounts[$detail->purchase_detail_id]['return_amount'] += $detail->return_amount;
            }
        }

        // Calculate the total discount for each returned product
        foreach ($product_discounts as $data) {
            $product_price = $data['price'] * $data['return_qty'];
            $discount = $product_price - $data['return_amount'];

            $total_discount += $discount;
        }

        return view('business::purchases.invoice', compact('purchase', 'purchase_returns', 'total_discount'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $business_id = auth()->user()->business_id;

        // Clears all cart items
        Cart::destroy();

        $suppliers = Party::where('type', 'Supplier')
            ->where('business_id', $business_id)
            ->latest()
            ->get();

        $products = Product::with([
            'category:id,categoryName',
            'unit:id,unitName',
            'brand:id,brandName',
        ])
            ->withSum('stocks', 'productStock')
            ->where('business_id', $business_id)
            ->latest()
            ->get();

        $cart_contents = Cart::content()->filter(fn($item) => $item->options->type == 'purchase');
        $categories = Category::where('business_id', $business_id)->latest()->get();
        $brands = Brand::where('business_id', $business_id)->latest()->get();
        $vats = Vat::where('business_id', $business_id)->whereStatus(1)->latest()->get();
        $payment_types = PaymentType::where('business_id', $business_id)->whereStatus(1)->latest()->get();

        // Generate a unique invoice number
        $purchase_id = (Purchase::max('id') ?? 0) + 1;
        $invoice_no = 'P-' . str_pad($purchase_id, 5, '0', STR_PAD_LEFT);

        return view('business::purchases.create', compact('suppliers', 'products', 'cart_contents', 'invoice_no', 'categories', 'brands', 'vats', 'payment_types'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'party_id' => 'required|exists:parties,id',
            'invoiceNumber' => 'required|string',
            'receive_amount' => 'nullable|numeric',
            'payment_type_id' => 'required|exists:payment_types,id',
            'vat_id' => 'nullable|exists:vats,id',
            'discountAmount' => 'nullable|numeric',
            'discount_type' => 'nullable|in:flat,percent',
            'shipping_charge' => 'nullable|numeric',
            'purchaseDate' => 'nullable|date',
        ]);

        $business_id = auth()->user()->business_id;

        // Check each cart item for batch duplication in Stock table
        $carts = Cart::content()->filter(fn($item) => $item->options->type == 'purchase');

        if ($carts->count() < 1) {
            return response()->json(['message' => __('Cart is empty. Add items first!')], 400);
        }

        DB::beginTransaction();
        try {
            // Subtotal
            $subtotal = $carts->sum(fn($cartItem) => (float)$cartItem->subtotal);

            // VAT
            $vat = Vat::find($request->vat_id);
            $vatAmount = $vat ? ($subtotal * $vat->rate) / 100 : 0;

            // Subtotal with GST
            $subtotalWithVat = $subtotal + $vatAmount;

            // Discount
            $discountAmount = $request->discountAmount ?? 0;
            if ($request->discount_type === 'percent') {
                $discountAmount = ($subtotalWithVat * $discountAmount) / 100;
            }
            if ($discountAmount > $subtotalWithVat) {
                return response()->json(['message' => __('Discount cannot be more than subtotal with GST!')], 400);
            }

            // Charges and totals
            $shippingCharge = $request->shipping_charge ?? 0;
            $totalAmount = $subtotalWithVat + $shippingCharge - $discountAmount;

            $receiveAmount = $request->receive_amount ?? 0;
            $changeAmount = max($receiveAmount - $totalAmount, 0);
            $dueAmount = max($totalAmount - $receiveAmount, 0);
            $paidAmount = $receiveAmount - $changeAmount;

            // Party due update
            if ($dueAmount > 0) {
                $party = Party::findOrFail($request->party_id);

                // Check party credit limit
                $newTotalDue = $party->due + $dueAmount;
                if ($party->credit_limit > 0 && $newTotalDue > $party->credit_limit) {
                    return response()->json([
                        'message' => __('Cannot create purchase. Party due will exceed credit limit!')
                    ], 400);
                }

                $party->update([
                    'due' => $newTotalDue
                ]);
            }

            // Business balance update
            updateBalance($paidAmount, 'decrement');

            // Create Purchase
            $purchase = Purchase::create($request->except('discountAmount', 'discount_type', 'discount_percent', 'shipping_charge') + [
                'business_id' => $business_id,
                'user_id' => auth()->id(),
                'vat_id' => $request->vat_id,
                'vat_amount' => $vatAmount,
                'discountAmount' => $discountAmount,
                'discount_type' => $request->discount_type ?? 'flat',
                'discount_percent' => $request->discount_type == 'percent' ? $request->discountAmount : 0,
                'totalAmount' => $totalAmount,
                'paidAmount' => $paidAmount,
                'change_amount' => $changeAmount,
                'dueAmount' => $dueAmount,
                'payment_type_id' => $request->payment_type_id,
                'shipping_charge' => $shippingCharge,
                'purchaseDate' => $request->purchaseDate ?? now(),
                'isPaid' => $dueAmount > 0 ? 0 : 1,
            ]);

            $purchase_details = [];

            // Insert Purchase Details and Create Stocks
            foreach ($carts as $cartItem) {

                $batchNo = $cartItem->options['batch_no'] ?? null;
                $expireDate = $cartItem->options['expire_date'] ?? null;
                $existingStock = Stock::where(['batch_no' => $batchNo, 'product_id' => $cartItem->id])->first();

                $stock = Stock::updateOrCreate(
                    ['batch_no' => $batchNo, 'business_id' => $business_id, 'product_id' => $cartItem->id],
                    [
                        'product_id' => $cartItem->id,
                        'expire_date' => $expireDate,
                        'productPurchasePrice' => $cartItem->price,
                        'productSalePrice' => $cartItem->options['sales_price'],
                        'productWholeSalePrice' => $cartItem->options['whole_sale_price'],
                        'productDealerPrice' => $cartItem->options['dealer_price'],
                        'productStock' => $cartItem->qty + ($existingStock->productStock ?? 0),
                    ]
                );

                // purchase detail
                $purchase_details[] = [
                    'stock_id' => $stock->id,
                    'purchase_id' => $purchase->id,
                    'product_id' => $cartItem->id,
                    'quantities' => $cartItem->qty,
                    'productPurchasePrice' => $cartItem->price,
                    'productDealerPrice' => $cartItem->options['dealer_price'],
                    'expire_date' => $expireDate,
                    'productSalePrice' => $cartItem->options['sales_price'],
                    'productWholeSalePrice' => $cartItem->options['whole_sale_price'],
                ];
            }

            PurchaseDetails::insert($purchase_details);

            // Clear cart
            foreach ($carts as $cartItem) {
                Cart::remove($cartItem->rowId);
            }

            sendNotifyToUser($purchase->id, route('business.purchases.index', ['id' => $purchase->id]), __('New Purchase created.'), $business_id);

            DB::commit();

            return response()->json([
                'message' => __('Purchase created successfully.'),
                'redirect' => route('business.purchases.index'),
                'secondary_redirect_url' => route('business.purchases.invoice', $purchase->id),
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => __('Something went wrong!')], 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $business_id = auth()->user()->business_id;

        // Clears all cart items
        Cart::destroy();

        $purchase = Purchase::with('details', 'details.product', 'details.stock:id,batch_no', 'details.product.unit', 'payment_type:id,name')
            ->where('business_id', $business_id)
            ->findOrFail($id);

        $suppliers = Party::where('type', 'Supplier')
            ->where('business_id', $business_id)
            ->latest()
            ->get();

        $products = Product::with('category:id,categoryName', 'unit:id,unitName', 'brand:id,brandName')
            ->where('business_id', $business_id)
            ->withSum('stocks as total_stock', 'productStock')
            ->having('total_stock', '>', 0)
            ->latest()
            ->get();

        $categories = Category::where('business_id', $business_id)->latest()->get();
        $brands = Brand::where('business_id', $business_id)->latest()->get();
        $vats = Vat::where('business_id', $business_id)->whereStatus(1)->latest()->get();
        $payment_types = PaymentType::where('business_id', $business_id)->whereStatus(1)->latest()->get();

        // Add purchase details to the cart
        foreach ($purchase->details as $detail) {
            // Add to cart
            Cart::add([
                'id' => $detail->product_id,
                'name' => $detail->product->productName ?? '',
                'qty' => $detail->quantities,
                'price' => $detail->productPurchasePrice,
                'options' => [
                    'type' => 'purchase',
                    'product_code' => $detail->product->productCode ?? '',
                    'product_unit_id' => $detail->product->unit_id ?? null,
                    'product_unit_name' => $detail->product->unit->unitName ?? '',
                    'product_image' => $detail->product->productPicture ?? '',
                    'sales_price' => $detail->productSalePrice,
                    'whole_sale_price' => $detail->productWholeSalePrice,
                    'dealer_price' => $detail->productDealerPrice,
                    'batch_no' => $detail->stock->batch_no ?? null,
                    'expire_date' => $detail->expire_date,
                ],
            ]);
        }

        $cart_contents = Cart::content()->filter(fn($item) => $item->options->type == 'purchase');

        return view('business::purchases.edit', compact('purchase', 'suppliers', 'products', 'cart_contents', 'categories', 'brands', 'vats', 'payment_types'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'party_id' => 'required|exists:parties,id',
            'invoiceNumber' => 'required|string',
            'receive_amount' => 'nullable|numeric',
            'payment_type_id' => 'required|exists:payment_types,id',
            'vat_id' => 'nullable|exists:vats,id',
            'discountAmount' => 'nullable|numeric',
            'discount_type' => 'nullable|in:flat,percent',
            'shipping_charge' => 'nullable|numeric',
            'purchaseDate' => 'nullable|date',
        ]);

        $business_id = auth()->user()->business_id;

        $carts = Cart::content()->filter(fn($item) => $item->options->type === 'purchase');

        if ($carts->count() < 1) {
            return response()->json(['message' => __('Cart is empty. Add items first!')], 400);
        }

        DB::beginTransaction();
        try {
            $purchase = Purchase::with('details')->findOrFail($id);

            // Calculate amounts
            $subtotal = $carts->sum(fn($cartItem) => (float)$cartItem->subtotal);

            // VAT
            $vat = Vat::find($request->vat_id);
            $vatAmount = 0;
            if ($vat) {
                $vatAmount = ($subtotal * $vat->rate) / 100;
            }

            // Subtotal with GST
            $subtotalWithVat = $subtotal + $vatAmount;

            // Discount
            $discountAmount = $request->discountAmount ?? 0;
            if ($request->discount_type == 'percent') {
                $discountAmount = ($subtotalWithVat * $discountAmount) / 100;
            }
            if ($discountAmount > $subtotalWithVat) {
                return response()->json([
                    'message' => __('Discount cannot be more than subtotal with GST!')
                ], 400);
            }

            // Shipping Charge
            $shippingCharge = $request->shipping_charge ?? 0;

            // Total Amount
            $totalAmount = $subtotalWithVat - $discountAmount + $shippingCharge;

            // Receive, Change, Due Amount Calculation
            $receiveAmount = $request->receive_amount ?? 0;
            $changeAmount = $receiveAmount > $totalAmount ? $receiveAmount - $totalAmount : 0;
            $dueAmount = max($totalAmount - $receiveAmount, 0);
            $paidAmount = $receiveAmount - $changeAmount;

            if ($purchase->dueAmount || $dueAmount) {
                $party = Party::findOrFail($request->party_id);

                // If same party, adjust old due and add new one
                $newDue = $request->party_id == $purchase->party_id ? ($party->due - $purchase->dueAmount) + $dueAmount : $party->due + $dueAmount;

                // Check credit limit
                if ($party->credit_limit > 0 && $newDue > $party->credit_limit) {
                    return response()->json(['message' => __('Party credit limit exceeded!')], 400);
                }

                $party->update(['due' => $newDue]);

                // If changed to a new party, reduce previous party’s due
                if ($request->party_id != $purchase->party_id) {
                    $prev_party = Party::findOrFail($purchase->party_id);
                    $prev_party->update(['due' => $prev_party->due - $purchase->dueAmount]);
                }
            }

            // branch/business balance
            updateBalance($purchase->paidAmount - $paidAmount, 'decrement');

            // Update purchase details
            $purchase->update($request->except('discountAmount', 'discount_type', 'discount_percent', 'shipping_charge') + [
                'business_id' => auth()->user()->business_id,
                'user_id' => auth()->id(),
                'vat_id' => $request->vat_id,
                'vat_amount' => $vatAmount,
                'discountAmount' => $discountAmount,
                'discount_type' => $request->discount_type ?? 'flat',
                'discount_percent' => $request->discount_type == 'percent' ? $request->discountAmount : 0,
                'totalAmount' => $totalAmount,
                'paidAmount' => $paidAmount,
                'change_amount' => $changeAmount,
                'dueAmount' => $dueAmount,
                'payment_type_id' => $request->payment_type_id,
                'shipping_charge' => $shippingCharge,
                'purchaseDate' => $request->purchaseDate ?? now(),
                'isPaid' => $dueAmount > 0 ? 0 : 1,
            ]);

            // Revert previous stock changes
            foreach ($purchase->details as $detail) {
                Stock::where('id', $detail->stock_id)->decrement('productStock', $detail->quantities);
            }

            // Delete existing purchase details
            $purchase->details()->delete();

            // Insert updated purchase details and adjust stock
            $purchaseDetailsData = [];
            foreach ($carts as $cartItem) {

                $batch_no = $cartItem->options['batch_no'] ?? NULL;
                $expire_date = $cartItem->options['expire_date'] ?? NULL;
                $dealer_price = $cartItem->options['dealer_price'] ?? 0.0;
                $sales_price = $cartItem->options['sales_price'] ?? 0.0;
                $whole_sale_price = $cartItem->options['whole_sale_price'] ?? 0.0;

                $existingStock = Stock::where(['batch_no' => $batch_no, 'product_id' => $cartItem->id])->first();

                // update or create stock
                $stock = Stock::updateOrCreate(
                    ['batch_no' => $batch_no, 'business_id' => $business_id, 'product_id' => $cartItem->id],
                    [
                        'product_id' => $cartItem->id,
                        'expire_date' => $expire_date,
                        'productSalePrice' => $sales_price,
                        'productDealerPrice' => $dealer_price,
                        'productPurchasePrice' => $cartItem->price,
                        'productWholeSalePrice' => $whole_sale_price,
                        'productStock' => $cartItem->qty + ($existingStock->productStock ?? 0),
                    ]
                );

                $purchaseDetailsData[] = [
                    'purchase_id' => $purchase->id,
                    'product_id' => $cartItem->id,
                    'quantities' => $cartItem->qty,
                    'productPurchasePrice' => $cartItem->price,
                    'productDealerPrice' => $dealer_price,
                    'stock_id' => $stock->id,
                    'expire_date' => $expire_date,
                    'productSalePrice' => $sales_price,
                    'productWholeSalePrice' => $whole_sale_price,
                ];
            }

            PurchaseDetails::insert($purchaseDetailsData);

            foreach ($purchaseDetailsData as $item) {
                $product = Product::findOrFail($item['product_id']);
                $product->update([
                    'productSalePrice' => $item['productSalePrice'] ?? $product->productSalePrice,
                    'productDealerPrice' => $item['productDealerPrice'] ?? $product->productDealerPrice,
                    'productPurchasePrice' => $item['productPurchasePrice'] ?? $product->productPurchasePrice,
                    'productWholeSalePrice' => $item['productWholeSalePrice'] ?? $product->productWholeSalePrice,
                ]);
            }

            // Clear the cart
            foreach ($carts as $cartItem) {
                Cart::remove($cartItem->rowId);
            }

            sendNotifyToUser($purchase->id, route('business.purchases.index', ['id' => $purchase->id]), __('Purchase has been updated.'), $business_id);

            DB::commit();
            return response()->json([
                'message' => __('Purchase updated successfully.'),
                'redirect' => route('business.purchases.index'),
                'secondary_redirect_url' => route('business.purchases.invoice', $purchase->id),
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => __('Something went wrong!')], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $purchase = Purchase::with('details')->findOrFail($id);

            foreach ($purchase->details as $detail) {
                Stock::where('id', $detail->stock_id)->decrement('productStock', $detail->quantities);
            }

            if ($purchase->party_id) {
                $party = Party::findOrFail($purchase->party_id);
                $party->update([
                    'due' => $party->due - $purchase->dueAmount
                ]);
            }

            updateBalance($purchase->paidAmount, 'increment');

            sendNotifyToUser($purchase->id, route('business.purchases.index', ['id' => $purchase->id]), __('Purchase has been deleted.'), $purchase->business_id);

            $purchase->delete();

            // Clears all cart items
            Cart::destroy();

            DB::commit();

            return response()->json([
                'message' => __('Purchase deleted successfully.'),
                'redirect' => route('business.purchases.index')
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => __('Something went wrong!')], 404);
        }
    }

    public function showPurchaseCart()
    {
        $cart_contents = Cart::content()->filter(fn($item) => $item->options->type == 'purchase');
        return view('business::purchases.cart-list', compact('cart_contents'));
    }

    public function getCartData()
    {
        $cart_contents = Cart::content()->filter(fn($item) => $item->options->type == 'purchase');
        $data['sub_total'] = 0;

        foreach ($cart_contents as $cart) {
            $data['sub_total'] += $cart->price;
        }
        $data['sub_total'] = currency_format($data['sub_total'], currency: business_currency());

        return response()->json($data);
    }

    public function deleteAll(Request $request)
    {
        DB::beginTransaction();

        try {
            $purchases = Purchase::whereIn('id', $request->ids)->get();
            $business = Business::findOrFail(auth()->user()->business_id);

            foreach ($purchases as $purchase) {
                foreach ($purchase->details as $detail) {
                    Stock::where('id', $detail->stock_id)->decrement('productStock', $detail->quantities);
                }

                if ($purchase->party_id) {
                    $party = Party::findOrFail($purchase->party_id);
                    $party->update([
                        'due' => $party->due - $purchase->dueAmount
                    ]);
                }

                updateBalance($purchases->paidAmount, 'decrement');

                sendNotifyToUser($purchase->id, route('business.purchases.index', ['id' => $purchase->id]), __('Purchases has been deleted.'), $purchase->business_id);

                $purchase->delete();
            }

            // Clears all cart items
            Cart::destroy();

            DB::commit();

            return response()->json([
                'message' => __('Selected purchases deleted successfully.'),
                'redirect' => route('business.purchases.index')
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => __('Something went wrong!')], 404);
        }
    }

    public function generatePDF($purchase_id)
    {
        $purchase = Purchase::with('user:id,name', 'party:id,name,phone', 'business:id,phoneNumber,companyName,vat_name,vat_no', 'details:id,productPurchasePrice,quantities,product_id,purchase_id', 'details.stock:id,batch_no', 'details.product:id,productName', 'payment_type:id,name')->findOrFail($purchase_id);

        $purchase_returns = PurchaseReturn::with('purchase:id,party_id,isPaid,totalAmount,dueAmount,paidAmount,invoiceNumber', 'purchase.party:id,name', 'details', 'details.purchaseDetail.product:id,productName')
            ->where('business_id', auth()->user()->business_id)
            ->where('purchase_id', $purchase_id)
            ->latest()
            ->get();

        $purchase->details = $purchase->details->map(function ($detail) use ($purchase_returns) {
            $return_qty_sum = $purchase_returns->flatMap(function ($return) use ($detail) {
                return $return->details->where('purchaseDetail.id', $detail->id)->pluck('return_qty');
            })->sum();

            $detail->quantities = $detail->quantities + $return_qty_sum;

            return $detail;
        });

        $pdf = Pdf::loadView('business::purchases.pdf', compact('purchase', 'purchase_returns'));
        return $pdf->download('purchases.pdf');
    }

    public function sendMail($purchase_id)
    {
        $purchase = Purchase::with('user:id,name', 'party:id,name,phone', 'business:id,phoneNumber,companyName,vat_name,vat_no', 'details:id,productPurchasePrice,quantities,product_id,purchase_id', 'details.product:id,productName', 'payment_type:id,name')
            ->findOrFail($purchase_id);

        $purchase_returns = PurchaseReturn::with('purchase:id,party_id,isPaid,totalAmount,dueAmount,paidAmount,invoiceNumber', 'purchase.party:id,name', 'details', 'details.purchaseDetail.product:id,productName')
            ->where('business_id', auth()->user()->business_id)
            ->where('purchase_id', $purchase_id)
            ->latest()
            ->get();

        $purchase->details = $purchase->details->map(function ($detail) use ($purchase_returns) {
            $return_qty_sum = $purchase_returns->flatMap(function ($return) use ($detail) {
                return $return->details->where('purchaseDetail.id', $detail->id)->pluck('return_qty');
            })->sum();

            $detail->quantities = $detail->quantities + $return_qty_sum;

            return $detail;
        });

        $pdf = Pdf::loadView('business::purchases.pdf', compact('purchase', 'purchase_returns'));

        // Send email with PDF attachment
        Mail::raw('Please find attached your Purchase invoice.', function ($message) use ($pdf) {
            $message->to(auth()->user()->email)
                ->subject('Purchase Invoice')
                ->attachData($pdf->output(), 'purchase-invoice.pdf', [
                    'mime' => 'application/pdf',
                ]);
        });

        return response()->json([
            'message' => __('Email Sent Successfully.'),
            'redirect' => route('business.purchases.index'),
        ]);
    }

    public function createSupplier(Request $request)
    {
        $request->validate([
            'phone' => 'nullable|max:20|' . Rule::unique('parties')->where('business_id', auth()->user()->business_id),
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:Retailer,Dealer,Wholesaler,Supplier',
            'email' => 'nullable|email',
            'image' => 'nullable|image',
            'address' => 'nullable|string|max:255',
            'due' => 'nullable|numeric|min:0',
        ]);

        Party::create($request->except('image', 'due') + [
            'due' => $request->due ?? 0,
            'image' => $request->image ? $this->upload($request, 'image') : NULL,
            'business_id' => auth()->user()->business_id
        ]);

        return response()->json([
            'message'   => __('Supplier created successfully'),
            'redirect'  => route('business.purchases.create')
        ]);
    }

    public function bulkIndex()
    {
        return view('business::purchases.bulk-upload.index');
    }

    // public function bulkStore(Request $request)
    // {
    //     $request->validate([
    //         'file' => 'required|file|mimes:xlsx,xls,csv'
    //     ]);

    //     $businessId = auth()->user()->business_id;
    //     $import = new PurchaseImport($businessId);

    //     Excel::import($import, $request->file('file'));

    //     $errors = $import->getErrors();
    //     if (!empty($errors)) {
    //         return response()->json([
    //             'message' => 'Some purchases failed to import.',
    //         ], 422);
    //     }

    //     return response()->json([
    //         'message' => __('Bulk upload successfully.'),
    //         'redirect' => route('business.purchases.create')
    //     ]);
    // }

    public function bulkCartStore(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        $businessId = auth()->user()->business_id;
        $import = new PurchaseProductImport($businessId);

        try {
            Excel::import($import, $request->file('file'));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => __('Bulk upload successfully.'),
            'redirect' => route('business.purchases.create')
        ]);
    }
}

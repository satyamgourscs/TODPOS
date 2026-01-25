<?php

namespace Modules\Business\App\Http\Controllers;

use App\Models\Vat;
use App\Models\Rack;
use App\Models\Unit;
use App\Models\Brand;
use App\Models\Stock;
use App\Models\Shelf;
use App\Models\Option;
use App\Models\Product;
use App\Models\Category;
use App\Models\Warehouse;
use App\Helpers\HasUploader;
use App\Models\ProductModel;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Modules\Business\App\Exports\ExportProduct;
use Modules\Business\App\Exports\ExportExpiredProduct;

class AcnooProductController extends Controller
{
    use HasUploader;

    public function __construct()
    {
        $this->middleware('check.permission:products.read')->only(['index', 'show', 'expiredProduct']);
        $this->middleware('check.permission:products.create')->only(['create', 'store']);
        $this->middleware('check.permission:products.update')->only(['edit', 'update', 'CreateStock']);
        $this->middleware('check.permission:products.delete')->only(['destroy', 'deleteAll']);
    }

    public function index()
    {
        $user = auth()->user();
        $products = Product::with(['stocks', 'unit:id,unitName', 'brand:id,brandName', 'category', 'warehouse:id,name', 'rack:id,name', 'shelf:id,name'])
            ->where('business_id', $user->business_id)
            ->withSum('stocks as total_stock', 'productStock')
            ->latest()
            ->paginate(20);

        return view('business::products.index', compact('products'));
    }

    public function acnooFilter(Request $request)
    {
        $search = $request->input('search');

        $products = Product::with(['stocks', 'unit:id,unitName', 'brand:id,brandName', 'category:id,categoryName', 'warehouse:id,name', 'rack:id,name', 'shelf:id,name'])
            ->where('business_id', auth()->user()->business_id)
            ->withSum('stocks as total_stock', 'productStock')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('productName', 'like', '%' . $search . '%')
                        ->orWhere('productCode', 'like', '%' . $search . '%')
                        ->orWhere('productPurchasePrice', 'like', '%' . $search . '%')
                        ->orWhere('productSalePrice', 'like', '%' . $search . '%')
                        ->orWhereHas('category', function ($q) use ($search) {
                            $q->where('categoryName', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('brand', function ($q) use ($search) {
                            $q->where('brandName', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('unit', function ($q) use ($search) {
                            $q->where('unitName', 'like', '%' . $search . '%');
                        });
                });
            })
            ->paginate($request->per_page ?? 20);

        if ($request->ajax()) {
            return response()->json([
                'data' => view('business::products.datas', compact('products'))->render()
            ]);
        }

        return redirect(url()->previous());
    }

    public function create()
    {
        $business_id = auth()->user()->business_id;
        $categories = Category::where('business_id', $business_id)->whereStatus(1)->latest()->get();
        $brands = Brand::where('business_id', $business_id)->whereStatus(1)->latest()->get();
        $units = Unit::where('business_id', $business_id)->whereStatus(1)->latest()->get();
        $product_id = (Product::where('business_id', $business_id)->count() ?? 0) + 1;
        $vats = Vat::where('business_id', $business_id)->latest()->get();
        $code = str_pad($product_id, 4, '0', STR_PAD_LEFT);
        $product_models = ProductModel::where('business_id', $business_id)->latest()->get();
        $warehouses = Warehouse::where('business_id', $business_id)->latest()->get();
        $racks = Rack::where('business_id', $business_id)->latest()->get();
        $shelves = Shelf::where('business_id', $business_id)->latest()->get();
        $profit_option = Option::where('key', 'business-settings')
            ->whereJsonContains('value->business_id', $business_id)
            ->first()
            ->value['product_profit_option'] ?? '';

        return view('business::products.create', compact('categories', 'brands', 'units', 'code', 'vats', 'product_models', 'warehouses', 'racks', 'shelves', 'profit_option'));
    }

    public function store(Request $request)
    {
        $business_id = auth()->user()->business_id;

        $request->validate([
            'vat_id' => 'nullable|exists:vats,id',
            'unit_id' => 'nullable|exists:units,id',
            'brand_id' => 'nullable|exists:brands,id',
            'category_id' => 'nullable|exists:categories,id',
            'model_id' => 'nullable|exists:product_models,id',
            'vat_type' => 'nullable|in:inclusive,exclusive',
            'productName' => 'required|string|max:255',
            'productPicture' => 'nullable|image|mimes:jpg,png,jpeg,svg',
            'productCode' => [
                'nullable',
                Rule::unique('products')->where(function ($query) {
                    return $query->where('business_id', auth()->user()->business_id);
                }),
            ],
            'alert_qty' => 'nullable|numeric|min:0',
            'size' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:255',
            'weight' => 'nullable|string|max:255',
            'capacity' => 'nullable|string|max:255',
            'productManufacturer' => 'nullable|string|max:255',
            'product_type' => 'required|in:single,variant',
            'stocks' => 'nullable|array',
            'stocks.*.batch_no' => [
                'nullable',
                Rule::unique('stocks', 'batch_no')->where(function ($query) use ($business_id) {
                    return $query->where('business_id', $business_id);
                }),
            ],
            'stocks.*.warehouse_id' => 'nullable|exists:warehouses,id',
            'stocks.*.productStock' => 'nullable|numeric|min:0|max:99999999.99',
            'stocks.*.exclusive_price' => 'nullable|numeric|min:0|max:99999999.99',
            'stocks.*.inclusive_price' => 'nullable|numeric|min:0|max:99999999.99',
            'stocks.*.profit_percent' => 'nullable|numeric|max:99999999.99',
            'stocks.*.productSalePrice' => 'nullable|numeric|min:0|max:99999999.99',
            'stocks.*.productWholeSalePrice' => 'nullable|numeric|min:0|max:99999999.99',
            'stocks.*.productDealerPrice' => 'nullable|numeric|min:0|max:99999999.99',
            'stocks.*.mfg_date' => 'nullable|date',
            'stocks.*.expire_date' => 'nullable|date|after_or_equal:stocks.*.mfg_date',
        ]);

        DB::beginTransaction();
        try {
            //vat calculation
            $vat = Vat::find($request->vat_id);
            $vat_rate = $vat->rate ?? 0;

            // Get purchase price from first stock item
            $lastStock = collect($request->stocks)->last();
            $basePrice = $lastStock['exclusive_price'] ?? 0;
            $vat_amount = 0;

            $product = Product::create($request->except(['productPicture', 'productPurchasePrice', 'productSalePrice', 'productDealerPrice', 'productWholeSalePrice', 'alert_qty', 'vat_amount']) + [
                'business_id' => $business_id,
                'alert_qty' => $request->alert_qty ?? 0,
                'productPicture' => $request->productPicture ? $this->upload($request, 'productPicture') : NULL,
            ]);

            // Create all stocks
            $stockData = [];
            if (!empty($request->stocks)) {
                foreach ($request->stocks as $stock) {
                    $base_price = $stock['exclusive_price'] ?? 0;
                    $purchasePrice = $request->vat_type === 'inclusive'
                        ? $base_price + ($base_price * $vat_rate / 100)
                        : $base_price;

                    // Calculate VAT for this stock and sum
                    $vat_amount += ($base_price * $vat_rate / 100);

                    $stockData[] = [
                        'business_id' => $business_id,
                        'product_id' => $product->id,
                        'branch_id' => auth()->user()->branch_id ?? auth()->user()->active_branch_id,
                        'batch_no' => $stock['batch_no'] ?? null,
                        'warehouse_id' => $stock['warehouse_id'] ?? null,
                        'productStock' => $stock['productStock'] ?? 0,
                        'productPurchasePrice' => $purchasePrice,
                        'profit_percent' => $stock['profit_percent'] ?? 0,
                        'productSalePrice' => $stock['productSalePrice'] ?? 0,
                        'productWholeSalePrice' => $stock['productWholeSalePrice'] ?? 0,
                        'productDealerPrice' => $stock['productDealerPrice'] ?? 0,
                        'mfg_date' => $stock['mfg_date'] ?? null,
                        'expire_date' => $stock['expire_date'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            } else {
                // default stock if no stock found
                $stockData[] = [
                    'business_id' => $business_id,
                    'product_id' => $product->id,
                    'warehouse_id' => $request->warehouse_id,
                    'branch_id' => auth()->user()->branch_id ?? auth()->user()->active_branch_id,
                    'batch_no' => null,
                    'productStock' => 0,
                    'productPurchasePrice' => $basePrice,
                    'profit_percent' => 0,
                    'productSalePrice' => 0,
                    'productWholeSalePrice' => 0,
                    'productDealerPrice' => 0,
                    'mfg_date' => null,
                    'expire_date' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            Stock::insert($stockData);

            // Update product with total vat amount
            $product->update(['vat_amount' => $vat_amount]);

            DB::commit();
            return response()->json([
                'message' => __('Product saved successfully.'),
                'redirect' => route('business.products.index')
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => __('Something went wrong.'),
            ], 406);
        }
    }

    public function edit($id)
    {
        $business_id = auth()->user()->business_id;
        $product = Product::with('stocks')->where('business_id', $business_id)->findOrFail($id);
        $categories = Category::where('business_id', $business_id)->whereStatus(1)->latest()->get();
        $brands = Brand::where('business_id', $business_id)->whereStatus(1)->latest()->get();
        $units = Unit::where('business_id', $business_id)->whereStatus(1)->latest()->get();
        $vats = Vat::where('business_id', $business_id)->latest()->get();
        $product_models = ProductModel::where('business_id', $business_id)->latest()->get();
        $warehouses = Warehouse::where('business_id', $business_id)->latest()->get();
        $racks = Rack::where('business_id', $business_id)->latest()->get();
        $shelves = Shelf::where('business_id', $business_id)->latest()->get();
        $profit_option = Option::where('key', 'business-settings')
            ->whereJsonContains('value->business_id', $business_id)
            ->first()
            ->value['product_profit_option'] ?? '';

        return view('business::products.edit', compact('categories', 'brands', 'units', 'product', 'vats', 'product_models', 'warehouses', 'racks', 'shelves', 'profit_option'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $business_id = auth()->user()->business_id;

        $request->validate([
            'vat_id' => 'nullable|exists:vats,id',
            'unit_id' => 'nullable|exists:units,id',
            'brand_id' => 'nullable|exists:brands,id',
            'category_id' => 'nullable|exists:categories,id',
            'model_id' => 'nullable|exists:product_models,id',
            'vat_type' => 'nullable|in:inclusive,exclusive',
            'productName' => 'required|string|max:255',
            'productPicture' => 'nullable|image|mimes:jpg,png,jpeg,svg',
            'productCode' => [
                'nullable',
                Rule::unique('products', 'productCode')->ignore($product->id)->where(function ($query) use ($business_id) {
                    return $query->where('business_id', $business_id);
                }),
            ],
            'alert_qty' => 'nullable|numeric|min:0',
            'size' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:255',
            'weight' => 'nullable|string|max:255',
            'capacity' => 'nullable|string|max:255',
            'productManufacturer' => 'nullable|string|max:255',
            'product_type' => 'required|in:single,variant',
            'stocks' => 'nullable|array',
            'stocks.*.warehouse_id' => 'nullable|exists:warehouses,id',
            'stocks.*.productStock' => 'nullable|numeric|min:0|max:99999999.99',
            'stocks.*.exclusive_price' => 'nullable|numeric|min:0|max:99999999.99',
            'stocks.*.inclusive_price' => 'nullable|numeric|min:0|max:99999999.99',
            'stocks.*.profit_percent' => 'nullable|numeric|max:99999999.99',
            'stocks.*.productSalePrice' => 'nullable|numeric|min:0|max:99999999.99',
            'stocks.*.productWholeSalePrice' => 'nullable|numeric|min:0|max:99999999.99',
            'stocks.*.productDealerPrice' => 'nullable|numeric|min:0|max:99999999.99',
            'stocks.*.mfg_date' => 'nullable|date',
            'stocks.*.expire_date' => 'nullable|date|after_or_equal:stocks.*.mfg_date',
        ]);

        DB::beginTransaction();
        try {
            // Vat calculation
            $vat = Vat::find($request->vat_id);
            $vat_rate = $vat->rate ?? 0;
            $lastStock = collect($request->stocks)->last();
            $basePrice = $lastStock['exclusive_price'] ?? 0;
            $vat_amount = 0;

            $prevStocks = Stock::where('product_id', $product->id)->get();
            // Delete existing stocks after saving the product
            Stock::where('product_id', $product->id)->delete();

            // Insert new stocks
            $stockData = [];
            if (!empty($request->stocks)) {
                foreach ($request->stocks as $key => $stock) {
                    $base_price = $stock['exclusive_price'] ?? 0;
                    $purchasePrice = $request->vat_type === 'inclusive'
                        ? $base_price + ($base_price * $vat_rate / 100)
                        : $base_price;

                    // Calculate VAT for this stock and sum
                    $vat_amount += ($base_price * $vat_rate / 100);

                    $stockData[] = [
                        'business_id' => $business_id,
                        'product_id' => $product->id,
                        'branch_id' => $prevStocks[$key]->branch_id ?? auth()->user()->branch_id ?? auth()->user()->active_branch_id ?? null,
                        'batch_no' => $stock['batch_no'] ?? null,
                        'warehouse_id' => $stock['warehouse_id'] ?? null,
                        'productStock' => $stock['productStock'] ?? 0,
                        'productPurchasePrice' => $purchasePrice,
                        'profit_percent' => $stock['profit_percent'] ?? 0,
                        'productSalePrice' => $stock['productSalePrice'] ?? 0,
                        'productWholeSalePrice' => $stock['productWholeSalePrice'] ?? 0,
                        'productDealerPrice' => $stock['productDealerPrice'] ?? 0,
                        'mfg_date' => $stock['mfg_date'] ?? null,
                        'expire_date' => $stock['expire_date'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            } else {
                // default stock if no stock provided
                $purchasePrice = $request->vat_type === 'inclusive' ? $basePrice + ($basePrice * $vat_rate / 100) : $basePrice;
                $stockData[] = [
                    'business_id' => $business_id,
                    'product_id' => $product->id,
                    'warehouse_id' => $request->warehouse_id ?? null,
                    'branch_id' => auth()->user()->branch_id ?? auth()->user()->active_branch_id,
                    'batch_no' => null,
                    'productStock' => 0,
                    'productPurchasePrice' => $purchasePrice,
                    'profit_percent' => 0,
                    'productSalePrice' => 0,
                    'productWholeSalePrice' => 0,
                    'productDealerPrice' => 0,
                    'mfg_date' => null,
                    'expire_date' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            Stock::insert($stockData);

            // Update product
            $product->update($request->except(['productPicture', 'productPurchasePrice', 'productSalePrice', 'productDealerPrice', 'productWholeSalePrice', 'productStock', 'alert_qty', 'vat_amount']) + [
                    'business_id' => $business_id,
                    'alert_qty' => $request->alert_qty ?? 0,
                    'vat_amount' => $vat_amount,
                    'productPicture' => $request->productPicture ? $this->upload($request, 'productPicture', $product->productPicture) : $product->productPicture,
                ]);

            DB::commit();

            return response()->json([
                'message' => __('Product updated successfully.'),
                'redirect' => route('business.products.index')
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'message' => __('Something went wrong.'),
                'error' => $e->getMessage()
            ], 406);
        }
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        if (file_exists($product->productPicture)) {
            Storage::delete($product->productPicture);
        }
        $product->delete();

        return response()->json([
            'message' => __('Product deleted successfully'),
            'redirect' => route('business.products.index')
        ]);
    }

    public function deleteAll(Request $request)
    {
        $products = Product::whereIn('id', $request->ids)->get();

        foreach ($products as $product) {
            if (file_exists($product->productPicture)) {
                Storage::delete($product->productPicture);
            }
        }
        Product::whereIn('id', $request->ids)->delete();
        return response()->json([
            'message'   => __('Selected product deleted successfully'),
            'redirect'  => route('business.products.index')
        ]);
    }

    public function getAllProduct()
    {
        $products = Product::with([
            'stocks' => function ($query) {
                $query->where('productStock', '>', 0);
            },
            'category:id,categoryName',
            'unit:id,unitName',
            'stocks.warehouse:id,name'
        ])
            ->where('business_id', auth()->user()->business_id)
            ->withSum('stocks as total_stock', 'productStock')
            ->having('total_stock', '>', 0)
            ->latest()
            ->get();

        return response()->json($products);
    }

    public function getByCategory($category_id)
    {
        $products = Product::where('business_id', auth()->user()->business_id)->where('category_id', $category_id)->get();
        return response()->json($products);
    }

    public function generatePDF(Request $request)
    {
        $products = Product::with('unit:id,unitName', 'brand:id,brandName', 'category:id,categoryName')->where('business_id', auth()->user()->business_id)->latest()->get();
        $pdf = Pdf::loadView('business::products.pdf', compact('products'));
        return $pdf->download('product.pdf');
    }

    public function exportExcel()
    {
        return Excel::download(new ExportProduct, 'product.xlsx');
    }

    public function exportCsv()
    {
        return Excel::download(new ExportProduct, 'product.csv');
    }

    public function expiredProduct()
    {
        $expired_products = Product::with('unit:id,unitName', 'brand:id,brandName', 'category:id,categoryName', 'stocks')
            ->withSum('stocks', 'productStock')
            ->where('business_id', auth()->user()->business_id)
            ->whereHas('stocks', function ($query) {
                $query->whereDate('expire_date', '<', today())
                    ->where('productStock', '>', 0);
            })
            ->latest()
            ->paginate(20);

        return view('business::products.expired-products.index', compact('expired_products'));
    }

    public function exportExpireProductExcel()
    {
        return Excel::download(new ExportExpiredProduct, 'expired-product.xlsx');
    }

    public function exportExpireProductCsv()
    {
        return Excel::download(new ExportExpiredProduct, 'expired-product.csv');
    }

    public function acnooExpireProductFilter(Request $request)
    {
        $expired_products = Product::with('unit:id,unitName', 'brand:id,brandName', 'category:id,categoryName', 'stocks')
            ->where('business_id', auth()->user()->business_id)
            ->whereHas('stocks', function ($query) {
                $query->whereDate('expire_date', '<', today())
                    ->where('productStock', '>', 0);
            })
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($q) use ($request) {
                    $q->where('type', 'like', '%' . $request->search . '%')
                        ->orWhere('productName', 'like', '%' . $request->search . '%')
                        ->orWhere('productCode', 'like', '%' . $request->search . '%')
                        ->orWhere('productSalePrice', 'like', '%' . $request->search . '%')
                        ->orWhere('productPurchasePrice', 'like', '%' . $request->search . '%')
                        ->orWhereHas('unit', function ($q) use ($request) {
                            $q->where('unitName', 'like', '%' . $request->search . '%');
                        })
                        ->orWhereHas('brand', function ($q) use ($request) {
                            $q->where('brandName', 'like', '%' . $request->search . '%');
                        })
                        ->orWhereHas('category', function ($q) use ($request) {
                            $q->where('categoryName', 'like', '%' . $request->search . '%');
                        });
                });
            })
            ->latest()
            ->paginate($request->per_page ?? 10);

        if ($request->ajax()) {
            return response()->json([
                'data' => view('business::products.expired-products.datas', compact('expired_products'))->render()
            ]);
        }

        return redirect(url()->previous());
    }

    public function show($id)
    {
        $business_id = auth()->user()->business_id;
        $product = Product::with('stocks')->where('business_id', $business_id)->findOrFail($id);
        $categories = Category::where('business_id', $business_id)->whereStatus(1)->latest()->get();
        $brands = Brand::where('business_id', $business_id)->whereStatus(1)->latest()->get();
        $units = Unit::where('business_id', $business_id)->whereStatus(1)->latest()->get();
        $vats = Vat::where('business_id', $business_id)->latest()->get();
        $profit_option = Option::where('key', 'business-settings')
            ->whereJsonContains('value->business_id', $business_id)
            ->first()
            ->value['product_profit_option'] ?? '';

        return view('business::products.create-stock', compact('categories', 'brands', 'units', 'product', 'vats', 'profit_option'));
    }

    public function CreateStock(Request $request, string $id)
    {
        $product = Product::findOrFail($id);
        $business_id = auth()->user()->business_id;

        $request->validate([
            'vat_id' => 'nullable|exists:vats,id',
            'vat_type' => 'nullable|in:inclusive,exclusive',
            'productDealerPrice' => 'nullable|numeric|min:0',
            'exclusive_price' => 'required|numeric|min:0',
            'inclusive_price' => 'required|numeric|min:0',
            'profit_percent' => 'nullable|numeric',
            'productSalePrice' => 'required|numeric|min:0',
            'productWholeSalePrice' => 'nullable|numeric|min:0',
            'productStock' => 'required|numeric|min:0',
            'expire_date' => 'nullable|date',
            'batch_no' => 'nullable|string',
            'productCode' => [
                'nullable',
                'unique:products,productCode,' . $product->id . ',id,business_id,' . $business_id,
            ],
        ]);

        DB::beginTransaction();
        try {
            // Calculate purchase price including VAT if applicable
            $vat = Vat::find($request->vat_id);
            $exclusive_price = $request->exclusive_price ?? 0;
            $vat_amount = ($exclusive_price * ($vat->rate ?? 0)) / 100;

            // Determine final purchase price based on VAT type
            $purchase_price = $request->vat_type === 'exclusive' ? $exclusive_price : $exclusive_price + $vat_amount;

            $batchNo = $request->batch_no ?? null;
            $stock = Stock::where(['batch_no' => $batchNo, 'product_id' => $product->id])->first();

            if ($stock) {
                $stock->update($request->except('productStock', 'productPurchasePrice', 'productSalePrice', 'productDealerPrice', 'productWholeSalePrice') + [
                    'productStock' => $stock->productStock + $request->productStock,
                    'productPurchasePrice' => $purchase_price,
                    'productSalePrice' => $request->productSalePrice,
                    'productDealerPrice' => $request->productDealerPrice ?? 0,
                    'productWholeSalePrice' => $request->productWholeSalePrice ?? 0,
                ]);
            } else {
                Stock::create($request->except('productStock', 'productPurchasePrice', 'productSalePrice', 'productDealerPrice', 'productWholeSalePrice') + [
                    'product_id' => $product->id,
                    'branch_id' => auth()->user()->branch_id ?? auth()->user()->active_branch_id,
                    'business_id' => $business_id,
                    'productStock' => $request->productStock ?? 0,
                    'productPurchasePrice' => $purchase_price,
                    'productSalePrice' => $request->productSalePrice,
                    'productDealerPrice' => $request->productDealerPrice ?? 0,
                    'productWholeSalePrice' => $request->productWholeSalePrice ?? 0,
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => __('Data saved successfully.'),
                'redirect' => route('business.products.index'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => __('Something went wrong.'),
            ], 406);
        }
    }

    public function getShelf(Request $request)
    {
        $rack = Rack::with('shelves')->find($request->rack_id);
        return response()->json($rack ? $rack->shelves : []);
    }
}

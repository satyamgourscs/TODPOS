<?php

namespace App\Http\Controllers\Api;

use App\Models\Stock;
use App\Models\Product;
use App\Helpers\HasUploader;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class AcnooProductController extends Controller
{
    use HasUploader;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();

        $products = Product::with(['unit:id,unitName', 'vat:id,rate', 'brand:id,brandName', 'category:id,categoryName', 'product_model:id,name', 'stocks'])
            ->withSum('stocks', 'productStock')
            ->where('business_id', $user->business_id)
            ->latest()
            ->get();

        $total_stock_value = $products->sum(function ($product) {
            return $product->stocks->sum(function ($stock) {
                return $stock->productPurchasePrice * $stock->productStock;
            });
        });

        return response()->json([
            'message' => __('Data fetched successfully.'),
            'total_stock_value' => $total_stock_value,
            'data' => $products,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $business_id = auth()->user()->business_id;

        $request->validate([
            'unit_id' => 'nullable|exists:units,id',
            'brand_id' => 'nullable|exists:brands,id',
            'productName' => 'required|string|max:250',
            'category_id' => 'nullable|exists:categories,id',
            'model_id' => 'nullable|exists:product_models,id',
            'vat_id' => 'nullable|exists:vats,id',
            'productCode' => [
                'nullable',
                Rule::unique('products')->where(function ($query) use ($business_id) {
                    return $query->where('business_id', $business_id);
                }),
            ],
        ]);

        DB::beginTransaction();
        try {
            $product = Product::create($request->except('productPicture', 'alert_qty', 'expire_date', 'productStock', 'profit_percent', 'productSalePrice', 'productDealerPrice', 'productPurchasePrice', 'productWholeSalePrice') + [
                'business_id' => $business_id,
                'alert_qty' => $request->alert_qty ?? 0,
                'productPicture' => $request->productPicture ? $this->upload($request, 'productPicture') : NULL,
            ]);

            if ($request->product_type == 'variant') {

                $stockData = [];

                foreach ($request->batch_no ?? [] as $key => $batch_no) {

                    $stockData[] = [
                        'batch_no' => $batch_no,
                        'business_id' => $business_id,
                        'product_id' => $product->id,
                        'branch_id' => auth()->user()->branch_id ?? auth()->user()->active_branch_id,
                        'productSalePrice' => $request->productSalePrice[$key] ?? 0,
                        'mfg_date' => $request->mfg_date[$key] ?? null,
                        'productStock' => $request->productStock[$key] ?? 0,
                        'expire_date' => $request->expire_date[$key] ?? null,
                        'profit_percent' => $request->profit_percent[$key] ?? 0,
                        'productPurchasePrice' => $request->productPurchasePrice[$key] ?? 0,
                        'productDealerPrice' => $request->productDealerPrice[$key] ?? 0,
                        'productWholeSalePrice' => $request->productWholeSalePrice[$key] ?? 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                Stock::insert($stockData);
            } else {
                Stock::create([
                    'business_id' => $business_id,
                    'product_id' => $product->id,
                    'branch_id' => auth()->user()->branch_id ?? auth()->user()->active_branch_id,
                    'mfg_date' => $request->mfg_date,
                    'productStock' => $request->productStock ?? 0,
                    'productPurchasePrice' => $request->productPurchasePrice ?? 0,
                    'profit_percent' => $request->profit_percent ?? 0,
                    'productSalePrice' => $request->productSalePrice ?? 0,
                    'productWholeSalePrice' => $request->productWholeSalePrice ?? 0,
                    'productDealerPrice' => $request->productDealerPrice ?? 0,
                    'expire_date' => $request->expire_date,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            DB::commit();
            return response()->json([
                'message' => __('Data saved successfully.'),
                'data' => $product
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => $e->getMessage()
            ], 406);
        }
    }

    public function show(string $id)
    {
        $data = Product::with('unit:id,unitName', 'vat:id,rate', 'brand:id,brandName', 'category:id,categoryName', 'product_model:id,name', 'stocks')
            ->withSum('stocks', 'productStock')
            ->findOrFail($id);

        return response()->json([
            'message' => __('Data fetched successfully.'),
            'data' => $data,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $business_id = auth()->user()->business_id;

        $request->validate([
            'unit_id' => 'nullable|exists:units,id',
            'brand_id' => 'nullable|exists:brands,id',
            'productName' => 'required|string|max:250',
            'category_id' => 'nullable|exists:categories,id',
            'model_id' => 'nullable|exists:product_models,id',
            'vat_id' => 'nullable|exists:vats,id',
            'productCode' => [
                'nullable',
                'unique:products,productCode,' . $product->id . ',id,business_id,' . $business_id,
            ],
        ]);

        DB::beginTransaction();
        try {

            $product->update($request->except('productPicture', 'alert_qty', 'expire_date', 'productStock', 'profit_percent', 'productSalePrice', 'productDealerPrice', 'productPurchasePrice', 'productWholeSalePrice') + [
                'alert_qty' => $request->alert_qty ?? 0,
                'productPicture' => $request->productPicture ? $this->upload($request, 'productPicture', $product->productPicture) : $product->productPicture,
            ]);

            $prevStocks = Stock::where('product_id', $product->id)->get();
            Stock::where('product_id', $product->id)->delete();

            if ($request->product_type == 'variant') {

                $stockData = [];

                foreach ($request->batch_no ?? [] as $key => $batch_no) {

                    $stockData[] = [
                        'batch_no' => $batch_no,
                        'business_id' => $business_id,
                        'product_id' => $product->id,
                        'warehouse_id' => $prevStocks[$key]->warehouse_id ?? null,
                        'branch_id' => $prevStocks[$key]->branch_id ?? auth()->user()->branch_id ?? auth()->user()->active_branch_id ?? null,
                        'productSalePrice' => $request->productSalePrice[$key] ?? 0,
                        'mfg_date' => $request->mfg_date[$key],
                        'productStock' => $request->productStock[$key] ?? 0,
                        'expire_date' => $request->expire_date[$key] ?? null,
                        'profit_percent' => $request->profit_percent[$key] ?? 0,
                        'productPurchasePrice' => $request->productPurchasePrice[$key] ?? 0,
                        'productDealerPrice' => $request->productDealerPrice[$key] ?? 0,
                        'productWholeSalePrice' => $request->productWholeSalePrice[$key] ?? 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                Stock::insert($stockData);
            } else {
                Stock::create([
                    'business_id' => $business_id,
                    'product_id' => $product->id,
                    'mfg_date' => $request->mfg_date,
                    'productStock' => $request->productStock ?? 0,
                    'productPurchasePrice' => $request->productPurchasePrice ?? 0,
                    'profit_percent' => $request->profit_percent ?? 0,
                    'productSalePrice' => $request->productSalePrice ?? 0,
                    'productWholeSalePrice' => $request->productWholeSalePrice ?? 0,
                    'productDealerPrice' => $request->productDealerPrice ?? 0,
                    'expire_date' => $request->expire_date,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => __('Data saved successfully.'),
                'data' => $product->load('stocks'),
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => $e->getMessage()
            ], 406);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        if (file_exists($product->productPicture)) {
            Storage::delete($product->productPicture);
        }
        $product->delete();
        return response()->json([
            'message' => __('Data deleted successfully.'),
        ]);
    }
}

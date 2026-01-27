<?php

namespace App\Http\Controllers\Api;

use App\Models\Sale;
use App\Models\Party;
use App\Models\Stock;
use App\Models\Business;
use App\Models\SaleDetails;
use App\Helpers\HasUploader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class TryonedigitalSaleController extends Controller
{
    use HasUploader;

    public function index()
    {
        $data = Sale::with('user:id,name,role', 'party:id,name,email,phone,type', 'details', 'details.stock:id,batch_no', 'details.product:id,productName,category_id,productCode,productPurchasePrice,productStock,product_type', 'details.product.category:id,categoryName', 'saleReturns.details', 'vat:id,name,rate', 'payment_type:id,name', 'branch:id,name,phone,address')
            ->when(request('returned-sales') == "true", function ($query) {
                $query->whereHas('saleReturns');
            })
            ->where('business_id', auth()->user()->business_id)
            ->latest()
            ->get();

        return response()->json([
            'message' => __('Data fetched successfully.'),
            'data' => $data,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'products' => 'required',
            'saleDate' => 'nullable|date',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
            'rounding_option' => 'nullable|in:none,round_up,nearest_whole_number,nearest_0.05,nearest_0.1,nearest_0.5',
        ]);

        DB::beginTransaction();
        try {

            $business_id = auth()->user()->business_id;
            $request_products = json_decode($request->products, true);

            if ($request->party_id) {
                $party = Party::findOrFail($request->party_id);
            }

            if ($request->dueAmount) {
                if (!$request->party_id) {
                    return response()->json([
                        'message' => __('You cannot sell on credit to a walk-in customer.')
                    ], 400);
                }

                $newDue = $party->due + $request->dueAmount;

                // Check credit limit
                if ($party->credit_limit > 0 && $newDue > $party->credit_limit) {
                    return response()->json(['message' => __('Sale cannot be created. Party due will exceed credit limit!')], 400);
                }

                $party->update(['due' => $newDue]);
            }

            $business = Business::findOrFail($business_id);
            $business_name = $business->companyName;

            updateBalance($request->paidAmount, 'increment');

            $lossProfit = collect($request_products)->pluck('lossProfit')->toArray();

            $sale = Sale::create($request->except('image', 'isPaid') + [
                'user_id' => auth()->id(),
                'business_id' => $business_id,
                'isPaid' => filter_var($request->isPaid, FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
                'lossProfit' => array_sum($lossProfit) - $request->discountAmount,
                'image' => $request->image ? $this->upload($request, 'image') : null,
                'meta' => [
                    'note' => $request->note,
                    'customer_phone' => $request->customer_phone,
                ],
            ]);

            $saleDetails = [];
            foreach ($request_products as $key => $productData) {

                $stock = Stock::findOrFail($productData['stock_id']);

                $saleDetails[$key] = [
                    'sale_id' => $sale->id,
                    'stock_id' => $stock->id,
                    'price' => $productData['price'],
                    'product_id' => $stock->product_id,
                    'lossProfit' => $productData['lossProfit'],
                    'quantities' => $productData['quantities'] ?? 0,
                ];

                $product_name = $productData['product_name'] ?? NULL;

                if ($stock->productStock < $request_products[$key]['quantities']) {
                    return response()->json([
                        'message' => "Stock not availabe for product : " . $product_name . ". Available stock is : " . $stock->productStock
                    ], 406);
                }

                $stock->decrement('productStock', $productData['quantities']);
            }

            SaleDetails::insert($saleDetails);

            if ($party ?? false && $party->phone) {
                if (env('MESSAGE_ENABLED')) {
                    sendMessage($party->phone, saleMessage($sale, $party, $business_name));
                }
            }

            DB::commit();
            return response()->json([
                'message' => __('Data saved successfully.'),
                'data' => $sale->load('user:id,name,role', 'party:id,name,email,phone,type', 'details', 'details.stock:id,batch_no', 'details.product:id,productName,category_id,product_type', 'details.product.category:id,categoryName', 'saleReturns.details', 'vat:id,name,rate', 'payment_type:id,name', 'branch:id,name,phone,address'),
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'products' => 'required',
            'saleDate' => 'nullable|date',
            'products.*.stock_id' => 'required|exists:stocks,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
            'rounding_option' => 'nullable|in:none,round_up,nearest_whole_number,nearest_0.05,nearest_0.1,nearest_0.5',
        ]);

        $sale = Sale::findOrFail($id);

        DB::beginTransaction();
        try {

            if ($sale->load('saleReturns')->saleReturns->count() > 0) {
                return response()->json([
                    'message' => __("You can not update this sale because it has already been returned.")
                ], 400);
            }

            $request_products = json_decode($request->products, true);
            $prevDetails = SaleDetails::where('sale_id', $sale->id)->get();
            $stockIds = collect($request_products)->pluck('stock_id')->toArray();
            $current_stocks = Stock::whereIn('id', $stockIds)->get();

            foreach ($current_stocks as $key => $current_stock) {
                $prevStock = $prevDetails->first(function ($item) use ($current_stock) {
                    return $item->stock_id == $current_stock->id;
                });

                $product_name = collect($request_products)->firstWhere('stock_id', $current_stock->id)['product_name'] ?? NULL;

                $product_stock = $prevStock ? ($current_stock->productStock + $prevStock->quantities) : $current_stock->productStock;
                if ($product_stock < $request_products[$key]['quantities']) {
                    return response()->json([
                        'message' => "Stock not availabe for product : " . $product_name . ". Available stock is : " . $current_stock->productStock
                    ], 406);
                }
            }

            foreach ($prevDetails as $prevItem) {
                Stock::findOrFail($prevItem->stock_id)->decrement('productStock', $prevItem->quantities);
            }

            $prevDetails->each->delete();

            $saleDetails = [];
            foreach ($request_products as $key => $productData) {

                $stock = Stock::findOrFail($productData['stock_id']);

                $saleDetails[$key] = [
                    'sale_id' => $sale->id,
                    'stock_id' => $stock->id,
                    'price' => $productData['price'],
                    'product_id' => $stock->product_id,
                    'lossProfit' => $productData['lossProfit'],
                    'quantities' => $productData['quantities'] ?? 0,
                ];

                $stock->decrement('productStock', $productData['quantities']);
            }

            SaleDetails::insert($saleDetails);

            if ($sale->dueAmount || $request->dueAmount) {
                $party = Party::findOrFail($request->party_id);

                // Party Limit Check
                if ($request->dueAmount > 0) {
                    $newDue = $request->party_id == $sale->party_id ? (($party->due - $sale->dueAmount) + $request->dueAmount) : ($party->due + $request->dueAmount);

                    if ($party->dueLimit > 0 && $newDue > $party->dueLimit) {
                        return response()->json([
                            'message' => __("Cannot update sale. Party due will exceed credit limit!")
                        ], 400);
                    }
                }

                $party->update([
                    'due' => $request->party_id == $sale->party_id ? (($party->due - $sale->dueAmount) + $request->dueAmount) : ($party->due + $request->dueAmount)
                ]);

                if ($request->party_id != $sale->party_id) {
                    $prev_party = Party::findOrFail($sale->party_id);
                    $prev_party->update([
                        'due' => $prev_party->due - $sale->dueAmount
                    ]);
                }
            }

            $balanceDiff = ($request->paidAmount ?? 0) - $sale->paidAmount;
            updateBalance($balanceDiff, 'increment');

            $lossProfit = collect($request_products)->pluck('lossProfit')->toArray();

            $sale->update($request->except('image', 'isPaid') + [
                'user_id' => auth()->id(),
                'isPaid' => filter_var($request->isPaid, FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
                'lossProfit' => array_sum($lossProfit) - $request->discountAmount,
                'image' => $request->image ? $this->upload($request, 'image', $sale->image) : $sale->image,
                'meta' => [
                    'note' => $request->note,
                    'customer_phone' => $request->customer_phone,
                ],
            ]);

            DB::commit();
            return response()->json([
                'message' => __('Data saved successfully.'),
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Sale Update Error: " . $e->getMessage());
            return response()->json([
                'message' => 'Something went wrong.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sale $sale)
    {
        foreach ($sale->details as $item) {
            Stock::findOrFail($item->stock_id)->increment('productStock', $item->quantities);
        }

        if ($sale->dueAmount) {
            $party = Party::findOrFail($sale->party_id);
            $party->update([
                'due' => $party->due - $sale->dueAmount
            ]);
        }

        updateBalance($sale->paidAmount, 'decrement');

        if (file_exists($sale->image)) {
            Storage::delete($sale->image);
        }

        $sale->delete();

        return response()->json([
            'message' => __('Data deleted successfully.'),
        ]);
    }
}

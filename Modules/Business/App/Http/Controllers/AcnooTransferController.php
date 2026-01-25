<?php

namespace Modules\Business\App\Http\Controllers;

use App\Models\Branch;
use App\Models\Stock;
use App\Models\Transfer;
use App\Models\TransferProduct;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Business\App\Exports\ExportTransfer;

class AcnooTransferController extends Controller
{
    public function __construct()
    {
        $this->middleware('check.permission:transfers.read')->only(['index']);
        $this->middleware('check.permission:transfers.create')->only(['create', 'store']);
        $this->middleware('check.permission:transfers.update')->only(['edit', 'update']);
        $this->middleware('check.permission:transfers.delete')->only(['destroy', 'deleteAll']);
    }

    public function index()
    {
        $query = Transfer::with(['fromWarehouse:id,name', 'toWarehouse:id,name', 'toBranch:id,name', 'fromBranch:id,name', 'transferProducts'])
            ->where('business_id', auth()->user()->business_id);

        // active branch check
        if (auth()->user()->active_branch_id) {
            $branchId = auth()->user()->active_branch_id;
            $query->where(function ($q) use ($branchId) {
                $q->where('from_branch_id', $branchId)
                    ->orWhere('to_branch_id', $branchId);
            });
        }

        $transfers = $query->latest()->paginate(20);

        $branches = Branch::withTrashed()
            ->where('business_id', auth()->user()->business_id)
            ->latest()
            ->get();

        return view('business::transfers.index', compact('transfers', 'branches'));
    }


    public function acnooFilter(Request $request)
    {
        $transfers = Transfer::with(['fromWarehouse:id,name', 'toWarehouse:id,name', 'toBranch:id,name', 'fromBranch:id,name', 'transferProducts'])
            ->where('business_id', auth()->user()->business_id)
            ->when($request->branch_id, function ($q) use ($request) {
                $q->where(function ($query) use ($request) {
                    $query->where('from_branch_id', $request->branch_id)->orWhere('to_branch_id', $request->branch_id);
                });
            })
            ->when($request->search, function ($q) use ($request) {
                $search = $request->search;

                $q->where(function ($q) use ($search) {
                    $q->where('note', 'like', '%' . $search . '%')
                        ->orWhere('status', 'like', '%' . $search . '%')
                        ->orWhere('invoice_no', 'like', '%' . $search . '%')
                        ->orWhereHas('fromWarehouse', function ($q) use ($search) {
                            $q->where('name', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('toWarehouse', function ($q) use ($search) {
                            $q->where('name', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('toBranch', function ($q) use ($search) {
                            $q->where('name', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('fromBranch', function ($q) use ($search) {
                            $q->where('name', 'like', '%' . $search . '%');
                        });
                });
            })
            ->latest()
            ->paginate($request->per_page ?? 10);

        if ($request->ajax()) {
            return response()->json([
                'data' => view('business::transfers.datas', compact('transfers'))->render()
            ]);
        }

        return redirect(url()->previous());
    }

    public function create()
    {
        $business_id =  auth()->user()->business_id;
        $warehouses = Warehouse::where('business_id', $business_id)->latest()->get();
        $branches = Branch::withTrashed()->where('business_id', auth()->user()->business_id)->latest()->get();
        return view('business::transfers.create', compact('warehouses', 'branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'from_branch_id' => 'nullable|exists:branches,id',
            'to_branch_id' => 'nullable|exists:branches,id|required_with:from_branch_id',
            'from_warehouse_id' => 'nullable|exists:warehouses,id|required_with:to_warehouse_id',
            'to_warehouse_id' => 'nullable|exists:warehouses,id|required_with:from_warehouse_id',
            'transfer_date' => 'required|date',
            'note' => 'nullable|string',
            'status' => 'required|in:pending,completed,cancelled',
            'shipping_charge' => 'nullable|numeric|min:0',
            'products' => 'required|array|min:1',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.unit_price' => 'required|numeric|min:0',
            'products.*.discount' => 'nullable|numeric|min:0',
            'products.*.tax' => 'nullable|numeric|min:0',
        ]);

        $user = auth()->user();
        $fromBranch = $request->from_branch_id ?? $user->branch_id ?? $user->active_branch_id;
        $toBranch = $request->to_branch_id;
        $fromWh = $request->from_warehouse_id;
        $toWh = $request->to_warehouse_id;

        // Transfer validation logic
        if ($user->active_branch_id && $toBranch && $toWh) {
            return response()->json([
                'message' => 'You cannot transfer to another branch warehouse.'
            ], 400);
        }
        if (!$toBranch && !$toWh) {
            // to_branch or to_warehouse no one exist
            return response()->json([
                'message' => 'Invalid transfer request. Please select a destination branch or warehouse.'
            ], 400);
        }
        if ($fromBranch && !$fromWh) {
            // Branch to Branch transfer only
            if ($fromBranch == $toBranch) {
                return response()->json([
                    'message' => 'Transfer not allowed: Same branch transfer is not possible.'
                ], 400);
            }
        } elseif (!$fromBranch && $fromWh) {
            // Warehouse to Warehouse transfer only
            if ($fromWh == $toWh) {
                return response()->json([
                    'message' => 'Transfer not allowed: Same warehouse transfer is not possible.'
                ], 400);
            }
        } elseif ($fromBranch && $fromWh) {
            // Both branch and warehouse present
            if ($fromBranch == $toBranch && $fromWh == $toWh) {
                return response()->json([
                    'message' => 'Transfer not allowed: Same warehouse transfer within the same branch.'
                ], 400);
            }
        } else {
            return response()->json([
                'message' => 'Invalid transfer request. Please provide branch or warehouse information.'
            ], 400);
        }

        DB::beginTransaction();

        try {
            $subTotal = 0;
            $totalDiscount = 0;
            $totalTax = 0;

            foreach ($request->products as $item) {
                $qty = $item['quantity'];
                $price = $item['unit_price'];
                $discount = $item['discount'] ?? 0;
                $tax = $item['tax'] ?? 0;

                $subTotal += ($qty * $price);
                $totalTax += $tax;
                $totalDiscount += $discount;
            }

            $shipping = $request->shipping_charge ?? 0;
            $grandTotal = $subTotal + $totalTax - $totalDiscount + $shipping;

            $transfer = Transfer::create($request->except('business_id', 'shipping_charge', 'sub_total', 'total_discount', 'total_tax', 'grand_total', 'from_warehouse_id', 'to_warehouse_id', 'to_branch_id', 'from_branch_id') + [
                'business_id' => auth()->user()->business_id,
                'from_branch_id' => $fromBranch,
                'from_warehouse_id' => $fromWh,
                'to_branch_id' => $toBranch,
                'to_warehouse_id' => $toWh,
                'shipping_charge' => $shipping,
                'sub_total' => $subTotal,
                'total_discount' => $totalDiscount,
                'total_tax' => $totalTax,
                'grand_total' => $grandTotal,
            ]);

            $transferProductData = [];

            foreach ($request->products as $stockId => $item) {
                // Find product_id from stock_id
                $stock = Stock::find($stockId);

                if (!$stock) {
                    return response()->json([
                        'message' => "Invalid stock ID: {$stockId}"
                    ], 400);
                }

                $transferProductData[] = [
                    'transfer_id' => $transfer->id,
                    'stock_id' => $stockId,
                    'product_id' => $stock->product_id,
                    'quantity' => $item['quantity'] ?? 0,
                    'unit_price' => $item['unit_price'] ?? 0,
                    'discount' => $item['discount'] ?? 0,
                    'tax' => $item['tax'] ?? 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if ($request->status === 'completed') {
                    foreach ($request->products as $stockId => $item) {
                        // Get the actual FROM stock
                        $fromStock = Stock::where('id', $stockId)
                            ->when($fromBranch, fn($q) => $q->where('branch_id', $fromBranch))
                            ->when($fromWh, fn($q) => $q->where('warehouse_id', $fromWh))
                            ->first();

                        if (!$fromStock) {
                            return response()->json([
                                'message' => "Stock not found in source (branch/warehouse) for stock ID: {$stockId}"
                            ], 400);
                        }

                        if ($fromStock->productStock < $item['quantity']) {
                            return response()->json([
                                'message' => "Insufficient stock in source for product ID: {$fromStock->product_id}, available: {$fromStock->productStock}"
                            ], 400);
                        }

                        // Decrease FROM stock
                        $fromStock->decrement('productStock', $item['quantity']);

                        // Get the TO stock
                        $toStock = Stock::where('product_id', $fromStock->product_id)
                            ->when($toBranch, fn($q) => $q->where('branch_id', $toBranch))
                            ->when($toWh, fn($q) => $q->where('warehouse_id', $toWh))
                            ->when(!is_null($fromStock->batch_no), fn($q) => $q->where('batch_no', $fromStock->batch_no))
                            ->first();

                        if (!$toStock) {
                            $toStock = new Stock([
                                'business_id' => auth()->user()->business_id,
                                'product_id' => $fromStock->product_id,
                                'warehouse_id' => $toWh,
                                'batch_no' => $fromStock->batch_no,
                                'productStock' => 0,
                                'productPurchasePrice' => $fromStock->productPurchasePrice,
                                'profit_percent' => $fromStock->profit_percent,
                                'productSalePrice' => $fromStock->productSalePrice,
                                'productWholeSalePrice' => $fromStock->productWholeSalePrice,
                                'productDealerPrice' => $fromStock->productDealerPrice,
                                'mfg_date' => $fromStock->mfg_date,
                                'expire_date' => $fromStock->expire_date,
                            ]);

                            // if active branch and to branch is null then use active branch id
                            if ($toBranch == null && $user->active_branch_id){
                                $toStock->branch_id = $user->active_branch_id;
                            }else{
                                $toStock->branch_id = $toBranch;
                            }

                            //  Update product_type
                            if ($fromStock->product->product_type !== 'variant') {
                                $fromStock->product->update([
                                    'product_type' => 'variant'
                                ]);
                            }

                            // Skip booted from model
                            Stock::withoutEvents(function () use ($toStock) {
                                $toStock->save();
                            });
                        }

                        // Increase TO stock safely
                        $toStock->increment('productStock', $item['quantity']);
                    }
                }
            }

            TransferProduct::insert($transferProductData);

            DB::commit();


            return response()->json([
                'message' => __('Transfer saved successfully.'),
                'redirect' => route('business.transfers.index'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        $user = auth()->user();
        $business_id = $user->business_id;

        $transfer = Transfer::with(['transferProducts.product', 'transferProducts.stock'])
            ->where('business_id', $business_id)
            ->findOrFail($id);

        // Determine the branch to filter warehouses
        $branchId = $user->branch_id ?? $user->active_branch_id;

        $warehousesQuery = Warehouse::where('business_id', $business_id);
        if ($branchId) {
            $warehousesQuery->where('branch_id', $branchId);
        }
        $warehouses = $warehousesQuery->latest()->get();

        $branches = Branch::withTrashed()
            ->where('business_id', $business_id)
            ->latest()
            ->get();

        return view('business::transfers.edit', compact('transfer', 'warehouses', 'branches'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'from_branch_id' => 'nullable|exists:branches,id',
            'to_branch_id' => 'nullable|exists:branches,id|required_with:from_branch_id',
            'from_warehouse_id' => 'nullable|exists:warehouses,id|required_with:to_warehouse_id',
            'to_warehouse_id' => 'nullable|exists:warehouses,id|required_with:from_warehouse_id',
            'transfer_date' => 'required|date',
            'note' => 'nullable|string',
            'status' => 'required|in:pending,completed,cancelled',
            'shipping_charge' => 'nullable|numeric|min:0',
            'products' => 'required|array|min:1',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.unit_price' => 'required|numeric|min:0',
            'products.*.discount' => 'nullable|numeric|min:0',
            'products.*.tax' => 'nullable|numeric|min:0',
        ]);

        $transfer = Transfer::findOrFail($id);

        if ($request->status == 'cancelled') {
            $transfer->update(['status' => 'cancelled']);
            return response()->json([
                'message' => __('Transfer cancelled successfully.'),
                'redirect' => route('business.transfers.index')
            ]);
        }

        $user = auth()->user();
        $fromBranch = $request->from_branch_id ?? $user->branch_id ?? $user->active_branch_id;
        $toBranch = $request->to_branch_id;
        $fromWh = $request->from_warehouse_id;
        $toWh = $request->to_warehouse_id;

        // Transfer validation
        if ($user->active_branch_id && $toBranch && $toWh) {
            return response()->json([
                'message' => 'You cannot transfer to another branch warehouse.'
            ], 400);
        }
        if (!$toBranch && !$toWh) {
            // to_branch or to_warehouse no one exist
            return response()->json([
                'message' => 'Invalid transfer request. Please select a destination branch or warehouse.'
            ], 400);
        }

        if ($fromBranch && !$fromWh) {
            if ($fromBranch == $toBranch) {
                return response()->json(['message' => 'Transfer not allowed: Same branch transfer is not possible.'], 400);
            }
        } elseif (!$fromBranch && $fromWh) {
            if ($fromWh == $toWh) {
                return response()->json(['message' => 'Transfer not allowed: Same warehouse transfer is not possible.'], 400);
            }
        } elseif ($fromBranch && $fromWh) {
            if ($fromBranch == $toBranch && $fromWh == $toWh) {
                return response()->json(['message' => 'Transfer not allowed: Same warehouse transfer within the same branch.'], 400);
            }
        } else {
            return response()->json(['message' => 'Invalid transfer request. Please provide branch or warehouse information.'], 400);
        }

        DB::beginTransaction();
        try {
            $oldStatus = $transfer->status;
            $subTotal = $totalDiscount = $totalTax = 0;
            foreach ($request->products as $item) {
                $subTotal += $item['quantity'] * $item['unit_price'];
                $totalDiscount += $item['discount'] ?? 0;
                $totalTax += $item['tax'] ?? 0;
            }
            $shipping = $request->shipping_charge ?? 0;
            $grandTotal = $subTotal + $totalTax - $totalDiscount + $shipping;

            // Update transfer
            $transfer->update([
                'from_branch_id' => $fromBranch,
                'to_branch_id' => $toBranch,
                'from_warehouse_id' => $fromWh,
                'to_warehouse_id' => $toWh,
                'transfer_date' => $request->transfer_date,
                'note' => $request->note,
                'status' => $request->status,
                'shipping_charge' => $shipping,
                'sub_total' => $subTotal,
                'total_discount' => $totalDiscount,
                'total_tax' => $totalTax,
                'grand_total' => $grandTotal,
            ]);

            // Update TransferProduct
            TransferProduct::where('transfer_id', $transfer->id)->delete();
            $transferProductData = [];
            foreach ($request->products as $stockId => $item) {
                $transferProductData[] = [
                    'transfer_id' => $transfer->id,
                    'stock_id' => $stockId,
                    'quantity' => $item['quantity'] ?? 0,
                    'unit_price' => $item['unit_price'] ?? 0,
                    'discount' => $item['discount'] ?? 0,
                    'tax' => $item['tax'] ?? 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            TransferProduct::insert($transferProductData);

            // Stock Handling
            foreach ($request->products as $stockId => $item) {
                $fromStock = Stock::where('id', $stockId)
                    ->when($fromBranch, fn($q) => $q->where('branch_id', $fromBranch))
                    ->when($fromWh, fn($q) => $q->where('warehouse_id', $fromWh))
                    ->first();

                if (!$fromStock) {
                    return response()->json([
                        'message' => "From stock not found for stock ID: {$stockId}"
                    ], 400);
                }
                $toStock = Stock::where('product_id', $fromStock->product_id)
                    ->when($toBranch, fn($q) => $q->where('branch_id', $toBranch))
                    ->when($toWh, fn($q) => $q->where('warehouse_id', $toWh))
                    ->when(!is_null($fromStock->batch_no), fn($q) => $q->where('batch_no', $fromStock->batch_no))
                    ->first();

                if (!$toStock) {
                    $toStock = new Stock([
                        'business_id' => auth()->user()->business_id,
                        'product_id' => $fromStock->product_id,
                        'warehouse_id' => $toWh,
                        'batch_no' => $fromStock->batch_no,
                        'productStock' => 0,
                        'productPurchasePrice' => $fromStock->productPurchasePrice,
                        'profit_percent' => $fromStock->profit_percent,
                        'productSalePrice' => $fromStock->productSalePrice,
                        'productWholeSalePrice' => $fromStock->productWholeSalePrice,
                        'productDealerPrice' => $fromStock->productDealerPrice,
                        'mfg_date' => $fromStock->mfg_date,
                        'expire_date' => $fromStock->expire_date,
                    ]);

                    // if active branch and to branch is null then use active branch id
                    if ($toBranch == null && $user->active_branch_id){
                        $toStock->branch_id = $user->active_branch_id;
                    }else{
                        $toStock->branch_id = $toBranch;
                    }

                    //  Update product_type
                    if ($fromStock->product->product_type !== 'variant') {
                        $fromStock->product->update([
                            'product_type' => 'variant'
                        ]);
                    }

                    Stock::withoutEvents(fn() => $toStock->save());
                }

                // Stock movement based on status change
                if ($oldStatus !== $request->status) {
                    if ($oldStatus === 'pending' && $request->status === 'completed') {
                        if ($fromStock->productStock >= $item['quantity']) {
                            $fromStock->decrement('productStock', $item['quantity']);
                            $toStock->increment('productStock', $item['quantity']);
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'message' => __('Transfer updated successfully.'),
                'redirect' => route('business.transfers.index'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        $business_id = auth()->user()->business_id;
        $transfer = Transfer::with('transferProducts')->where('business_id', $business_id)->findOrFail($id);

        DB::beginTransaction();

        try {
            if ($transfer->status == 'completed') {
                foreach ($transfer->transferProducts as $tp) {
                    $stock = Stock::find($tp->stock_id);

                    // check if destination has enough stock to rollback
                    $toStock = Stock::where('product_id', $tp->product_id)
                        ->where('warehouse_id', $transfer->to_warehouse_id)
                        ->where('branch_id', $transfer->to_branch_id)
                        ->where('batch_no', optional($stock)->batch_no)
                        ->first();

                    if (!$toStock || $toStock->quantity < $tp->quantity) {
                        return response()->json([
                            'message' => __('Cannot delete. Destination stock not enough to reverse this transfer'),
                        ], 422);
                    }
                }

                foreach ($transfer->transferProducts as $tp) {
                    $stock = Stock::find($tp->stock_id);

                    // decrease destination stock
                    $toStock = Stock::where('product_id', $tp->product_id)
                        ->where('warehouse_id', $transfer->to_warehouse_id)
                        ->where('branch_id', $transfer->to_branch_id)
                        ->where('batch_no', optional($stock)->batch_no)
                        ->first();
                    if ($toStock) {
                        $toStock->decrement('quantity', $tp->quantity);
                    }

                    // increase source stock
                    $fromStock = Stock::where('product_id', $tp->product_id)
                        ->where('warehouse_id', $transfer->from_warehouse_id)
                        ->where('branch_id', $transfer->from_branch_id)
                        ->where('batch_no', optional($stock)->batch_no)
                        ->first();
                    if ($fromStock) {
                        $fromStock->increment('quantity', $tp->quantity);
                    }
                }
            }

            $transfer->delete();

            DB::commit();

            return response()->json([
                'message' => __('Transfer deleted successfully'),
                'redirect' => route('business.transfers.index'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => __('Error while deleting transfer: ') . $e->getMessage(),
            ], 500);
        }
    }

    public function deleteAll(Request $request)
    {
        $business_id = auth()->user()->business_id;

        DB::beginTransaction();

        try {
            $transfers = Transfer::with('transferProducts')
                ->where('business_id', $business_id)
                ->whereIn('id', $request->ids)
                ->get();

            foreach ($transfers as $transfer) {
                if ($transfer->status === 'completed') {
                    foreach ($transfer->transferProducts as $tp) {
                        $stock = Stock::find($tp->stock_id);

                        // check if destination has enough stock to rollback
                        $toStock = Stock::where('product_id', $tp->product_id)
                            ->where('warehouse_id', $transfer->to_warehouse_id)
                            ->where('branch_id', $transfer->to_branch_id)
                            ->where('batch_no', optional($stock)->batch_no)
                            ->first();

                        if (!$toStock || $toStock->quantity < $tp->quantity) {
                            return response()->json([
                                'message' => __('Cannot delete. Destination stock not enough to reverse transfer ID: ') . $transfer->id,
                            ], 422);
                        }
                    }
                }
            }

            foreach ($transfers as $transfer) {
                if ($transfer->status === 'completed') {
                    foreach ($transfer->transferProducts as $tp) {
                        $stock = Stock::find($tp->stock_id);

                        // decrease destination stock
                        $toStock = Stock::where('product_id', $tp->product_id)
                            ->where('warehouse_id', $transfer->to_warehouse_id)
                            ->where('branch_id', $transfer->to_branch_id)
                            ->where('batch_no', optional($stock)->batch_no)
                            ->first();
                        if ($toStock) {
                            $toStock->decrement('quantity', $tp->quantity);
                        }

                        // increase source stock
                        $fromStock = Stock::where('product_id', $tp->product_id)
                            ->where('warehouse_id', $transfer->from_warehouse_id)
                            ->where('branch_id', $transfer->from_branch_id)
                            ->where('batch_no', optional($stock)->batch_no)
                            ->first();
                        if ($fromStock) {
                            $fromStock->increment('quantity', $tp->quantity);
                        }
                    }
                }

                $transfer->delete();
            }

            DB::commit();

            return response()->json([
                'message' => __('Selected transfers deleted successfully.'),
                'redirect' => route('business.transfers.index'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => __('Error while deleting transfers: ') . $e->getMessage(),
            ], 500);
        }
    }

    public function exportExcel()
    {
        return Excel::download(new ExportTransfer, 'transfer.xlsx');
    }

    public function exportCsv()
    {
        return Excel::download(new ExportTransfer, 'transfer.csv');
    }
}

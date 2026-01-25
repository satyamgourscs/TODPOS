<?php

namespace Modules\WarehouseAddon\App\Http\Controllers;

use App\Models\Branch;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Product;

class AcnooWarehouseController extends Controller
{
    public function __construct()
    {
        $this->middleware('check.permission:warehouses.read')->only(['index']);
        $this->middleware('check.permission:warehouses.create')->only(['store']);
        $this->middleware('check.permission:warehouses.update')->only(['update']);
        $this->middleware('check.permission:warehouses.delete')->only(['destroy', 'deleteAll']);
    }

    public function index()
    {
        $businessId = auth()->user()->business_id;

        $warehouses = Warehouse::with('branch:id,name')
            ->where('business_id', $businessId)
            ->withSum(['stocks as total_qty' => function ($query) {
                $query->when(request()->has('branch_id'), function ($q) {
                    $q->whereColumn('stocks.branch_id', 'warehouses.branch_id');
                });
            }], 'productStock')
            ->withSum(['stocks as total_value' => function ($query) {
                $query->when(request()->has('branch_id'), function ($q) {
                    $q->whereColumn('stocks.branch_id', 'warehouses.branch_id');
                });
            }], DB::raw('productStock * productPurchasePrice'))
            ->latest()
            ->paginate(20);


        $branches = Branch::withTrashed()
            ->where('business_id', $businessId)
            ->latest()
            ->get();

        return view('warehouseaddon::warehouse.index', compact('warehouses', 'branches'));
    }

    public function acnooFilter(Request $request)
    {
        $warehouses = Warehouse::with('branch:id,name')->where('business_id', auth()->user()->business_id)
            ->when($request->branch_id, function ($q) use ($request) {
                $q->where('branch_id', $request->branch_id);
            })
            ->when(request('search'), function ($q) use ($request) {
                $q->where(function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%')
                        ->orWhere('address', 'like', '%' . $request->search . '%')
                        ->orWhere('phone', 'like', '%' . $request->search . '%')
                        ->orWhereHas('branch', function ($q) use ($request) {
                            $q->where('name', 'like', '%' . $request->search . '%');
                        });
                });
            })
            ->latest()
            ->paginate($request->per_page ?? 10);

        if ($request->ajax()) {
            return response()->json([
                'data' => view('warehouseaddon::warehouse.datas', compact('warehouses'))->render()
            ]);
        }
        return redirect(url()->previous());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'phone'    => 'nullable|string|max:20',
            'address'  => 'nullable|string|max:500',
            'email'    => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('warehouses')->where(function ($query) {
                    return $query->where('business_id', auth()->user()->business_id);
                }),
            ],
        ]);

        Warehouse::create($request->except('business_id', 'branch_id') + [
                'branch_id' => $request->branch_id ?? auth()->user()->branch_id ?? auth()->user()->active_branch_id,
                'business_id' => auth()->user()->business_id,
            ]);

        return response()->json([
            'message' => __('Warehouse saved successfully.'),
            'redirect' => route('warehouse.warehouses.index')
        ]);
    }

    public function update(Request $request, $id)
    {
        $warehouse = Warehouse::findOrFail($id);
        $request->validate([
            'name'     => 'required|string|max:255',
            'phone'    => 'nullable|string|max:20',
            'address'  => 'nullable|string|max:500',
            'email'    => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('warehouses')->ignore($warehouse->id)->where(function ($query) {
                    return $query->where('business_id', auth()->user()->business_id);
                }),
            ],
        ]);

        $warehouse->update($request->except('business_id', 'branch_id') + [
                'branch_id' => $request->branch_id ?? auth()->user()->branch_id ?? auth()->user()->active_branch_id,
            ]);

        return response()->json([
            'message' => __('Warehouse updated successfully.'),
            'redirect' => route('warehouse.warehouses.index')
        ]);
    }

    public function destroy($id)
    {
        $warehouse = Warehouse::findOrFail($id);
        $warehouse->delete();

        return response()->json([
            'message' => __('Warehouse deleted successfully'),
            'redirect' => route('warehouse.warehouses.index'),
        ]);
    }

    public function deleteAll(Request $request)
    {
        Warehouse::whereIn('id', $request->ids)->delete();

        return response()->json([
            'message' => __('Selected items deleted successfully.'),
            'redirect' => route('warehouse.warehouses.index'),
        ]);
    }

    public function branchWiseWarehouses(Request $request)
    {
        $user = auth()->user();

        // Determine the branch
        $branchId = $user->branch_id ?? $user->active_branch_id;

        $query = Warehouse::query()->where('business_id', $user->business_id);

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        } elseif ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $warehouses = $query->get();

        return response()->json($warehouses);
    }

    public function warehouseProduct()
    {
        $user = auth()->user();
        $businessId = $user->business_id;
        $activeBranchId = $user->active_branch_id;

        $products = Product::with('stocks.warehouse', 'unit:id,unitName', 'brand:id,brandName', 'category', 'warehouse:id,name', 'rack:id,name', 'shelf:id,name')
            ->where('business_id', $businessId)
            ->whereHas('stocks', function ($query) use ($activeBranchId) {
                $query->whereNotNull('warehouse_id')
                    ->when($activeBranchId, fn($q) => $q->where('branch_id', $activeBranchId));
            })
            ->withSum('stocks as total_stock', 'productStock')
            ->latest()
            ->paginate(10);

        return view('warehouseaddon::warehouse.products.index', compact('products'));
    }

    public function acnooProductFilter(Request $request)
    {
        $user = auth()->user();
        $businessId = $user->business_id;
        $activeBranchId = $user->active_branch_id;
        $search = $request->input('search');

        $products = Product::with([
            'stocks',
            'unit:id,unitName',
            'brand:id,brandName',
            'category:id,categoryName',
            'warehouse:id,name',
            'rack:id,name',
            'shelf:id,name'
        ])
            ->where('business_id', $businessId)
            ->whereHas('stocks', function ($query) use ($activeBranchId) {
                $query->whereNotNull('warehouse_id')
                    ->when($activeBranchId, fn($q) => $q->where('branch_id', $activeBranchId));
            })
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
            ->latest()
            ->paginate($request->per_page ?? 10);

        if ($request->ajax()) {
            return response()->json([
                'data' => view('warehouseaddon::warehouse.products.datas', compact('products'))->render()
            ]);
        }

        return redirect(url()->previous());
    }

}

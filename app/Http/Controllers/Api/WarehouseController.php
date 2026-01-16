<?php

namespace App\Http\Controllers\Api;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;

class WarehouseController extends Controller
{
    public function index()
    {
        $businessId = auth()->user()->business_id;
        $data = Warehouse::with(['products.stocks'])
        ->where('business_id', $businessId)
            ->latest()
            ->get()
            ->map(function ($warehouse) {
                $totalQty = 0;
                $totalValue = 0;

                foreach ($warehouse->products as $product) {
                    foreach ($product->stocks as $stock) {
                        $totalQty += $stock->productStock;
                        $totalValue += $stock->productStock * $stock->productPurchasePrice;
                    }
                }

                return [
                    'id' => $warehouse->id,
                    'business_id' => $warehouse->business_id,
                    'name' => $warehouse->name,
                    'phone' => $warehouse->phone,
                    'email' => $warehouse->email,
                    'address' => $warehouse->address,
                    'total_quantity' => $totalQty,
                    'total_value' => $totalValue,
                ];
            });

        return response()->json([
            'message' => __('Data fetched successfully.'),
            'data' => $data,
        ]);
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

        $data =  Warehouse::create($request->except('business_id') + [
                'business_id' => auth()->user()->business_id,
                'branch_id' => $request->branch_id ?? auth()->user()->branch_id ?? auth()->user()->active_branch_id,
            ]);

        return response()->json([
            'message' => __('Data saved successfully.'),
            'data' => $data,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $warehouse = Warehouse::find($id);
        if (!$warehouse) {
            return response()->json([
                'message' => __('Warehouse not found.'),
                'data' => null,
            ], 404);
        }

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

        $warehouse->update($request->except('branch_id', 'business_id') + [
                'branch_id' => $request->branch_id ?? auth()->user()->branch_id ?? auth()->user()->active_branch_id,
            ]);

        return response()->json([
            'message' => __('Data saved successfully.'),
            'data' => $warehouse,
        ]);
    }

    public function destroy(string $id)
    {
        $warehouse = Warehouse::find($id);

        if (!$warehouse) {
            return response()->json([
                'message' => __('Warehouse not found.'),
            ], 404);
        }

        $warehouse->delete();
        return response()->json([
            'message' => __('Data deleted successfully.'),
        ]);
    }
}

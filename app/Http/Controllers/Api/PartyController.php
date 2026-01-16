<?php

namespace App\Http\Controllers\Api;

use App\Models\Party;
use App\Models\Business;
use App\Helpers\HasUploader;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PartyController extends Controller
{
    use HasUploader;

    public function index()
    {
        $user = auth()->user();
        $activeBranch = $user->active_branch;

        $parties = Party::where('business_id', $user->business_id)
            ->latest()
            ->get();

        if ($activeBranch) {
            foreach ($parties as $party) {
                // Calculate due only for the active branch
                if ($party->type === 'Supplier') {
                    $branchPurchases = $party->purchases_dues->where('branch_id', $activeBranch->id);
                    $party->due = $branchPurchases->sum('dueAmount');
                } else {
                    $branchSales = $party->sales_dues->where('branch_id', $activeBranch->id);
                    $party->due = $branchSales->sum('dueAmount');
                }
            }
        }

        return response()->json([
            'message' => __('Data fetched successfully.'),
            'data' => $parties,
        ]);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $business_id = auth()->user()->business_id;

        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:Retailer,Dealer,Wholesaler,Supplier',
            'phone' => 'nullable|max:20|' . Rule::unique('parties')->where('business_id', $business_id),
            'image' => 'nullable|image|mimes:jpeg,png,jpg,svg',
            'address' => 'nullable|string|max:255',
            'credit_limit' => 'nullable|numeric|min:0|max:999999999999.99',
            'opening_balance' => 'nullable|numeric|min:-999999999999.99|max:999999999999.99',
            'opening_balance_type' => 'required|in:due,advance',
        ]);

        $data = Party::create($request->except('image', 'due', 'wallet', 'opening_balance', 'credit_limit') + [
                'due' => ($request->opening_balance_type == 'due') ? ($request->opening_balance ?? 0) : 0,
                'wallet' => ($request->opening_balance_type == 'advance') ? ($request->opening_balance ?? 0) : 0,
                'opening_balance' => $request->opening_balance ?? 0,
                'credit_limit' => $request->credit_limit ?? 0,
                'image' => $request->image ? $this->upload($request, 'image') : NULL,
                'business_id' => $business_id
            ]);

        return response()->json([
            'message' => __('Data saved successfully.'),
            'data' => $data,
        ]);
    }

    public function show(Party $party)
    {
        if (env('MESSAGE_ENABLED')) {
            if ($party->due) {
                $business = Business::findOrFail($party->business_id);
                $response = sendMessage($party->phone, dueMessage($party, $business->companyName));

                if ($response->successful()) {
                    return response()->json([
                        'message' => __('Message has been send successfully.'),
                    ]);
                }

                return response()->json([
                    'message' => __('Something was wrong, Please contact with admin.'),
                ], 406);
            } else {
                return response()->json([
                    'message' => __('This party has no due balance.'),
                ], 406);
            }
        } else {
            return response()->json([
                'message' => __('Message has been disabled by admin.'),
            ], 406);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Party $party)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:Retailer,Dealer,Wholesaler,Supplier',
            'phone' => 'nullable|max:20|unique:parties,phone,' . $party->id . ',id,business_id,' . auth()->user()->business_id,
            'image' => 'nullable|image|mimes:jpeg,png,jpg,svg',
            'address' => 'nullable|string|max:255',
            'credit_limit' => 'nullable|numeric|min:0|max:999999999999.99',
            'opening_balance' => 'nullable|numeric|min:-999999999999.99|max:999999999999.99',
            'opening_balance_type' => 'required|in:due,advance',
        ]);

        // Previous
        $prevOpening = $party->opening_balance ?? 0;
        $prevType = $party->opening_balance_type;

        // Current
        $currentOpening = $request->opening_balance ?? 0;
        $currentType = $request->opening_balance_type;

        // Start with existing balance
        $due = $party->due;
        $wallet = $party->wallet;

        if ($prevType == $currentType) {
            // Same type → adjust by difference
            if ($currentType == 'due') {
                $due += ($currentOpening - $prevOpening);
            } else {
                $wallet += ($currentOpening - $prevOpening);
            }
        } else {
            // Type changed → shift balances
            if ($prevType == 'due' && $currentType == 'advance') {
                $due -= $prevOpening;
                $wallet += $currentOpening;
            } elseif ($prevType == 'advance' && $currentType == 'due') {
                $wallet -= $prevOpening;
                $due += $currentOpening;
            }
        }

        $party->update(
            $request->except('image', 'due', 'wallet', 'opening_balance', 'credit_limit', 'business_id') + [
                'due' => $due,
                'wallet' => $wallet,
                'opening_balance' => $currentOpening,
                'opening_balance_type' => $currentType,
                'credit_limit' => $request->credit_limit ?? $party->credit_limit,
                'image' => $request->image ? $this->upload($request, 'image', $party->image) : $party->image,
            ]
        );

        return response()->json([
            'message' => __('Data saved successfully.'),
            'data'    => $party,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Party $party)
    {
        if (!$party->canBeDeleted()) {
            return response()->json([
                'message' => __('This party cannot be deleted.'),
            ], 400);
        }

        if (file_exists($party->image)) {
            Storage::delete($party->image);
        }

        $party->delete();
        return response()->json([
            'message' => __('Data deleted successfully.'),
        ]);
    }
}

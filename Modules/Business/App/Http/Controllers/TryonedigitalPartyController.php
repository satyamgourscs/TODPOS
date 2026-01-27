<?php

namespace Modules\Business\App\Http\Controllers;

use App\Models\Party;
use App\Helpers\HasUploader;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TryonedigitalPartyController extends Controller
{
    use HasUploader;

    public function __construct()
    {
        $this->middleware('check.permission:parties.read')->only(['index']);
        $this->middleware('check.permission:parties.create')->only(['create', 'store']);
        $this->middleware('check.permission:parties.update')->only(['edit', 'update']);
        $this->middleware('check.permission:parties.delete')->only(['destroy', 'deleteAll']);
    }

    public function index()
    {
        $user = auth()->user();
        $business_id = $user->business_id;
        $party_type = request('type');

        $query = Party::where('business_id', $business_id);

        // Filter by party type
        if ($party_type === 'Customer') {
            $query->whereIn('type', ['Retailer', 'Dealer', 'Wholesaler']);
        } elseif ($party_type === 'Supplier') {
            $query->where('type', 'Supplier');
        }

        $parties = $query->latest()->paginate(20);

        $parties->setCollection(
            $parties->getCollection()->transform(function ($party) {
                $party->due = $party->type === 'Supplier'
                    ? $party->purchases_dues->sum('dueAmount')
                    : $party->sales_dues->sum('dueAmount');
                return $party;
            })
        );

        return view('business::parties.index', compact('parties', 'party_type'));
    }

    public function tryonedigitalFilter(Request $request)
    {
        $user = auth()->user();
        $business_id = $user->business_id;
        $search = $request->input('search');
        $party_type = $request->input('type');

        $query = Party::where('business_id', $business_id)
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%')
                        ->orWhere('type', 'like', '%' . $search . '%')
                        ->orWhere('address', 'like', '%' . $search . '%')
                        ->orWhere('due', 'like', '%' . $search . '%');
                });
            });

        // Filter by party type
        if ($party_type === 'Customer') {
            $query->whereIn('type', ['Retailer', 'Dealer', 'Wholesaler']);
        } elseif ($party_type === 'Supplier') {
            $query->where('type', 'Supplier');
        }

        $parties = $query->latest()->paginate($request->per_page ?? 10);

        $parties->setCollection(
            $parties->getCollection()
                ->transform(function ($party) {
                    $party->due = $party->type === 'Supplier'
                        ? $party->purchases_dues->sum('dueAmount')
                        : $party->sales_dues->sum('dueAmount');
                    return $party;
                })
        );

        if ($request->ajax()) {
            return response()->json([
                'data' => view('business::parties.datas', compact('parties'))->render()
            ]);
        }

        return redirect()->back();
    }

    public function create()
    {
        return view('business::parties.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'phone' => 'nullable|max:20|' . Rule::unique('parties')->where('business_id', auth()->user()->business_id),
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:Retailer,Dealer,Wholesaler,Supplier',
            'email' => 'nullable|email',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,svg',
            'address' => 'nullable|string|max:255',
            'due' => 'nullable|numeric|min:0',
            'billing_address' => 'nullable|array',
            'billing_address.address' => 'nullable|string|max:255',
            'billing_address.city' => 'nullable|string|max:255',
            'billing_address.state' => 'nullable|string|max:255',
            'billing_address.zip_code' => 'nullable|string|max:20',
            'billing_address.country' => 'nullable|string|max:255',
            'shipping_address' => 'nullable|array',
            'shipping_address.address' => 'nullable|string|max:255',
            'shipping_address.city' => 'nullable|string|max:255',
            'shipping_address.state' => 'nullable|string|max:255',
            'shipping_address.zip_code' => 'nullable|string|max:20',
            'shipping_address.country' => 'nullable|string|max:255',
            'credit_limit' => 'nullable|numeric|min:0|max:999999999999.99',
            'opening_balance' => 'nullable|numeric|min:-999999999999.99|max:999999999999.99',
            'opening_balance_type' => 'required|in:due,advance',
            'meta' => 'nullable|array',
        ]);

        Party::create($request->except('image', 'due', 'wallet', 'opening_balance', 'credit_limit','business_id') + [
            'due' => ($request->opening_balance_type == 'due') ? ($request->opening_balance ?? 0) : 0,
            'wallet' => ($request->opening_balance_type == 'advance') ? ($request->opening_balance ?? 0) : 0,
            'opening_balance' => $request->opening_balance ?? 0,
            'credit_limit' => $request->credit_limit ?? 0,
            'image' => $request->image ? $this->upload($request, 'image') : NULL,
            'business_id' => auth()->user()->business_id
        ]);

        $type = in_array($request->type, ['Retailer', 'Dealer', 'Wholesaler']) ? 'Customer' : ($request->type === 'Supplier' ? 'Supplier' : '');

        return response()->json([
            'message'   => __(ucfirst($type) . ' created successfully'),
            'redirect'  => route('business.parties.index', ['type' => $type])
        ]);
    }

    public function edit($id)
    {
        $party = Party::where('business_id', auth()->user()->business_id)->findOrFail($id);
        return view('business::parties.edit', compact('party'));
    }

    public function update(Request $request, $id)
    {
        $party = Party::findOrFail($id);

        $request->validate([
            'phone' => 'nullable|max:20|unique:parties,phone,' . $party->id . ',id,business_id,' . auth()->user()->business_id,
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:Retailer,Dealer,Wholesaler,Supplier',
            'email' => 'nullable|email',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,svg',
            'address' => 'nullable|string|max:255',
            'due' => 'nullable|numeric|min:0',
            'billing_address' => 'nullable|array',
            'billing_address.address' => 'nullable|string|max:255',
            'billing_address.city' => 'nullable|string|max:255',
            'billing_address.state' => 'nullable|string|max:255',
            'billing_address.zip_code' => 'nullable|string|max:20',
            'billing_address.country' => 'nullable|string|max:255',
            'shipping_address' => 'nullable|array',
            'shipping_address.address' => 'nullable|string|max:255',
            'shipping_address.city' => 'nullable|string|max:255',
            'shipping_address.state' => 'nullable|string|max:255',
            'shipping_address.zip_code' => 'nullable|string|max:20',
            'shipping_address.country' => 'nullable|string|max:255',
            'credit_limit' => 'nullable|numeric|min:0|max:999999999999.99',
            'opening_balance' => 'nullable|numeric|min:-999999999999.99|max:999999999999.99',
            'opening_balance_type' => 'required|in:due,advance',
            'meta' => 'nullable|array',
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

        if ($prevType === $currentType) {
            // Same type then adjust by difference
            if ($currentType === 'due') {
                $due += ($currentOpening - $prevOpening);
            } else {
                $wallet += ($currentOpening - $prevOpening);
            }
        } else {
            // Type changed then shift balances
            if ($prevType === 'due' && $currentType === 'advance') {
                $due -= $prevOpening;
                $wallet += $currentOpening;
            } elseif ($prevType === 'advance' && $currentType === 'due') {
                $wallet -= $prevOpening;
                $due += $currentOpening;
            }
        }

        $party->update($request->except('image', 'due', 'wallet', 'opening_balance', 'credit_limit', 'opening_balance_type','business_id') + [
                'due' => $due,
                'wallet' => $wallet,
                'opening_balance' => $currentOpening,
                'opening_balance_type' => $currentType,
                'credit_limit' => $request->credit_limit ?? $party->credit_limit,
                'image' => $request->image ? $this->upload($request, 'image', $party->image) : $party->image,
            ]
        );

        $type = in_array($party->type, ['Retailer', 'Dealer', 'Wholesaler']) ? 'Customer' : ($party->type === 'Supplier' ? 'Supplier' : '');

        return response()->json([
            'message'   => __(ucfirst($type) . ' updated successfully'),
            'redirect'  => route('business.parties.index', ['type' => $type])
        ]);
    }

    public function destroy($id)
    {
        $party = Party::findOrFail($id);

        if (!$party->canBeDeleted()) {
            return response()->json([
                'message' => __('This party cannot be deleted.'),
            ], 400);
        }

        if (file_exists($party->image)) {
            Storage::delete($party->image);
        }

        $party->delete();
        $type = in_array($party->type, ['Retailer', 'Dealer', 'Wholesaler']) ? 'Customer' : ($party->type === 'Supplier' ? 'Supplier' : '');

        return response()->json([
            'message' => ucfirst($party->type) . ' deleted successfully',
            'redirect' => route('business.parties.index', ['type' => $type]),
        ]);
    }

    public function deleteAll(Request $request)
    {
        $parties = Party::whereIn('id', $request->ids)->get();
        $partyType = null;
        $undeletable = [];

        foreach ($parties as $party) {
            if (!$party->canBeDeleted()) {
                $undeletable[] = $party->name;
                continue;
            }

            if (file_exists($party->image)) {
                Storage::delete($party->image);
            }

            $party->delete();

            if ($partyType === null) {
                $partyType = in_array($party->type, ['Retailer', 'Dealer', 'Wholesaler']) ? 'Customer' : ($party->type === 'Supplier' ? 'Supplier' : '');
            }
        }

        $message = __('Selected parties deleted successfully');
        if (!empty($undeletable)) {
            $message .= ' (Some parties were skipped: ' . implode(', ', $undeletable) . ')';
        }

        return response()->json([
            'message' => $message,
            'redirect' => route('business.parties.index', ['type' => $partyType]),
        ]);
    }

}

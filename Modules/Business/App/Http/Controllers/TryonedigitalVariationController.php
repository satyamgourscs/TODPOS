<?php

namespace Modules\Business\App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Variation;

class TryonedigitalVariationController extends Controller
{

    public function index()
    {
        $variations = Variation::where('business_id', auth()->user()->business_id)->latest()->paginate(20);
        return view('business::variations.index', compact('variations'));
    }

    public function tryonedigitalFilter(Request $request)
    {
        $search = $request->input('search');
        $variations = Variation::where('business_id', auth()->user()->business_id)->when($search, function ($q) use ($search) {
            $q->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('values', 'like', '%'.$search.'%');
            });
        })
            ->latest()
            ->paginate($request->per_page ?? 10);

        if ($request->ajax()) {
            return response()->json([
                'data' => view('business::variations.datas', compact('variations'))->render()
            ]);
        }

        return redirect(url()->previous());
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'values' => 'required|string',
        ]);

        $values = json_decode($request->values, true);

        if (!is_array($values) || !collect($values)->every(fn($v) => is_string($v))) {
            return response()->json(['message' => 'The values must be an array of strings.'], 422);
        }

        Variation::create([
            'business_id' => auth()->user()->business_id,
            'name' => $request->name,
            'values' => $values,
        ]);

        return response()->json([
            'message' => __('Variation saved successfully.'),
            'redirect' => route('business.variations.index')
        ]);
    }

    public function update(Request $request, $id)
    {
        $variation = Variation::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'values' => 'required|string',
        ]);

        $values = json_decode($request->values, true);

        if (!is_array($values) || !collect($values)->every(fn($v) => is_string($v))) {
            return response()->json(['message' => 'The values must be an array of strings.'], 422);
        }

        $variation->update([
            'business_id' => auth()->user()->business_id,
            'name' => $request->name,
            'values' => $values,
        ]);

        return response()->json([
            'message' => __('Variation updated successfully.'),
            'redirect' => route('business.variations.index')
        ]);
    }


    public function destroy($id)
    {
        Variation::findOrFail($id)->delete();

        return response()->json([
            'message' => __('Variation deleted successfully'),
            'redirect' => route('business.variations.index')
        ]);
    }

    public function deleteAll(Request $request)
    {
        $variations = Variation::whereIn('id', $request->ids)->get();

        Variation::whereIn('id', $request->ids)->delete();
        return response()->json([
            'message'   => __('Selected variations deleted successfully'),
            'redirect'  => route('business.variations.index')
        ]);
    }

    public function status(Request $request, $id)
    {
        $variation = Variation::findOrFail($id);
        $variation->update(['status' => $request->status]);
        return response()->json(['message' => __('Variation')]);
    }

}

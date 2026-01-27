<?php

namespace Modules\Business\App\Http\Controllers;

use App\Models\Shelf;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TryonedigitalShelfController extends Controller
{
    public function __construct()
    {
        $this->middleware('check.permission:shelfs.read')->only(['index']);
        $this->middleware('check.permission:shelfs.create')->only(['store']);
        $this->middleware('check.permission:shelfs.update')->only(['update', 'status']);
        $this->middleware('check.permission:shelfs.delete')->only(['destroy', 'deleteAll']);
    }

    public function index()
    {
        $shelves = Shelf::where('business_id', auth()->user()->business_id)->latest()->paginate(10);
        return view('business::shelves.index', compact('shelves'));
    }

    public function tryonedigitalFilter(Request $request)
    {
        $search = $request->search;

        $shelves = Shelf::where('business_id', auth()->user()->business_id)
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                });
            })
            ->latest()
            ->paginate($request->per_page ?? 10);

        if ($request->ajax()) {
            return response()->json([
                'data' => view('business::shelves.datas', compact('shelves'))->render()
            ]);
        }

        return redirect(url()->previous());
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Shelf::create($request->except('business_id') + [
            'business_id' => auth()->user()->business_id
        ]);

        return response()->json([
            'message' => 'Shelf created cuccessfully.',
            'redirect' => route('business.shelfs.index'),
        ]);
    }

    public function update(Request $request, Shelf $shelf)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $shelf->update($request->except('business_id') + [
            'business_id' => auth()->user()->business_id
        ]);

        return response()->json([
            'message' => 'Shelf updated successfully.',
            'redirect' => route('business.shelfs.index'),
        ]);
    }

    public function destroy(Shelf $shelf)
    {
        $shelf->delete();

        return response()->json([
            'message' => 'Shelf deleted successfully',
            'redirect' => route('business.shelfs.index')
        ]);
    }

    public function status(Request $request, $id)
    {
        $shelf = Shelf::findOrFail($id);
        $shelf->update(['status' => $request->status]);
        return response()->json(['message' => 'Shelf']);
    }

    public function deleteAll(Request $request)
    {
        $shelf = Shelf::whereIn('id', $request->input('ids'));
        $shelf->delete();

        return response()->json([
            'message' => __('Shelf deleted successfully.'),
            'redirect' => route('business.shelfs.index')
        ]);
    }
}

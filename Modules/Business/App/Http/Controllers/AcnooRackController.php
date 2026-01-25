<?php

namespace Modules\Business\App\Http\Controllers;

use App\Models\Rack;
use App\Models\Shelf;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AcnooRackController extends Controller
{
    public function __construct()
    {
        $this->middleware('check.permission:racks.create')->only(['store']);
        $this->middleware('check.permission:racks.read')->only(['index']);
        $this->middleware('check.permission:racks.update')->only(['update', 'status']);
        $this->middleware('check.permission:racks.delete')->only(['destroy', 'deleteAll']);
    }

    public function index()
    {
        $racks = Rack::with('shelves:id,name')->where('business_id', auth()->user()->business_id)->latest()->paginate(10);
        $shelves = Shelf::whereStatus(1)->where('business_id', auth()->user()->business_id)->latest()->get();
        return view('business::racks.index', compact('racks', 'shelves'));
    }

    public function acnooFilter(Request $request)
    {
        $search = $request->search;

        $racks = Rack::with('shelves:id,name')
            ->where('business_id', auth()->user()->business_id)
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhereHas('shelves', function ($q3) use ($search) {
                            $q3->where('name', 'like', '%' . $search . '%');
                        });
                });
            })
            ->latest()
            ->paginate($request->per_page ?? 10);

        if ($request->ajax()) {
            return response()->json([
                'data' => view('business::racks.datas', compact('racks'))->render()
            ]);
        }

        return redirect(url()->previous());
    }


    public function store(Request $request)
    {
        $request->validate([
            'shelf_id'     => 'required|array',
            'shelf_id.*'   => 'exists:shelves,id',
            'name'         => 'required|string|max:255',
            'status'       => 'nullable|boolean',
        ]);


        $rack = Rack::create($request->except('business_id') + [
            'business_id' => auth()->user()->business_id
        ]);

        $rack->shelves()->sync($request->shelf_id);

        return response()->json([
            'message' => 'Rack created cuccessfully.',
            'redirect' => route('business.racks.index'),
        ]);
    }

    public function update(Request $request, Rack $rack)
    {
        $request->validate([
            'shelf_id'     => 'required|array',
            'shelf_id.*'   => 'exists:shelves,id',
            'name'         => 'required|string|max:255',
            'status'       => 'nullable|boolean',
        ]);

        $rack->update($request->except('business_id') + [
            'business_id' => auth()->user()->business_id
        ]);

        $rack->shelves()->sync($request->shelf_id);

        return response()->json([
            'message' => 'Rack updated successfully.',
            'redirect' => route('business.racks.index'),
        ]);
    }

    public function destroy(Rack $rack)
    {
        $rack->delete();

        return response()->json([
            'message' => 'Rack deleted successfully',
            'redirect' => route('business.racks.index')
        ]);
    }

    public function status(Request $request, $id)
    {
        $rack = Rack::findOrFail($id);
        $rack->update(['status' => $request->status]);
        return response()->json(['message' => 'Rack']);
    }

    public function deleteAll(Request $request)
    {
        $rack = Rack::whereIn('id', $request->input('ids'));
        $rack->delete();

        return response()->json([
            'message' => __('Rack deleted successfully.'),
            'redirect' => route('business.racks.index')
        ]);
    }
}

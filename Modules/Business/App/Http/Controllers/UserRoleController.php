<?php

namespace Modules\Business\App\Http\Controllers;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;

class UserRoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('check.permission:roles.read')->only(['index']);
        $this->middleware('check.permission:roles.create')->only(['create', 'store']);
        $this->middleware('check.permission:roles.update')->only(['edit', 'update']);
        $this->middleware('check.permission:roles.delete')->only(['destroy', 'deleteAll']);
    }

    public function index()
    {
        $user = auth()->user();

        $users = User::with('branch:id,name')
            ->where('business_id', $user->business_id)
            ->when($user->role == 'staff', function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id);
            })
            ->when($user->branch_id || $user->active_branch_id, function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id ?? $user->active_branch_id);
            })
            ->where('id', '!=', auth()->id()) // remove auth user in listing
            ->where('role', 'staff')
            ->latest()
            ->paginate(10);

        $branches = Branch::where('business_id', $user->business_id)->latest()->get();

        return view('business::roles.index', compact('users', 'branches'));
    }

    public function acnooFilter(Request $request)
    {
        $user = auth()->user();

        $search = $request->input('search');
        $users = User::with('branch:id,name')
            ->where('business_id', $user->business_id)
            ->when($user->role == 'staff', function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id);
            })
            ->where('id', '!=', auth()->id())
            ->where('role', 'staff')
            ->when($request->branch_id, function ($q) use ($request) {
                $q->where('branch_id', $request->branch_id);
            })
            ->when($user->branch_id || $user->active_branch_id, function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id ?? $user->active_branch_id);
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhereHas('branch', function ($q) use ($search) {
                            $q->where('name', 'like', '%' . $search . '%');
                        });
                });
            })
            ->latest()
            ->paginate($request->per_page ?? 10);


        if ($request->ajax()) {
            return response()->json([
                'data' => view('business::roles.datas', compact('users'))->render()
            ]);
        }

        return redirect(url()->previous());
    }

    public function create()
    {
        $branches = Branch::withTrashed()->where('business_id', auth()->user()->business_id)->latest()->get();
        return view('business::roles.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:30',
            'password' => 'required|min:4|max:15',
            'email' => 'required|email|unique:users,email',
        ]);

        User::create([
            'role' => 'staff',
            'name' => $request->name,
            'email' => $request->email,
            'visibility' => $request->permissions,
            'password' => Hash::make($request->password),
            'business_id' => auth()->user()->business_id,
            'branch_id' => $request->branch_id ?? auth()->user()->branch_id ?? auth()->user()->active_branch_id,
        ]);

        return response()->json([
            'message' => __('User role created successfully'),
            'redirect' => route('business.roles.index')
        ]);
    }

    public function edit($id)
    {
        $user = User::where('business_id', auth()->user()->business_id)->findOrFail($id);
        $branches = Branch::withTrashed()->where('business_id', auth()->user()->business_id)->latest()->get();
        return view('business::roles.edit', compact('user', 'branches'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|max:30',
            'password' => 'nullable|min:4|max:15',
            'email' => 'required|email|unique:users,email,' . $id,
        ]);

        $user = User::where('business_id', auth()->user()->business_id)->findOrFail($id);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'visibility' => $request->permissions,
            'business_id' => auth()->user()->business_id,
            'password' => $request->password ? Hash::make($request->password) : $user->password,
            'branch_id' => $request->branch_id ?? auth()->user()->branch_id ?? auth()->user()->active_branch_id,
        ]);

        return response()->json([
            'message' => __('User role updated successfully'),
            'redirect' => route('business.roles.index')
        ]);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json([
            'message' => __('User role deleted successfully'),
            'redirect' => route('business.roles.index')
        ]);
    }

    public function deleteAll(Request $request)
    {
        User::whereIn('id', $request->ids)->delete();

        return response()->json([
            'message'   => __('Selected role deleted successfully'),
            'redirect'  => route('business.roles.index')
        ]);
    }
}

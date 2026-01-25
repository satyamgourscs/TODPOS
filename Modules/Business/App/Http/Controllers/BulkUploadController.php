<?php

namespace Modules\Business\App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\ProductImport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class BulkUploadController extends Controller
{
    public function __construct()
    {
        $this->middleware('check.permission:bulk-uploads.read')->only(['index']);
        $this->middleware('check.permission:bulk-uploads.create')->only(['store']);
    }

    public function index()
    {
        return view('business::bulk-uploads.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        $businessId = auth()->user()->business_id;

        Excel::import(new ProductImport($businessId), $request->file('file'));

        return response()->json([
            'message' => __('Bulk upload successfully.'),
            'redirect' => route('business.products.index')
        ]);
    }
}

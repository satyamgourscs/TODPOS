<?php

namespace Modules\Business\App\Exports;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ExportExpiredProductReport implements FromView
{
    public function view(): View
    {
        return view('business::reports.expired-products.excel-csv', [
            'expired_products' => Product::with('unit:id,unitName', 'brand:id,brandName', 'category:id,categoryName', 'stocks')
                ->withSum('stocks', 'productStock')
                ->where('business_id', auth()->user()->business_id)
                ->whereHas('stocks', function ($query) {
                    $query->whereDate('expire_date', '<', today())->where('productStock', '>', 0);
                })
                ->latest()
                ->get()
        ]);
    }
}

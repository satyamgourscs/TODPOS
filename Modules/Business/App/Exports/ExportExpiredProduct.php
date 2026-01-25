<?php

namespace Modules\Business\App\Exports;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ExportExpiredProduct implements FromView
{
    public function view(): View
    {
        $expired_products = Product::with('stocks', 'unit:id,unitName', 'brand:id,brandName', 'category:id,categoryName')
            ->where('business_id', auth()->user()->business_id)
            ->withSum('stocks as total_stock', 'productStock')
            ->whereHas('stocks', function ($query) {
                $query->whereDate('expire_date', '<', today())->where('productStock', '>', 0);
             })
            ->latest()->get();

        return view('business::expired-products.excel-csv', [
            'expired_products' => $expired_products
        ]);
    }
}

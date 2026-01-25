<?php

namespace Modules\Business\App\Exports;

use App\Models\Transfer;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ExportTransfer implements FromView
{
    public function view(): View
    {
        return view('warehouseaddon::transfers.excel-csv', [
            'transfers' => Transfer::with(['fromWarehouse:id,name', 'toWarehouse:id,name', 'toBranch:id,name', 'fromBranch:id,name', 'transferProducts'])->where('business_id', auth()->user()->business_id)->latest()->get()
        ]);
    }
}

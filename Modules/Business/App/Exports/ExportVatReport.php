<?php

namespace Modules\Business\App\Exports;

use App\Models\Vat;
use App\Models\Sale;
use App\Models\Purchase;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ExportVatReport implements FromView
{
    protected $sales, $purchases, $vats;

    public function __construct($sales, $purchases, $vats)
    {
        $this->sales = $sales;
        $this->purchases = $purchases;
        $this->vats = $vats;
    }

    public function view(): View
    {
        return view('business::reports.vats.excel', [
            'sales' => $this->sales,
            'purchases' => $this->purchases,
            'vats' => $this->vats,
        ]);
    }
}

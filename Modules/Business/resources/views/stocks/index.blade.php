@extends('layouts.business.master')

@section('title')
{{ __('Stock List') }}
@endsection

@php
    $modules = product_setting()->modules ?? [];
@endphp

@section('main_content')
<div class="erp-table-section">
    <div class="container-fluid">
        <div class="mb-4 d-flex loss-flex  gap-3 loss-profit-container d-print-none">
            <div class="d-flex align-items-center justify-content-center gap-3">
                <div class="profit-card p-3 text-white">
                    <p class="stat-title">{{ __('Total Quantity') }}</p>
                    <p class="stat-value">{{ $total_qty }}</p>
                </div>

                <div class="loss-card p-3 text-white">
                    <p class="stat-title">{{ __('Total Stock Value') }}</p>
                    <p class="stat-value">{{ currency_format($total_stock_value, currency : business_currency()) }}</p>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-bodys">
                <div class="table-header p-16 d-print-none">
                    <h4>{{ __('Stock List') }}</h4>
                </div>

                <div class="table-header justify-content-center border-0 d-none d-block d-print-block  text-center">
                    @include('business::print.header')
                    <h4 class="mt-2">{{ __('Stock List') }}</h4>
                </div>

                <div class="table-top-form p-16">
                    <form action="{{ route('business.stocks.filter', ['alert_qty' => request('alert_qty')]) }}" method="post" class="filter-form" table="#stock-data">
                        @csrf
                        <div class="table-top-left d-flex gap-3">
                            <div class="gpt-up-down-arrow position-relative d-print-none">
                                <select name="per_page" class="form-control">
                                    <option value="10">{{__('Show- 10')}}</option>
                                    <option value="25">{{__('Show- 25')}}</option>
                                    <option value="50">{{__('Show- 50')}}</option>
                                    <option value="100">{{__('Show- 100')}}</option>
                                </select>
                                <span></span>
                            </div>
                            <div class="table-search position-relative d-print-none">
                                <input type="text" name="search" class="form-control" placeholder="{{ __('Search...') }}">
                                <span class="position-absolute">
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M14.582 14.582L18.332 18.332" stroke="#4D4D4D" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M16.668 9.16797C16.668 5.02584 13.3101 1.66797 9.16797 1.66797C5.02584 1.66797 1.66797 5.02584 1.66797 9.16797C1.66797 13.3101 5.02584 16.668 9.16797 16.668C13.3101 16.668 16.668 13.3101 16.668 9.16797Z" stroke="#4D4D4D" stroke-width="1.25" stroke-linejoin="round"/>
                                        </svg>

                                </span>
                            </div>
                        </div>
                    </form>

                    <div class="table-top-btn-group d-print-none">
                        <ul>

                            <li>
                                <a href="{{ route('business.stocks.csv') }}">
                                    <img src="{{ asset('assets/images/logo/csv.svg') }}" alt="">
                                </a>
                            </li>

                            <li>
                                <a href="{{ route('business.stocks.excel') }}">
                                    <img src="{{ asset('assets/images/logo/excel.svg') }}" alt="">
                                </a>
                            </li>

                           <li>
                                @if (invoice_setting() == '3_inch_80mm' && moduleCheck('ThermalPrinterAddon'))
                                    <a onclick="print80mmInvoice()" class="print-window">
                                        <img src="{{ asset('assets/images/logo/printer.svg') }}" alt="">
                                    </a>
                                @else
                                    <a onclick="window.print()" class="print-window">
                                        <img src="{{ asset('assets/images/logo/printer.svg') }}" alt="">
                                    </a>
                                @endif
                            </li>
                        </ul>
                    </div>

                </div>
            </div>
            <div class="responsive-table m-0">
                <table class="table" id="datatable">
                    <thead>
                    <tr>
                        <th>{{ __('SL') }}.</th>
                        <th class="text-start">{{ __('Product') }}</th>
                        <th class="text-start">{{ __('Code') }}</th>
                        <th class="text-start">{{ __('Category') }}</th>
                        @usercan('stocks.price')
                        <th class="text-start">{{ __('Cost') }}</th>
                        @endusercan
                        <th class="text-start">{{ __('Qty') }}</th>
                        <th class="text-center">{{ __('Sale') }}</th>
                        <th class="text-end">{{ __('Stock Value') }}</th>
                    </tr>
                    </thead>
                    <tbody id="stock-data">
                        @include('business::stocks.datas')
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $products->links('vendor.pagination.bootstrap-5') }}
            </div>
        </div>
    </div>
    <div class="8mm-stock-list hidden" id="invoice-80mm" >
        <div class="invoice-container-sm">
            <div class="invoice-content invoice-content-size">
                 <div class="invoice-logo">
                        <img src="{{ asset(get_business_option('business-settings')['invoice_logo'] ?? 'assets/images/icons/default.svg') ?? '' }}"
                            alt="Logo">
                    </div>
                    <div class="mt-2">
                        <h4 class="company-name">{{ auth()->user()->business->companyName ?? 'Pos Pro' }}</h4>
                        <div class="company-info">
                            <p> {{__('Address')}} : {{ auth()->user()->business->address ?? '' }}</p>
                            <p> {{__('Mobile')}} : {{ auth()->user()->business->phoneNumber ?? '' }}</p>
                            <p> {{__('Email')}} : {{ auth()->user()->email ?? '' }}</p>
                            @if (!empty(auth()->user()->business->vat_name))
                            <p>{{ auth()->user()->business->vat_name }} : {{ auth()->user()->business->vat_no ?? '' }}</p>
                            @endif
                        </div>
                    </div>
                    <h3 class="invoice-title my-1">
                        {{__('Current Stock')}}
                    </h3>
                        <div class="invoice-info">
                            <div>
                                <p class="text-end date"> {{__('Date')}} : 07/13/23</p>
                                <p class="text-end time"> {{__('Time')}} : 02:00 am</p>
                                <p class="text-end"> {{__('Print By')}} : Admin</p>
                            </div>
                        </div>
                     <div>
                        <table class="ph-invoice-table" id="datatable">
                            <thead>
                            <tr>
                                <th>{{ __('SL') }}.</th>
                                <th class="text-start">{{ __('Product') }}</th>
                                <th class="text-start">{{ __('Qty') }}</th>
                                <th class="text-center">{{ __('Cost') }}</th>
                                <th class="stock-list-amount">{{ __('Stock Value') }}</th>
                            </tr>
                            </thead>
                            <tbody id="stock-data">
                                @foreach ($products as $product)
                                @php
                                    $total_stock = $product->stocks->sum('productStock');
                                    $first_stock_price = optional($product->stocks->first())->productPurchasePrice ?? 0;
                                    $total_value = $product->stocks->sum(function ($stock) {
                                        return $stock->productPurchasePrice * $stock->productStock;
                                    });
                                @endphp
                                <tr>
                                    <td>{{ ($products->currentPage() - 1) * $products->perPage() + $loop->iteration }}</td>

                                    <td class="text-start">
                                        @php
                                            $stocks = $product->stocks->map(function ($batch) {
                                                return [
                                                    'batch_no' => $batch->batch_no,
                                                    'expire_date' => $batch->expire_date ? formatted_date($batch->expire_date) : 'N/A',
                                                    'productStock' => $batch->productStock ?? 0,
                                                    'productSalePrice' => $batch->productSalePrice ?? 0,
                                                    'productDealerPrice' => $batch->productDealerPrice ?? 0,
                                                    'productPurchasePrice' => $batch->productPurchasePrice ?? 0,
                                                    'productWholeSalePrice' => $batch->productWholeSalePrice ?? 0,
                                                ];
                                            });
                                        @endphp
                                        <a href="javascript:void(0);" class="stock-view-data text-primary" data-stocks='@json($stocks)'>
                                            {{ $product->productName }}
                                        </a>
                                    </td>
                                    <td class="{{ $total_stock <= $product->alert_qty ? 'text-danger' : 'text-success' }} text-start">
                                        {{ $total_stock }}
                                    </td>
                                    <td class="text-start">{{ currency_format($first_stock_price, currency : business_currency()) }}
                                    <td class="stock-list-amount">{{ currency_format($total_value, currency : business_currency()) }}</td>
                                </tr>
                                @endforeach
                                <tr>
                                    <td colspan="4" class="stock-list-amount"><strong>{{ __('Total Stock Value:') }}</strong></td>
                                    <td class="stock-list-amount"><strong>{{ currency_format($total_stock_value, currency : business_currency()) }}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
</div>

@usercan('stocks.price')
<input type="hidden" id="canStockPrice" value="1">
@endusercan
<input type="hidden" id="canStockPrice" value="0">

<input type="hidden" id="canStockPrice" value="0">
<input type="hidden" id="show_expire_date" value="{{ is_module_enabled($modules, 'show_expire_date') ? 1 : 0 }}">
<input type="hidden" id="warehouse_module_check" value="{{ moduleCheck('WarehouseAddon') ? 1 : 0 }}">
<input type="hidden" id="show_weight" value="{{ is_module_enabled($modules, 'show_weight') ? 1 : 0 }}">
<input type="hidden" id="show_warehouse" value="{{ is_module_enabled($modules, 'show_warehouse') ? 1 : 0 }}">
<input type="hidden" id="show_rack" value="{{ is_module_enabled($modules, 'show_rack') ? 1 : 0 }}">
<input type="hidden" id="show_shelf" value="{{ is_module_enabled($modules, 'show_shelf') ? 1 : 0 }}">

@endsection

@push('modal')
    @include('business::stocks.stock-modal')
@endpush

@push('js')

<script>
    document.addEventListener("DOMContentLoaded", function () {
        window.print80mmInvoice = function () {
            const invoiceDiv = document.getElementById('invoice-80mm');

            if (!invoiceDiv) {
                alert("Invoice content not found!");
                return;
            }

            const content = invoiceDiv.innerHTML;

            const printWindow = window.open('', '', 'width=900,height=650');
            printWindow.document.open();
            printWindow.document.write('<html><head><title>Print Invoice</title>');
            printWindow.document.write('<link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ time() }}">');
            // printWindow.document.write('<link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">');
            printWindow.document.write('<style>');
            printWindow.document.write('body { font-family: Arial, sans-serif; font-size: 12px; }');
            printWindow.document.write('</style>');
            printWindow.document.write('</head><body>');
            printWindow.document.write(content);
            printWindow.document.write('</body></html>');
            printWindow.document.close();

            printWindow.onload = function () {
                printWindow.focus();
                printWindow.print();
                printWindow.close();
            };
        };
    });
</script>

@endpush

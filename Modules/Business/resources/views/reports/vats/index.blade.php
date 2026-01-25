@extends('layouts.business.master')

@section('title')
    {{ __('Sale Reports') }}
@endsection

@section('main_content')
<div class="min-vh-100">
    <div class="erp-table-section">
        <div class="container-fluid">
            <div class="card">
                <div class="card-bodys">
                    <div class="tab-table-container">

                        <div class="table-header p-16 d-print-none">
                            <h4>{{ __('Tax Report List') }}</h4>
                        </div>
                        <div class="table-header justify-content-center border-0 d-none d-block d-print-block text-center">
                            @include('business::print.header')
                            <h4 class="mt-2">{{ __('Tax Report List') }}</h4>
                        </div>

                        <div class="table-top-btn-group d-print-none">
                            <ul>
                                <input type="hidden" id="csvBaseUrl" value="{{ route('business.vat.reports.csv') }}">
                                <input type="hidden" id="excelBaseUrl" value="{{ route('business.vat.reports.excel') }}">

                                <li>
                                    <a id="csvExportLink" href="#">
                                        <img src="{{ asset('assets/images/logo/csv.svg') }}" alt="CSV">
                                    </a>
                                </li>
                                <li>
                                    <a id="excelExportLink" href="#">
                                        <img src="{{ asset('assets/images/logo/excel.svg') }}" alt="Excel">
                                    </a>
                                </li>

                                <li>
                                    <a onclick="window.print()" class="print-window">
                                        <img src="{{ asset('assets/images/logo/printer.svg') }}" alt="">
                                    </a>
                                </li>
                            </ul>

                        </div>

                        <div class="custom-tabs">
                            <button class="tab-item active" onclick="showTab('sales')">{{ __('Sales') }}</button>
                            <button class="tab-item" onclick="showTab('purchase')">{{ __('Purchases') }}</button>
                        </div>

                        <div id="sales" class="tab-content dashboard-tab active">
                            <div class="table-container">
                                <table class="table dashboard-table-content">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="text-start" scope="col">{{ __('Date') }}</th>
                                            <th class="text-center" scope="col">{{ __('Invoice') }}</th>
                                            <th class="text-center" scope="col">{{ __('Customer') }}</th>
                                            <th class="text-center" scope="col">{{ __('Total Amount') }}</th>
                                            <th class="text-center" scope="col">{{ __('Payment Method') }}</th>
                                            <th class="text-center" scope="col">{{ __('Discount') }}</th>
                                            @foreach ($vats as $vat)
                                                <th class="text-center">{{ $vat->name }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($sales as $sale)
                                            <tr>
                                                <td class="text-start">{{ formatted_date($sale->created_at) }}</td>
                                                <td class="text-center">{{ $sale->invoiceNumber }}</td>
                                                <td class="text-center">{{ $sale->party->name ?? '' }}</td>
                                                <td class="text-center">
                                                    {{ currency_format($sale->totalAmount, currency: business_currency()) }}
                                                </td>
                                                <td class="text-center">{{ $sale->payment_type->name ?? '' }}</td>
                                                <td class="text-center">
                                                    {{ currency_format($sale->discountAmount, currency: business_currency()) }}</td>
                                                @foreach ($vats as $vat)
                                                    <td class="text-center">
                                                        {{ $sale->vat_id == $vat->id ? currency_format($sale->vat_amount, currency: business_currency()) : '0' }}
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div id="purchase" class="tab-content dashboard-tab">
                            <div class="table-container">
                                <table class="table dashboard-table-content">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="text-start" scope="col">{{ __('Date') }}</th>
                                            <th class="text-center" scope="col">{{ __('Invoice') }}</th>
                                            <th class="text-center" scope="col">{{ __('Supplier') }}</th>
                                            <th class="text-center" scope="col">{{ __('Total Amount') }}</th>
                                            <th class="text-center" scope="col">{{ __('Payment Method') }}</th>
                                            <th class="text-center" scope="col">{{ __('Discount') }}</th>
                                            @foreach ($vats as $vat)
                                                <th class="text-center">{{ $vat->name }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($purchases as $purchase)
                                            <tr>
                                                <td class="text-start">{{ formatted_date($purchase->created_at) }}</td>
                                                <td class="text-center">{{ $purchase->invoiceNumber }}</td>
                                                <td class="text-center">{{ $purchase->party->name ?? '' }}</td>
                                                <td class="text-center">
                                                    {{ currency_format($purchase->totalAmount, currency: business_currency()) }}</td>
                                                <td class="text-center">{{ $purchase->payment_type->name ?? '' }}</td>
                                                <td class="text-center">
                                                    {{ currency_format($purchase->discountAmount, currency: business_currency()) }}</td>
                                                @foreach ($vats as $vat)
                                                    <td class="text-center">
                                                        {{ $purchase->vat_id == $vat->id ? currency_format($purchase->vat_amount, currency: business_currency()) : '0' }}
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

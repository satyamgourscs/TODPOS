@extends('layouts.business.master')

@section('title')
    {{ __('Sales Invoice Details') }}
@endsection

@section('main_content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-bodys">
                <div class="table-header p-16">
                    <h4>{{ __('Sales Invoice Details') }}</h4>
                    <a href="{{ route('business.sales-invoices.index') }}" class="btn btn-secondary">{{ __('Back') }}</a>
                </div>
                <div class="p-16">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>{{ __('Invoice Number') }}:</strong> {{ $sale->invoiceNumber }}</p>
                            <p><strong>{{ __('Date') }}:</strong> {{ formatted_date($sale->saleDate) }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>{{ __('Customer') }}:</strong> {{ $sale->party->name ?? __('Walk-in Customer') }}</p>
                            <p><strong>{{ __('Status') }}:</strong> 
                                @if($sale->isPaid)
                                    <span class="badge bg-success">{{ __('Paid') }}</span>
                                @else
                                    <span class="badge bg-warning">{{ __('Unpaid') }}</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ __('Product') }}</th>
                                <th>{{ __('Quantity') }}</th>
                                <th>{{ __('Price') }}</th>
                                <th>{{ __('Total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sale->details as $detail)
                                <tr>
                                    <td>{{ $detail->product->productName ?? __('N/A') }}</td>
                                    <td>{{ $detail->quantities }}</td>
                                    <td>{{ currency_format($detail->price) }}</td>
                                    <td>{{ currency_format($detail->total) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3">{{ __('Total Amount') }}</th>
                                <th>{{ currency_format($sale->totalAmount) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

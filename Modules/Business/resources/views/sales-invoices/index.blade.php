@extends('layouts.business.master')

@section('title')
    {{ __('Sales Invoices') }}
@endsection

@section('main_content')
    <div class="erp-table-section">
        <div class="container-fluid">
            <div class="card">
                <div class="card-bodys">
                    <div class="table-header p-16">
                        <h4>{{ __('Sales Invoices') }}</h4>
                        <a type="button" href="{{ route('business.sales-invoices.create') }}" class="add-order-btn rounded-2 active">
                            <i class="fas fa-plus-circle me-1"></i> {{ __('Create Sales Invoice') }}
                        </a>
                    </div>
                    <div class="table-top-form p-16-0">
                        <div class="d-flex align-items-center gap-3 flex-wrap margin-lr-16">
                            <form action="" method="get" class="report-filter-form">
                                <div class="table-top-left d-flex gap-3 flex-wrap">
                                    <div class="table-search position-relative">
                                        <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}" placeholder="{{ __('From Date') }}">
                                    </div>
                                    <div class="table-search position-relative">
                                        <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}" placeholder="{{ __('To Date') }}">
                                    </div>
                                    <button type="submit" class="theme-btn">{{ __('Filter') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="responsive-table mt-3">
                    <table class="table" id="datatable">
                        <thead>
                            <tr>
                                <th>{{ __('SL') }}</th>
                                <th>{{ __('Invoice Number') }}</th>
                                <th>{{ __('Customer') }}</th>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Total Amount') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody id="sales-invoices-data">
                            @forelse($sales as $sale)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $sale->invoiceNumber }}</td>
                                    <td>{{ $sale->party->name ?? __('Walk-in Customer') }}</td>
                                    <td>{{ formatted_date($sale->saleDate) }}</td>
                                    <td>{{ currency_format($sale->totalAmount) }}</td>
                                    <td>
                                        @if($sale->isPaid)
                                            <span class="badge bg-success">{{ __('Paid') }}</span>
                                        @else
                                            <span class="badge bg-warning">{{ __('Unpaid') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('business.sales-invoices.show', $sale->id) }}" class="btn btn-sm btn-primary">{{ __('View') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">{{ __('No sales invoices found.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

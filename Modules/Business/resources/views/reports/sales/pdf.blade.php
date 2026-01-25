@extends('layouts.business.pdf.pdf_layout')

@section('pdf_title')
<div class="table-header justify-content-center border-0 d-none d-block d-print-block  text-center">
    @include('business::print.header')
    <h4 class="mt-2">{{ __('Sales Report List') }}</h4>
</div>
@endsection

@section('pdf_content')
    <table class="styled-table">
        <thead>
            <tr>
                <th>{{ __('SL') }}.</th>
                <th class="text-start">{{ __('Invoice No') }}</th>
                <th class="text-start">{{ __('Party Name') }}</th>
                <th class="text-start">{{ __('Total Amount') }}</th>
                <th class="text-start">{{ __('Discount Amount') }}</th>
                <th class="text-start">{{ __('Paid Amount') }}</th>
                <th class="text-start">{{ __('Due Amount') }}</th>
                <th class="text-start">{{ __('Payment Type') }}</th>
                <th class="text-start">{{ __('Sale Date') }}</th>
            </tr>
        </thead>
        <tbody>
        @foreach($sales as $sale)
            <tr>
                <td>{{ $loop->index+1 }}</td>
                <td class="text-start">{{ $sale->invoiceNumber }}</td>
                <td class="text-start">{{ $sale->party?->name }}</td>
                <td class="text-start">{{ currency_format($sale->totalAmount, currency: business_currency()) }}</td>
                <td class="text-start">{{ currency_format($sale->discountAmount, currency: business_currency()) }}</td>
                <td class="text-start">{{ currency_format($sale->paidAmount, currency: business_currency()) }}</td>
                <td class="text-start">{{ currency_format($sale->dueAmount, currency: business_currency()) }}</td>
                <td class="text-start">{{ $sale->payment_type_id != null ? $sale->payment_type->name ?? '' : $sale->paymentType }}</td>
                <td class="text-start">{{ formatted_date($sale->saleDate) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection


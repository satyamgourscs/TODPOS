@extends('layouts.business.blank')

@section('title')
    {{ __('Invoice') }}
@endsection

@section('main_content')
    @if (invoice_setting() == '3_inch_80mm' && moduleCheck('ThermalPrinterAddon'))
        @include('thermalprinteraddon::sales.3_inch_80mm')
    @else
        @include('business::sales.invoices.a4-size')
    @endif
@endsection

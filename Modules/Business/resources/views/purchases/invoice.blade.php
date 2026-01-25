@extends('layouts.business.blank')

@section('title')
    {{ __('Invoice') }}
@endsection

@section('main_content')

@if (invoice_setting() == '3_inch_80mm' && moduleCheck('ThermalPrinterAddon'))
    @include('thermalprinteraddon::purchases.3_inch_80mm')
@else
    @include('business::purchases.invoices.a4-size')
@endif

@endsection

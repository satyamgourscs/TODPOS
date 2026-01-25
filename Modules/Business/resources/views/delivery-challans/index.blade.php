@extends('layouts.business.master')

@section('title')
    {{ __('Delivery Challans') }}
@endsection

@section('main_content')
    <div class="erp-table-section">
        <div class="container-fluid">
            <div class="card">
                <div class="card-bodys">
                    <div class="table-header p-16">
                        <h4>{{ __('Delivery Challans') }}</h4>
                        <a type="button" href="{{ route('business.delivery-challans.create') }}" class="add-order-btn rounded-2 active">
                            <i class="fas fa-plus-circle me-1"></i> {{ __('Create Delivery Challan') }}
                        </a>
                    </div>
                </div>
                <div class="responsive-table mt-3">
                    <table class="table" id="datatable">
                        <thead>
                            <tr>
                                <th>{{ __('SL') }}</th>
                                <th>{{ __('Challan Number') }}</th>
                                <th>{{ __('Customer') }}</th>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6" class="text-center">{{ __('No delivery challans found. Please create models and migrations first.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

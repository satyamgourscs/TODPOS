@extends('layouts.business.master')

@section('title')
    {{ __('Product List') }}
@endsection

@php
    $modules = product_setting()->modules ?? [];
@endphp

@section('main_content')
    <div class="erp-table-section">
        <div class="container-fluid">
            <div class="card">
                <div class="card-bodys">

                    <div class="table-header p-16">
                        <h4>{{ __('Product List') }}</h4>
                     </div>

                    <div class="table-top-form p-16-0">
                        <form action="{{ route('warehouse.product.filter') }}" method="post" class="filter-form"
                            table="#product-data">
                            @csrf
                            <div class="table-top-left d-flex gap-3 margin-l-16">

                                <div class="gpt-up-down-arrow position-relative">
                                    <select name="per_page" class="form-control">
                                        <option value="10">{{ __('Show- 10') }}</option>
                                        <option value="25">{{ __('Show- 25') }}</option>
                                        <option value="50">{{ __('Show- 50') }}</option>
                                        <option value="100">{{ __('Show- 100') }}</option>
                                    </select>
                                    <span></span>
                                </div>

                                <div class="table-search position-relative">
                                    <input type="text" name="search" class="form-control"
                                        placeholder="{{ __('Search...') }}">
                                    <span class="position-absolute">
                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M14.582 14.582L18.332 18.332" stroke="#4D4D4D" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M16.668 9.16797C16.668 5.02584 13.3101 1.66797 9.16797 1.66797C5.02584 1.66797 1.66797 5.02584 1.66797 9.16797C1.66797 13.3101 5.02584 16.668 9.16797 16.668C13.3101 16.668 16.668 13.3101 16.668 9.16797Z" stroke="#4D4D4D" stroke-width="1.25" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="responsive-table m-0">
                    <table class="table" id="datatable">
                        <thead>
                            <tr>
                                <th> {{ __('SL') }}. </th>
                                <th> {{ __('Image') }} </th>
                                <th> {{ __('Product Name') }} </th>
                                <th class="d-print-none"> {{ __('Code') }} </th>
                                <th> {{ __('Brand') }} </th>
                                <th> {{ __('Category') }} </th>
                                <th> {{ __('Warehouse') }} </th>
                                <th> {{ __('Unit') }} </th>
                                <th> {{ __('Purchase price') }}</th>
                                <th> {{ __('Sale price') }}</th>
                                <th> {{ __('Stock') }}</th>
                                <th> {{ __('Rack') }}</th>
                                <th> {{ __('Shelf') }}</th>
                            </tr>
                        </thead>
                        <tbody id="product-data">
                            @include('warehouseaddon::warehouse.products.datas')
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $products->links('vendor.pagination.bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>

    @usercan('stocks.price')
    <input type="hidden" id="canStockPrice" value="1">
    @endusercan
    <input type="hidden" id="canStockPrice" value="0">
    <input type="hidden" id="show_expire_date" value="{{ is_module_enabled($modules, 'show_expire_date') ? 1 : 0 }}">
    <input type="hidden" id="warehouse_module_check" value="{{ moduleCheck('WarehouseAddon') ? 1 : 0 }}">
    <input type="hidden" id="show_weight" value="{{ is_module_enabled($modules, 'show_weight') ? 1 : 0 }}">
    <input type="hidden" id="show_warehouse" value="{{ is_module_enabled($modules, 'show_warehouse') ? 1 : 0 }}">
    <input type="hidden" id="show_rack" value="{{ is_module_enabled($modules, 'show_rack') ? 1 : 0 }}">
    <input type="hidden" id="show_shelf" value="{{ is_module_enabled($modules, 'show_shelf') ? 1 : 0 }}">


@endsection

@push('modal')
    @include('warehouseaddon::warehouse.products.stock-modal')
@endpush

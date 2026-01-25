@extends('layouts.business.master')

@section('title')
    {{ __('Add Stock') }}
@endsection

@php
    $modules = product_setting()->modules ?? [];
@endphp

@section('main_content')
    <div class="erp-table-section">
        <div class="container-fluid">
            <div class="card border-0">
                <div class="card-bodys">
                    <div class="table-header p-16">
                        <h4>{{ __('Add Stock') }}</h4>
                        <a href="{{ route('business.products.index') }}" class="add-order-btn rounded-2 {{ Route::is('business.products.create') ? 'active' : '' }}"><i class="far fa-list" aria-hidden="true"></i> {{ __('Product List') }}</a>
                    </div>
                    <div class="order-form-section p-16">
                        <form action="{{ route('business.products.create-stock', $product->id) }}" method="POST" class="ajaxform_instant_reload">
                            @csrf
                            <input type="hidden" id="profit_option" value="{{ $profit_option }}">
                            <div class="add-suplier-modal-wrapper d-block">
                                <div class="row">
                                    @if (is_module_enabled($modules, 'show_product_stock'))
                                        <div class="col-lg-6 mb-2">
                                            <label>{{ __('Stock') }}</label>
                                            <input type="number" name="productStock" class="form-control" placeholder="{{ __('Enter stock qty') }}" required>
                                        </div>
                                    @endif
                                    @if (is_module_enabled($modules, 'show_batch_no'))
                                        <div class="col-lg-6 mb-2 ">
                                            <label>{{ __('Batch No') }}</label>
                                            <input type="hidden" name="batch_no" id="selected_batch_no" value="">
                                            <div class="custom-dropdown" id="myDropdown">
                                                <div class="dropdown-selected" id="selectedOption">
                                                    <span>{{ __('Select Batch') }}</span>
                                                    <span class="dropdown-arrow" id="dropdownArrow">
                                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                                             xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M10.0001 13.9585C9.84014 13.9585 9.68012 13.8977 9.55845 13.7752L3.72512 7.94183C3.48095 7.69767 3.48095 7.3018 3.72512 7.05764C3.96928 6.81347 4.36515 6.81347 4.60931 7.05764L10.001 12.4493L15.3926 7.05764C15.6368 6.81347 16.0326 6.81347 16.2768 7.05764C16.521 7.3018 16.521 7.69767 16.2768 7.94183L10.4435 13.7752C10.3201 13.8977 10.1601 13.9585 10.0001 13.9585Z" fill="#7B7C84"/>
                                                        </svg>
                                                    </span>
                                                </div>
                                                <div class="dropdown-options" id="dropdownOptions">
                                                    @foreach ($product->stocks as $stock)
                                                        <div class="dropdown-option" data-batch-no="{{ $stock->batch_no }}">
                                                            {{ __('Batch') }}: {{ $stock->batch_no ?? 'N/A' }},
                                                            <span class="text-green">{{ __('In Stock') }}: {{ $stock->productStock }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if (is_module_enabled($modules, 'show_expire_date'))
                                        @if(isset($modules['expire_date_type']) && ($modules['expire_date_type'] == 'dmy' || is_null($modules['expire_date_type'])))
                                            <div class="col-lg-6 mb-2">
                                                <label>{{ __('Expire Date') }}</label>
                                                <input type="month" name="expire_date" value="{{ $modules['default_expired_date'] ?? '' }}" class="form-control">
                                            </div>
                                        @else
                                            <div class="col-lg-6 mb-2">
                                                <label>{{ __('Expire Date') }}</label>
                                                <input type="date" name="expire_date" value="{{ $modules['default_expired_date'] ?? '' }}" class="form-control">
                                            </div>
                                        @endif
                                    @endif

                                    @if (is_module_enabled($modules, 'show_model_no'))
                                        <div class="col-lg-6 mb-2">
                                            <label>{{ __('Model No') }}</label>
                                            <input type="text" name="model_no" class="form-control" placeholder="{{ __('Model No') }}">
                                        </div>
                                    @endif

                                    @if (is_module_enabled($modules, 'show_mfg_date'))
                                        @if(isset($modules['mfg_date_type']) && ($modules['mfg_date_type'] == 'dmy' || is_null($modules['mfg_date_type'])))
                                            <div class="col-lg-6 mb-2">
                                                <label>{{ __('Manufacturing Date') }}</label>
                                                <input type="month" name="mfg_date" value="{{ $modules['default_mfg_date'] ?? '' }}" class="form-control">
                                            </div>
                                        @else
                                            <div class="col-lg-6 mb-2">
                                                <label>{{ __('Manufacturing Date') }}</label>
                                                <input type="date" name="mfg_date" value="{{ $modules['default_mfg_date'] ?? '' }}" class="form-control">
                                            </div>
                                        @endif
                                    @endif
                                    @if (is_module_enabled($modules, 'show_vat_id'))
                                        <div class="col-lg-6 mb-2">
                                            <label>{{ __('Select Vat') }}</label>
                                            <div class="gpt-up-down-arrow position-relative">
                                                <select id="vat_id" name="vat_id" class="form-control table-select w-100">
                                                    <option value="">{{ __('Select vat') }}</option>
                                                    @foreach ($vats as $vat)
                                                        <option value="{{ $vat->id }}"
                                                                data-vat_rate="{{ $vat->rate }}" @selected($product->vat_id ==  $vat->id)>
                                                            {{ $vat->name }} ({{ $vat->rate }}%)
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <span></span>
                                            </div>
                                        </div>
                                    @endif

                                    @if (is_module_enabled($modules, 'show_vat_type'))
                                        <div class="col-lg-6 mb-2">
                                            <label>{{ __('Vat Type') }}</label>
                                            <div class="gpt-up-down-arrow position-relative">
                                                <select id="vat_type" name="vat_type" class="form-control table-select w-100">
                                                    <option value="exclusive" @selected($product->vat_type == 'exclusive')>{{ __('Exclusive') }}</option>
                                                    <option value="inclusive" @selected($product->vat_type == 'inclusive')>{{ __('Inclusive') }}</option>
                                                </select>
                                                <span></span>
                                            </div>
                                        </div>
                                    @endif
                                    @usercan('products.price')
                                    @if (is_module_enabled($modules, 'show_exclusive_price'))
                                        <div class="col-lg-6 mb-2">
                                            <label>{{ __('Purchase Price Exclusive') }}</label>
                                            <input type="number" name="exclusive_price" value="{{ $product->vat_type == 'exclusive' ? $product->productPurchasePrice : $product->productPurchasePrice -  $product->vat_amount}}" id="exclusive_price" required class="form-control" placeholder="{{ __('Enter purchase price') }}">
                                        </div>
                                    @endif

                                    @if (is_module_enabled($modules, 'show_inclusive_price'))
                                        <div class="col-lg-6 mb-2">
                                            <label>{{ __('Purchase Price Inclusive') }}</label>
                                            <input type="number" name="inclusive_price" id="inclusive_price" value="{{ $product->vat_type == 'inclusive' ? $product->productPurchasePrice : $product->productPurchasePrice +  $product->vat_amount}}" required class="form-control" placeholder="{{ __('Enter purchase price') }}">
                                        </div>
                                    @endif

                                     @if (is_module_enabled($modules, 'show_profit_percent'))
                                        <div class="col-lg-6 mb-2">
                                            <label>{{ __('Profit Percentage (%)') }}</label>
                                            <input type="number" id="profit_percent" name="profit_percent" value="{{ $product->profit_percent }}" required class="form-control" placeholder="{{ __('Enter profit percent') }}">
                                        </div>
                                    @endif
                                    @endusercan
                                    @if (is_module_enabled($modules, 'show_product_sale_price'))
                                        <div class="col-lg-6 mb-2">
                                            <label>{{ __('MRP') }}</label>
                                            <input type="number" name="productSalePrice" value="{{ $product->productSalePrice }}" id="mrp_price" required class="form-control" placeholder="{{ __('Enter sale price') }}">
                                        </div>
                                    @endif

                                    @if (is_module_enabled($modules, 'show_product_wholesale_price'))
                                        <div class="col-lg-6 mb-2">
                                            <label>{{ __('Wholesale Price') }}</label>
                                            <input type="number" value="{{ $product->productWholeSalePrice }}" name="productWholeSalePrice" class="form-control" placeholder="{{ __('Enter wholesale price') }}">
                                        </div>
                                    @endif

                                    @if (is_module_enabled($modules, 'show_product_dealer_price'))
                                        <div class="col-lg-6 mb-2">
                                            <label>{{ __('Dealer Price') }}</label>
                                            <input type="number" value="{{ $product->productDealerPrice }}" name="productDealerPrice" class="form-control" placeholder="{{ __('Enter dealer price') }}">
                                        </div>
                                    @endif

                                    <div class="col-lg-12">
                                        <div class="button-group text-center mt-5">
                                            <a href="{{ route('business.products.index') }}"
                                               class="theme-btn border-btn m-2">{{ __('Cancel') }}</a>
                                               @usercan('products.update')
                                               <button class="theme-btn m-2 submit-btn">{{ __('Save') }}</button>
                                               @endusercan
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@push('js')
    <script src="{{ asset('assets/js/custom/product.js') }}"></script>
@endpush

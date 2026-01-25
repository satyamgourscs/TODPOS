@extends('layouts.business.master')

@section('title')
    {{ __('Create Transfer') }}
@endsection

@section('main_content')
    <div class="erp-table-section">
        <div class="container-fluid">
            <div class="card border-0">
                <div class="card-bodys ">
                    <div class="table-header p-16">
                        <h4>{{ __('Add New Transfer') }}</h4>
                    </div>
                    <div class="order-form-section p-16">
                        <form action="{{ route('business.transfers.store') }}" method="POST"
                            class="ajaxform_instant_reload">
                            @csrf
                            <div class="add-suplier-modal-wrapper d-block">
                                <div class="row">
                                    <div class="col-lg-4 mb-2">
                                        <label>{{ __('Date') }}</label>
                                        <input type="date" name="transfer_date" value="{{ date('Y-m-d') }}" required class="form-control">
                                    </div>
                                    @if((moduleCheck('MultiBranchAddon') && multibranch_active()) && !auth()->user()->active_branch_id)
                                        <div class="col-lg-4 mb-2">
                                            <label>{{ __('From Branch') }}</label>
                                            <div class="gpt-up-down-arrow position-relative">
                                                <select name="from_branch_id" id="from_branch" class="form-control table-select w-100 role">
                                                    <option value="">{{ __('Select one') }}</option>
                                                    @foreach ($branches as $branch)
                                                        <option value="{{ $branch->id }}">{{ ucfirst($branch->name) }}</option>
                                                    @endforeach
                                                </select>
                                                <span></span>
                                            </div>
                                        </div>
                                    @endif
                                    @if(moduleCheck('MultiBranchAddon') && multibranch_active())
                                        <div class="col-lg-4 mb-2">
                                            <label>{{ __('To Branch') }}</label>
                                            <div class="gpt-up-down-arrow position-relative">
                                                <select name="to_branch_id" id="to_branch" class="form-control table-select w-100 role">
                                                    <option value="">{{ __('Select one') }}</option>
                                                    @foreach ($branches as $branch)
                                                        @if (!auth()->user()->active_branch || auth()->user()->active_branch->id != $branch->id)
                                                            <option value="{{ $branch->id }}">{{ ucfirst($branch->name) }}</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                                <span></span>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 mb-2"></div>
                                    @endif
                                    @if(moduleCheck('warehouseAddon'))
                                        <div class="col-lg-4 mb-2">
                                            <label>{{ __('From Warehouse') }}</label>
                                            <div class="gpt-up-down-arrow position-relative">
                                                <select name="from_warehouse_id" id="from_warehouse" class="form-control table-select w-100 role">
                                                    <option value="">{{ __('Select one') }}</option>
                                                </select>
                                                <span></span>
                                            </div>
                                        </div>

                                        <div class="col-lg-4 mb-2">
                                            <label>{{ __('To Warehouse') }}</label>
                                            <div class="gpt-up-down-arrow position-relative">
                                                <select name="to_warehouse_id" id="to_warehouse" class="form-control table-select w-100 role">
                                                    <option value="">{{ __('Select one') }}</option>
                                                </select>
                                                <span></span>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="col-lg-8 mb-2"></div>
                                    <div class="col-lg-8 mb-2">
                                        <label>{{ __('Select Product') }}</label>
                                        <div class="d-flex align-items-center w-100">
                                            <div class="product-dropdown  transfer-product-select w-100" id="productDropdown">
                                                <div class="product-selected">
                                                    <span id="selectedValue">{{ __('Search Product') }}</span><span id="arrow">
                                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 6L8 10L12 6" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                    </span>
                                                </div>
                                                <div class="dropdown-search hidden" id="searchContainer">
                                                    <input type="text" id="productSearch" placeholder="Search product..."/>
                                                </div>
                                                <div class="product-dropdown-options" id="dropdownList">
                                                    {{-- fetch dynamically from js --}}
                                                </div>
                                            </div>
                                            <a href="{{ route('business.products.create') }}" class="btn btn-danger square-btn trasfer-square-btn d-flex justify-content-center align-items-center">
                                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M10 4.16797V15.8346" stroke="#C52127" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M4.16602 10H15.8327" stroke="#C52127" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            </a>
                                        </div>
                                    </div>

                                    <div class="col-lg-4 mb-2">
                                        <label>{{ __('Status') }}</label>
                                        <div class="gpt-up-down-arrow position-relative">
                                            <select name="status" class="form-control table-select w-100">
                                                <option value="pending">{{ __('Pending') }}</option>
                                                <option value="completed">{{ __('Completed') }}</option>
                                            </select>
                                            <span></span>
                                        </div>
                                    </div>

                                    <div id="product-table-container" class="table-responsive mt-4">
                                        <table class="table table-bordered text-center">
                                            <thead>
                                                <tr>
                                                    <th class="border p-2 table-background">{{ __('Image') }}</th>
                                                    <th class="border p-2 table-background">{{ __('Items') }}</th>
                                                    <th class="border p-2 table-background">{{ __('Code') }}</th>
                                                    <th class="border p-2 table-background">{{ __('Batch') }}</th>
                                                    <th class="border p-2 table-background">{{ __('Qty') }}</th>
                                                    <th class="border p-2 table-background">{{ __('Unit Price') }}</th>
                                                    <th class="border p-2 table-background">{{ __('Tax') }}</th>
                                                    <th class="border p-2 table-background">{{ __('Discount') }}</th>
                                                    <th class="border p-2 table-background">{{ __('Sub Total') }}</th>
                                                    <th class="border p-2 table-background">{{ __('Action') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody id="product-list">
                                                {{-- added via jquery --}}
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="col-lg-8 mb-2">
                                        <p class="note-label">{{ __('Transfer Note') }}</p>
                                        <textarea cols="10" rows="3" name="note" class="form-control transfer-note" placeholder="{{ __('Type note...') }}"></textarea>
                                    </div>

                                    <div class=" col-lg-4 sub-total-container">
                                        <div
                                            class="payment-container mb-3 amount-info-container inventory-amount-info-container transfer-amount-info">
                                            <div class="mb-2 d-flex align-items-center justify-content-between">
                                                <h6>{{ __('Sub Total') }}</h6>
                                                <h6 class="fw-bold" id="total_amount">{{ currency_format(0, currency: business_currency()) }}</h6>
                                            </div>

                                            <div class="mb-2 d-flex align-items-center justify-content-between">
                                                <h6>{{ __('Discount') }}</h6>
                                                <h6 class="fw-bold" id="discount_amount">{{ currency_format(0, currency: business_currency()) }}</h6>
                                            </div>

                                            <div class="mb-2 d-flex align-items-center justify-content-between">
                                                <h6>{{ __('Tax') }}</h6>
                                                <h6 class="fw-bold" id="tax_amount">{{ currency_format(0, currency: business_currency()) }}</h6>
                                            </div>

                                            <div class="row ">
                                                <div class="col-6">
                                                    <h6 class="payment-title">{{ __('Shipping Charge') }}</h6>
                                                </div>
                                                <div class="col-6">
                                                    <input type="number" step="any" name="shipping_charge"id="shipping_amount" class="form-control right-start-input" placeholder="0">
                                                </div>
                                            </div>
                                            <div class=" d-flex align-items-center justify-content-between fw-bold">
                                                <div class="fw-bold">{{ __('Total Amount') }}</div>
                                                <h6 class='fw-bold' id="grand_total_amount">{{ currency_format(0, currency: business_currency()) }}</h6>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-12">
                                        <div class="button-group text-center mt-5">
                                            <button type="reset" class="theme-btn border-btn m-2">{{ __('Reset') }}</button>
                                            @usercan('transfers.create')
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

    <input type="hidden" id="all-products" value="{{ route('business.products.all-product') }}">
    <input type="hidden" id="cart-store-url" value="{{ route('business.carts.store') }}">
    <input type="hidden" id="selectedProductValue" name="selectedProductValue">
    <input type="hidden" id="asset_base_url" value="{{ asset('') }}">
    <input type="hidden" id="get_stock_prices" value="{{ route('business.products.stocks-prices') }}">
    @if(moduleCheck('warehouseAddon'))
    <input type="hidden" id="branch_wise_warehouses" value="{{ route('warehouse.warehouses.branch') }}">
    @endif
    <input type="hidden" id="hasActiveBranch" value="{{ auth()->user()->active_branch ? 1 : 0 }}">

@endsection

@push('js')
    <script src="{{ asset('assets/js/custom/transfer.js') }}"></script>
@endpush

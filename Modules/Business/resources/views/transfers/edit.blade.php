@extends('layouts.business.master')

@section('title')
    {{ __('Edit Transfer') }}
@endsection

@section('main_content')
    <div class="erp-table-section">
        <div class="container-fluid">
            <div class="card border-0">
                <div class="card-bodys">
                    <div class="table-header p-16">
                        <h4>{{ __('Edit Transfer') }}</h4>
                        @usercan('transfers.read')
                        <a href="{{ route('business.transfers.index') }}" class="add-order-btn rounded-2">
                            <i class="far fa-list"></i> {{ __('Transfer List') }}
                        </a>
                        @endusercan
                    </div>
                    <div class="order-form-section p-16">
                        <form action="{{ route('business.transfers.update', $transfer->id) }}" method="POST"
                              class="ajaxform_instant_reload">
                            @csrf
                            @method('PUT')
                            <div class="add-suplier-modal-wrapper d-block">
                                <div class="row">

                                    <div class="col-lg-4 mb-2">
                                        <label>{{ __('Date') }}</label>
                                        <input type="date" name="transfer_date" value="{{ $transfer->transfer_date }}" required class="form-control">
                                    </div>
                                    @if((moduleCheck('MultiBranchAddon') && multibranch_active()) && !auth()->user()->active_branch_id)
                                        <div class="col-lg-4 mb-2">
                                            <label>{{ __('From Branch') }}</label>
                                            <div class="gpt-up-down-arrow position-relative">
                                                <select name="from_branch_id" id="from_branch" class="form-control table-select w-100 role">
                                                    <option value=""> {{ __('Select one') }}</option>
                                                    @foreach ($branches as $branch)
                                                        <option value="{{ $branch->id }}" @selected($transfer->from_branch_id == $branch->id)> {{ ucfirst($branch->name) }}</option>
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
                                                <select name="to_branch_id" class="form-control table-select w-100 role">
                                                    <option value=""> {{ __('Select one') }}</option>
                                                    @foreach ($branches as $branch)
                                                        <option value="{{ $branch->id }}" @selected($transfer->to_branch_id == $branch->id)> {{ ucfirst($branch->name) }}</option>
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
                                                <option value=""> {{ __('Select one') }}</option>
                                                @foreach ($warehouses as $warehouse)
                                                    <option value="{{ $warehouse->id }}" @selected($transfer->from_warehouse_id == $warehouse->id)> {{ ucfirst($warehouse->name) }}</option>
                                                @endforeach
                                            </select>
                                            <span></span>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 mb-2">
                                        <label>{{ __('To Warehouse') }}</label>
                                        <div class="gpt-up-down-arrow position-relative">
                                            <select name="to_warehouse_id" class="form-control table-select w-100 role">
                                                <option value=""> {{ __('Select one') }}</option>
                                                @foreach ($warehouses as $warehouse)
                                                    <option value="{{ $warehouse->id }}" @selected($transfer->to_warehouse_id == $warehouse->id)> {{ ucfirst($warehouse->name) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <span></span>
                                        </div>
                                    </div>
                                    @endif
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
                                            @if($transfer->status === 'cancelled')
                                                <input type="text" class="form-control" value="{{ __('Cancel') }}" disabled>
                                            @else
                                                <select name="status" class="form-control table-select w-100">
                                                    @if($transfer->status === 'pending')
                                                        <option value="pending" @selected($transfer->status == 'pending')>{{ __('Pending') }}</option>
                                                        <option value="completed" @selected($transfer->status == 'completed')>{{ __('Completed') }}</option>
                                                        <option value="cancelled" @selected($transfer->status == 'cancelled')>{{ __('Cancel') }}</option>
                                                    @elseif($transfer->status === 'completed')
                                                        <option value="completed" selected>{{ __('Completed') }}</option>
                                                    @endif
                                                </select>
                                            @endif
                                            <span></span>
                                        </div>
                                    </div>

                                    {{--Cart list --}}
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
                                            @foreach($transfer->transferProducts as $tp)
                                                <tr id="product-row-{{ $tp->stock_id }}">
                                                    <td>
                                                        <img src="{{ asset($tp->stock->product->productPicture ?? 'assets/images/products/box.svg') }}" width="40">
                                                    </td>
                                                    <td>{{ $tp->stock->product->productName ?? '' }}</td>
                                                    <td>{{ $tp->stock->product->productCode ?? '' }}</td>
                                                    <td>{{ $tp->stock->batch_no ?? '' }}</td>
                                                    <td>
                                                        <input type="number" name="products[{{ $tp->stock_id }}][quantity]" value="{{ $tp->quantity }}" class="dynamic-qty form-control text-center">
                                                    </td>
                                                    <td>
                                                        <input type="number" name="products[{{ $tp->stock_id }}][unit_price]" value="{{ $tp->unit_price }}" class="unit-price form-control text-center">
                                                    </td>
                                                    <td>
                                                        <input type="number" name="products[{{ $tp->stock_id }}][tax]" value="{{ $tp->tax }}" class="tax form-control text-center">
                                                    </td>
                                                    <td>
                                                        <input type="number" name="products[{{ $tp->stock_id }}][discount]" value="{{ $tp->discount }}" class="discount form-control text-center">
                                                    </td>
                                                    <td class="sub-total text-center">
                                                        {{ number_format(($tp->quantity * $tp->unit_price) + $tp->tax - $tp->discount, 2) }}
                                                    </td>
                                                    <td>
                                                        <button type="button" class="x-btn remove-btn" data-id="{{ $tp->stock_id }}">
                                                            <svg width="25" height="24" viewBox="0 0 25 24" fill="none">
                                                                <path d="M18.5 6L6.5 18" stroke="#E13F3D" stroke-width="2"
                                                                      stroke-linecap="round" stroke-linejoin="round"/>
                                                                <path d="M6.5 6L18.5 18" stroke="#E13F3D" stroke-width="2"
                                                                      stroke-linecap="round" stroke-linejoin="round"/>
                                                            </svg>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="col-lg-8 mb-2">
                                        <p class="note-label">{{ __('Transfer Note') }}</p>
                                        <textarea cols="10" rows="3" name="note" class="form-control" placeholder="{{ __('Type note...') }}">{{ $transfer->note }}</textarea>
                                    </div>

                                    <div class=" col-lg-4 sub-total-container">
                                        <div
                                            class="payment-container mb-3 amount-info-container inventory-amount-info-container transfer-amount-info">
                                            <div class="mb-2 d-flex align-items-center justify-content-between">
                                                <h6>{{ __('Sub Total') }}</h6>
                                                <h6 class="fw-bold" id="total_amount">{{ currency_format($transfer->sub_total) }}</h6>
                                            </div>

                                            <div class="mb-2 d-flex align-items-center justify-content-between">
                                                <h6>{{ __('Discount') }}</h6>
                                                <h6 class="fw-bold" id="discount_amount">{{ currency_format($transfer->total_discount) }}</h6>
                                            </div>

                                            <div class="mb-2 d-flex align-items-center justify-content-between">
                                                <h6>{{ __('Tax') }}</h6>
                                                <h6 class="fw-bold" id="tax_amount">{{ currency_format($transfer->total_tax) }}</h6>
                                            </div>

                                            <div class="row ">
                                                <div class="col-6">
                                                    <h6 class="payment-title">{{ __('Shipping Charge') }}</h6>
                                                </div>
                                                <div class="col-6">
                                                    <input type="number" step="any" name="shipping_charge" value="{{ $transfer->shipping_charge }}" id="shipping_amount" class="form-control right-start-input" placeholder="0">
                                                </div>
                                            </div>
                                            <div class=" d-flex align-items-center justify-content-between fw-bold">
                                                <div class="fw-bold">{{ __('Total Amount') }}</div>
                                                <h6 class='fw-bold' id="grand_total_amount">{{ currency_format($transfer->grand_total) }}</h6>
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
    <input type="hidden" id="asset_base_url" value="{{ url('/') }}">
    <input type="hidden" id="get_stock_prices" value="{{ route('business.products.stocks-prices') }}">
    <input type="hidden" id="hasActiveBranch" value="{{ auth()->user()->active_branch ? 1 : 0 }}">

@endsection

@push('js')
    <script src="{{ asset('assets/js/custom/transfer.js') }}"></script>
@endpush

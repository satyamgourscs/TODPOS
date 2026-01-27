@extends('layouts.business.master')

@section('title')
    {{ __('Create Sales Invoice') }}
@endsection

@section('main_content')
    <div class="container-fluid">
        <div class="card">
            <div class="table-header p-16">
                <h4>{{ __('Create Sales Invoice') }}</h4>
            </div>
            <div class="order-form-section p-16">
                <form action="{{ route('business.sales.store') }}" method="post" enctype="multipart/form-data"
                    class="ajaxform">
                    @csrf
                    <input type="hidden" name="type" value="inventory">
                    <div class="row mt-3">
                        <div class="col-lg-4">
                            <label>{{ __('Customer') }}</label>
                            <div class="input-group">
                                <select name="party_id" class="form-control inventory-customer-select" aria-label="Select Customer">
                                    <option value="">{{ __('Select Customer') }}</option>
                                    <option class="guest-option" value="guest">{{ __('Guest') }}</option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}" data-type="{{ $customer->type }}">
                                            {{ $customer->name }} ({{ $customer->type }}{{ $customer->due ? ' ' . currency_format($customer->due, currency:business_currency()) : '' }})
                                        </option>
                                    @endforeach
                                </select>
                                <a type="button" href="#customer-create-modal" data-bs-toggle="modal"
                                   class="btn btn-danger square-btn d-flex justify-content-center align-items-center">
                                    <img src="{{ asset('assets/images/icons/plus.svg') }}" alt=""></a>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <label>{{ __('Invoice No') }}.</label>
                            <input type="text" name="invoiceNumber" value="{{ $invoice_no }}" class="form-control"
                                placeholder="{{ __('Invoice no') }}." readonly>
                        </div>
                        <div class="col-lg-4">
                            <label>{{ __('Date') }}</label>
                            <input type="date" name="saleDate" class="form-control"
                                value="{{ now()->format('Y-m-d') }}">
                        </div>

                        <div class="col-lg-8 d-none guest_phone">
                            <label>{{ __('Phone Number') }} <span class="text-danger">*</span></label>
                            <input type="text" name="customer_phone" id="customer_phone" required pattern="[0-9]{10}" maxlength="10" minlength="10" class="form-control"
                                placeholder="{{ __('Enter Customer Phone Number (10 digits)') }}" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>
                        <div class="col-lg-8">
                            <label>{{ __('Select Product') }}</label>
                            <div class="product-dropdown" id="productDropdown">
                                <div class="product-selected">
                                    <span id="selectedValue">{{ __('Select Product') }}</span><span id="arrow">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M4 6L8 10L12 6" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                </div>

                                <div class="dropdown-search hidden" id="searchContainer">
                                    <input type="text" id="productSearch" placeholder="Search product..." />
                                </div>

                                <div class="product-dropdown-options" id="dropdownList">
                                    @foreach ($products as $product)
                                        @if (!empty($product->stocks) && $product->stocks->count() > 1)
                                            {{-- when multiple stock --}}
                                            <div class="product-option-item single-product {{ $product->id }}" data-product_id="{{ $product->id }}" data-default_price="{{ $product->productSalePrice }}" data-route="{{ route('business.carts.store') }}" data-product_name="{{ $product->productName }}" data-product_code="{{ $product->productCode }}" data-product_unit_id="{{ $product->unit_id }}" data-product_unit_name="{{ $product->unit->unitName ?? '' }}" data-purchase_price="{{ $product->productPurchasePrice }}" data-product_image="{{ $product->productPicture }}">
                                                <div class="product-left">
                                                    <img src="{{ asset($product->productPicture ?? 'assets/images/products/box.svg') }}" alt="">
                                                    <div class="product-text">
                                                        <div class="d-flex align-items-center justify-content-between w-100">
                                                            <div class="product-title">{{ $product->productName }}</div>
                                                            <p>Code : {{ $product->productCode }}</p>
                                                        </div>
                                                        @foreach ($product->stocks as $stock)
                                                            <div class="d-flex align-items-center justify-content-between w-100 multi-items add-batch-item"
                                                                 data-product_stock_id="{{ $stock->id }}"
                                                                 data-product_id="{{ $product->id }}"
                                                                 data-product_batch_no="{{ $stock->batch_no ?? '' }}"
                                                                 data-product_expire_date="{{ $stock->expire_date ?? '' }}"
                                                                 data-product_name="{{ $product->productName }}"
                                                                 data-product_code="{{ $product->productCode }}"
                                                                 data-default_price="{{ $stock->productSalePrice }}"
                                                                 data-product_unit_id="{{ $product->unit_id }}"
                                                                 data-product_unit_name="{{ $product->unit->unitName ?? '' }}"
                                                                 data-purchase_price="{{ $stock->productPurchasePrice }}"
                                                                 data-product_image="{{ $product->productPicture }}"
                                                                 data-route="{{ route('business.carts.store') }}">
                                                                <div class="product-des">
                                                                    {{ __('Batch') }}: {{ $stock->batch_no ?? 'N/A' }}
                                                                    {{ $product->color ? ', Color: ' . $product->color : '' }}
                                                                    <span class="product-in-stock">{{ __('In Stock') }}: {{ $stock->productStock }}</span>
                                                                </div>
                                                                <div class="product-price product_price">
                                                                    {{ currency_format($stock->productSalePrice, currency: business_currency()) }}
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            {{-- when single stock --}}
                                            @php $stock = $product->stocks->first(); @endphp
                                            <div class="product-option-item single-product {{ $product->id }} add-batch-item"
                                                 data-product_stock_id ="{{ $stock->id ?? '' }}"
                                                 data-product_id="{{ $product->id }}"
                                                 data-default_price="{{ $stock->productSalePrice }}"
                                                 data-product_name="{{ $product->productName }}"
                                                 data-product_code="{{ $product->productCode }}"
                                                 data-product_unit_id="{{ $product->unit_id }}"
                                                 data-product_unit_name="{{ $product->unit->unitName ?? '' }}"
                                                 data-purchase_price="{{ $stock->productPurchasePrice }}"
                                                 data-product_image="{{ $product->productPicture }}"
                                                 data-product_batch_no="{{ $stock->batch_no ?? '' }}"
                                                 data-product_expire_date="{{ $stock->expire_date ?? '' }}"
                                                 data-route="{{ route('business.carts.store') }}">
                                                <div class="product-left">
                                                    <img src="{{ asset($product->productPicture ?? 'assets/images/products/box.svg') }}" alt="">
                                                    <div class="product-text">
                                                        <div class="d-flex align-items-center justify-content-between w-100">
                                                            <div class="product-title">{{ $product->productName }}</div>
                                                            <p>{{ __('Code') }}: {{ $product->productCode }}</p>
                                                        </div>
                                                        <div class="d-flex align-items-center justify-content-between w-100">
                                                            <div class="product-des">
                                                                {{ __('Batch') }}: {{ $stock->batch_no ?? 'N/A' }}
                                                                {{ $product->color ? ', Color: ' . $product->color : '' }}
                                                                <span class="product-in-stock">{{ __('In Stock') }}: {{ $stock->productStock ?? 0 }}</span>
                                                            </div>
                                                            <div class="product-price product_price">
                                                                {{ currency_format($stock->productSalePrice ?? $product->productSalePrice, currency: business_currency()) }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>

                            </div>
                        </div>

                        <div class="col-lg-4">
                            <label>{{ __('Category') }}</label>
                            <div class="input-group">
                                <select name="category_id" id="categorySelect" class="form-control" aria-label="Select Category">
                                    <option value="">{{ __('All') }}</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->categoryName }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="table-responsive mt-4">
                            <table class="table table-bordered text-center">
                                <thead>
                                    <tr>
                                        <th class="border p-2 table-background">{{ __('Image') }}</th>
                                        <th class="border p-2 table-background">{{ __('Items') }}</th>
                                        <th class="border p-2 table-background">{{ __('Code') }}</th>
                                        <th class="border p-2 table-background">{{ __('Batch') }}</th>
                                        <th class="border p-2 table-background">{{ __('Unit') }}</th>
                                        <th class="border p-2 table-background">{{ __('Sale Price') }}</th>
                                        <th class="border p-2 table-background">{{ __('Qty') }}</th>
                                        <th class="border p-2 table-background">{{ __('Sub Total') }}</th>
                                        <th class="border p-2 table-background">{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="cart-list">
                                    @include('business::sales.cart-list')
                                </tbody>
                            </table>
                        </div>


                        <div class="col-sm-12 col-md-6 col-lg-6 mt-5">
                            <div class="amount-info-container inventory-amount-info-container">
                                <div class="row amount-container  align-items-center mb-2">
                                    <h6 class="payment-title">{{ __('Receive Amount') }}</h6>
                                    <input name="receive_amount" type="number" step="any" id="receive_amount"
                                        min="0" class="form-control" placeholder="0">
                                </div>
                                <div class="row amount-container  align-items-center mb-2">
                                    <h6 class="payment-title">{{ __('Change Amount') }}</h6>
                                    <input type="number" step="any" id="change_amount" class="form-control"
                                        placeholder="0" readonly>
                                </div>
                                <div class="row amount-container  align-items-center mb-2">
                                    <h6 class="payment-title">{{ __('Due Amount') }}</h6>
                                    <input type="number" step="any" id="due_amount" class="form-control"
                                        placeholder="0" readonly>
                                </div>
                                <div class="row amount-container  align-items-center mb-2">
                                    <h6 class="payment-title">{{ __('Payment Type') }}</h6>
                                    <select name="payment_type_id" class="form-select" id='form-ware'>
                                        @foreach ($payment_types as $type)
                                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="row amount-container  align-items-center mb-2">
                                    <h6 class="payment-title">{{ __('Note') }}</h6>
                                    <input type="text" name="note" class="form-control"
                                        placeholder="{{ __('Type note...') }}">
                                </div>
                            </div>

                        </div>
                        <div class="col-sm-12 col-md-6 col-lg-6 mt-5 sub-total-container">
                            <div class="payment-container mb-3 amount-info-container inventory-amount-info-container">
                                <div class="mb-2 d-flex align-items-center justify-content-between">
                                    <h6>{{ __('Sub Total') }}</h6>
                                    <h6 class="fw-bold" id="sub_total">
                                        {{ currency_format(0, currency: business_currency()) }}</h6>
                                </div>
                                <div class="row save-amount-container  align-items-center mb-2">
                                    <h6 class="payment-title col-6">{{ __('Gst') }}</h6>
                                    <div class="col-6 w-100 d-flex justify-content-between gap-2">
                                        <div class="d-flex d-flex align-items-center gap-2">
                                            <select name="vat_id" class="form-select vat_select" id='form-ware'>
                                                <option value="">{{ __('Select') }}</option>
                                                @foreach ($vats as $vat)
                                                    <option value="{{ $vat->id }}" data-rate="{{ $vat->rate }}">
                                                        {{ $vat->name }}
                                                        ({{ $vat->rate }}%)
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <input type="number" step="any" name="vat_amount" id="vat_amount"
                                            min="0" class="form-control right-start-input"
                                            placeholder="{{ __('0') }}" readonly>
                                    </div>
                                </div>
                                <div class="row save-amount-container  align-items-center mb-2">
                                    <h6 class="payment-title col-6">{{ __('Discount') }}</h6>
                                    <div class="col-6 w-100 d-flex justify-content-between gap-2">
                                        <div class="d-flex d-flex align-items-center gap-2">
                                            <select name="discount_type" class="form-select discount_type"
                                                id='form-ware'>
                                                <option value="flat">{{ __('Flat') }}
                                                    ({{ business_currency()->symbol }})</option>
                                                <option value="percent">{{ __('Percent (%)') }}</option>
                                            </select>
                                        </div>
                                        <input type="number" step="any" name="discountAmount" id="discount_amount"
                                            min="0" class="form-control right-start-input"
                                            placeholder="{{ __('0') }}">
                                    </div>
                                </div>
                                <div class="row save-amount-container  align-items-center mb-2">
                                    <h6 class="payment-title col-6">{{ __('Shipping Charge') }}</h6>
                                    <div class="col-12 ">
                                        <input type="number" step="any" name="shipping_charge" id="shipping_charge"
                                            class="form-control right-start-input" placeholder="0">
                                    </div>
                                </div>
                                <div class=" d-flex align-items-center justify-content-between fw-bold">
                                    <div class="fw-bold">{{ __('Total Amount') }}</div>
                                    <h6 class='fw-bold' id="total_amount">
                                        {{ currency_format(0, currency: business_currency()) }}</h6>
                                </div>
                                <div class="mb-2 d-flex align-items-center justify-content-between">
                                    <h6>{{ __('Rounding(+/-)') }}</h6>
                                    <h6 id="rounding_amount">
                                        {{ currency_format(0, currency: business_currency()) }}</h6>
                                </div>
                                <div class="mb-2 d-flex align-items-center justify-content-between">
                                    <h6 class="fw-bold">{{ __('Payable Amount') }}</h6>
                                    <h6 class="fw-bold" id="payable_amount">
                                        {{ currency_format(0, currency: business_currency()) }}</h6>
                                </div>

                            </div>

                        </div>

                        <div class="col-lg-12">
                            <div class="button-group text-center ">
                                <button data-route="{{ route('business.carts.remove-all') }}"
                                    class="theme-btn border-btn m-2">Cancel
                                </button>
                                @usercan('inventory.create')
                                <button class="theme-btn  m-2 submit-btn">Submit</button>
                                @endusercan
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @php
        $currency = business_currency();
        $rounding_amount_option = sale_rounding();
    @endphp
    {{-- Hidden input fields to store currency details --}}
    <input type="hidden" id="currency_symbol" value="{{ $currency->symbol }}">
    <input type="hidden" id="currency_position" value="{{ $currency->position }}">
    <input type="hidden" id="currency_code" value="{{ $currency->code }}">

    <input type="hidden" id="get_product" value="{{ route('business.products.prices') }}">
    <input type="hidden" value="{{ route('business.carts.index') }}" id="get-cart">
    <input type="hidden" value="{{ route('business.sales.cart-data') }}" id="get-cart-data">
    <input type="hidden" value="{{ route('business.carts.remove-all') }}" id="clear-cart">

    <input type="hidden" id="rounding_amount_option" value="{{ $rounding_amount_option }}">
    <input type="hidden" id="all-products" value="{{ route('business.products.all-product') }}">
    <input type="hidden" id="get-by-category" value="{{ route('business.products.get-by-category') }}">
    <input type="hidden" id="cart-store-url" value="{{ route('business.carts.store') }}">
    <input type="hidden" id="selectedProductValue" name="selectedProductValue">
    <input type="hidden" id="asset_base_url" value="{{ url('/') }}">
    <input type="hidden" id="get_stock_prices" value="{{ route('business.products.stocks-prices') }}">

@endsection

@push('modal')
    @include('business::sales.calculator')
    @include('business::sales.customer-create')
@endpush

@push('js')
    <script src="{{ asset('assets/js/custom/sale.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('assets/js/custom/math.min.js') }}"></script>
    <script src="{{ asset('assets/js/custom/calculator.js') }}"></script>
@endpush

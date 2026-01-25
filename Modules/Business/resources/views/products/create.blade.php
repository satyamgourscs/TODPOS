@extends('layouts.business.master')

@section('title')
    {{ __('Create Product') }}
@endsection

@php
    $modules = product_setting()->modules ?? [];
    $showSingle  = !isset($modules['show_product_type_single']) || $modules['show_product_type_single'];
    $showVariant = isset($modules['show_product_type_variant']) && $modules['show_product_type_variant'];
    $hasVisibleColumn = is_module_enabled($modules, 'show_batch_no') || is_module_enabled($modules, 'show_product_stock') || is_module_enabled($modules, 'show_exclusive_price') ||is_module_enabled($modules, 'show_inclusive_price') ||is_module_enabled($modules, 'show_profit_percent') ||is_module_enabled($modules, 'show_product_sale_price') ||is_module_enabled($modules, 'show_product_wholesale_price') ||is_module_enabled($modules, 'show_product_dealer_price') ||is_module_enabled($modules, 'show_mfg_date') ||is_module_enabled($modules, 'show_expire_date') ||is_module_enabled($modules, 'show_action');
    $defaultPermissions = [
        'show_batch_no',
        'show_product_stock',
        'show_exclusive_price',
        'show_inclusive_price',
        'show_profit_percent',
        'show_product_sale_price',
        'show_product_wholesale_price',
        'show_product_dealer_price',
        'show_mfg_date',
        'show_expire_date',
        'show_action',
    ];
@endphp

@section('main_content')
    <div class="erp-table-section">
        <div class="container-fluid">
            <form action="{{ route('business.products.store') }}" method="POST" class="ajaxform_instant_reload" enctype="multipart/form-data">
                @csrf
                <div class="row g-2">
                    <div class="col-lg-12">
                        <div class="card product-card border-0 ">
                            <div class="card-bodys ">
                                <div class="table-header p-16">
                                    <h4>{{ __('Add new Product') }}</h4>
                                <div class="d-flex align-items-center gap-3">
                                <a class="save-publish-btn" href="{{ route('business.bulk-uploads.index') }}">
                                    Bulk Upload
                                </a>
                                @usercan('products.create')
                                <button class="save-publish-btn submit-btn" href="">
                                    Save & Published
                                </button>
                                @endusercan
                                <div class="position-relative">
                                    <a href={{ route('business.manage-settings.index') }}#product class="product-settings">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                             <path d="M16.3083 4.38394C15.7173 4.38394 15.4217 4.38394 15.1525 4.28405C15.1151 4.27017 15.0783 4.25491 15.042 4.23828C14.781 4.11855 14.5721 3.90959 14.1541 3.49167C13.1922 2.52977 12.7113 2.04882 12.1195 2.00447C12.04 1.99851 11.96 1.99851 11.8805 2.00447C11.2887 2.04882 10.8077 2.52977 9.84585 3.49166C9.42793 3.90959 9.21897 4.11855 8.95797 4.23828C8.92172 4.25491 8.88486 4.27017 8.84747 4.28405C8.57825 4.38394 8.28273 4.38394 7.69171 4.38394H7.58269C6.07478 4.38394 5.32083 4.38394 4.85239 4.85239C4.38394 5.32083 4.38394 6.07478 4.38394 7.58269V7.69171C4.38394 8.28273 4.38394 8.57825 4.28405 8.84747C4.27017 8.88486 4.25491 8.92172 4.23828 8.95797C4.11855 9.21897 3.90959 9.42793 3.49166 9.84585C2.52977 10.8077 2.04882 11.2887 2.00447 11.8805C1.99851 11.96 1.99851 12.04 2.00447 12.1195C2.04882 12.7113 2.52977 13.1922 3.49166 14.1541C3.90959 14.5721 4.11855 14.781 4.23828 15.042C4.25491 15.0783 4.27017 15.1151 4.28405 15.1525C4.38394 15.4217 4.38394 15.7173 4.38394 16.3083V16.4173C4.38394 17.9252 4.38394 18.6792 4.85239 19.1476C5.32083 19.6161 6.07478 19.6161 7.58269 19.6161H7.69171C8.28273 19.6161 8.57825 19.6161 8.84747 19.716C8.88486 19.7298 8.92172 19.7451 8.95797 19.7617C9.21897 19.8815 9.42793 20.0904 9.84585 20.5083C10.8077 21.4702 11.2887 21.9512 11.8805 21.9955C11.96 22.0015 12.0399 22.0015 12.1195 21.9955C12.7113 21.9512 13.1922 21.4702 14.1541 20.5083C14.5721 20.0904 14.781 19.8815 15.042 19.7617C15.0783 19.7451 15.1151 19.7298 15.1525 19.716C15.4217 19.6161 15.7173 19.6161 16.3083 19.6161H16.4173C17.9252 19.6161 18.6792 19.6161 19.1476 19.1476C19.6161 18.6792 19.6161 17.9252 19.6161 16.4173V16.3083C19.6161 15.7173 19.6161 15.4217 19.716 15.1525C19.7298 15.1151 19.7451 15.0783 19.7617 15.042C19.8815 14.781 20.0904 14.5721 20.5083 14.1541C21.4702 13.1922 21.9512 12.7113 21.9955 12.1195C22.0015 12.0399 22.0015 11.96 21.9955 11.8805C21.9512 11.2887 21.4702 10.8077 20.5083 9.84585C20.0904 9.42793 19.8815 9.21897 19.7617 8.95797C19.7451 8.92172 19.7298 8.88486 19.716 8.84747C19.6161 8.57825 19.6161 8.28273 19.6161 7.69171V7.58269C19.6161 6.07478 19.6161 5.32083 19.1476 4.85239C18.6792 4.38394 17.9252 4.38394 16.4173 4.38394H16.3083Z" stroke="#4B5563" stroke-width="1.5"/>
                                            <path d="M15.5 12C15.5 13.933 13.933 15.5 12 15.5C10.067 15.5 8.5 13.933 8.5 12C8.5 10.067 10.067 8.5 12 8.5C13.933 8.5 15.5 10.067 15.5 12Z" stroke="#4B5563" stroke-width="1.5"/>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                                </div>
                                <div class="order-form-section p-16">

                                    <input type="hidden" id="profit_option" value="{{ $profit_option }}">
                                    <div class="add-suplier-modal-wrapper d-block">
                                        <div class="row">
                                            <div class="col-lg-4 mb-2">
                                                <label>{{ __('Product Name') }}</label>
                                                <input type="text" name="productName" required class="form-control" placeholder="{{ __('Enter Product Name') }}">
                                            </div>

                                            @if (is_module_enabled($modules, 'show_product_category'))
                                            <div class="col-lg-4 mb-2">
                                                <label>{{ __('Category') }}</label>
                                                <div class="gpt-up-down-arrow position-relative">
                                                    <select name="category_id" id="category-select" class="form-control table-select w-100 role">
                                                        <option value=""> {{ __('Select Category') }}</option>
                                                        @foreach ($categories as $category)
                                                            <option value="{{ $category->id }}"
                                                                    data-capacity=" {{ $category->variationCapacity }}"
                                                                    data-color="{{ $category->variationColor }}"
                                                                    data-size="{{ $category->variationSize }}"
                                                                    data-type="{{ $category->variationType }}"
                                                                    data-weight="{{ $category->variationWeight }}">
                                                                {{ ucfirst($category->categoryName) }} </option>
                                                        @endforeach
                                                    </select>
                                                    <span></span>
                                                </div>
                                            </div>
                                            @endif

                                            @if (is_module_enabled($modules, 'show_product_unit'))
                                            <div class="col-lg-4 mb-2">
                                                <label>{{ __('Unit') }}</label>
                                                <div class="gpt-up-down-arrow position-relative">
                                                    <select name="unit_id" class="form-control table-select w-100 role">
                                                        <option value=""> {{ __('Select one') }}</option>
                                                        @foreach ($units as $unit)
                                                            <option value="{{ $unit->id }}"> {{ ucfirst($unit->unitName) }}</option>
                                                        @endforeach
                                                    </select>
                                                    <span></span>
                                                </div>
                                            </div>
                                            @endif


                                            <div>
                                                <div id="dynamic-fields" class="row">

                                                </div>
                                            </div>


                                            @if (is_module_enabled($modules, 'show_product_code'))
                                            <div class="col-lg-4 mb-2">
                                                <label>{{ __('SKU / Code') }}</label>
                                                <input type="text" value="{{ $code }}" name="productCode" class="form-control" placeholder="{{ __('Enter Product Code') }}">
                                            </div>
                                            @endif

                                            @if (is_module_enabled($modules, 'show_product_brand'))
                                            <div class="col-lg-4 mb-2">
                                                <label>{{ __('Brand') }}</label>
                                                <div class="gpt-up-down-arrow position-relative">
                                                    <select name="brand_id" class="form-control table-select w-100 role">
                                                        <option value=""> {{ __('Select Brand') }}</option>
                                                        @foreach ($brands as $brand)
                                                            <option value="{{ $brand->id }}"> {{ ucfirst($brand->brandName) }}</option>
                                                        @endforeach
                                                    </select>
                                                    <span></span>
                                                </div>
                                            </div>
                                            @endif

                                            @if (is_module_enabled($modules, 'show_model_no'))
                                            <div class="col-lg-4 mb-2">
                                                <label>{{ __('Model') }}</label>
                                                <div class="gpt-up-down-arrow position-relative">
                                                    <select name="model_id" class="form-control table-select w-100 role">
                                                        <option value="">{{ __('Select Model') }}</option>
                                                        @foreach ($product_models as $model)
                                                            <option value="{{ $model->id }}"> {{ ucfirst($model->name) }}</option>
                                                        @endforeach
                                                    </select>
                                                    <span></span>
                                                </div>
                                            </div>
                                            @endif
                                                @if (moduleCheck('WarehouseAddon'))
                                                    @if (is_module_enabled($modules, 'show_warehouse'))
                                                        <div class="col-lg-4 mb-2 warehouse_id">
                                                            <label>{{ __('Warehouse') }}</label>
                                                            <div class="gpt-up-down-arrow position-relative">
                                                                <select name="stocks[0][warehouse_id]" class="form-control table-select w-100 role">
                                                                    <option value="">{{ __('Select a Warehouse') }}</option>
                                                                    @foreach ($warehouses as $warehouse )
                                                                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                                <span></span>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endif
                                                @if (is_module_enabled($modules, 'show_rack'))
                                                <div class="col-lg-4 mb-2">
                                                    <label>{{ __('Rack') }}</label>
                                                    <div class="gpt-up-down-arrow position-relative">
                                                        <select name="rack_id" id="rack_id" class="form-control table-select w-100 role">
                                                            <option value="">{{ __('Select one') }}</option>
                                                            @foreach ($racks as $rack )
                                                                <option value="{{ $rack->id }}">{{ $rack->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        <span></span>
                                                    </div>
                                                </div>
                                                @endif

                                                @if (is_module_enabled($modules, 'show_shelf'))
                                                <div class="col-lg-4 mb-2">
                                                    <label>{{ __('Shelf') }}</label>
                                                    <div class="gpt-up-down-arrow position-relative">
                                                        <select name="shelf_id" id="shelf_id" class="form-control table-select w-100 role disabled">
                                                            <option value="">{{ __('Select one') }}</option>

                                                        </select>
                                                        <span></span>
                                                    </div>
                                                </div>
                                                @endif

                                            @if (is_module_enabled($modules, 'show_alert_qty'))
                                            <div class="col-lg-4 mb-2">
                                                <label>{{ __('Low Stock Alert') }}</label>
                                                <input type="number" step="any" name="alert_qty" class="form-control" placeholder="{{ __('EX: 5') }}">
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="product-price-container min-h-80vh">
                            <div class="product-price-header">
                                <h3>Product price, stock</h3>
                                <div class="d-flex align-items-center justify-content-center gap-3">
                                    @if ($showSingle && $showVariant)
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="product_type" id="singleOption" value="single" checked>
                                            <label class="form-check-label" for="singleOption">Single</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="product_type" id="variantOption" value="variant">
                                            <label class="form-check-label" for="variantOption">Batch</label>
                                        </div>
                                    @elseif ($showSingle)
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="product_type" id="singleOption" value="single" checked>
                                            <label class="form-check-label" for="singleOption">Single</label>
                                        </div>
                                    @elseif ($showVariant)
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="product_type" id="variantOption" value="variant" checked>
                                            <label class="form-check-label" for="variantOption">Batch</label>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- mutiple varient --}}
                            <div class="variant-container">
                                <div class="main-variant-content">
                                    <div class="variant-content mt-3">
                                        @if (is_module_enabled($modules, 'show_vat_id'))
                                        <h5>Tax</h5>
                                        @endif
                                        <div class="d-flex align-items-center flex-wrap gap-2 justify-content-between">
                                            @if (is_module_enabled($modules, 'show_vat_id'))
                                            <div class="dual-dropdown">
                                                <select id="vat_id" name="vat_id">
                                                    <option value="">{{ __('Select one') }}</option>
                                                    @foreach ($vats as $vat)
                                                        <option value="{{ $vat->id }}" data-vat_rate="{{ $vat->rate }}">
                                                            {{ $vat->name }} ({{ $vat->rate }}%)
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <select id="vat_type" name="vat_type">
                                                    <option value="exclusive">{{ __('Exclusive') }}</option>
                                                    <option value="inclusive">{{ __('Inclusive') }}</option>
                                                </select>
                                            </div>
                                            @endif
                                            @if ($hasVisibleColumn)
                                            <div class="d-flex align-items-center gap-2">
                                                <a class="save-publish-btn add-variant-btn"> + Add </a>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="responsive-table product-table m-0 mt-3">
                                    <table class="table" id="datatable">
                                        <thead>
                                        <tr>
                                            @if (is_module_enabled($modules, 'show_batch_no'))
                                            <th>{{ __('Batch No') }}.</th>
                                            @endif
                                            @if (moduleCheck('WarehouseAddon'))
                                                @if (is_module_enabled($modules, 'show_warehouse'))
                                                 <th>{{ __('Warehouse') }}.</th>
                                                @endif
                                            @endif
                                            @if (is_module_enabled($modules, 'show_product_stock'))
                                            <th>{{ __('Qty') }}</th>
                                            @endif
                                            @usercan('products.price')
                                            @if (is_module_enabled($modules, 'show_exclusive_price'))
                                            <th>{{ __('Cost exc. tax') }}</th>
                                            @endif
                                            @if (is_module_enabled($modules, 'show_inclusive_price'))
                                            <th>{{ __('Cost inc. tax') }}</th>
                                            @endif
                                            @if (is_module_enabled($modules, 'show_profit_percent'))
                                            <th>{{ __('Profit') }} (%)</th>
                                            @endif
                                            @endusercan
                                            @if (is_module_enabled($modules, 'show_product_sale_price'))
                                            <th>{{ __('Sales Price') }}</th>
                                            @endif
                                            @if (is_module_enabled($modules, 'show_product_wholesale_price'))
                                            <th>{{ __('Wholesale') }}</th>
                                            @endif
                                            @if (is_module_enabled($modules, 'show_product_dealer_price'))
                                            <th>{{ __('Dealer') }}</th>
                                            @endif
                                            @if (is_module_enabled($modules, 'show_mfg_date'))
                                            <th>{{ __('Manufacture') }}</th>
                                            @endif
                                            @if (is_module_enabled($modules, 'show_expire_date'))
                                            <th>{{ __('Expired') }}</th>
                                            @endif
                                            @if (is_module_enabled($modules, 'show_action'))
                                            <th>{{ __('Action') }}</th>
                                            @endif
                                        </tr>
                                        </thead>
                                        <tbody id="product-data">
                                        <tr>
                                            @if (is_module_enabled($modules, 'show_batch_no'))
                                            <td><input type="text" name="stocks[0][batch_no]" class="form-control form-control-sm custom-table-input" placeholder="25632"></td>
                                            @endif

                                            @if (moduleCheck('WarehouseAddon'))
                                                @if (is_module_enabled($modules, 'show_warehouse'))
                                                   <td><select name="stocks[0][warehouse_id]" class="form-control table-select w-100 role">
                                                        <option value="">{{ __('Select') }}</option>
                                                            @foreach ($warehouses as $warehouse )
                                                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        <span></span>
                                                    </td>
                                                @endif
                                            @endif

                                            @if (is_module_enabled($modules, 'show_product_stock'))
                                            <td><input type="number" step="any" min="0" name="stocks[0][productStock]" class="form-control form-control-sm custom-table-input" placeholder="3"></td>
                                            @endif
                                            @usercan('products.price')
                                            @if (is_module_enabled($modules, 'show_exclusive_price'))
                                            <td>
                                                <input type="number" step="any" min="0" class="form-control form-control-sm custom-table-input exclusive_price" name="stocks[0][exclusive_price]" placeholder="Ex: 50">
                                            </td>
                                            @endif

                                            @if (is_module_enabled($modules, 'show_inclusive_price'))
                                            <td>
                                                <input type="number" step="any" min="0" class="form-control form-control-sm custom-table-input inclusive_price" name="stocks[0][inclusive_price]" placeholder="Ex: 50">
                                            </td>
                                            @endif

                                            @if (is_module_enabled($modules, 'show_profit_percent'))
                                            <td>
                                                <input type="number" class="form-control form-control-sm custom-table-input profit_percent" name="stocks[0][profit_percent]" placeholder="25%">
                                            </td>
                                            @endif
                                            @endusercan
                                            @if (is_module_enabled($modules, 'show_product_sale_price'))
                                            <td>
                                                <input type="number" step="any" min="0" class="form-control form-control-sm custom-table-input productSalePrice" name="stocks[0][productSalePrice]" placeholder="Ex: 200">
                                            </td>
                                            @endif
                                            @if (is_module_enabled($modules, 'show_product_wholesale_price'))
                                            <td><input type="number" step="any" min="0" name="stocks[0][productWholeSalePrice]" class="form-control form-control-sm custom-table-input" placeholder="Ex: 200"></td>
                                            @endif
                                            @if (is_module_enabled($modules, 'show_product_dealer_price'))
                                            <td><input type="number" step="any" min="0" name="stocks[0][productDealerPrice]" class="form-control form-control-sm custom-table-input" placeholder="Ex: 200"></td>
                                            @endif

                                            @if (is_module_enabled($modules, 'show_mfg_date'))
                                                @if(isset($modules['mfg_date_type']) && ($modules['mfg_date_type'] == 'dmy' || is_null($modules['mfg_date_type'])))
                                                    <td><input type="month" name="stocks[0][mfg_date]" value="{{ $modules['default_mfg_date'] ?? '' }}" class="form-control"></td>
                                                @else
                                                    <td><input type="date" name="stocks[0][mfg_date]" value="{{ $modules['default_mfg_date'] ?? '' }}" class="form-control"></td>
                                                @endif
                                            @endif

                                            @if (is_module_enabled($modules, 'show_expire_date'))
                                                @if(isset($modules['expire_date_type']) && ($modules['expire_date_type'] == 'dmy' || is_null($modules['expire_date_type'])))
                                                    <td><input type="month" name="stocks[0][expire_date]" value="{{ $modules['default_expired_date'] ?? '' }}" class="form-control"></td>
                                                @else
                                                    <td><input type="date" name="stocks[0][expire_date]" value="{{ $modules['default_expired_date'] ?? '' }}" class="form-control"></td>
                                                @endif
                                            @endif

                                            @if (is_module_enabled($modules, 'show_action'))
                                            <td>
                                                <a href="#" class="text-danger remove-row">
                                                    <i class="fal fa-times fa-lg"></i>
                                                </a>
                                            </td>
                                            @endif
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                            {{-- single varient --}}
                            <div class="single-container order-form-section m-3 pb-3 mt-0 mb-0">
                                <div class="row">
                                    @if (is_module_enabled($modules, 'show_vat_type'))
                                    <div class="col-lg-6 mb-2">
                                        <label>{{ __('Tax Type') }}</label>
                                        <div class="gpt-up-down-arrow position-relative">
                                            <select id="vat_type" name="vat_type" class="form-control table-select w-100">
                                                <option value="exclusive">{{ __('Exclusive') }}</option>
                                                <option value="inclusive">{{ __('Inclusive') }}</option>
                                            </select>
                                            <span></span>
                                        </div>
                                    </div>
                                    @endif

                                    @if (is_module_enabled($modules, 'show_vat_id'))
                                    <div class="col-lg-6 mb-2">
                                        <label>{{ __('Select Tax') }}</label>
                                        <div class="gpt-up-down-arrow position-relative">
                                            <select id="vat_id" name="vat_id" class="form-control table-select w-100">
                                                <option value="">{{ __('Select one') }}</option>
                                                @foreach ($vats as $vat)
                                                    <option value="{{ $vat->id }}" data-vat_rate="{{ $vat->rate }}">
                                                        {{ $vat->name }} ({{ $vat->rate }}%)
                                                    </option>
                                                @endforeach
                                            </select>
                                            <span></span>
                                        </div>
                                    </div>
                                    @endif

                                    @if (is_module_enabled($modules, 'show_product_stock'))
                                    <div class="col-lg-6 mb-2">
                                        <label>{{ __('Quantity') }}</label>
                                        <input type="number" name="stocks[0][productStock]"  class="form-control" placeholder="{{ __('EX: 10') }}">
                                    </div>
                                    @endif
                                    @usercan('products.price')
                                    @if (is_module_enabled($modules, 'show_exclusive_price'))
                                    <div class="col-lg-6 mb-2">
                                        <label>{{ __('Cost exc. tax') }}</label>
                                        <input type="number" class="form-control exclusive_price" name="stocks[0][exclusive_price]" placeholder="Enter Purchase Price">
                                    </div>
                                    @endif

                                    @if (is_module_enabled($modules, 'show_inclusive_price'))
                                    <div class="col-lg-6 mb-2">
                                        <label>{{ __('Cost inc. tax') }}</label>
                                        <input type="number" class="form-control inclusive_price" name="stocks[0][inclusive_price]" placeholder="Enter Purchase Price">
                                    </div>
                                    @endif

                                    @if (is_module_enabled($modules, 'show_profit_percent'))
                                    <div class="col-lg-6 mb-2">
                                        <label>{{ __('Profit (%)') }}</label>
                                        <input type="number" class="form-control profit_percent" name="stocks[0][profit_percent]" placeholder="Enter profit margin">
                                    </div>
                                    @endif
                                    @endusercan

                                    @if (is_module_enabled($modules, 'show_product_sale_price'))
                                    <div class="col-lg-6 mb-2">
                                        <label>{{ __('MRP/Sales Price') }}</label>
                                        <input type="number" class="form-control productSalePrice" name="stocks[0][productSalePrice]" placeholder="Ex: 10">
                                    </div>
                                    @endif

                                    @if (is_module_enabled($modules, 'show_product_wholesale_price'))
                                    <div class="col-lg-6 mb-2">
                                        <label>{{ __('Wholesale Price') }}</label>
                                        <input type="number" name="stocks[0][productWholeSalePrice]" class="form-control" placeholder="Ex: 10">
                                    </div>
                                    @endif

                                    @if (is_module_enabled($modules, 'show_product_dealer_price'))
                                    <div class="col-lg-6 mb-2">
                                        <label>{{ __('Dealer Price') }}</label>
                                        <input type="number" name="stocks[0][productDealerPrice]" class="form-control" placeholder="Ex: 10">
                                    </div>
                                    @endif

                                    @if (is_module_enabled($modules, 'show_mfg_date'))
                                        @if(isset($modules['mfg_date_type']) && ($modules['mfg_date_type'] == 'dmy' || is_null($modules['mfg_date_type'])))
                                            <div class="col-lg-6 mb-2">
                                                <label>{{ __('Manufacturing Date') }}</label>
                                                <input type="month" name="stocks[0][mfg_date]" value="{{ $modules['default_mfg_date'] ?? '' }}" class="form-control">
                                            </div>
                                        @else
                                            <div class="col-lg-6 mb-2">
                                                <label>{{ __('Manufacturing Date') }}</label>
                                                <input type="date" name="stocks[0][mfg_date]" value="{{ $modules['default_mfg_date'] ?? '' }}" class="form-control">
                                            </div>
                                        @endif
                                    @endif

                                    @if (is_module_enabled($modules, 'show_expire_date'))
                                        @if(isset($modules['expire_date_type']) && ($modules['expire_date_type'] == 'dmy' || is_null($modules['expire_date_type'])))
                                            <div class="col-lg-6 mb-2">
                                                <label>{{ __('Expire Date') }}</label>
                                                <input type="month" name="stocks[0][expire_date]" value="{{ $modules['default_expired_date'] ?? '' }}" class="form-control">
                                            </div>
                                        @else
                                            <div class="col-lg-6 mb-2">
                                                <label>{{ __('Expire Date') }}</label>
                                                <input type="date" name="stocks[0][expire_date]" value="{{ $modules['default_expired_date'] ?? '' }}" class="form-control">
                                            </div>
                                        @endif
                                    @endif

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        @if (is_module_enabled($modules, 'show_product_image'))
                        <div class="upload-product-img">
                            <div class="upload-img-title">
                                <h3>Product Image</h3>
                            </div>
                            <div class="upload-container mt-2" id="uploadContainer">
                                <div class="upload-img-title border-0 p-0 m-0 mb-1">
                                <h3><span>Image (Size 150x130)</span></h3>
                            </div>
                                <div class="upload-box">
                                    <svg width="40" height="40" viewBox="0 0 40 40" fill="none"
                                         xmlns="http://www.w3.org/2000/svg">
                                        <g clip-path="url(#clip0_40001300_65043)">
                                            <path d="M33.3333 40H6.66667C3 40 0 37 0 33.3333V6.66667C0 3 3 0 6.66667 0H33.3333C37 0 40 3 40 6.66667V33.3333C40 37 37 40 33.3333 40Z" fill="#69A1FF"/>
                                            <path d="M40 6.66667V33.3333C40 37 37 40 33.3333 40H6.33333C2.83333 39.8333 0 36.8333 0 33.3333V6.66667C0 3 3 0 6.66667 0H33.3333C37 0 40 3 40 6.66667Z" fill="#E6E6E6"/>
                                            <path d="M27.8333 40H6.66667C3 40 0 37 0 33.3333V27.8333L3.83333 24C6 21.8333 9.66667 21.8333 11.8333 24L27.8333 40Z" fill="#999999"/>
                                            <path d="M39.9987 23.834V33.334C39.9987 37.0007 36.9987 40.0007 33.332 40.0007H6.33203L27.1654 19.0007C29.332 16.834 32.9987 16.834 35.1654 19.0007L39.9987 23.834ZM24.332 10.5007H21.6654V17.334C21.6654 18.334 20.832 19.0007 19.9987 19.0007C19.1654 19.0007 18.332 18.334 18.332 17.334V10.5007H15.6654C14.6654 10.5007 13.9987 9.33398 14.6654 8.50065L18.9987 3.33398C19.4987 2.66732 20.4987 2.66732 20.832 3.33398L25.1654 8.66732C25.832 9.33398 25.332 10.5007 24.332 10.5007Z" fill="#999999"/>
                                        </g>
                                        <defs>
                                            <clipPath id="clip0_40001300_65043">
                                                <rect width="40" height="40" fill="white"/>
                                            </clipPath>
                                        </defs>
                                    </svg>
                                    <div class="upload-text">Drag & drop image</div>
                                    <input type="file" name="productPicture" accept="image/*" class="handle-image-Upload">
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- module permission as hidden--}}
    @foreach($defaultPermissions as $key)
        @php
            // if $modules is not set, default to true (1)
            $value = isset($modules[$key]) ? ($modules[$key] ? 1 : 0) : 1;
        @endphp
        <input type="hidden" class="module-permission" data-key="{{ $key }}" value="{{ $value }}">
    @endforeach

    @usercan('products.price')
    <input type="hidden" id="canSeePrice" value="1">
    @endusercan
    <input type="hidden" id="canSeePrice" value="0">

    <input type="hidden" id="getShelfRoute" value="{{ route('business.product.get.shelf') }}">
    @if (moduleCheck('WarehouseAddon') && is_module_enabled($modules, 'show_warehouse'))
        <input type="hidden" id="warehouses-data" value='@json($warehouses)'>
    @endif

@endsection


@push('js')
    <script src="{{ asset('assets/js/custom/product.js') }}"></script>
@endpush


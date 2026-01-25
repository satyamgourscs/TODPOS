<div class="modal fade" id="product-modal">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center justify-content-between">
                <h1 class="modal-title fs-5">{{ __('Add Items') }}</h1>
                <div class="custom-modal-header">
                    <button type="button" class="btn-close custom-close-btn" data-bs-dismiss="modal" aria-label="Close" ></button>
                </div>
            </div>

            <div class="modal-body">
                <div class="personal-info">
                        <form id="purchase_modal" data-route="{{ route('business.carts.store') }}">
                        @csrf
                            <input type="hidden" id="setting_expire_type" value="{{ $modules['expire_date_type'] ?? 'dmy' }}">
                            <div class="row">
                            <div class="col-lg-6 mb-2 mt-2">
                                <ul>
                                    <li><span class="fw-bold">{{ __('Product Name') }}</span> <span>:</span> <span id="product_name"></span></li>
                                    <li><span class="fw-bold">{{ __('Brand') }}</span> <span>:</span> <span id="brand"></span></li>
                                </ul>
                            </div>
                            <div class="col-lg-6 mb-2 mt-2 text-end">
                                <ul>
                                    <li><span class="fw-bold">{{ __('Stock') }}</span> <span>:</span> <span id="stock"></span></li>
                                </ul>
                            </div>

                            <div class="col-lg-6 mb-2">
                                <label>{{ __('Quantity') }}</label>
                                <input type="number" step="any" name="amount" id="product_qty" value="" required class="form-control" placeholder="{{ __('Enter Quantity') }}">
                            </div>
                            @usercan('purchases.price')
                            <div class="col-lg-6 mb-2">
                                <label>{{ __('Purchase Price') }}</label>
                                <input type="number" step="any" name="amount" id="purchase_price" required class="form-control" placeholder="{{ __('Enter Purchase Price') }}">
                            </div>
                            @endusercan
                            <div class="col-lg-6 mb-2">
                                <label>{{ __('Sales Price') }}</label>
                                <input type="number" step="any" name="amount" id="sales_price" required class="form-control" placeholder="{{ __('Enter Sales Price') }}">
                            </div>
                            <div class="col-lg-6 mb-2">
                                <label>{{ __('WholeSale Price') }}</label>
                                <input type="number" step="any" name="amount" id="whole_sale_price" required class="form-control" placeholder="{{ __('Enter WholeSale Price') }}">
                            </div>
                            <div class="col-lg-6 mb-2">
                                <label>{{ __('Dealer Price') }}</label>
                                <input type="number" step="any" name="amount" id="dealer_price" required class="form-control" placeholder="{{ __('Enter Dealer Price') }}">
                            </div>
                            @if (is_module_enabled($modules, 'show_product_batch_no'))
                            <div class="col-lg-6 mb-2">
                                <label>{{ __('Batch') }}</label>
                                <input type="text" name="batch_no" id="batch_no" value="{{ $modules['default_batch_no'] ?? '' }}" class="form-control" placeholder="{{ __('Enter Batch No.') }}">
                            </div>
                            @endif
                            @if (is_module_enabled($modules, 'show_product_expire_date'))
                                <div class="col-lg-6 mb-2">
                                    <label>{{ __('Expire Date') }}</label>
                                    <input type="{{ isset($modules['expire_date_type']) && $modules['expire_date_type'] == 'my' ? 'month' : 'date' }}" name="expire_date" value="{{ $modules['default_expired_date'] ?? '' }}" id="expire_date" class="form-control">
                                </div>
                            @endif
                        </div>
                        <div class="col-lg-12">
                            <div class="button-group text-center mt-5">
                                <button type="reset" class="theme-btn border-btn m-2">{{ __('Reset') }}</button>
                                <button class="theme-btn m-2 submit-btn">{{ __('Save') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

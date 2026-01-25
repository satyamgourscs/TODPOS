<div class="modal fade p-0" id="stock-modal-view" >
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5">{{ __('View Stock') }}</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body order-form-section">
                <div class="costing-list">
                    <div class="table-responsive mt-3">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('Batch') }}</th>
                                    <th>{{ __('Stock') }}</th>
                                    @usercan('stocks.price')
                                    <th>{{ __('Purchase Price') }}</th>
                                    @endusercan
                                    <th>{{ __('MRP') }}</th>
                                    <th>{{ __('WholeSale Price') }}</th>
                                    <th>{{ __('Dealer Price') }}</th>
                                    @if (is_module_enabled($modules, 'show_expire_date'))
                                    <th>{{ __('Expire Date') }}</th>
                                    @endif
                                    @if (moduleCheck('WarehouseAddon') && is_module_enabled($modules, 'show_warehouse'))
                                    <th>{{ __('Warehouse') }}</th>
                                    @endif
                                    @if (is_module_enabled($modules, 'show_weight'))
                                    <th id="weight-header" class="d-none">{{ __('Weight') }}</th>
                                    @endif
                                    @if (is_module_enabled($modules, 'show_rack'))
                                    <th>{{ __('Rack') }}</th>
                                    @endif
                                    @if (is_module_enabled($modules, 'show_shelf'))
                                    <th>{{ __('Shelf') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody id="stocks-table-data">
                                 {{-- Filled via jQuery --}}
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>


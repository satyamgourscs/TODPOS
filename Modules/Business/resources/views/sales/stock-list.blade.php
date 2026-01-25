<div class="modal fade" id="stock-list-modal">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header ">
                <h1 class="modal-title fs-5">{{ __('Product List') }}</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body ">
                <div class="my-3 position-relative search-input-container">
                    <input type="text" name="search" class="form-control sale-purchase-input stock-search" placeholder="{{ __('Search') }}">
                </div>
                <div class="table-responsive save-order-table">
                    <table id="lowStock" class="table text-center">
                        <thead class="table-light">
                            <tr>
                                <th> {{__('SL.')}} </th>
                                <th class="text-start"> {{__('Product Name')}} </th>
                                <th> {{__('Batch')}} </th>
                                @if (moduleCheck('WarehouseAddon'))
                                <th> {{__('Warehouse')}} </th>
                                @endif
                                <th> {{__('Stock')}} </th>
                                <th> {{__('Sale Price')}} </th>
                            </tr>
                        </thead>
                        <tbody class="stock-table">
                           {{--Loaded data dynamically --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

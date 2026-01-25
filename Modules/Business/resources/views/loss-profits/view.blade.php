<div class="modal fade p-0" id="loss-profit-view">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5">{{ __('Invoice: S01 - Demo User') }}</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body order-form-section">
                <div class="costing-list">
                    <div class="responsive-table m-0">
                        <table class="table" id="datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('SL') }}.</th>
                                    <th class="text-start">{{ __('Item Name') }}</th>
                                    <th class="text-start">{{ __('Batch No') }}</th>
                                    <th class="text-start">{{ __('Quantity') }}</th>
                                    <th class="text-start">{{ __('Purchases') }}</th>
                                    <th class="text-start">{{ __('Sale Price') }}</th>
                                    <th class="text-start">{{ __('Profit') }}</th>
                                    <th class="text-start">{{ __('Loss') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be dynamically injected here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

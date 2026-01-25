<div class="modal fade p-0" id="warehouse-view">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5">{{ __('View') }}</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body order-form-section">
                <div class="costing-list">
                    <ul>
                        <li><span>{{ __('Name') }}</span> <span>:</span> <span id="warehouseName"></span></li>
                        @if(auth()->user()->accessToMultiBranch())
                        <li><span>{{ __('Branch') }}</span> <span>:</span> <span id="branchName"></span></li>
                        @endif
                        <li><span>{{ __('Phone') }}</span> <span>:</span> <span id="warehousePhone"></span></li>
                        <li><span>{{ __('Email') }}</span> <span>:</span> <span id="warehouseEmail"></span></li>
                        <li><span>{{ __('Address') }}</span> <span>:</span> <span id="warehouseAddress"></span></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

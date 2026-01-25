<div class="modal fade common-validation-modal" id="warehouses-edit-modal">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5">{{ __('Edit Warehouse') }}</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="personal-info">
                    <form action="" method="post" enctype="multipart/form-data" class="ajaxform_instant_reload warehouseUpdateForm">
                        @csrf
                        @method('put')
                        <div class="row">
                            @if((moduleCheck('MultiBranchAddon') && multibranch_active()) && !auth()->user()->active_branch_id)
                                <div class="col-lg-6 mb-2">
                                    <label>{{ __('Branch') }}</label>
                                    <div class="gpt-up-down-arrow position-relative">
                                        <select name="branch_id" id="branch_id" class="form-control table-select w-100 role">
                                            <option value=""> {{ __('Select one') }}</option>
                                            @foreach ($branches as $branch)
                                                <option value="{{ $branch->id }}"> {{ ucfirst($branch->name) }}</option>
                                            @endforeach
                                        </select>
                                        <span></span>
                                    </div>
                                </div>
                            @endif
                            <div class="col-lg-6 mb-2">
                                <label>{{ __('Name') }}</label>
                                <input type="text" name="name" id="name" class="form-control"  placeholder="{{ __('Enter name') }}" required>
                            </div>

                            <div class="col-lg-6">
                                <label>{{ __('Phone') }}</label>
                                <input type="text" name="phone" id="phone" class="form-control" placeholder="{{ __('Enter phone') }}">
                            </div>

                            <div class="col-lg-6">
                                <label>{{ __('Email') }}</label>
                                <input type="text" name="email" id="email" class="form-control" placeholder="{{ __('Enter email') }}">
                            </div>

                            <div class="col-lg-6">
                                <label>{{ __('Address') }}</label>
                                <input type="text" name="address" id="address" class="form-control" placeholder="{{ __('Enter address') }}">
                            </div>

                        </div>

                        <div class="col-lg-12">
                            <div class="button-group text-center mt-5">
                                <a href="{{ route('warehouse.warehouses.index') }}" class="theme-btn border-btn m-2">{{ __('Cancel') }}</a>
                                @usercan('warehouses.update')
                                <button class="theme-btn m-2 submit-btn">{{ __('Save') }}</button>
                                @endusercan
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

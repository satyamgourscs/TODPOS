<div class="modal fade common-validation-modal" id="model-edit-modal">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5">{{ __('Edit Model') }}</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="personal-info">
                    <form action="" method="post" enctype="multipart/form-data" class="ajaxform_instant_reload modelUpdateForm">
                        @csrf
                        @method('put')
                        <div class="row">
                            <div class="col-lg-6 mb-2">
                                <label>{{ __('Model Name') }}</label>
                                <input type="text" name="name" id="model_name" required class="form-control" placeholder="{{ __('Enter Model Name') }}">
                            </div>

                            <div class="col-lg-6 mb-2">
                                <div class="col-lg-12">
                                    <label>{{ __('Status') }}</label>
                                    <div class="gpt-up-down-arrow position-relative">
                                        <select name="status" class="form-control" id="model_status">
                                            <option value="1">{{ __('Active') }}</option>
                                            <option value="0">{{ __('Deactive') }}</option>
                                        </select>
                                        <span></span>
                                    </div>
                                </div>
                            </div>

                         </div>

                        <div class="col-lg-12">
                            <div class="button-group text-center mt-5">
                                <a href="{{ route('business.product-models.index') }}" class="theme-btn border-btn m-2">{{ __('Cancel') }}</a>
                                @usercan('product-models.update')
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

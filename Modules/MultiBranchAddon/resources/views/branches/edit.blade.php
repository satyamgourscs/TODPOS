<div class="modal fade common-validation-modal" id="branches-edit-modal">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5">{{ __('Edit Branch') }}</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="personal-info">
                    <form action="" method="post" enctype="multipart/form-data"
                        class="ajaxform_instant_reload branchUpdateForm">
                        @csrf
                        @method('put')
                        <div class="row">
                            <div class="col-lg-6 mb-2">
                                <label>{{ __('Name') }}</label>
                                <input type="text" name="name" id="brnch_name" class="form-control" placeholder="{{ __('Enter name') }}" required>
                            </div>
                            <div class="col-lg-6 mb-2">
                                <label>{{ __('Phone') }}</label>
                                <input type="number" name="phone" id="brnch_phone" class="form-control" placeholder="{{ __('Enter phone') }}">
                            </div>
                            <div class="col-lg-6 mb-2">
                                <label>{{ __('Email') }}</label>
                                <input type="email" name="email" id="brnch_email" class="form-control" placeholder="{{ __('Enter email') }}">
                            </div>
                            <div class="col-lg-6 mb-2">
                                <label>{{ __('Address') }}</label>
                                <input type="text" name="address" id="brnch_address" class="form-control" placeholder="{{ __('Enter address') }}">
                            </div>
                            <div class="col-lg-6 mb-2">
                                <label>{{ __('Opening Balance') }}</label>
                                <input type="number" name="branchOpeningBalance" id="opening_balance" class="form-control" placeholder="{{ __('Enter balance') }}">
                            </div>
                            <div class="col-lg-12">
                                <label>{{__('Description')}}</label>
                                <textarea name="description" id="brnch_desc" class="form-control" placeholder="{{ __('Enter description') }}"></textarea>
                            </div>
                         </div>

                        <div class="col-lg-12">
                            <div class="button-group text-center mt-5">
                                <a href="{{ route('multibranch.branches.index') }}" class="theme-btn border-btn m-2">{{ __('Cancel') }}</a>
                                @usercan('branches.update')
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

<div class="modal fade common-validation-modal" id="shelf-edit-modal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5">{{ __('Edit shelf') }}</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="personal-info">
                    <form action="" method="post" enctype="multipart/form-data" class="ajaxform_instant_reload shelfUpdateForm">
                        @csrf
                        @method('put')

                        <div class="row">
                            <div class="col-lg-12 mb-2">
                                <label>{{ __('Name') }}</label>
                                <input type="text" name="name" id="name" required class="form-control" placeholder="{{ __('Enter name') }}">
                            </div>
                        </div>

                        <div class="col-lg-12">
                            <div class="button-group text-center mt-3">
                                <a href="{{ route('business.shelfs.index') }}" class="theme-btn border-btn m-2">{{ __('Cancel') }}</a>
                                @usercan('shelfs.update')
                                <button class="theme-btn m-2 submit-btn">{{ __('Update') }}</button>
                                @endusercan
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

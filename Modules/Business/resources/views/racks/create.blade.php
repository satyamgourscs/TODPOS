
<div class="modal fade common-validation-modal" id="rack-create-modal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5">{{ __('Add new rack') }}</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="personal-info">
                    <form action="{{ route('business.racks.store') }}" method="post" enctype="multipart/form-data" class="ajaxform_instant_reload">
                        @csrf

                        <div class="row">

                            <div class="col-lg-12 mb-2">
                                <label>{{ __('Rack Name') }}</label>
                                <input type="text" name="name" required class="form-control" placeholder="{{ __('Enter name') }}">
                            </div>

                            <div class="col-lg-12 mb-2">
                                <label>{{ __('Shelfs') }}</label>
                                <div class="gpt-up-down-arrow position-relative">
                                    <select name="shelf_id[]" id="shelf-select" class="form-control" multiple required>
                                        @foreach ($shelves as $shelf)
                                            <option value="{{ $shelf->id }}">{{ $shelf->name }}</option>
                                        @endforeach
                                    </select>
                                    <span></span>
                                </div>
                            </div>

                         </div>

                        <div class="col-lg-12">
                            <div class="button-group text-center mt-5">
                                <button type="reset" class="theme-btn border-btn m-2">{{ __('Reset') }}</button>
                                @usercan('racks.create')
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

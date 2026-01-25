<div class="modal fade common-validation-modal" id="customer-create-modal">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5">{{ __('Create New Party') }}</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="personal-info">
                    <form action="{{ route('business.sales-invoices.store.customer') }}" method="post" enctype="multipart/form-data"
                        class="ajaxform_instant_reload">
                        @csrf
                        <div class="row">
                            {{-- Party Name --}}
                            <div class="col-lg-6 mb-2">
                                <label>{{ __('Party Name') }} <span class="text-danger">*</span></label>
                                <input type="text" name="name" required class="form-control @error('name') is-invalid @enderror" 
                                    placeholder="{{ __('Enter name') }}" value="{{ old('name') }}">
                                @error('name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Mobile Number --}}
                            <div class="col-lg-6 mb-2">
                                <label>{{ __('Mobile Number') }}</label>
                                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                                    placeholder="{{ __('Enter Mobile Number') }}" value="{{ old('phone') }}">
                                @error('phone')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Have GST Checkbox --}}
                            <div class="col-lg-6 mb-2">
                                <label>{{ __('Have GST') }}</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="have_gst" name="have_gst" value="1">
                                    <label class="form-check-label" for="have_gst">
                                        {{ __('Have GST Number') }}
                                    </label>
                                </div>
                            </div>

                            {{-- GST Number (hidden by default, shown only if Have GST is checked) --}}
                            <div class="col-lg-6 mb-2" id="gst-field-wrapper" style="display: none;">
                                <label>{{ __('GST Number') }}</label>
                                <input type="text" name="gst" id="gst_field" class="form-control @error('gst') is-invalid @enderror" 
                                    placeholder="{{ __('Enter GST Number') }}" value="{{ old('gst') }}">
                                @error('gst')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Address Section (Optional) --}}
                            <div class="col-lg-12 mb-2" id="address-section" style="display: none;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label>{{ __('Address (Optional)') }}</label>
                                    <button type="button" class="btn btn-sm btn-link text-danger p-0" id="remove-address">
                                        {{ __('Remove') }}
                                    </button>
                                </div>

                                <div class="row">
                                    {{-- State --}}
                                    <div class="col-lg-4 mb-2">
                                        <label>{{ __('State') }}</label>
                                        <div class="gpt-up-down-arrow position-relative">
                                            <select name="state" id="state" class="form-control table-select w-100 choices-select">
                                                <option value="">{{ __('Select State') }}</option>
                                                @php
                                                    $states = [
                                                        'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chhattisgarh',
                                                        'Goa', 'Gujarat', 'Haryana', 'Himachal Pradesh', 'Jharkhand',
                                                        'Karnataka', 'Kerala', 'Madhya Pradesh', 'Maharashtra', 'Manipur',
                                                        'Meghalaya', 'Mizoram', 'Nagaland', 'Odisha', 'Punjab',
                                                        'Rajasthan', 'Sikkim', 'Tamil Nadu', 'Telangana', 'Tripura',
                                                        'Uttar Pradesh', 'Uttarakhand', 'West Bengal', 'Andaman and Nicobar Islands',
                                                        'Chandigarh', 'Dadra and Nagar Haveli and Daman and Diu', 'Delhi',
                                                        'Jammu and Kashmir', 'Ladakh', 'Lakshadweep', 'Puducherry'
                                                    ];
                                                @endphp
                                                @foreach($states as $state)
                                                    <option value="{{ $state }}" {{ old('state') == $state ? 'selected' : '' }}>{{ $state }}</option>
                                                @endforeach
                                            </select>
                                            <span></span>
                                        </div>
                                    </div>

                                    {{-- City --}}
                                    <div class="col-lg-4 mb-2">
                                        <label>{{ __('City') }}</label>
                                        <input type="text" name="city" class="form-control @error('city') is-invalid @enderror" 
                                            placeholder="{{ __('Enter City') }}" value="{{ old('city') }}">
                                        @error('city')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- House No --}}
                                    <div class="col-lg-4 mb-2">
                                        <label>{{ __('House No') }}</label>
                                        <input type="text" name="house_no" class="form-control @error('house_no') is-invalid @enderror" 
                                            placeholder="{{ __('Enter House No') }}" value="{{ old('house_no') }}">
                                        @error('house_no')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Show Address Section Button --}}
                            <div class="col-lg-12 mb-2" id="show-address-btn-wrapper">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="show-address-btn">
                                    {{ __('Add Address') }}
                                </button>
                            </div>
                        </div>

                        <div class="col-lg-12">
                            <div class="button-group text-center mt-5">
                                <button type="button" class="theme-btn border-btn m-2" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                <button type="submit" class="theme-btn m-2 submit-btn">{{ __('Save') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('script')
<script>
    $(document).ready(function() {
        // Show address section when "Add Address" is clicked
        $('#show-address-btn').on('click', function() {
            $('#address-section').slideDown(300);
            $('#show-address-btn-wrapper').slideUp(300);
        });

        // Hide address section when "Remove" is clicked
        $('#remove-address').on('click', function() {
            $('#address-section').slideUp(300);
            $('#show-address-btn-wrapper').slideDown(300);
            // Clear address fields
            $('#state').val('').trigger('change');
            $('input[name="city"]').val('');
            $('input[name="house_no"]').val('');
        });

        // Show/Hide GST field based on checkbox
        $('#have_gst').on('change', function() {
            if ($(this).is(':checked')) {
                $('#gst-field-wrapper').slideDown(300);
                $('#gst_field').focus();
            } else {
                $('#gst-field-wrapper').slideUp(300);
                $('#gst_field').val('');
            }
        });

        // Initialize Choices.js for state dropdown
        if (typeof Choices !== 'undefined' && $('#state').length) {
            const stateSelect = document.getElementById('state');
            if (stateSelect && !stateSelect.choices) {
                const choicesInstance = new Choices(stateSelect, {
                    searchEnabled: true,
                    itemSelectText: "",
                    shouldSort: false,
                });
                if (typeof window.choicesMap !== 'undefined') {
                    window.choicesMap.set('state', choicesInstance);
                }
            }
        }
    });
</script>
@endpush

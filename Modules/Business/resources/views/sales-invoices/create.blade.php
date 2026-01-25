@extends('layouts.business.master')

@section('title')
    {{ __('Create Sales Invoice') }}
@endsection

@section('main_content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-bodys">
                <div class="table-header p-16">
                    <h4>{{ __('Create Sales Invoice') }}</h4>
                </div>
                <div class="p-16">
                    <form action="{{ route('business.sales-invoices.store') }}" method="post" class="ajaxform_instant_reload">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('Invoice Number') }}</label>
                                    <input type="text" name="invoiceNumber" class="form-control" value="{{ $invoice_no }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('Customer') }}</label>
                                    <div class="d-flex align-items-center">
                                        <select name="party_id" id="party_id" class="form-select customer-select choices-select" aria-label="Select Customer">
                                            <option value="">{{ __('Select Customer') }}</option>
                                            <option class="guest-option" value="guest">{{ __('Guest') }}</option>
                                            @foreach ($customers as $customer)
                                                <option value="{{ $customer->id }}" data-type="{{ $customer->type }}" data-phone="{{ $customer->phone }}">
                                                    {{ $customer->name }}({{ $customer->type }}{{ $customer->due ? ' ' . currency_format($customer->due, currency: business_currency()) : '' }})
                                                    {{ $customer->phone }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <a type="button" href="#customer-create-modal" data-bs-toggle="modal"
                                           class="btn btn-danger square-btn d-flex justify-content-center align-items-center ms-2">
                                            <img src="{{ asset('assets/images/icons/plus-square.svg') }}" alt=""></a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 d-none guest_phone">
                                <div class="form-group">
                                    <label>{{ __('Customer Phone') }}</label>
                                    <input type="text" name="customer_phone" class="form-control"
                                           placeholder="{{ __('Enter Customer Phone Number') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('Sale Date') }}</label>
                                    <input type="date" name="saleDate" class="form-control" value="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('Payment Type') }}</label>
                                    <select name="payment_type_id" class="form-control" required>
                                        <option value="">{{ __('Select Payment Type') }}</option>
                                        @foreach($payment_types as $payment_type)
                                            <option value="{{ $payment_type->id }}">{{ $payment_type->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <h5>{{ __('Products') }}</h5>
                                <div id="products-container">
                                    <div class="product-row row mb-2">
                                        <div class="col-md-4">
                                            <select name="products[0][product_id]" class="form-control product-select" required>
                                                <option value="">{{ __('Select Product') }}</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}" data-price="{{ $product->productSalePrice }}">{{ $product->productName }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="number" name="products[0][quantity]" class="form-control" placeholder="{{ __('Qty') }}" min="1" required>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="number" name="products[0][price]" class="form-control price-input" placeholder="{{ __('Price') }}" step="0.01" required>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="text" class="form-control total-input" readonly placeholder="{{ __('Total') }}">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-danger remove-product">{{ __('Remove') }}</button>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-primary mt-2" id="add-product">{{ __('Add Product') }}</button>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('VAT') }}</label>
                                    <select name="vat_id" class="form-control">
                                        <option value="">{{ __('No VAT') }}</option>
                                        @foreach($vats as $vat)
                                            <option value="{{ $vat->id }}">{{ $vat->name }} ({{ $vat->rate }}%)</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('Discount Amount') }}</label>
                                    <input type="number" name="discountAmount" class="form-control" step="0.01" value="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('Shipping Charge') }}</label>
                                    <input type="number" name="shipping_charge" class="form-control" step="0.01" value="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('Paid Amount') }}</label>
                                    <input type="number" name="paidAmount" class="form-control" step="0.01" value="0">
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="submit" class="theme-btn submit-btn">{{ __('Create Invoice') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@include('business::sales-invoices.customer-create')

@push('script')
<script>
    $(document).ready(function() {
        let productIndex = 1;
        
        $('#add-product').on('click', function() {
            const productRow = `
                <div class="product-row row mb-2">
                    <div class="col-md-4">
                        <select name="products[${productIndex}][product_id]" class="form-control product-select" required>
                            <option value="">{{ __('Select Product') }}</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" data-price="{{ $product->productSalePrice }}">{{ $product->productName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="number" name="products[${productIndex}][quantity]" class="form-control" placeholder="{{ __('Qty') }}" min="1" required>
                    </div>
                    <div class="col-md-2">
                        <input type="number" name="products[${productIndex}][price]" class="form-control price-input" placeholder="{{ __('Price') }}" step="0.01" required>
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control total-input" readonly placeholder="{{ __('Total') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger remove-product">{{ __('Remove') }}</button>
                    </div>
                </div>
            `;
            $('#products-container').append(productRow);
            productIndex++;
        });

        $(document).on('click', '.remove-product', function() {
            $(this).closest('.product-row').remove();
        });

        $(document).on('change', '.product-select', function() {
            const price = $(this).find('option:selected').data('price');
            $(this).closest('.product-row').find('.price-input').val(price || 0);
            calculateTotal($(this).closest('.product-row'));
        });

        $(document).on('input', '.price-input, input[name*="[quantity]"]', function() {
            calculateTotal($(this).closest('.product-row'));
        });

        function calculateTotal(row) {
            const qty = parseFloat(row.find('input[name*="[quantity]"]').val()) || 0;
            const price = parseFloat(row.find('.price-input').val()) || 0;
            const total = qty * price;
            row.find('.total-input').val(total.toFixed(2));
        }

        // Handle customer selection
        $('#party_id').on('change', function() {
            if ($(this).val() === 'guest') {
                $('.guest_phone').removeClass('d-none');
            } else {
                $('.guest_phone').addClass('d-none');
            }
        });

        // ============================================
        // CLEAN CUSTOMER CREATION HANDLER
        // ============================================
        $(document).on('submit', '#customer-create-modal form', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            
            const form = $(this);
            const submitBtn = form.find('.submit-btn');
            const originalHtml = submitBtn.html();
            
            // Remove ajaxform_instant_reload class to prevent default handler
            const hadClass = form.hasClass('ajaxform_instant_reload');
            if (hadClass) {
                form.removeClass('ajaxform_instant_reload');
            }
            
            // Validate required fields
            let isValid = true;
            form.find('[required]').each(function() {
                if (!$(this).val()) {
                    isValid = false;
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid');
                }
            });
            
            if (!isValid) {
                if (typeof Notify !== 'undefined') {
                    Notify('error', null, '{{ __('Please fill in all required fields') }}');
                }
                if (hadClass) form.addClass('ajaxform_instant_reload');
                return false;
            }
            
            // Show loading
            submitBtn.html('<div class="spinner-border spinner-border-sm custom-text-primary" role="status"></div>')
                .prop('disabled', true);
            
            // Make AJAX call
            $.ajax({
                type: 'POST',
                url: form.attr('action'),
                data: new FormData(form[0]),
                dataType: 'json',
                contentType: false,
                cache: false,
                processData: false,
                success: function(response) {
                    submitBtn.html(originalHtml).prop('disabled', false);
                    
                    // Show success message
                    const successMsg = response.message || '{{ __('Customer created successfully') }}';
                    if (typeof Notify !== 'undefined') {
                        Notify('success', null, successMsg);
                    } else {
                        alert(successMsg);
                    }
                    
                    // If customer data exists, add to dropdown and select
                    if (response && response.customer) {
                        const customer = response.customer;
                        const customerId = customer.id;
                        const selectElement = document.getElementById('party_id');
                        
                        if (selectElement) {
                            // Format display text
                            const dueText = customer.due ? ' ' + formatCurrency(customer.due) : '';
                            const displayText = customer.name + '(' + customer.type + dueText + ') ' + (customer.phone || '');
                            
                            // Add option to select
                            const newOption = document.createElement('option');
                            newOption.value = customerId;
                            newOption.setAttribute('data-type', customer.type);
                            newOption.setAttribute('data-phone', customer.phone || '');
                            newOption.textContent = displayText;
                            
                            // Insert before guest option
                            const guestOption = selectElement.querySelector('option.guest-option');
                            if (guestOption) {
                                selectElement.insertBefore(newOption, guestOption);
                            } else {
                                selectElement.appendChild(newOption);
                            }
                            
                            // Refresh Choices.js if available
                            if (typeof Choices !== 'undefined') {
                                // Destroy existing instance
                                let existingInstance = null;
                                if (typeof window.choicesMap !== 'undefined' && window.choicesMap.has('party_id')) {
                                    existingInstance = window.choicesMap.get('party_id');
                                } else if (selectElement.choices) {
                                    existingInstance = selectElement.choices;
                                }
                                
                                if (existingInstance) {
                                    try {
                                        existingInstance.destroy();
                                    } catch(e) {}
                                }
                                
                                // Remove wrapper
                                $(selectElement).siblings('.choices').remove();
                                
                                // Reinitialize Choices
                                const choicesInstance = new Choices(selectElement, {
                                    searchEnabled: true,
                                    itemSelectText: "",
                                    shouldSort: false,
                                });
                                
                                // Store in map
                                if (typeof window.choicesMap !== 'undefined') {
                                    window.choicesMap.set('party_id', choicesInstance);
                                }
                                
                                // Set selected value
                                setTimeout(function() {
                                    $(selectElement).val(customerId);
                                    choicesInstance.setChoiceByValue(customerId.toString());
                                    $(selectElement).trigger('change');
                                }, 300);
                            } else {
                                // Fallback for native select
                                $(selectElement).val(customerId).trigger('change');
                            }
                        }
                    }
                    
                    // Reset form
                    form[0].reset();
                    form.find('input[type="file"]').val('');
                    form.find('select').each(function() {
                        $(this).val($(this).find('option:first').val());
                    });
                    form.find('.is-invalid').removeClass('is-invalid');
                    const imagePreview = form.find('#image');
                    if (imagePreview.length) {
                        imagePreview.attr('src', '{{ asset('assets/images/icons/upload.png') }}');
                    }
                    
                    // Close modal
                    const modal = $('#customer-create-modal');
                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        const modalInstance = bootstrap.Modal.getInstance(modal[0]);
                        if (modalInstance) {
                            modalInstance.hide();
                        } else {
                            new bootstrap.Modal(modal[0]).hide();
                        }
                    } else {
                        modal.modal('hide');
                    }
                    
                    // Force close backup
                    setTimeout(function() {
                        if (modal.is(':visible')) {
                            modal.hide();
                            $('.modal-backdrop').remove();
                            $('body').removeClass('modal-open').css('padding-right', '');
                        }
                    }, 300);
                    
                    // Restore class
                    if (hadClass) {
                        form.addClass('ajaxform_instant_reload');
                    }
                },
                error: function(xhr) {
                    submitBtn.html(originalHtml).prop('disabled', false);
                    
                    // Restore class
                    if (hadClass) {
                        form.addClass('ajaxform_instant_reload');
                    }
                    
                    // Show errors
                    if (xhr.responseJSON) {
                        if (typeof showInputErrors === 'function') {
                            showInputErrors(xhr.responseJSON);
                        }
                        
                        let errorMessage = '{{ __('Something went wrong!') }}';
                        if (xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseJSON.errors) {
                            const errors = xhr.responseJSON.errors;
                            const firstKey = Object.keys(errors)[0];
                            if (firstKey) {
                                const firstError = errors[firstKey];
                                if (Array.isArray(firstError) && firstError.length > 0) {
                                    errorMessage = firstError[0];
                                } else if (typeof firstError === 'string') {
                                    errorMessage = firstError;
                                }
                            }
                        }
                        
                        if (typeof Notify !== 'undefined') {
                            Notify('error', null, errorMessage);
                        } else {
                            alert(errorMessage);
                        }
                    } else {
                        if (typeof Notify !== 'undefined') {
                            Notify('error', null, '{{ __('Something went wrong! Please try again.') }}');
                        } else {
                            alert('Something went wrong! Please try again.');
                        }
                    }
                }
            });
            
            return false;
        });

        // Helper function to format currency
        function formatCurrency(amount) {
            @php
                $currency = business_currency();
            @endphp
            const currencySymbol = '{{ $currency->symbol ?? "₹" }}';
            const currencyPosition = '{{ $currency->position ?? "before" }}';
            const formattedAmount = parseFloat(amount).toFixed(2);
            
            if (currencyPosition === 'before') {
                return currencySymbol + formattedAmount;
            } else {
                return formattedAmount + ' ' + currencySymbol;
            }
        }
    });
</script>
@endpush

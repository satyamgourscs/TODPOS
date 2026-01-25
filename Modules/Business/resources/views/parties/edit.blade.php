@extends('layouts.business.master')

@section('title')
    {{ request('type') !== 'Supplier' ? __('Edit Customer') : __('Edit Supplier') }}
@endsection

@php
    $file = base_path('lang/countrylist.json');

    if (file_exists($file)) {
        $countries = json_decode(file_get_contents($file), true);
    } else {
        $countries = [];
    }
    $type = request('type') !== 'Supplier' ? 'Customer' : 'Supplier';
@endphp

@section('main_content')
    <div class="erp-table-section">
        <div class="container-fluid">
            <div class="card border-0">
                <div class="card-bodys ">
                    <div class="table-header p-16">
                        <h4>{{ __('Edit ') . ucfirst(request('type')) }}</h4>
                        @usercan('parties.read')
                        <a href="{{ route('business.parties.index', ['type' => request('type')]) }}"
                            class="add-order-btn rounded-2 {{ Route::is('business.parties.create') ? 'active' : '' }}">
                            <i class="far fa-list" aria-hidden="true"></i>{{ ucfirst(request('type')) . __(' List') }}
                        </a>
                        @endusercan
                    </div>
                    <div class="order-form-section p-16">
                        <form action="{{ route('business.parties.update', $party->id) }}" method="POST"
                            class="ajaxform_instant_reload">
                            @csrf
                            @method('put')
                            <div class="add-suplier-modal-wrapper d-block">
                                <div class="row">
                                    <div class="row col-lg-9">
                                        <div class="col-lg-6 mb-2">
                                            <label>{{ __($type . ' Name') }}</label>
                                            <input type="text" value="{{ $party->name }}" name="name" required class="form-control" placeholder="{{ __('Enter '.$type.' Name') }}">
                                        </div>

                                        <div class="col-lg-6 mb-2">
                                            <label>{{ __('Phone') }}</label>
                                            <input type="number" value="{{ $party->phone }}" name="phone"
                                                class="form-control" placeholder="{{ __('Enter phone number') }}">
                                        </div>

                                        @if (request('type') !== 'Supplier')
                                            <div class="col-lg-6 mb-2">
                                                <label>{{ __('Party Type') }}</label>
                                                <div class="gpt-up-down-arrow position-relative">
                                                    <select name="type" class="form-control table-select w-100" required>
                                                        <option value=""> {{ __('Select one') }}</option>
                                                        <option @selected($party->type == 'Retailer') value="Retailer">
                                                            {{ __('Customer') }}</option>
                                                        <option @selected($party->type == 'Dealer') value="Dealer">
                                                            {{ __('Dealer') }}
                                                        </option>
                                                        <option @selected($party->type == 'Wholesaler') value="Wholesaler">
                                                            {{ __('Wholesaler') }}</option>
                                                    </select>
                                                    <span></span>
                                                </div>
                                            </div>
                                        @else
                                            <div>
                                                <input type="hidden" name="type" value="Supplier">
                                            </div>
                                        @endif

                                        <div class="col-lg-6 mb-2">
                                            <div class="form-group">
                                                <label>{{ __('Balance') }}</label>
                                                <div class="input-select-wrapper">
                                                    <input type="number" step="any" name="opening_balance" value="{{ $party->opening_balance }}" placeholder="Ex: 500">
                                                    <select name="opening_balance_type">
                                                        <option value="due" {{ $party->opening_balance_type == 'due' ? 'selected' : '' }}>Due</option>
                                                        <option value="advance" {{ $party->opening_balance_type == 'advance' ? 'selected' : '' }}>Advance</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-6 mb-2">
                                            <label>{{ __('Email') }}</label>
                                            <input type="email" value="{{ $party->email }}" name="email"
                                                class="form-control" placeholder="{{ __('Enter Email') }}">
                                        </div>

                                        @if (request('type') !== 'Supplier')
                                        <div class="col-lg-6 mb-2">
                                            <label>{{ __('Party Credit Limit') }}</label>
                                            <input type="number" name="credit_limit" value="{{ $party->credit_limit }}"
                                                step="any" class="form-control" placeholder="{{ __('Ex: 800') }}">
                                        </div>
                                        @endif

                                        <div class="col-lg-6 mb-2">
                                            <label>{{ __('Address') }}</label>
                                            <input type="text" value="{{ $party->address }}" name="address"
                                                class="form-control" placeholder="{{ __('Enter Address') }}">
                                        </div>

                                        <div class="accordion" id="customAccordion">
                                            <div class="accordion-item border-0">
                                                <h2 class="accordion-header">
                                                    <button
                                                        class="accordion-button collapsed fw-medium  text-primary bg-transparent shadow-none"
                                                        type="button" data-bs-toggle="collapse"
                                                        data-bs-target="#collapseOne" aria-expanded="false"
                                                        aria-controls="collapseOne">
                                                        <span class="icon me-2">+</span> {{ __('Billing Address') }}
                                                    </button>
                                                </h2>
                                                <div id="collapseOne" class="accordion-collapse collapse show"
                                                    data-bs-parent="#customAccordion">
                                                    <div class="accordion-body fst-italic text-secondary p-0">
                                                        <div class="row">
                                                            <div class="col-lg-6 mb-2">
                                                                <label>{{ __('Address') }}</label>
                                                                <input type="text" name="billing_address[address]"
                                                                    value="{{ $party->billing_address['address'] ?? '' }}"
                                                                    class="form-control"
                                                                    placeholder="{{ __('Enter address') }}">
                                                            </div>

                                                            <div class="col-lg-6 mb-2">
                                                                <label>{{ __('City') }}</label>
                                                                <input type="text" name="billing_address[city]"
                                                                    value="{{ $party->billing_address['city'] ?? '' }}"
                                                                    class="form-control"
                                                                    placeholder="{{ __('Enter city') }}">
                                                            </div>

                                                            <div class="col-lg-6 mb-2">
                                                                <label>{{ __('State') }}</label>
                                                                <input type="text" name="billing_address[state]"
                                                                    value="{{ $party->billing_address['state'] ?? '' }}"
                                                                    class="form-control"
                                                                    placeholder="{{ __('Enter state') }}">
                                                            </div>

                                                            <div class="col-lg-6 mb-2">
                                                                <label>{{ __('Zip Code') }}</label>
                                                                <input type="text" name="billing_address[zip_code]"
                                                                    value="{{ $party->billing_address['zip_code'] ?? '' }}"
                                                                    class="form-control"
                                                                    placeholder="{{ __('Enter zip code') }}">
                                                            </div>

                                                            @php
                                                                $billing = is_array($party->billing_address)  ? $party->billing_address  : json_decode($party->billing_address, true) ?? [];
                                                            @endphp

                                                            <div class="col-lg-6 mb-2">
                                                                <label>{{ __('Country') }}</label>
                                                                <select name="billing_address[country]"
                                                                    class="form-control">
                                                                    <option value="">{{ __('Select a country') }}
                                                                    </option>
                                                                    @foreach ($countries as $country)
                                                                        <option value="{{ $country['name'] }}"
                                                                           @selected(($billing['country'] ?? '') == $country['name'])>
                                                                            {{ __($country['name']) }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Accordion #2 -->
                                            <div class="accordion-item border-0">
                                                <h2 class="accordion-header">
                                                    <button
                                                        class="accordion-button fw-medium text-dark bg-transparent shadow-none"
                                                        type="button" data-bs-toggle="collapse"
                                                        data-bs-target="#collapseTwo" aria-expanded="true"
                                                        aria-controls="collapseTwo">
                                                        <span class="icon me-2">−</span> {{ __('Shipping Address') }}
                                                    </button>
                                                </h2>
                                                <div id="collapseTwo" class="accordion-collapse collapse "
                                                    data-bs-parent="#customAccordion">
                                                    <div class="accordion-body fst-italic text-secondary ">
                                                        <div class="row">
                                                            <div class="col-lg-6 mb-2">
                                                                <label>{{ __('Address') }}</label>
                                                                <input type="text" name="shipping_address[address]"
                                                                    value="{{ $party->billing_address['address'] ?? '' }}"
                                                                    class="form-control"
                                                                    placeholder="{{ __('Enter address') }}">
                                                            </div>

                                                            <div class="col-lg-6 mb-2">
                                                                <label>{{ __('City') }}</label>
                                                                <input type="text" name="shipping_address[city]"
                                                                    value="{{ $party->billing_address['city'] ?? '' }}"
                                                                    class="form-control"
                                                                    placeholder="{{ __('Enter city') }}">
                                                            </div>

                                                            <div class="col-lg-6 mb-2">
                                                                <label>{{ __('State') }}</label>
                                                                <input type="text" name="shipping_address[state]"
                                                                    value="{{ $party->billing_address['state'] ?? '' }}"
                                                                    class="form-control"
                                                                    placeholder="{{ __('Enter state') }}">
                                                            </div>

                                                            <div class="col-lg-6 mb-2">
                                                                <label>{{ __('Zip Code') }}</label>
                                                                <input type="text" name="shipping_address[zip_code]"
                                                                    value="{{ $party->billing_address['zip_code'] ?? '' }}"
                                                                    class="form-control"
                                                                    placeholder="{{ __('Enter zip code') }}">
                                                            </div>

                                                            @php
                                                                $shipping = is_array($party->shipping_address) ? $party->shipping_address : json_decode($party->shipping_address, true) ?? [];
                                                            @endphp

                                                            <div class="col-lg-6 mb-2">
                                                                <label>{{ __('Country') }}</label>
                                                                <select name="shipping_address[country]"
                                                                    class="form-control">
                                                                    <option value="">{{ __('Select a country') }}
                                                                    </option>
                                                                    @foreach ($countries as $country)
                                                                        <option value="{{ $country['name'] }}"
                                                                            @selected(($shipping['country'] ?? '') == $country['name'])>
                                                                            {{ __($country['name']) }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <h6 class="img-title">Image <span>(PNG & JPG)</span></h6>
                                        <div id="uploadBox">
                                            <div id="previewArea">
                                                <div id="iconArea">
                                                    <img src="{{ asset( $party->image ?? 'assets/images/icons/img.png') }}" alt="icon" />
                                                </div>
                                                <p>Drag & drop your Image</p>
                                                <p>or <span class="browse-text">Browse</span></p>
                                            </div>
                                        </div>

                                        <input type="file" name="image" id="fileInput" accept="image/*">
                                    </div>
                                </div>

                                <div class="col-lg-12">
                                    <div class="button-group text-center mt-5">
                                        <a href="{{ route('business.parties.index') }}"
                                            class="theme-btn border-btn m-2">{{ __('Cancel') }}</a>
                                            @usercan('parties.update')
                                            <button class="theme-btn m-2 submit-btn">{{ __('Save') }}</button>
                                            @endusercan
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
    </div>
@endsection

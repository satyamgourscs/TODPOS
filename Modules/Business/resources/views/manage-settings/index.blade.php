@extends('layouts.business.master')

@section('title')
    {{ __('Settings') }}
@endsection
@php
    $modules = optional($product_setting?->modules) ?? [];
@endphp

@section('main_content')
    <div class="erp-table-section">
        <div class="container-fluid">
            <div class="card shadow-sm">
                <div class="card-bodys">
                    <div class="table-header p-16">
                        <h4>{{ __('Settings') }}</h4>
                    </div>

                    <ul class="nav nav-tabs " id="settingsTab" role="tablist">
                        <li class="nav-item settings-item" role="presentation">
                            <button class="nav-link settings-link active" id="all-tab" data-bs-toggle="tab"
                                data-bs-target="#all" type="button" role="tab">
                                All Settings
                            </button>
                        </li>

                        <li class="nav-item settings-item" role="presentation">
                            <button class="nav-link settings-link" id="general-tab" data-bs-toggle="tab"
                                data-bs-target="#general" type="button" role="tab">
                                General
                            </button>
                        </li>

                        <li class="nav-item settings-item" role="presentation">
                            <button class="nav-link settings-link" id="product-tab" data-bs-toggle="tab"
                                data-bs-target="#product" type="button" role="tab">
                                Product
                            </button>
                        </li>

                        <li class="nav-item settings-item" role="presentation">
                            <button class="nav-link settings-link" id="invoice-tab" data-bs-toggle="tab"
                                data-bs-target="#invoice" type="button" role="tab">
                                Invoice Print
                            </button>
                        </li>
                        <li class="nav-item settings-item" role="presentation">
                            <button class="nav-link settings-link" id="role-tab" data-bs-toggle="tab"
                                data-bs-target="#role" type="button" role="tab">
                                Role & Permission
                            </button>
                        </li>
                        <li class="nav-item settings-item" role="presentation">
                            <button class="nav-link settings-link" id="role-tab" data-bs-toggle="tab"
                                data-bs-target="#Currencies" type="button" role="tab">
                                Currencies
                            </button>
                        </li>
                        <li class="nav-item settings-item" role="presentation">
                            <button class="nav-link settings-link" id="role-tab" data-bs-toggle="tab"
                                data-bs-target="#Payment" type="button" role="tab">
                                Payment Type
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content mt-3" id="settingsTabContent">

                        <div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab">
                            <div class="settings-box-container">
                                <div>
                                    <a href="{{ route('business.settings.index') }}" class="text-decoration-none text-dark">
                                        <div class=" setting-box">
                                            <div class="d-flex align-items-center jusitfy-content-center gap-3">
                                                <div class="settings-icon">
                                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <path
                                                            d="M15.5 12C15.5 13.933 13.933 15.5 12 15.5C10.067 15.5 8.5 13.933 8.5 12C8.5 10.067 10.067 8.5 12 8.5C13.933 8.5 15.5 10.067 15.5 12Z"
                                                            stroke="#C52127" stroke-width="1.5" />
                                                        <path
                                                            d="M21.011 14.0949C21.5329 13.9542 21.7939 13.8838 21.8969 13.7492C22 13.6147 22 13.3982 22 12.9653V11.0316C22 10.5987 22 10.3822 21.8969 10.2477C21.7938 10.1131 21.5329 10.0427 21.011 9.90194C19.0606 9.37595 17.8399 7.33687 18.3433 5.39923C18.4817 4.86635 18.5509 4.59992 18.4848 4.44365C18.4187 4.28738 18.2291 4.1797 17.8497 3.96432L16.125 2.98509C15.7528 2.77375 15.5667 2.66808 15.3997 2.69058C15.2326 2.71308 15.0442 2.90109 14.6672 3.27709C13.208 4.73284 10.7936 4.73278 9.33434 3.277C8.95743 2.90099 8.76898 2.71299 8.60193 2.69048C8.43489 2.66798 8.24877 2.77365 7.87653 2.98499L6.15184 3.96423C5.77253 4.17959 5.58287 4.28727 5.51678 4.44351C5.45068 4.59976 5.51987 4.86623 5.65825 5.39916C6.16137 7.33686 4.93972 9.37599 2.98902 9.90196C2.46712 10.0427 2.20617 10.1131 2.10308 10.2476C2 10.3822 2 10.5987 2 11.0316V12.9653C2 13.3982 2 13.6147 2.10308 13.7492C2.20615 13.8838 2.46711 13.9542 2.98902 14.0949C4.9394 14.6209 6.16008 16.66 5.65672 18.5976C5.51829 19.1305 5.44907 19.3969 5.51516 19.5532C5.58126 19.7095 5.77092 19.8172 6.15025 20.0325L7.87495 21.0118C8.24721 21.2231 8.43334 21.3288 8.6004 21.3063C8.76746 21.2838 8.95588 21.0957 9.33271 20.7197C10.7927 19.2628 13.2088 19.2627 14.6689 20.7196C15.0457 21.0957 15.2341 21.2837 15.4012 21.3062C15.5682 21.3287 15.7544 21.223 16.1266 21.0117L17.8513 20.0324C18.2307 19.8171 18.4204 19.7094 18.4864 19.5531C18.5525 19.3968 18.4833 19.1304 18.3448 18.5975C17.8412 16.66 19.0609 14.621 21.011 14.0949Z"
                                                            stroke="#C52127" stroke-width="1.5" stroke-linecap="round" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h6>General Settings</h6>
                                                    <small class="text-muted d-block">Configure the fundamental information
                                                        of the site.</small>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>

                                <div>
                                    <a href="{{ route('business.notifications.index') }}"
                                        class="text-decoration-none text-dark">
                                        <div class="setting-box">
                                            <div class="d-flex align-items-center jusitfy-content-center gap-3">
                                                <div class="settings-icon">
                                                    <svg width="25" height="24" viewBox="0 0 25 24" fill="none"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <path
                                                            d="M5.49235 11.491C5.41887 12.887 5.50334 14.373 4.25611 15.3084C3.67562 15.7438 3.33398 16.427 3.33398 17.1527C3.33398 18.1508 4.11578 19 5.13398 19H19.534C20.5522 19 21.334 18.1508 21.334 17.1527C21.334 16.427 20.9924 15.7438 20.4119 15.3084C19.1646 14.373 19.2491 12.887 19.1756 11.491C18.9841 7.85223 15.9778 5 12.334 5C8.69015 5 5.68386 7.85222 5.49235 11.491Z"
                                                            stroke="#C52127" stroke-width="1.5" stroke-linecap="round"
                                                            stroke-linejoin="round" />
                                                        <path
                                                            d="M10.834 3.125C10.834 3.95343 11.5056 5 12.334 5C13.1624 5 13.834 3.95343 13.834 3.125C13.834 2.29657 13.1624 2 12.334 2C11.5056 2 10.834 2.29657 10.834 3.125Z"
                                                            stroke="#C52127" stroke-width="1.5" />
                                                        <path
                                                            d="M15.334 19C15.334 20.6569 13.9909 22 12.334 22C10.6771 22 9.33398 20.6569 9.33398 19"
                                                            stroke="#C52127" stroke-width="1.5" stroke-linecap="round"
                                                            stroke-linejoin="round" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h6>Notifications</h6>
                                                    <small class="text-muted d-block">Control and configure overall
                                                        notification systems</small>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>

                                <div>
                                    <a href="{{ route('business.currencies.index') }}"
                                        class="text-decoration-none text-dark">
                                        <div class="setting-box">
                                            <div class="d-flex align-items-center jusitfy-content-center gap-3">
                                                <div class="settings-icon">
                                                    <svg width="25" height="24" viewBox="0 0 25 24" fill="none"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <path
                                                            d="M3.16602 12C3.16602 7.77027 3.16602 5.6554 4.36399 4.25276C4.5341 4.05358 4.7196 3.86808 4.91878 3.69797C6.32142 2.5 8.43629 2.5 12.666 2.5C16.8957 2.5 19.0106 2.5 20.4132 3.69797C20.6124 3.86808 20.7979 4.05358 20.968 4.25276C22.166 5.6554 22.166 7.77027 22.166 12C22.166 16.2297 22.166 18.3446 20.968 19.7472C20.7979 19.9464 20.6124 20.1319 20.4132 20.302C19.0106 21.5 16.8957 21.5 12.666 21.5C8.43629 21.5 6.32142 21.5 4.91878 20.302C4.7196 20.1319 4.5341 19.9464 4.36399 19.7472C3.16602 18.3446 3.16602 16.2297 3.16602 12Z"
                                                            stroke="#C52127" stroke-width="1.5" />
                                                        <path
                                                            d="M15.3762 10.063C15.2771 9.30039 14.4014 8.06817 12.8268 8.06814C10.9972 8.06811 10.2274 9.08141 10.0712 9.58806C9.82746 10.2657 9.8762 11.659 12.0207 11.8109C14.7014 12.0009 15.7753 12.3174 15.6387 13.958C15.502 15.5985 14.0077 15.953 12.8268 15.9149C11.6458 15.877 9.71365 15.3344 9.63867 13.8752M12.6394 7V8.07177M12.6394 15.9051V16.9999"
                                                            stroke="#C52127" stroke-width="1.5" stroke-linecap="round" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h6>Currencies</h6>
                                                    <small class="text-muted d-block">View and update currency
                                                        settings</small>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>

                                <div>
                                    <a href="{{ route('business.roles.index') }}" class="text-decoration-none text-dark">
                                        <div class="setting-box">
                                            <div class="d-flex align-items-center jusitfy-content-center gap-3">
                                                <div class="settings-icon">
                                                    <svg width="24" height="24" viewBox="0 0 24 24"
                                                        fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path
                                                            d="M11.5 14.0116C9.45338 13.9164 7.38334 14.4064 5.57757 15.4816C4.1628 16.324 0.453366 18.0441 2.71266 20.1966C3.81631 21.248 5.04549 22 6.59087 22H12"
                                                            stroke="#C52127" stroke-width="1.5" stroke-linecap="round"
                                                            stroke-linejoin="round" />
                                                        <path
                                                            d="M15.5 6.5C15.5 8.98528 13.4853 11 11 11C8.51472 11 6.5 8.98528 6.5 6.5C6.5 4.01472 8.51472 2 11 2C13.4853 2 15.5 4.01472 15.5 6.5Z"
                                                            stroke="#C52127" stroke-width="1.5" />
                                                        <path
                                                            d="M18 20.7143V22M18 20.7143C16.8432 20.7143 15.8241 20.1461 15.2263 19.2833M18 20.7143C19.1568 20.7143 20.1759 20.1461 20.7737 19.2833M15.2263 19.2833L14.0004 20.0714M15.2263 19.2833C14.8728 18.773 14.6667 18.1597 14.6667 17.5C14.6667 16.8403 14.8727 16.2271 15.2262 15.7169M20.7737 19.2833L21.9996 20.0714M20.7737 19.2833C21.1272 18.773 21.3333 18.1597 21.3333 17.5C21.3333 16.8403 21.1273 16.2271 20.7738 15.7169M18 14.2857C19.1569 14.2857 20.1761 14.854 20.7738 15.7169M18 14.2857C16.8431 14.2857 15.8239 14.854 15.2262 15.7169M18 14.2857V13M20.7738 15.7169L22 14.9286M15.2262 15.7169L14 14.9286"
                                                            stroke="#C52127" stroke-width="1.5" stroke-linecap="round" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h6>User Role</h6>
                                                    <small class="text-muted d-block">Add new users, Provide role and
                                                        Permission</small>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>

                                <div>
                                    <a href="{{ route('business.payment-types.index') }}"
                                        class="text-decoration-none text-dark">
                                        <div class="setting-box">
                                            <div class="d-flex align-items-center jusitfy-content-center gap-3">
                                                <div class="settings-icon">
                                                    <svg width="24" height="24" viewBox="0 0 24 24"
                                                        fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path
                                                            d="M13.5 15H6C4.11438 15 3.17157 15 2.58579 14.4142C2 13.8284 2 12.8856 2 11V7C2 5.11438 2 4.17157 2.58579 3.58579C3.17157 3 4.11438 3 6 3H18C19.8856 3 20.8284 3 21.4142 3.58579C22 4.17157 22 5.11438 22 7V12C22 12.9319 22 13.3978 21.8478 13.7654C21.6448 14.2554 21.2554 14.6448 20.7654 14.8478C20.3978 15 19.9319 15 19 15"
                                                            stroke="#C52127" stroke-width="1.5" stroke-linecap="round"
                                                            stroke-linejoin="round" />
                                                        <path
                                                            d="M14 9C14 10.1045 13.1046 11 12 11C10.8954 11 10 10.1045 10 9C10 7.89543 10.8954 7 12 7C13.1046 7 14 7.89543 14 9Z"
                                                            stroke="#C52127" stroke-width="1.5" stroke-linecap="round"
                                                            stroke-linejoin="round" />
                                                        <path
                                                            d="M13 17C13 15.3431 14.3431 14 16 14V12C16 10.3431 17.3431 9 19 9V14.5C19 16.8346 19 18.0019 18.5277 18.8856C18.1548 19.5833 17.5833 20.1548 16.8856 20.5277C16.0019 21 14.8346 21 12.5 21H12C10.1362 21 9.20435 21 8.46927 20.6955C7.48915 20.2895 6.71046 19.5108 6.30448 18.5307C6 17.7956 6 16.8638 6 15"
                                                            stroke="#C52127" stroke-width="1.5" stroke-linecap="round"
                                                            stroke-linejoin="round" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h6>Payment Type</h6>
                                                    <small class="text-muted d-block">Manage payment method for
                                                        purchase.</small>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>

                            </div>
                        </div>

                        <div class="tab-pane fade" id="general" role="tabpanel" aria-labelledby="general-tab">
                            <div class="settings-box-container">
                                <div>
                                    <a href="{{ route('business.settings.index') }}"
                                        class="text-decoration-none text-dark">
                                        <div class=" setting-box">
                                            <div class="d-flex align-items-center jusitfy-content-center gap-3">
                                                <div class="settings-icon">
                                                    <svg width="24" height="24" viewBox="0 0 24 24"
                                                        fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path
                                                            d="M15.5 12C15.5 13.933 13.933 15.5 12 15.5C10.067 15.5 8.5 13.933 8.5 12C8.5 10.067 10.067 8.5 12 8.5C13.933 8.5 15.5 10.067 15.5 12Z"
                                                            stroke="#C52127" stroke-width="1.5" />
                                                        <path
                                                            d="M21.011 14.0949C21.5329 13.9542 21.7939 13.8838 21.8969 13.7492C22 13.6147 22 13.3982 22 12.9653V11.0316C22 10.5987 22 10.3822 21.8969 10.2477C21.7938 10.1131 21.5329 10.0427 21.011 9.90194C19.0606 9.37595 17.8399 7.33687 18.3433 5.39923C18.4817 4.86635 18.5509 4.59992 18.4848 4.44365C18.4187 4.28738 18.2291 4.1797 17.8497 3.96432L16.125 2.98509C15.7528 2.77375 15.5667 2.66808 15.3997 2.69058C15.2326 2.71308 15.0442 2.90109 14.6672 3.27709C13.208 4.73284 10.7936 4.73278 9.33434 3.277C8.95743 2.90099 8.76898 2.71299 8.60193 2.69048C8.43489 2.66798 8.24877 2.77365 7.87653 2.98499L6.15184 3.96423C5.77253 4.17959 5.58287 4.28727 5.51678 4.44351C5.45068 4.59976 5.51987 4.86623 5.65825 5.39916C6.16137 7.33686 4.93972 9.37599 2.98902 9.90196C2.46712 10.0427 2.20617 10.1131 2.10308 10.2476C2 10.3822 2 10.5987 2 11.0316V12.9653C2 13.3982 2 13.6147 2.10308 13.7492C2.20615 13.8838 2.46711 13.9542 2.98902 14.0949C4.9394 14.6209 6.16008 16.66 5.65672 18.5976C5.51829 19.1305 5.44907 19.3969 5.51516 19.5532C5.58126 19.7095 5.77092 19.8172 6.15025 20.0325L7.87495 21.0118C8.24721 21.2231 8.43334 21.3288 8.6004 21.3063C8.76746 21.2838 8.95588 21.0957 9.33271 20.7197C10.7927 19.2628 13.2088 19.2627 14.6689 20.7196C15.0457 21.0957 15.2341 21.2837 15.4012 21.3062C15.5682 21.3287 15.7544 21.223 16.1266 21.0117L17.8513 20.0324C18.2307 19.8171 18.4204 19.7094 18.4864 19.5531C18.5525 19.3968 18.4833 19.1304 18.3448 18.5975C17.8412 16.66 19.0609 14.621 21.011 14.0949Z"
                                                            stroke="#C52127" stroke-width="1.5" stroke-linecap="round" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h6>General Settings</h6>
                                                    <small class="text-muted d-block">Configure the fundamental information
                                                        of the site.</small>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="product" role="tabpanel" aria-labelledby="product-tab">
                            <div class="table-header p-16">
                                <h4>{{ __('Product Settings') }}</h4>
                                <div class="custom-control custom-checkbox d-flex align-items-center gap-2">
                                    <input type="checkbox" class="custom-control-input delete-checkbox-item  multi-delete"
                                        id="selectAll">
                                    <label class="custom-control-label fw-bold"
                                        for="selectAll">{{ __('Select All') }}</label>
                                </div>
                            </div>
                            <div class="order-form-section p-16">
                                <form action="{{ route('business.product.settings.update') }}" method="post"
                                    class="ajaxform">
                                    @csrf
                                    <div class="row product-setting-form mt-3">
                                        <div class="col-lg-4">
                                            <h3 class="title">{{ __('Add Product Settings') }}
                                                <svg class="svg" width="16" height="17" viewBox="0 0 16 17"
                                                    fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <g opacity="0.8">
                                                        <path
                                                            d="M7.99967 15.1615C11.6816 15.1615 14.6663 12.1767 14.6663 8.49479C14.6663 4.81289 11.6816 1.82812 7.99967 1.82812C4.31778 1.82812 1.33301 4.81289 1.33301 8.49479C1.33301 12.1767 4.31778 15.1615 7.99967 15.1615Z"
                                                            fill="#97979F" stroke="#97979F" stroke-width="1.5"
                                                            stroke-linecap="round" stroke-linejoin="round" />
                                                        <path d="M8 5.82812V8.49479" stroke="white" stroke-width="1.5"
                                                            stroke-linecap="round" stroke-linejoin="round" />
                                                        <path d="M8 11.1719H8.00833" stroke="white" stroke-width="1.5"
                                                            stroke-linecap="round" stroke-linejoin="round" />
                                                    </g>
                                                </svg>
                                            </h3>

                                            <div class="d-flex align-items-center mb-3">
                                                <input type="hidden" name="show_product_price" value="0">
                                                <input id="product_price" class="delete-checkbox-item  multi-delete" type="checkbox" name="show_product_price" value="1"
                                                    {{ is_null($product_setting) || is_null($product_setting->modules) || (optional($product_setting->modules)['show_product_price'] ?? false) ? 'checked' : '' }}>
                                                <label for="product_price">
                                                    Product Price
                                                </label>
                                            </div>

                                            <div class="d-flex align-items-center mb-3">
                                                <input type="hidden" name="show_product_code" value="0">
                                                <input id="product_code" class="delete-checkbox-item multi-delete" type="checkbox" name="show_product_code" value="1"
                                                    {{ optional($product_setting?->modules)['show_product_code'] === "1" ? 'checked' : '' }}>
                                                <label for="product_code">Product Code</label>
                                            </div>

                                            <div class="d-flex align-items-center mb-3">
                                                <input type="hidden" name="show_product_stock" value="0">
                                                <input id="stock" class="delete-checkbox-item multi-delete"
                                                    type="checkbox" name="show_product_stock" value="1"
                                                    {{ is_null($product_setting) || is_null($product_setting->modules) || (optional($product_setting->modules)['show_product_stock'] ?? false) ? 'checked' : '' }}>
                                                <label for="stock">Product Stock</label>
                                            </div>

                                            <div class="d-flex align-items-center mb-3">
                                                <input type="hidden" name="show_product_unit" value="0">
                                                <input id="unit" class="delete-checkbox-item multi-delete"
                                                    type="checkbox" name="show_product_unit" value="1"
                                                    {{ is_null($product_setting) || is_null($product_setting->modules) || (optional($product_setting->modules)['show_product_unit'] ?? false) ? 'checked' : '' }}>
                                                <label for="unit">Product Unit</label>
                                            </div>

                                            <div class="d-flex align-items-center mb-3">
                                                <input type="hidden" name="show_product_brand" value="0">
                                                <input id="brand" class="delete-checkbox-item multi-delete"
                                                       type="checkbox" name="show_product_brand" value="1"
                                                    {{ optional($product_setting?->modules)['show_product_brand'] === "1" ? 'checked' : '' }}>
                                                <label for="brand">Product Brand</label>
                                            </div>

                                            <div class="d-flex align-items-center mb-3">
                                                <input type="hidden" name="show_model_no" value="0">
                                                <input id="model" type="checkbox" class="delete-checkbox-item multi-delete"
                                                       name="show_model_no" value="1"
                                                    {{ optional($product_setting?->modules)['show_model_no'] === "1" ? 'checked' : '' }}>
                                                <label for="model">Model No</label>
                                            </div>

                                            <div class="d-flex align-items-center mb-3">
                                                <input type="hidden" name="show_product_category" value="0">
                                                <input id="category" class="delete-checkbox-item multi-delete"
                                                    type="checkbox" name="show_product_category" value="1"
                                                    {{ is_null($product_setting) || is_null($product_setting->modules) || (optional($product_setting->modules)['show_product_category'] ?? false) ? 'checked' : '' }}>
                                                <label for="category">Product Category</label>
                                            </div>

                                            <div class="d-flex align-items-center mb-3">
                                                <input type="hidden" name="show_product_manufacturer" value="0">
                                                <input id="manufacturer" class="delete-checkbox-item multi-delete"
                                                       type="checkbox" name="show_product_manufacturer" value="1"
                                                    {{ optional($product_setting?->modules)['show_product_manufacturer'] === "1" ? 'checked' : '' }}>
                                                <label for="manufacturer">Product Manufacturer</label>
                                            </div>

                                            <div class="d-flex align-items-center mb-3">
                                                <input type="hidden" name="show_product_image" value="0">
                                                <input id="image" class="delete-checkbox-item multi-delete"
                                                       type="checkbox" name="show_product_image" value="1"
                                                    {{ optional($product_setting?->modules)['show_product_image'] === "1" ? 'checked' : '' }}>
                                                <label for="image">Product Image</label>
                                            </div>


                                            <div class="d-flex align-items-center mb-3">
                                                <input type="hidden" name="show_alert_qty" value="0">
                                                <input id="quantity" class="delete-checkbox-item multi-delete"
                                                    type="checkbox" name="show_alert_qty" value="1"
                                                    {{ is_null($product_setting) || is_null($product_setting->modules) || (optional($product_setting->modules)['show_alert_qty'] ?? false) ? 'checked' : '' }}>
                                                <label for="quantity">Low Stock Alert</label>
                                            </div>

                                            <div class="d-flex align-items-center mb-3">
                                                <input type="hidden" name="show_vat_id" value="0">
                                                <input id="vat" class="delete-checkbox-item multi-delete"
                                                       type="checkbox" name="show_vat_id" value="1"
                                                    {{ optional($product_setting?->modules)['show_vat_id'] === "1" ? 'checked' : '' }}>
                                                <label for="vat">VAT ID</label>
                                            </div>

                                            <div class="d-flex align-items-center mb-3">
                                                <input type="hidden" name="show_vat_type" value="0">
                                                <input id="type" class="delete-checkbox-item multi-delete"
                                                       type="checkbox" name="show_vat_type" value="1"
                                                    {{ optional($product_setting?->modules)['show_vat_type'] === "1" ? 'checked' : '' }}>
                                                <label for="type">VAT Type</label>
                                            </div>

                                            <div class="d-flex align-items-center mb-3">
                                                <input type="hidden" name="show_exclusive_price" value="0">
                                                <input id="exclusive" class="delete-checkbox-item multi-delete"
                                                    type="checkbox" name="show_exclusive_price" value="1"
                                                    {{ is_null($product_setting) || is_null($product_setting->modules) || (optional($product_setting->modules)['show_exclusive_price'] ?? false) ? 'checked' : '' }}>
                                                <label for="exclusive">Exclusive Price</label>
                                            </div>


                                            <div class="d-flex align-items-center mb-3">
                                                <input type="hidden" name="show_inclusive_price" value="0">
                                                <input id="inclusive" class="delete-checkbox-item multi-delete"
                                                    type="checkbox" name="show_inclusive_price" value="1"
                                                    {{ is_null($product_setting) || is_null($product_setting->modules) || (optional($product_setting->modules)['show_inclusive_price'] ?? false) ? 'checked' : '' }}>
                                                <label for="inclusive">Inclusive Price</label>
                                            </div>

                                            <div class="d-flex align-items-center mb-3">
                                                <input type="hidden" name="show_profit_percent" value="0">
                                                <input id="percent" class="delete-checkbox-item multi-delete"
                                                    type="checkbox" name="show_profit_percent" value="1"
                                                    {{ is_null($product_setting) || is_null($product_setting->modules) || (optional($product_setting->modules)['show_profit_percent'] ?? false) ? 'checked' : '' }}>
                                                <label for="percent">Profit Percent</label>
                                            </div>




                                            <div class="d-flex align-items-center mb-3">
                                                <input type="hidden" name="show_warehouse" value="0">
                                                <input id="warehouse" class="delete-checkbox-item multi-delete"
                                                       type="checkbox" name="show_warehouse" value="1"
                                                    {{ optional($product_setting?->modules)['show_warehouse'] === "1" ? 'checked' : '' }}>
                                                <label for="warehouse">Warehouse</label>
                                            </div>

                                            <div class="d-flex align-items-center mb-3">
                                                <input type="hidden" name="show_rack" value="0">
                                                <input id="rack" class="delete-checkbox-item multi-delete"
                                                       type="checkbox" name="show_rack" value="1"
                                                    {{ optional($product_setting?->modules)['show_rack'] === "1" ? 'checked' : '' }}>
                                                <label for="rack">Rack</label>
                                            </div>


                                            <div class="d-flex align-items-center mb-3">
                                                <input type="hidden" name="show_shelf" value="0">
                                                <input id="shelf" class="delete-checkbox-item multi-delete"
                                                       type="checkbox" name="show_shelf" value="1"
                                                    {{ optional($product_setting?->modules)['show_shelf'] === "1" ? 'checked' : '' }}>
                                                <label for="shelf">Shelf</label>
                                            </div>

                                            <div class="d-flex align-items-center mb-3">
                                                <input type="hidden" name="show_action" value="0">
                                                <input id="show_action" class="delete-checkbox-item multi-delete"
                                                       type="checkbox" name="show_action" value="1"
                                                    {{ optional($product_setting?->modules)['show_action'] === "1" ? 'checked' : '' }}>
                                                <label for="show_action">Action</label>
                                            </div>

                                            <div class="d-flex align-items-center mb-3">
                                                <input type="hidden" name="show_weight" value="0">
                                                <input id="show_weight" class="delete-checkbox-item multi-delete"
                                                       type="checkbox" name="show_weight" value="1"
                                                    {{ optional($product_setting?->modules)['show_weight'] === "1" ? 'checked' : '' }}>
                                                <label for="show_weight">Weight</label>
                                            </div>

                                        </div>
                                        <div class="col-lg-4">
                                            <h3 class="title">{{ __('Additional Product Field') }}
                                                <svg class="svg" width="16" height="17" viewBox="0 0 16 17"
                                                    fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <g opacity="0.8">
                                                        <path
                                                            d="M7.99967 15.1615C11.6816 15.1615 14.6663 12.1767 14.6663 8.49479C14.6663 4.81289 11.6816 1.82812 7.99967 1.82812C4.31778 1.82812 1.33301 4.81289 1.33301 8.49479C1.33301 12.1767 4.31778 15.1615 7.99967 15.1615Z"
                                                            fill="#97979F" stroke="#97979F" stroke-width="1.5"
                                                            stroke-linecap="round" stroke-linejoin="round" />
                                                        <path d="M8 5.82812V8.49479" stroke="white" stroke-width="1.5"
                                                            stroke-linecap="round" stroke-linejoin="round" />
                                                        <path d="M8 11.1719H8.00833" stroke="white" stroke-width="1.5"
                                                            stroke-linecap="round" stroke-linejoin="round" />
                                                    </g>
                                                </svg>
                                            </h3>

                                            <h6>{{ __('MRP/PRICE') }}</h6>
                                            <div class="additional-input">
                                                <div class="d-flex align-items-center mb-3">
                                                    <input type="hidden" name="show_product_sale_price" value="0">
                                                    <input id="mrp" type="checkbox"
                                                        class="delete-checkbox-item multi-delete"
                                                        name="show_product_sale_price" value="1"
                                                        {{ is_null($product_setting) || is_null($product_setting->modules) || (optional($product_setting->modules)['show_product_sale_price'] ?? false) ? 'checked' : '' }}>
                                                    <label for="mrp">MRP</label>
                                                </div>

                                                <input type="number" class="form-control additional-input-field"
                                                    name="default_sale_price"
                                                    value="{{ optional($product_setting)->modules['default_sale_price'] ?? '' }}"
                                                    min="0" step="1"
                                                    placeholder="{{ __('Enter Sale Price') }}">
                                            </div>


                                            <div class="additional-input">
                                                <div class="d-flex align-items-center mb-3">
                                                    <input type="hidden" name="show_product_wholesale_price" value="0">
                                                    <input id="wholesale" type="checkbox"
                                                           class="delete-checkbox-item multi-delete"
                                                           name="show_product_wholesale_price" value="1"
                                                        {{ optional($product_setting?->modules)['show_product_wholesale_price'] === "1" ? 'checked' : '' }}>
                                                    <label for="wholesale">Wholesale Price</label>
                                                </div>

                                                <input type="number" class="form-control additional-input-field"
                                                    name="default_wholesale_price"
                                                    value="{{ optional($product_setting)->modules['default_wholesale_price'] ?? '' }}"
                                                    min="0" step="1"
                                                    placeholder="{{ __('Enter Wholesale Price') }}">
                                            </div>

                                            <div class="additional-input">
                                                <div class="d-flex align-items-center mb-3">
                                                    <input type="hidden" name="show_product_dealer_price" value="0">
                                                    <input id="dealer" type="checkbox"
                                                           class="delete-checkbox-item multi-delete"
                                                           name="show_product_dealer_price" value="1"
                                                        {{ optional($product_setting?->modules)['show_product_dealer_price'] === "1" ? 'checked' : '' }}>
                                                    <label for="dealer">Dealer Price</label>
                                                </div>
                                                <input type="number" class="form-control additional-input-field"
                                                    name="default_dealer_price"
                                                    value="{{ optional($product_setting)->modules['default_dealer_price'] ?? '' }}"
                                                    min="0" step="1"
                                                    placeholder="{{ __('Enter Dealer Price') }}">
                                            </div>

                                            <h6>{{ __('Batch Tracking') }}</h6>

                                            <div class="additional-input">
                                                <div class="d-flex align-items-center mb-2">
                                                    <input type="hidden" name="show_batch_no" value="0">
                                                    <input id="batch" type="checkbox"
                                                           class="delete-checkbox-item multi-delete"
                                                           name="show_batch_no" value="1"
                                                        {{ optional($product_setting?->modules)['show_batch_no'] === "1" ? 'checked' : '' }}>
                                                    <label for="batch">Batch No</label>
                                                </div>
                                                <input type="text" class="form-control additional-input-field"
                                                    name="default_batch_no"
                                                    value="{{ optional($product_setting)->modules['default_batch_no'] ?? '' }}"
                                                    placeholder="{{ __('Batch No') }}">
                                            </div>

                                            {{-- Expiry Date --}}
                                            <div class="additional-input">
                                                <div class="d-flex align-items-center mb-2">
                                                    <input type="hidden" name="show_expire_date" value="0">
                                                    <input id="expiry" type="checkbox"
                                                           class="delete-checkbox-item multi-delete"
                                                           name="show_expire_date" value="1"
                                                        {{ optional($product_setting?->modules)['show_expire_date'] === "1" ? 'checked' : '' }}>
                                                    <label for="expiry">Expiry Date</label>
                                                </div>

                                                <div>
                                                    <select class="form-select date-type-selector" data-target="expired"
                                                        name="expire_date_type">
                                                        <option value="">Select</option>
                                                        <option value="dmy"
                                                            {{ optional($product_setting->modules ?? null)['expire_date_type'] == 'dmy' ? 'selected' : '' }}>
                                                            Day / Month / Year</option>
                                                        <option value="my"
                                                            {{ optional($product_setting->modules ?? null)['expire_date_type'] == 'my' ? 'selected' : '' }}>
                                                            Month / Year
                                                        </option>
                                                    </select>
                                                </div>

                                                <div class="expired-inputs">
                                                    <input type="date" id="expired_dmy"
                                                        name="default_expired_date_dmy"
                                                        value="{{ optional($product_setting->modules ?? null)['expire_date_type'] == 'dmy' ? optional($product_setting->modules)['default_expired_date'] : '' }}"
                                                        class="form-control expired-dmy"
                                                        style="{{ optional($product_setting->modules ?? null)['expire_date_type'] == 'dmy' ? '' : 'display:none;' }}">

                                                    <input type="month" id="expired_my" name="default_expired_date_my"
                                                        value="{{ optional($product_setting->modules ?? null)['expire_date_type'] == 'my' ? optional($product_setting->modules)['default_expired_date'] : '' }}"
                                                        class="form-control expired-my"
                                                        style="{{ optional($product_setting->modules ?? null)['expire_date_type'] == 'my' ? '' : 'display:none;' }}">
                                                </div>

                                            </div>

                                            {{-- MFG Date --}}
                                            <div class="additional-input">
                                                <div class="d-flex align-items-center mb-2">
                                                    <input type="hidden" name="show_mfg_date" value="0">
                                                    <input id="mfg" type="checkbox"
                                                           class="delete-checkbox-item multi-delete"
                                                           name="show_mfg_date" value="1"
                                                        {{ optional($product_setting?->modules)['show_mfg_date'] === "1" ? 'checked' : '' }}>
                                                    <label for="mfg">Mfg Date</label>
                                                </div>

                                                <div>
                                                    <select class="form-select date-type-selector" data-target="mfg"
                                                        name="mfg_date_type">
                                                        <option value="">Select</option>
                                                        <option value="dmy"
                                                            {{ optional($product_setting->modules ?? null)['mfg_date_type'] == 'dmy' ? 'selected' : '' }}>
                                                            Day / Month / Year
                                                        </option>
                                                        <option value="my"
                                                            {{ optional($product_setting->modules ?? null)['mfg_date_type'] == 'my' ? 'selected' : '' }}>
                                                            Month / Year
                                                        </option>
                                                    </select>
                                                </div>


                                                <div class="mfg-inputs">
                                                    <input type="date" id="mfg_dmy" name="default_mfg_date_dmy"
                                                        value="{{ optional($product_setting->modules ?? null)['mfg_date_type'] == 'dmy' ? optional($product_setting->modules)['default_mfg_date'] : '' }}"
                                                        class="form-control mfg-dmy"
                                                        style="{{ optional($product_setting->modules ?? null)['mfg_date_type'] == 'dmy' ? '' : 'display:none;' }}">

                                                    <input type="month" id="mfg_my" name="default_mfg_date_my"
                                                        value="{{ optional($product_setting->modules ?? null)['mfg_date_type'] == 'my' ? optional($product_setting->modules)['default_mfg_date'] : '' }}"
                                                        class="form-control mfg-my"
                                                        style="{{ optional($product_setting->modules ?? null)['mfg_date_type'] == 'my' ? '' : 'display:none;' }}">
                                                </div>

                                            </div>

                                            <h6>{{ __('Product Type') }}</h6>

                                            <div class="d-flex align-items-center mb-3">
                                                <input type="hidden" name="show_product_type_single" value="0">
                                                <input id="single" class="delete-checkbox-item multi-delete"
                                                    type="checkbox" name="show_product_type_single" value="1"
                                                    {{ is_null($product_setting) || is_null($product_setting->modules) || (optional($product_setting->modules)['show_product_type_single'] ?? false) ? 'checked' : '' }}>
                                                <label for="single">
                                                    Single
                                                </label>
                                            </div>

                                            <div class="d-flex align-items-center mb-3">
                                                <input type="hidden" name="show_product_type_variant" value="0">
                                                <input id="variant" class="delete-checkbox-item multi-delete"
                                                       type="checkbox" name="show_product_type_variant" value="1"
                                                    {{ optional($product_setting?->modules)['show_product_type_variant'] === "1" ? 'checked' : '' }}>
                                                <label for="variant">Batch</label>
                                            </div>

                                        </div>
                                        <div class="col-lg-4">
                                            <h3 class="title">{{ __('Purchase Setting') }}
                                                <svg class="svg" width="16" height="17" viewBox="0 0 16 17"
                                                    fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <g opacity="0.8">
                                                        <path
                                                            d="M7.99967 15.1615C11.6816 15.1615 14.6663 12.1767 14.6663 8.49479C14.6663 4.81289 11.6816 1.82812 7.99967 1.82812C4.31778 1.82812 1.33301 4.81289 1.33301 8.49479C1.33301 12.1767 4.31778 15.1615 7.99967 15.1615Z"
                                                            fill="#97979F" stroke="#97979F" stroke-width="1.5"
                                                            stroke-linecap="round" stroke-linejoin="round" />
                                                        <path d="M8 5.82812V8.49479" stroke="white" stroke-width="1.5"
                                                            stroke-linecap="round" stroke-linejoin="round" />
                                                        <path d="M8 11.1719H8.00833" stroke="white" stroke-width="1.5"
                                                            stroke-linecap="round" stroke-linejoin="round" />
                                                    </g>
                                                </svg>
                                            </h3>
                                            <div class="d-flex align-items-center mb-3">
                                                <input type="hidden" name="show_product_batch_no" value="0">
                                                <input id="batch_no" type="checkbox"
                                                       class="delete-checkbox-item multi-delete"
                                                       name="show_product_batch_no" value="1"
                                                    {{ optional($product_setting?->modules)['show_product_batch_no'] === "1" ? 'checked' : '' }}>
                                                <label for="batch_no">{{ __('Batch No') }}</label>
                                            </div>
                                            <div class="d-flex align-items-center mb-3">
                                                <input type="hidden" name="show_product_expire_date" value="0">
                                                <input id="expire" type="checkbox"
                                                       class="delete-checkbox-item multi-delete"
                                                       name="show_product_expire_date" value="1"
                                                    {{ optional($product_setting?->modules)['show_product_expire_date'] === "1" ? 'checked' : '' }}>
                                                <label for="expire">{{ __('Expire Date') }}</label>
                                            </div>

                                        </div>
                                        <div class="col-lg-12">
                                            <div class="text-center mt-5">
                                                <button type="submit" class="theme-btn m-2 submit-btn">{{ __('Update') }}</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="invoice" role="tabpanel" aria-labelledby="invoice-tab">
                            <div class="row g-3 m-2">
                                <form action="{{ route('business.invoice.update') }}" method="post"
                                    class="invoice_form">
                                    @csrf

                                    <div class="row justify-content-center">
                                        {{-- A4 Option --}}
                                        <div class="col-lg-5 mb-4 text-center">
                                            <label class="invoice-option position-relative d-inline-block">
                                                <div class="invoice-size-radio">
                                                    <div class="custom-radio">
                                                        <input type="radio" name="invoice_size" value="a4" {{ data_get($invoice_setting, 'value') == 'a4' ? 'checked' : 'checked' }}>
                                                        <span class="checkmark"></span>
                                                    </div>
                                                </div>
                                                <div class="mb-2">{{ __('Printer A4') }}</div>
                                                <img src="{{ asset('assets/images/logo/a4-invoice.svg') }}"
                                                    alt="A4 Invoice" class="img-fluid border rounded invoice-image ">
                                            </label>
                                        </div>

                                        @if (moduleCheck('ThermalPrinterAddon'))
                                        <div class="col-lg-3 mb-4 text-center">
                                            <label class="invoice-option position-relative d-inline-block">
                                                <div class="invoice-size-radio">
                                                    <div class="custom-radio">
                                                        <input type="radio" name="invoice_size" value="3_inch_80mm" {{ data_get($invoice_setting, 'value') == '3_inch_80mm' ? 'checked' : '' }}>
                                                        <span class="checkmark"></span>
                                                    </div>
                                                </div>
                                                <div class="mb-2">{{ __('Thermal: 3 inch 80mm') }}</div>
                                                <img src="{{ asset('assets/images/logo/3-inch-size-invoice.svg') }}" alt="Invoice" class="img-fluid border rounded invoice-image">
                                            </label>
                                        </div>
                                        <div class="col-lg-4 mb-4"></div>
                                        @endif
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="role" role="tabpanel" aria-labelledby="invoice-tab">
                            <div>
                                <div class="settings-box-container">
                                    <a href="{{ route('business.roles.index') }}" class="text-decoration-none text-dark">
                                        <div class="setting-box">
                                            <div class="d-flex align-items-center jusitfy-content-center gap-3">
                                                <div class="settings-icon">
                                                    <svg width="24" height="24" viewBox="0 0 24 24"
                                                        fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path
                                                            d="M11.5 14.0116C9.45338 13.9164 7.38334 14.4064 5.57757 15.4816C4.1628 16.324 0.453366 18.0441 2.71266 20.1966C3.81631 21.248 5.04549 22 6.59087 22H12"
                                                            stroke="#C52127" stroke-width="1.5" stroke-linecap="round"
                                                            stroke-linejoin="round" />
                                                        <path
                                                            d="M15.5 6.5C15.5 8.98528 13.4853 11 11 11C8.51472 11 6.5 8.98528 6.5 6.5C6.5 4.01472 8.51472 2 11 2C13.4853 2 15.5 4.01472 15.5 6.5Z"
                                                            stroke="#C52127" stroke-width="1.5" />
                                                        <path
                                                            d="M18 20.7143V22M18 20.7143C16.8432 20.7143 15.8241 20.1461 15.2263 19.2833M18 20.7143C19.1568 20.7143 20.1759 20.1461 20.7737 19.2833M15.2263 19.2833L14.0004 20.0714M15.2263 19.2833C14.8728 18.773 14.6667 18.1597 14.6667 17.5C14.6667 16.8403 14.8727 16.2271 15.2262 15.7169M20.7737 19.2833L21.9996 20.0714M20.7737 19.2833C21.1272 18.773 21.3333 18.1597 21.3333 17.5C21.3333 16.8403 21.1273 16.2271 20.7738 15.7169M18 14.2857C19.1569 14.2857 20.1761 14.854 20.7738 15.7169M18 14.2857C16.8431 14.2857 15.8239 14.854 15.2262 15.7169M18 14.2857V13M20.7738 15.7169L22 14.9286M15.2262 15.7169L14 14.9286"
                                                            stroke="#C52127" stroke-width="1.5" stroke-linecap="round" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h6>User Role</h6>
                                                    <small class="text-muted d-block">Add new users, Provide role and
                                                        Permission</small>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="Currencies" role="tabpanel" aria-labelledby="invoice-tab">
                            <div>
                                <div class="settings-box-container">
                                     <a href="{{ route('business.currencies.index') }}"
                                        class="text-decoration-none text-dark">
                                        <div class="setting-box">
                                            <div class="d-flex align-items-center jusitfy-content-center gap-3">
                                                <div class="settings-icon">
                                                    <svg width="25" height="24" viewBox="0 0 25 24" fill="none"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <path
                                                            d="M3.16602 12C3.16602 7.77027 3.16602 5.6554 4.36399 4.25276C4.5341 4.05358 4.7196 3.86808 4.91878 3.69797C6.32142 2.5 8.43629 2.5 12.666 2.5C16.8957 2.5 19.0106 2.5 20.4132 3.69797C20.6124 3.86808 20.7979 4.05358 20.968 4.25276C22.166 5.6554 22.166 7.77027 22.166 12C22.166 16.2297 22.166 18.3446 20.968 19.7472C20.7979 19.9464 20.6124 20.1319 20.4132 20.302C19.0106 21.5 16.8957 21.5 12.666 21.5C8.43629 21.5 6.32142 21.5 4.91878 20.302C4.7196 20.1319 4.5341 19.9464 4.36399 19.7472C3.16602 18.3446 3.16602 16.2297 3.16602 12Z"
                                                            stroke="#C52127" stroke-width="1.5" />
                                                        <path
                                                            d="M15.3762 10.063C15.2771 9.30039 14.4014 8.06817 12.8268 8.06814C10.9972 8.06811 10.2274 9.08141 10.0712 9.58806C9.82746 10.2657 9.8762 11.659 12.0207 11.8109C14.7014 12.0009 15.7753 12.3174 15.6387 13.958C15.502 15.5985 14.0077 15.953 12.8268 15.9149C11.6458 15.877 9.71365 15.3344 9.63867 13.8752M12.6394 7V8.07177M12.6394 15.9051V16.9999"
                                                            stroke="#C52127" stroke-width="1.5" stroke-linecap="round" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h6>Currencies</h6>
                                                    <small class="text-muted d-block">View and update currency
                                                        settings</small>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="Payment" role="tabpanel" aria-labelledby="invoice-tab">
                            <div>
                                <div class="settings-box-container">
                                         <a href="{{ route('business.payment-types.index') }}"
                                        class="text-decoration-none text-dark">
                                        <div class="setting-box">
                                            <div class="d-flex align-items-center jusitfy-content-center gap-3">
                                                <div class="settings-icon">
                                                    <svg width="24" height="24" viewBox="0 0 24 24"
                                                        fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path
                                                            d="M13.5 15H6C4.11438 15 3.17157 15 2.58579 14.4142C2 13.8284 2 12.8856 2 11V7C2 5.11438 2 4.17157 2.58579 3.58579C3.17157 3 4.11438 3 6 3H18C19.8856 3 20.8284 3 21.4142 3.58579C22 4.17157 22 5.11438 22 7V12C22 12.9319 22 13.3978 21.8478 13.7654C21.6448 14.2554 21.2554 14.6448 20.7654 14.8478C20.3978 15 19.9319 15 19 15"
                                                            stroke="#C52127" stroke-width="1.5" stroke-linecap="round"
                                                            stroke-linejoin="round" />
                                                        <path
                                                            d="M14 9C14 10.1045 13.1046 11 12 11C10.8954 11 10 10.1045 10 9C10 7.89543 10.8954 7 12 7C13.1046 7 14 7.89543 14 9Z"
                                                            stroke="#C52127" stroke-width="1.5" stroke-linecap="round"
                                                            stroke-linejoin="round" />
                                                        <path
                                                            d="M13 17C13 15.3431 14.3431 14 16 14V12C16 10.3431 17.3431 9 19 9V14.5C19 16.8346 19 18.0019 18.5277 18.8856C18.1548 19.5833 17.5833 20.1548 16.8856 20.5277C16.0019 21 14.8346 21 12.5 21H12C10.1362 21 9.20435 21 8.46927 20.6955C7.48915 20.2895 6.71046 19.5108 6.30448 18.5307C6 17.7956 6 16.8638 6 15"
                                                            stroke="#C52127" stroke-width="1.5" stroke-linecap="round"
                                                            stroke-linejoin="round" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h6>Payment Type</h6>
                                                    <small class="text-muted d-block">Manage payment method for
                                                        purchase.</small>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

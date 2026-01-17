@extends('layouts.master')

@section('title', 'Create Subscription Plan')

@section('main_content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-8 offset-lg-2">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Create New Plan</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.saas.plans.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <!-- Basic Info -->
                            <div class="col-12">
                                <h5 class="mb-3">Plan Information</h5>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="subscriptionName" class="form-label">Plan Name *</label>
                                <input type="text" class="form-control @error('subscriptionName') is-invalid @enderror" 
                                       id="subscriptionName" name="subscriptionName" required value="{{ old('subscriptionName') }}"
                                       placeholder="e.g., Basic, Standard, Premium">
                                @error('subscriptionName')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="duration" class="form-label">Duration (Days) *</label>
                                <input type="number" class="form-control @error('duration') is-invalid @enderror" 
                                       id="duration" name="duration" required value="{{ old('duration', 30) }}" min="1">
                                @error('duration')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Pricing -->
                            <div class="col-12 mt-3">
                                <h5 class="mb-3">Pricing</h5>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="subscriptionPrice" class="form-label">Price (₹) *</label>
                                <input type="number" class="form-control @error('subscriptionPrice') is-invalid @enderror" 
                                       id="subscriptionPrice" name="subscriptionPrice" required value="{{ old('subscriptionPrice') }}" 
                                       step="0.01" min="0">
                                @error('subscriptionPrice')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="offerPrice" class="form-label">Offer Price (₹)</label>
                                <input type="number" class="form-control" id="offerPrice" name="offerPrice" 
                                       value="{{ old('offerPrice') }}" step="0.01" min="0">
                            </div>

                            <!-- Limits -->
                            <div class="col-12 mt-3">
                                <h5 class="mb-3">Usage Limits</h5>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="max_users" class="form-label">Max Users *</label>
                                <input type="number" class="form-control @error('max_users') is-invalid @enderror" 
                                       id="max_users" name="max_users" required value="{{ old('max_users', 1) }}" min="1">
                                @error('max_users')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="max_invoices_per_month" class="form-label">Max Invoices/Month *</label>
                                <input type="number" class="form-control @error('max_invoices_per_month') is-invalid @enderror" 
                                       id="max_invoices_per_month" name="max_invoices_per_month" required 
                                       value="{{ old('max_invoices_per_month', 100) }}" 
                                       placeholder="Use -1 for unlimited">
                                <small class="text-muted">Use -1 for unlimited</small>
                                @error('max_invoices_per_month')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Features -->
                            <div class="col-12 mt-3">
                                <h5 class="mb-3">Features</h5>
                            </div>

                            <div class="col-12">
                                <div class="form-check mb-2">
                                    <input type="checkbox" class="form-check-input" id="pos_enabled" name="pos_enabled" 
                                           value="1" {{ old('pos_enabled') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="pos_enabled">
                                        Enable POS Billing
                                    </label>
                                </div>

                                <div class="form-check mb-2">
                                    <input type="checkbox" class="form-check-input" id="gst_reports_enabled" 
                                           name="gst_reports_enabled" value="1" {{ old('gst_reports_enabled') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="gst_reports_enabled">
                                        Enable GST Reports
                                    </label>
                                </div>

                                <div class="form-check mb-2">
                                    <input type="checkbox" class="form-check-input" id="whatsapp_integration_enabled" 
                                           name="whatsapp_integration_enabled" value="1" {{ old('whatsapp_integration_enabled') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="whatsapp_integration_enabled">
                                        Enable WhatsApp Integration
                                    </label>
                                </div>

                                <div class="form-check mb-2">
                                    <input type="checkbox" class="form-check-input" id="mobile_app_access" 
                                           name="mobile_app_access" value="1" {{ old('mobile_app_access', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="mobile_app_access">
                                        Mobile App Access
                                    </label>
                                </div>

                                <div class="form-check mb-2">
                                    <input type="checkbox" class="form-check-input" id="multi_branch_enabled" 
                                           name="multi_branch_enabled" value="1" {{ old('multi_branch_enabled') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="multi_branch_enabled">
                                        Enable Multi-branch
                                    </label>
                                </div>
                            </div>

                            <div class="col-12 mt-3 mb-3">
                                <label for="features" class="form-label">Additional Features (JSON)</label>
                                <textarea class="form-control" id="features" name="features" rows="4" 
                                          placeholder='{"advanced_analytics": true, "api_access": true}'>{{ old('features') }}</textarea>
                            </div>

                            <!-- Submit -->
                            <div class="col-12 mt-4">
                                <a href="{{ route('admin.saas.plans.index') }}" class="btn btn-secondary">
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Create Plan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.master')

@section('title', 'Create New Store')

@section('main_content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-8 offset-lg-2">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Create New Store</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.saas.stores.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <!-- Store Information -->
                            <div class="col-12">
                                <h5 class="mb-3">Store Information</h5>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="companyName" class="form-label">Store Name *</label>
                                <input type="text" class="form-control @error('companyName') is-invalid @enderror" 
                                       id="companyName" name="companyName" required value="{{ old('companyName') }}">
                                @error('companyName')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="business_category_id" class="form-label">Category *</label>
                                <select class="form-control @error('business_category_id') is-invalid @enderror" 
                                        id="business_category_id" name="business_category_id" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ old('business_category_id') == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('business_category_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="phoneNumber" class="form-label">Phone Number *</label>
                                <input type="tel" class="form-control @error('phoneNumber') is-invalid @enderror" 
                                       id="phoneNumber" name="phoneNumber" required value="{{ old('phoneNumber') }}">
                                @error('phoneNumber')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="address" class="form-label">Address</label>
                                <input type="text" class="form-control" id="address" name="address" 
                                       value="{{ old('address') }}">
                            </div>

                            <!-- Plan Selection -->
                            <div class="col-12 mt-4">
                                <h5 class="mb-3">Subscription Plan</h5>
                            </div>

                            <div class="col-12 mb-3">
                                <label for="plan_id" class="form-label">Select Plan *</label>
                                <select class="form-control @error('plan_id') is-invalid @enderror" 
                                        id="plan_id" name="plan_id" required>
                                    <option value="">Select a Plan</option>
                                    @foreach($plans as $plan)
                                        <option value="{{ $plan->id }}" {{ old('plan_id') == $plan->id ? 'selected' : '' }}>
                                            {{ $plan->subscriptionName }} - ₹{{ $plan->subscriptionPrice }}/month
                                        </option>
                                    @endforeach
                                </select>
                                @error('plan_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Owner Information -->
                            <div class="col-12 mt-4">
                                <h5 class="mb-3">Store Owner Information</h5>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="owner_name" class="form-label">Owner Name *</label>
                                <input type="text" class="form-control @error('owner_name') is-invalid @enderror" 
                                       id="owner_name" name="owner_name" required value="{{ old('owner_name') }}">
                                @error('owner_name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="owner_email" class="form-label">Owner Email *</label>
                                <input type="email" class="form-control @error('owner_email') is-invalid @enderror" 
                                       id="owner_email" name="owner_email" required value="{{ old('owner_email') }}">
                                @error('owner_email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="owner_phone" class="form-label">Owner Phone *</label>
                                <input type="tel" class="form-control @error('owner_phone') is-invalid @enderror" 
                                       id="owner_phone" name="owner_phone" required value="{{ old('owner_phone') }}">
                                @error('owner_phone')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Submit -->
                            <div class="col-12 mt-4">
                                <a href="{{ route('admin.saas.stores.index') }}" class="btn btn-secondary">
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Create Store
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

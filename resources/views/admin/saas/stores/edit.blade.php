@extends('layouts.master')

@section('title', 'Edit Store')

@section('main_content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-8 offset-lg-2">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Edit Store</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.saas.stores.update', $business->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="companyName" class="form-label">Store Name *</label>
                                <input type="text" class="form-control @error('companyName') is-invalid @enderror" 
                                       id="companyName" name="companyName" required value="{{ $business->companyName }}">
                                @error('companyName')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="store_slug" class="form-label">Store Slug *</label>
                                <input type="text" class="form-control @error('store_slug') is-invalid @enderror" 
                                       id="store_slug" name="store_slug" required value="{{ $business->store_slug }}"
                                       placeholder="e.g., rajesh-medicals">
                                <small class="text-muted">Used in store URL: tryonedigital.com/store/{{ $business->store_slug }}</small>
                                @error('store_slug')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="phoneNumber" class="form-label">Phone Number *</label>
                                <input type="tel" class="form-control @error('phoneNumber') is-invalid @enderror" 
                                       id="phoneNumber" name="phoneNumber" required value="{{ $business->phoneNumber }}">
                                @error('phoneNumber')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="address" class="form-label">Address</label>
                                <input type="text" class="form-control" id="address" name="address" 
                                       value="{{ $business->address }}">
                            </div>

                            <div class="col-12 mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-control" id="status" name="status" required>
                                    <option value="1" {{ $business->status == 1 ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ $business->status == 0 ? 'selected' : '' }}>Suspended</option>
                                </select>
                            </div>

                            <div class="col-12 mt-4">
                                <a href="{{ route('admin.saas.stores.show', $business->id) }}" class="btn btn-secondary">
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Changes
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

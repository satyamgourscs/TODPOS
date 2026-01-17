@extends('layouts.master')

@section('title', 'Store Details')

@section('main_content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Store Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Basic Info</h6>
                            <p><strong>Store Name:</strong> {{ $business->companyName }}</p>
                            <p><strong>Store Slug:</strong> <code>{{ $business->store_slug }}</code></p>
                            <p><strong>Category:</strong> {{ $business->category->name ?? 'N/A' }}</p>
                            <p><strong>Phone:</strong> {{ $business->phoneNumber }}</p>
                            <p><strong>Address:</strong> {{ $business->address ?? 'N/A' }}</p>
                            <p><strong>Status:</strong> 
                                <span class="badge badge-{{ $business->status ? 'success' : 'danger' }}">
                                    {{ $business->status ? 'Active' : 'Inactive' }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6>Subscription Info</h6>
                            <p><strong>Plan:</strong> {{ $business->enrolled_plan?->plan->subscriptionName ?? 'None' }}</p>
                            <p><strong>Plan Price:</strong> ₹{{ $business->enrolled_plan?->price ?? 'N/A' }}</p>
                            <p><strong>Subscription Date:</strong> {{ $business->subscriptionDate?->format('M d, Y') ?? 'N/A' }}</p>
                            <p><strong>Expires:</strong> 
                                @if($business->will_expire)
                                    @if($stats['is_expired'])
                                        <span class="badge badge-danger">Expired on {{ $business->will_expire->format('M d, Y') }}</span>
                                    @else
                                        <span class="badge badge-warning">{{ $business->will_expire->format('M d, Y') }}</span>
                                    @endif
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Actions</h6>
                </div>
                <div class="card-body">
                    <a href="{{ route('admin.saas.stores.edit', $business->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit Store
                    </a>
                    
                    <form action="{{ route('admin.saas.stores.toggle-status', $business->id) }}" method="POST" style="display:inline-block;">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-{{ $business->status ? 'danger' : 'success' }}">
                            <i class="fas fa-power-off"></i> {{ $business->status ? 'Suspend' : 'Activate' }}
                        </button>
                    </form>

                    <button class="btn btn-info" data-toggle="modal" data-target="#upgradePlanModal">
                        <i class="fas fa-arrow-up"></i> Upgrade Plan
                    </button>

                    <a href="{{ route('admin.saas.stores.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Statistics -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <p class="text-muted">Total Users</p>
                        <h4>{{ $stats['total_users'] }}</h4>
                    </div>
                    <div class="mb-3">
                        <p class="text-muted">Total Invoices</p>
                        <h4>{{ $stats['total_invoices'] }}</h4>
                    </div>
                    <div class="mb-3">
                        <p class="text-muted">Active Plan</p>
                        <h5>{{ $stats['active_plan'] ?? 'N/A' }}</h5>
                    </div>
                </div>
            </div>

            <!-- Store Users -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Store Users</h6>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        @forelse($business->user as $user)
                        <a href="#" class="list-group-item list-group-item-action">
                            <strong>{{ $user->name }}</strong>
                            <br>
                            <small class="text-muted">{{ $user->email }}</small>
                            <br>
                            <span class="badge badge-primary">{{ ucfirst($user->role_type) }}</span>
                            <span class="badge badge-{{ $user->is_active ? 'success' : 'danger' }}">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </a>
                        @empty
                        <p class="text-muted text-center py-3">No users found</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upgrade Plan Modal -->
<div class="modal fade" id="upgradePlanModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upgrade Plan</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.saas.stores.upgrade-plan', $business->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="plan_id">Select New Plan</label>
                        <select class="form-control" id="plan_id" name="plan_id" required>
                            <option value="">Choose a plan</option>
                            @foreach(\App\Models\Plan::where('status', 1)->get() as $plan)
                            <option value="{{ $plan->id }}">
                                {{ $plan->subscriptionName }} - ₹{{ $plan->subscriptionPrice }}/month
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upgrade</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

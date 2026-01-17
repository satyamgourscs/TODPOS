@extends('layouts.master')

@section('title', 'Subscription Plans')

@section('main_content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Subscription Plans</h6>
                    <a href="{{ route('admin.saas.plans.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add Plan
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        @forelse($plans as $plan)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card plan-card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">{{ $plan->subscriptionName }}</h5>
                                    
                                    <div class="price mb-3">
                                        <h3 class="text-primary">₹{{ $plan->subscriptionPrice }}</h3>
                                        <small class="text-muted">per month ({{ $plan->duration }} days)</small>
                                    </div>

                                    <ul class="list-unstyled mb-3">
                                        <li><i class="fas fa-check text-success"></i> Max Users: {{ $plan->max_users }}</li>
                                        <li><i class="fas fa-check text-success"></i> Invoices: 
                                            @if($plan->max_invoices_per_month == -1)
                                                Unlimited
                                            @else
                                                {{ $plan->max_invoices_per_month }}/month
                                            @endif
                                        </li>
                                        <li>
                                            <i class="fas fa-{{ $plan->pos_enabled ? 'check text-success' : 'times text-danger' }}"></i>
                                            POS Enabled
                                        </li>
                                        <li>
                                            <i class="fas fa-{{ $plan->gst_reports_enabled ? 'check text-success' : 'times text-danger' }}"></i>
                                            GST Reports
                                        </li>
                                        <li>
                                            <i class="fas fa-{{ $plan->whatsapp_integration_enabled ? 'check text-success' : 'times text-danger' }}"></i>
                                            WhatsApp Integration
                                        </li>
                                        <li>
                                            <i class="fas fa-{{ $plan->multi_branch_enabled ? 'check text-success' : 'times text-danger' }}"></i>
                                            Multi-branch
                                        </li>
                                    </ul>

                                    <div class="d-flex gap-2">
                                        <a href="{{ route('admin.saas.plans.edit', $plan->id) }}" class="btn btn-sm btn-warning flex-grow-1">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <form action="{{ route('admin.saas.plans.toggle-status', $plan->id) }}" method="POST" style="display:inline-block;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-{{ $plan->status ? 'danger' : 'success' }} flex-grow-1">
                                                {{ $plan->status ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>
                                    </div>

                                    <form action="{{ route('admin.saas.plans.destroy', $plan->id) }}" method="POST" 
                                          class="mt-2" onsubmit="return confirm('Are you sure?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger w-100">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12">
                            <p class="text-center text-muted py-4">
                                No plans found. <a href="{{ route('admin.saas.plans.create') }}">Create one</a>
                            </p>
                        </div>
                        @endforelse
                    </div>

                    @if($plans->hasPages())
                    <div class="mt-4">
                        {{ $plans->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .plan-card {
        border: 1px solid #e3e6f0;
        transition: all 0.3s ease;
    }
    .plan-card:hover {
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        transform: translateY(-5px);
    }
</style>
@endsection

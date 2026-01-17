@extends('layouts.master')

@section('title', 'Manage Stores')

@section('main_content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">All Stores</h6>
                    <a href="{{ route('admin.saas.stores.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add Store
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Store Name</th>
                                    <th>Owner</th>
                                    <th>Plan</th>
                                    <th>Status</th>
                                    <th>Expires</th>
                                    <th>Users</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stores as $store)
                                <tr>
                                    <td>
                                        <strong>{{ $store->companyName }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $store->store_slug ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        @php
                                            $owner = $store->user->first();
                                        @endphp
                                        {{ $owner?->name ?? 'N/A' }}
                                    </td>
                                    <td>
                                        {{ $store->enrolled_plan?->plan->subscriptionName ?? 'Free' }}
                                    </td>
                                    <td>
                                        @if($store->status)
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($store->will_expire)
                                            @if(now()->isAfter($store->will_expire))
                                                <span class="badge badge-danger">Expired</span>
                                            @else
                                                <span class="badge badge-warning">
                                                    {{ $store->will_expire->format('M d, Y') }}
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-info">
                                            {{ $store->user->count() }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.saas.stores.show', $store->id) }}" 
                                               class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.saas.stores.edit', $store->id) }}" 
                                               class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.saas.stores.destroy', $store->id) }}" 
                                                  method="POST" 
                                                  style="display:inline-block;"
                                                  onsubmit="return confirm('Are you sure?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        No stores found. <a href="{{ route('admin.saas.stores.create') }}">Create one</a>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $stores->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

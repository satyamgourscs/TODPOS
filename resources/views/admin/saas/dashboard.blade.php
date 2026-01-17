@extends('layouts.master')

@section('title', 'Super Admin Dashboard')

@section('main_content')
<div class="container-fluid py-4">
    <!-- Stats Row -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-primary shadow h-100">
                <div class="card-body">
                    <div class="text-primary font-weight-bold text-uppercase mb-1">
                        Total Stores
                    </div>
                    <div class="h3 mb-0">{{ $stats['total_stores'] }}</div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-success shadow h-100">
                <div class="card-body">
                    <div class="text-success font-weight-bold text-uppercase mb-1">
                        Active Subscriptions
                    </div>
                    <div class="h3 mb-0">{{ $stats['active_subscriptions'] }}</div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-warning shadow h-100">
                <div class="card-body">
                    <div class="text-warning font-weight-bold text-uppercase mb-1">
                        Monthly Revenue
                    </div>
                    <div class="h3 mb-0">₹{{ number_format($stats['monthly_revenue'], 2) }}</div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-danger shadow h-100">
                <div class="card-body">
                    <div class="text-danger font-weight-bold text-uppercase mb-1">
                        Expiring Soon
                    </div>
                    <div class="h3 mb-0">{{ $stats['expiring_soon'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Monthly Revenue Trend</h6>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Store Status</h6>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <p><strong>Active Stores:</strong> {{ $stats['active_stores_count'] }}</p>
                        <p><strong>Total Users:</strong> {{ $stats['total_users'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Stores -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Stores</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Store Name</th>
                                    <th>Plan</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentStores as $store)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.saas.stores.show', $store->id) }}">
                                            {{ $store->companyName }}
                                        </a>
                                    </td>
                                    <td>{{ $store->enrolled_plan?->plan->subscriptionName ?? 'N/A' }}</td>
                                    <td>{{ $store->created_at->format('M d, Y') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No stores yet</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expiring Subscriptions -->
        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">Expiring Subscriptions</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Store Name</th>
                                    <th>Expires On</th>
                                    <th>Days Left</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expiringSubscriptions as $store)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.saas.stores.show', $store->id) }}">
                                            {{ $store->companyName }}
                                        </a>
                                    </td>
                                    <td>{{ $store->will_expire->format('M d, Y') }}</td>
                                    <td>
                                        <span class="badge badge-warning">
                                            {{ $store->will_expire->diffInDays(now()) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No expiring subscriptions</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <a href="{{ route('admin.saas.stores.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Store
                    </a>
                    <a href="{{ route('admin.saas.plans.index') }}" class="btn btn-secondary">
                        <i class="fas fa-list"></i> Manage Plans
                    </a>
                    <a href="{{ route('admin.saas.stores.index') }}" class="btn btn-info">
                        <i class="fas fa-building"></i> View All Stores
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    // Revenue Chart
    const ctx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($revenueData['months']) !!},
            datasets: [{
                label: 'Revenue (₹)',
                data: {!! json_encode($revenueData['revenues']) !!},
                borderColor: '#4e73df',
                backgroundColor: 'rgba(78, 115, 223, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
@endpush

@endsection

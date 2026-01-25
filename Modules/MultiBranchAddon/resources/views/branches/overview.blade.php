@extends('business::layouts.master')

@section('title')
    {{ __('Overview') }}
@endsection

@section('main_content')
    <div class="container-fluid m-h-100">
        <div class="row gpt-dashboard-chart mb-30">
            <div class="col-md-12 col-lg-12 col-xl-8">
                <div class="card new-card dashboard-card border-0 p-0">
                    <div class="dashboard-chart">
                        <h4>{{ __('Revenue Statistic') }}</h4>
                        <div class="gpt-up-down-arrow position-relative">
                            <select class="form-control overview-year">
                                @for ($i = date('Y'); $i >= 2022; $i--)
                                    <option @selected($i == date('Y')) value="{{ $i }}">{{ $i }}
                                    </option>
                                @endfor
                            </select>
                            <span></span>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="d-flex align-items-center justify-content-center gap-3 pb-2">
                            <div class="d-flex align-items-center gap-1">
                                <div class="income-bulet2"></div>
                                <p>{{__('Income')}}: <strong class="profit-value">0</strong></p>
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <div class="expense-bulet2"></div>
                                <p>{{__('Expense')}}: <strong class="loss-value">0</strong></p>
                            </div>
                        </div>
                        <div class="content">
                            <canvas id="branchRevenueChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 col-lg-12 col-xl-4">
                <div class="card new-card dashboard-card border-0 p-0 statement-container">
                    <div class="dashboard-chart">
                        <h4>{{ __('Statement') }}</h4>
                        <div class="gpt-up-down-arrow position-relative">
                            <select class="form-control overview-loss-profit-year">
                                @for ($i = date('Y'); $i >= 2022; $i--)
                                    <option @selected($i == date('Y')) value="{{ $i }}">{{ $i }}
                                    </option>
                                @endfor
                            </select>
                            <span></span>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="profit-loss-content">
                            <canvas id="profitLossChart"></canvas>
                        </div>
                           <div class="d-flex align-items-center justify-content-center gap-3 pb-2 mt-4">
                            <div class="d-flex align-items-center gap-1">
                                <div class="income-bulet"></div>
                                <p>{{__('Profit')}}: <strong class="profit">0</strong></p>
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <div class="expense-bulet"></div>
                                <p>{{__('Loss')}}: <strong class="loss">0</strong></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

         <div class="row gpt-dashboard-chart mb-30">

            <div class="col-md-12 col-lg-12 col-xl-8">
                <div class="card new-card dashboard-card border-0 p-0">
                    <div class="dashboard-chart">
                        <h4>{{ __('Branch Wise Sales') }}</h4>
                        <div class="gpt-up-down-arrow position-relative">
                            <select class="form-control branch-wise-sales-year">
                                @for ($i = date('Y'); $i >= 2022; $i--)
                                    <option @selected($i == date('Y')) value="{{ $i }}">{{ $i }}
                                    </option>
                                @endfor
                            </select>
                            <span></span>
                        </div>
                    </div>

                    <div class="responsive-table branch-overview-table vatlist-body mt-0">
                        <table class="table" id="datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('SL') }}.</th>
                                    <th>{{ __('Branch') }}</th>
                                    <th>{{ __('Total Sales') }}</th>
                                    <th>{{ __('Paid') }}</th>
                                    <th>{{ __('Due') }}</th>
                                </tr>
                            </thead>
                            <tbody id="sale-data">
                                {{-- Dynamic data come --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

             <div class="col-md-12 col-lg-12 col-xl-4">
                <div class="card new-card dashboard-card border-0 statement-container p-0">
                    <div class="dashboard-chart mb-1">
                        <h4>{{ __('Expire Product') }}</h4>
                    </div>
                    <div class="responsive-table branch-overview-table vatlist-body mt-0">
                        <table class="table" id="datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('SL') }}.</th>
                                    <th>{{ __('Branch') }}</th>
                                    <th>{{ __('Qty') }}</th>
                                </tr>
                            </thead>
                            <tbody id="vat-data">
                                @foreach ($branches_expired_products as $branch)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $branch->name }}</td>
                                    <td><div class="text-primary">{{ $branch->expired_stocks_count }}</div></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

           <div class="row gpt-dashboard-chart">
            <div class="col-md-12 col-lg-12 col-xl-8">
                <div class="card new-card dashboard-card border-0 p-0">
                    <div class="dashboard-chart">
                        <h4>{{ __('Branch Wise Purchases') }}</h4>
                        <div class="gpt-up-down-arrow position-relative">
                            <select class="form-control batch-wise-purchases-year">
                                @for ($i = date('Y'); $i >= 2022; $i--)
                                    <option @selected($i == date('Y')) value="{{ $i }}">{{ $i }}
                                    </option>
                                @endfor
                            </select>
                            <span></span>
                        </div>
                    </div>

                    <div class="responsive-table branch-overview-table vatlist-body mt-0">
                        <table class="table" id="datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('SL') }}.</th>
                                    <th>{{ __('Branch') }}</th>
                                    <th>{{ __('Total Purchase') }}</th>
                                    <th>{{ __('Paid') }}</th>
                                    <th>{{ __('Due') }}</th>
                                </tr>
                            </thead>
                            <tbody id="purchase-data">
                                {{-- Dynamic data --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
             <div class="col-md-12 col-lg-12 col-xl-4">
                <div class="card new-card dashboard-card statement-container border-0 p-0">
                    <div class="dashboard-chart">
                        <h4>{{ __('Employee Overview') }}</h4>
                    </div>
                    <div class="responsive-table branch-overview-table vatlist-body mt-0">
                        <table class="table" id="datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('SL') }}.</th>
                                    <th>{{ __('Branch') }}</th>
                                    <th>{{ __('Staffs') }}</th>
                                </tr>
                            </thead>
                            <tbody id="vat-data">
                                @foreach ($branches as $branch)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $branch->name }}</td>
                                        <td>{{ $branch->employees_count }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
        $currency = business_currency();
    @endphp
    <input type="hidden" id="currency_symbol" value="{{ $currency->symbol }}">
    <input type="hidden" id="currency_position" value="{{ $currency->position }}">
    <input type="hidden" id="currency_code" value="{{ $currency->code }}">

    <input type="hidden" value="{{ route('multibranch.charts.income-expense') }}" id="incomeExpenseRoute">
    <input type="hidden" value="{{ route('multibranch.charts.sale-purchase') }}" id="salePurchaseChartRoute">
    <input type="hidden" value="{{ route('multibranch.branch.wise.sales') }}" id="branchWiseSaleRoute">
    <input type="hidden" value="{{ route('multibranch.branch.wise.purchases') }}" id="branchWisePurchaseRoute">
@endsection


@push('js')
    <script src="{{ asset('assets/js/chart.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/custom/branch-overview.js') }}"></script>
@endpush

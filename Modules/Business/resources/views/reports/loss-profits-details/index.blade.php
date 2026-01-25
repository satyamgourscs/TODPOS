@extends('layouts.business.master')

@section('title')
    {{ __('Loss Profit Reports') }}
@endsection

@section('main_content')
    <div class="erp-table-section">
        <div class="container-fluid">
            <div class="card">
                <div class="card-bodys">
                    <div class="table-header p-16 d-print-none">
                        <h4>{{ __('Loss/Profit Report Details') }}</h4>
                    </div>
                    <div class="table-top-form p-16">
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <div class="m-0 p-0 d-print-none">
                                <form action="{{ route('business.loss-profit.details.reports.filter') }}" method="post" class="report-filter-form">
                                    @csrf
                                    <div class="date-filters-container">
                                        <div class="input-wrapper align-items-center date-filters d-none">
                                            <label class="header-label">{{ __('From Date') }}</label>
                                            <input type="date" name="from_date" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" class="form-control">
                                        </div>
                                        <div class="input-wrapper align-items-center date-filters d-none">
                                            <label class="header-label">{{ __('To Date') }}</label>
                                            <input type="date" name="to_date" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" class="form-control">
                                        </div>
                                        <div class="gpt-up-down-arrow position-relative d-print-none custom-date-filter">
                                            <select name="custom_days" class="form-control custom-days">
                                                <option value="today">{{__('Today')}}</option>
                                                <option value="yesterday">{{__('Yesterday')}}</option>
                                                <option value="last_seven_days">{{__('Last 7 Days')}}</option>
                                                <option value="last_thirty_days">{{__('Last 30 Days')}}</option>
                                                <option value="current_month">{{__('Current Month')}}</option>
                                                <option value="last_month">{{__('Last Month')}}</option>
                                                <option value="current_year">{{__('Current Year')}}</option>
                                                <option value="custom_date">{{__('Custom Date')}}</option>
                                            </select>
                                            <span></span>
                                            <div class="calendar-icon">
                                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M12.6667 2.66797H3.33333C2.59695 2.66797 2 3.26492 2 4.0013V13.3346C2 14.071 2.59695 14.668 3.33333 14.668H12.6667C13.403 14.668 14 14.071 14 13.3346V4.0013C14 3.26492 13.403 2.66797 12.6667 2.66797Z" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M10.666 1.33203V3.9987" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M5.33398 1.33203V3.9987" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M2 6.66797H14" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="container my-4">
                    <div class="row g-4">
                        {{-- Left Column--}}
                        <div class="col-md-6 table-responsive">
                            <table class="table table-striped bg-white border rounded">
                                <tbody>
                                    <tr>
                                        <td class="text-start">Opening Stock</td>
                                        <td id="opening_stock_by_purchase">{{ $opening_stock_by_purchase }}</td>
                                    </tr>
                                    <tr>
                                        <td  class="text-start" colspan="2">(By purchase price)</td>
                                    </tr>
                                    <tr>
                                        <td  class="text-start">Opening Stock</td>
                                        <td id="opening_stock_by_sale">{{ $opening_stock_by_sale }}</td>
                                    </tr>
                                    <tr>
                                        <td  class="text-start" colspan="2">(By sale price)</td>
                                    </tr>
                                    <tr>
                                        <td  class="text-start">Total purchase:</td>
                                        <td id="total_purchase_price">{{ $total_purchase_price }}</td>
                                    </tr>
                                    <tr>
                                        <td  class="text-start">Total purchase shipping charge:</td>
                                        <td id="total_purchase_shipping_charge">{{ $total_purchase_shipping_charge }}</td>
                                    </tr>
                                    <tr>
                                        <td  class="text-start">Total Sell discount:</td>
                                        <td id="total_sale_discount">{{ $total_sale_discount }}</td>
                                    </tr>
                                    <tr>
                                        <td  class="text-start">Total Sell Return:</td>
                                        <td id="all_sale_return">{{ $all_sale_return }}</td>
                                    </tr>
                                </tbody>
                            </table>

                        </div>

                        {{-- Right Column--}}
                        <div class="col-md-6 table-responsive">
                            <table class="table table-striped bg-white border rounded">
                                <tbody>
                                    <tr>
                                        <td class='text-start'>Closing stock</td>
                                        <td id="closing_stock_by_purchase">{{ $closing_stock_by_purchase }}</td>
                                    </tr>
                                    <tr>
                                        <td class='text-start' colspan="2">(By purchase price)</td>
                                    </tr>
                                    <tr>
                                        <td class='text-start'>Closing stock</td>
                                        <td id="closing_stock_by_sale">{{ $closing_stock_by_sale }}</td>
                                    </tr>
                                    <tr>
                                        <td class='text-start' colspan="2">(By sale price)</td>
                                    </tr>
                                    <tr>
                                        <td class='text-start'>Total Sales:</td>
                                        <td id="total_sale_price">{{ $total_sale_price }}</td>
                                    </tr>
                                    <tr>
                                        <td class='text-start'>Total sell shipping charge:</td>
                                        <td id="total_sale_shipping_charge">{{ $total_sale_shipping_charge }}</td>
                                    </tr>
                                    <tr>
                                        <td class='text-start'>Total Purchase Return:</td>
                                        <td id="all_purchase_return">{{ $all_purchase_return }}</td>
                                    </tr>
                                    <tr>
                                        <td class='text-start'>Total Purchase discount:</td>
                                        <td id="total_purchase_discount">{{ $total_purchase_discount }}</td>
                                    </tr>
                                    <tr>
                                        <td class='text-start'>Total sell round off:</td>
                                        <td id="total_sale_rounding_off">{{ $total_sale_rounding_off }}</td>
                                    </tr>
                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" value="{{ route('business.loss-profit.details.reports.filter') }}" id="get-loss-profit">

@endsection

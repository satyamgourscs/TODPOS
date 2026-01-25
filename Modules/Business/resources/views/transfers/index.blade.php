@extends('layouts.business.master')

@section('title')
    {{ __('Transfer List') }}
@endsection

@section('main_content')
    <div class="erp-table-section">
        <div class="container-fluid">
            <div class="card ">
                <div class="card-bodys">
                    <div class="table-header p-16 d-print-none">
                        <h4>{{ __('Transfer List') }}</h4>
                        @usercan('transfers.create')
                        <a type="button" href="{{ route('business.transfers.create') }}" class="add-order-btn rounded-2"><i class="fas fa-plus-circle me-1"></i>{{ __('Add new Transfer') }}</a>
                        @endusercan
                    </div>

                    <div class="table-header justify-content-center border-0 text-center d-none d-block d-print-block">
                        @include('business::print.header')
                        <h4 class="mt-2">{{ __('Transfer List') }}</h4>
                    </div>

                    <div class="table-top-form p-16">
                        <form action="{{ route('business.transfers.filter') }}" method="post" class="filter-form"
                            table="#transfer-data">
                            @csrf

                            <div class="table-top-left d-flex gap-3">

                                <div class="gpt-up-down-arrow position-relative d-print-none">
                                    <select name="per_page" class="form-control">
                                        <option value="10">{{ __('Show- 10') }}</option>
                                        <option value="25">{{ __('Show- 25') }}</option>
                                        <option value="50">{{ __('Show- 50') }}</option>
                                        <option value="100">{{ __('Show- 100') }}</option>
                                    </select>
                                    <span></span>
                                </div>

                                @if(auth()->user()->accessToMultiBranch())
                                <div class="table-search position-relative d-print-none">
                                    <div class="gpt-up-down-arrow position-relative">
                                        <select name="branch_id" class="form-control">
                                            <option value="">{{ __('Select Branch') }}</option>
                                            @foreach ($branches as $branch)
                                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                            @endforeach
                                        </select>
                                        <span></span>
                                    </div>
                                </div>
                                @endif


                                <div class="table-search position-relative d-print-none">
                                    <input class="form-control searchInput" type="text" name="search"
                                        placeholder="{{ __('Search...') }}" value="{{ request('search') }}">
                                    <span class="position-absolute">
                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M14.582 14.582L18.332 18.332" stroke="#4D4D4D" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M16.668 9.16797C16.668 5.02584 13.3101 1.66797 9.16797 1.66797C5.02584 1.66797 1.66797 5.02584 1.66797 9.16797C1.66797 13.3101 5.02584 16.668 9.16797 16.668C13.3101 16.668 16.668 13.3101 16.668 9.16797Z" stroke="#4D4D4D" stroke-width="1.25" stroke-linejoin="round"/>
                                            </svg>

                                    </span>
                                </div>
                            </div>
                        </form>

                        <div class="table-top-btn-group d-print-none">
                            <ul>
                                <li>
                                    <a href="{{ route('business.transfers.csv') }}">
                                        <img src="{{ asset('assets/images/logo/csv.svg') }}" alt="">

                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('business.transfers.excel') }}">
                                        <img src="{{ asset('assets/images/logo/excel.svg') }}" alt="">

                                    </a>
                                </li>


                                <li>
                                    <a onclick="window.print()" class="print-window">
                                        <img src="{{ asset('assets/images/logo/printer.svg') }}" alt="">
                                    </a>
                                </li>
                            </ul>
                        </div>

                    </div>
                </div>

                <div class="delete-item delete-show d-none">
                    <div class="delete-item-show">
                        <p class="fw-bold"><span class="selected-count"></span> {{ __('items show') }}</p>
                        <button data-bs-toggle="modal" class="trigger-modal" data-bs-target="#multi-delete-modal" data-url="{{ route('business.transfers.delete-all') }}">{{ __('Delete') }}</button>
                    </div>
                </div>

                <div class="responsive-table m-0">
                    <table class="table" id="datatable">
                        <thead>
                            <tr>
                                @usercan('transfers.delete')
                                <th class="w-60 d-print-none">
                                    <div class="d-flex align-items-center gap-3">
                                        <input type="checkbox" class="select-all-delete multi-delete">
                                    </div>
                                </th>
                                @endusercan
                                <th>{{ __('SL') }}.</th>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Invoice') }}</th>
                                @if(moduleCheck('MultiBranchAddon') && multibranch_active())
                                <th>{{ __('From Branch') }}</th>
                                @endif
                                @if (moduleCheck('WarehouseAddon'))
                                <th>{{ __('From Warehouse') }}</th>
                                @endif
                                @if(moduleCheck('MultiBranchAddon') && multibranch_active())
                                <th>{{ __('To Branch') }}</th>
                                @endif
                                @if (moduleCheck('WarehouseAddon'))
                                <th>{{ __('To Warehouse') }}</th>
                                @endif
                                <th>{{ __('Qty') }}</th>
                                <th>{{ __('Stock Values') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody id="transfer-data">
                            @include('business::transfers.datas')
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $transfers->links('vendor.pagination.bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('modal')
    @include('business::component.delete-modal')
@endpush


@extends('layouts.business.master')

@section('title')
    {{ __('Credit Notes') }}
@endsection

@section('main_content')
    <div class="erp-table-section">
        <div class="container-fluid">
            <div class="card">
                <div class="card-bodys">
                    <div class="table-header p-16">
                        <h4>{{ __('Credit Notes') }}</h4>
                        <a type="button" href="{{ route('business.credit-notes.create') }}" class="add-order-btn rounded-2 active">
                            <i class="fas fa-plus-circle me-1"></i> {{ __('Create Credit Note') }}
                        </a>
                    </div>
                </div>
                <div class="responsive-table mt-3">
                    <table class="table" id="datatable">
                        <thead>
                            <tr>
                                <th>{{ __('SL') }}</th>
                                <th>{{ __('Credit Note Number') }}</th>
                                <th>{{ __('Customer') }}</th>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Amount') }}</th>
                                <th>{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6" class="text-center">{{ __('No credit notes found. Please create models and migrations first.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

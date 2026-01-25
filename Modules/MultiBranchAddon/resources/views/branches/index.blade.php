@extends('business::layouts.master')

@section('title')
    {{ __('Branch List') }}
@endsection

@section('main_content')
    <div class="erp-table-section">
        <div class="container-fluid">
            @if (session('error'))
            <div class="alert alert-dismissible fade show text-center mb-3 text-white gradient-alert" role="alert">
                <b class="text-light">{{__('Note')}}:</b> {{ session('error') }}
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

           <div class="alert-box" id="alertBox">
                <ul class="alert-list">
                <li>
                    <svg width="7" height="7" viewBox="0 0 7 7" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3.50488 6.5957C2.98926 6.5957 2.51838 6.46999 2.09224 6.21857C1.66611 5.96289 1.3252 5.62198 1.06951 5.19584C0.818093 4.76971 0.692383 4.29883 0.692383 3.7832C0.692383 3.26331 0.818093 2.79243 1.06951 2.37056C1.3252 1.94442 1.66611 1.60564 2.09224 1.35422C2.51838 1.09854 2.98926 0.970703 3.50488 0.970703C4.02477 0.970703 4.49565 1.09854 4.91753 1.35422C5.34366 1.60564 5.68244 1.94442 5.93386 2.37056C6.18954 2.79243 6.31738 3.26331 6.31738 3.7832C6.31738 4.29883 6.18954 4.76971 5.93386 5.19584C5.68244 5.62198 5.34366 5.96289 4.91753 6.21857C4.49565 6.46999 4.02477 6.5957 3.50488 6.5957Z" fill="black"/>
                    </svg>
                    {{__('Previously, you didn’t have a branch section. So, when you create your first branch, another branch will be created automatically using your company/business name.')}} <br>
                    <i><span class="fw-bold">{{__('Example')}}:</span> {{__('If your company/business name is')}} <span class="fw-bold">{{__('Acnoo')}},</span> {{__('when you create your first branch, another branch will automatically be created with the name')}} <span class="fw-bold">{{__('Acnoo')}}.</span></i>
                </li>
                <li>
                    <svg width="7" height="7" viewBox="0 0 7 7" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3.50488 6.5957C2.98926 6.5957 2.51838 6.46999 2.09224 6.21857C1.66611 5.96289 1.3252 5.62198 1.06951 5.19584C0.818093 4.76971 0.692383 4.29883 0.692383 3.7832C0.692383 3.26331 0.818093 2.79243 1.06951 2.37056C1.3252 1.94442 1.66611 1.60564 2.09224 1.35422C2.51838 1.09854 2.98926 0.970703 3.50488 0.970703C4.02477 0.970703 4.49565 1.09854 4.91753 1.35422C5.34366 1.60564 5.68244 1.94442 5.93386 2.37056C6.18954 2.79243 6.31738 3.26331 6.31738 3.7832C6.31738 4.29883 6.18954 4.76971 5.93386 5.19584C5.68244 5.62198 5.34366 5.96289 4.91753 6.21857C4.49565 6.46999 4.02477 6.5957 3.50488 6.5957Z" fill="black"/>
                    </svg>
                    {{__('All your previous data will be assigned to the automatically created branch')}} (<span class="fw-bold">{{__('Acnoo')}}</span> {{__('in this example')}}).
                </li>
                <li>
                    <svg width="7" height="7" viewBox="0 0 7 7" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3.50488 6.5957C2.98926 6.5957 2.51838 6.46999 2.09224 6.21857C1.66611 5.96289 1.3252 5.62198 1.06951 5.19584C0.818093 4.76971 0.692383 4.29883 0.692383 3.7832C0.692383 3.26331 0.818093 2.79243 1.06951 2.37056C1.3252 1.94442 1.66611 1.60564 2.09224 1.35422C2.51838 1.09854 2.98926 0.970703 3.50488 0.970703C4.02477 0.970703 4.49565 1.09854 4.91753 1.35422C5.34366 1.60564 5.68244 1.94442 5.93386 2.37056C6.18954 2.79243 6.31738 3.26331 6.31738 3.7832C6.31738 4.29883 6.18954 4.76971 5.93386 5.19584C5.68244 5.62198 5.34366 5.96289 4.91753 6.21857C4.49565 6.46999 4.02477 6.5957 3.50488 6.5957Z" fill="black"/>
                    </svg>
                    {{__('You cannot delete the automatically created branch. This is because if a branch is deleted, it is removed from every section, making it impossible to filter branch data. Therefore, the automatically created branch cannot be deleted.')}}</li>
                </ul>
                <span class="alert-close-btn">&times;</span>
            </div>

            <div class="card">
                <div class="card-bodys">
                    <div class="table-header p-16">
                        <h4>{{ __('Branch List') }}</h4>
                        <a type="button" href="#branches-create-modal" data-bs-toggle="modal"
                            class="add-order-btn rounded-2 {{ Route::is('multibranch.branches.create') ? 'active' : '' }}"
                            @usercan('branches.create')
                            class="btn btn-primary"><i class="fas fa-plus-circle me-1"></i>{{ __('Add new Branch') }}</a>
                            @endusercan
                    </div>

                    <div class="table-top-form p-16-0">
                        <form action="{{ route('multibranch.branches.filter') }}" method="post" class="filter-form"
                            table="#branches-data">
                            @csrf
                            <div class="table-top-left d-flex gap-3 margin-l-16">
                                <div class="gpt-up-down-arrow position-relative">
                                    <select name="per_page" class="form-control">
                                        <option value="10">{{ __('Show- 10') }}</option>
                                        <option value="25">{{ __('Show- 25') }}</option>
                                        <option value="50">{{ __('Show- 50') }}</option>
                                        <option value="100">{{ __('Show- 100') }}</option>
                                    </select>
                                    <span></span>
                                </div>
                                <div class="table-search position-relative">
                                    <input type="text" name="search" class="form-control" placeholder="{{ __('Search...') }}">
                                    <span class="position-absolute">
                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M14.582 14.582L18.332 18.332" stroke="#4D4D4D" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M16.668 9.16797C16.668 5.02584 13.3101 1.66797 9.16797 1.66797C5.02584 1.66797 1.66797 5.02584 1.66797 9.16797C1.66797 13.3101 5.02584 16.668 9.16797 16.668C13.3101 16.668 16.668 13.3101 16.668 9.16797Z" stroke="#4D4D4D" stroke-width="1.25" stroke-linejoin="round"/>
                                            </svg>
                                    </span>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="delete-item delete-show d-none">
                    <div class="delete-item-show">
                        <p class="fw-bold"><span class="selected-count"></span> {{ __('items show') }}</p>
                        <button data-bs-toggle="modal" class="trigger-modal" data-bs-target="#multi-delete-modal" data-url="{{ route('multibranch.branches.delete-all') }}">{{ __('Delete') }}</button>
                    </div>
                </div>

                <div class="responsive-table m-0">
                    <table class="table" id="datatable">
                        <thead>
                            <tr>
                                @usercan('branches.delete')
                                <th class="w-60">
                                    <div class="d-flex align-items-center gap-3">
                                        <input type="checkbox" class="select-all-delete  multi-delete">
                                    </div>
                                </th>
                                @endusercan
                                <th>{{ __('SL') }}.</th>
                                <th class="text-start">{{ __('Name') }}</th>
                                <th class="text-start">{{ __('Phone') }}</th>
                                <th class="text-start">{{ __('Email') }}</th>
                                <th class="text-start">{{ __('Address') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody id="branches-data">
                            @include('multibranchaddon::branches.datas')
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $branches->links('vendor.pagination.bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('modal')
    @include('multibranchaddon::component.delete-modal')
    @include('multibranchaddon::branches.create')
    @include('multibranchaddon::branches.edit')
@endpush


@push('js')
    <script src="{{ asset('assets/js/custom/custom-alart.js') }}"></script>
@endpush

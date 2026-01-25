@foreach($warehouses as $warehouse)
    <tr>
        @usercan('warehouses.delete')
        <td class="w-60 checkbox">
            <input type="checkbox" name="ids[]" class="delete-checkbox-item multi-delete" value="{{ $warehouse->id }}">
        </td>
        @endusercan
        <td>{{ ($warehouses->currentPage() - 1) * $warehouses->perPage() + $loop->iteration }}</td>
        @if(moduleCheck('MultiBranchAddon') && multibranch_active())
        <td class="text-start">{{ $warehouse->branch->name ?? '' }}</td>
        @endif
        <td class="text-start">{{ $warehouse->name }}</td>
        <td class="text-start">{{ $warehouse->phone }}</td>
        <td class="text-start">{{ $warehouse->email }}</td>
        <td class="text-start">{{ $warehouse->address }}</td>
        <td class="text-start">{{ $warehouse->total_qty }}</td>
        <td class="text-start">{{ currency_format($warehouse->total_value, currency: business_currency()) }}</td>

        <td class="d-print-none">
            <div class="dropdown table-action">
                <button type="button" data-bs-toggle="dropdown">
                    <i class="far fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu">

                    @usercan('warehouses.read')
                    <li>
                        <a href="#warehouse-view" class="warehouse-view-btn" data-bs-toggle="modal"
                            data-warehouse-name="{{ $warehouse->name }}"
                            data-branch-name="{{ $warehouse->branch->name ?? '' }}"
                            data-warehouse-email="{{ $warehouse->email }}"
                            data-warehouse-phone="{{ $warehouse->phone }}"
                            data-warehouse-address="{{ $warehouse->address }}"
                            >
                            <i class="fal fa-eye"></i>
                            {{ __('View') }}
                        </a>
                    </li>
                    @endusercan

                    @usercan('warehouses.update')
                    <li>
                        <a href="#warehouses-edit-modal" data-bs-toggle="modal" class="warehouse-edit-btn"
                           data-url="{{ route('warehouse.warehouses.update', $warehouse->id) }}"
                           data-name="{{ $warehouse->name }}"
                           data-branch-id="{{ $warehouse->branch->id ?? ''}}"
                           data-phone="{{ $warehouse->phone }}"
                           data-email="{{ $warehouse->email }}"
                           data-address="{{ $warehouse->address }}"
                           >
                           <i class="fal fa-edit"></i></i>{{ __('Edit') }}
                        </a>
                    </li>
                    @endusercan

                    @usercan('warehouses.delete')
                    <li>
                        <a href="{{ route('warehouse.warehouses.destroy', $warehouse->id) }}" class="confirm-action" data-method="DELETE">
                            <i class="fal fa-trash-alt"></i>
                            {{ __('Delete') }}
                        </a>
                    </li>
                    @endusercan
                </ul>
            </div>
        </td>
    </tr>
@endforeach

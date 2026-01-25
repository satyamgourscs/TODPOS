@foreach($units as $unit)
    <tr>
        @usercan('units.delete')
        <td class="w-60 checkbox">
            <input type="checkbox" name="ids[]" class="delete-checkbox-item  multi-delete" value="{{ $unit->id }}">
        </td>
        @endusercan

        <td>{{ ($units->currentPage() - 1) * $units->perPage() + $loop->iteration }}</td>

        <td class="text-start">{{ $unit->unitName }}</td>
        <td class="text-center">
                <label class="switch">
                    <input type="checkbox" {{ $unit->status == 1 ? 'checked' : '' }} class="status" data-url="{{ route('business.units.status', $unit->id) }}">
                    <span class="slider round"></span>
                </label>
        </td>
        <td class="d-print-none">
            <div class="dropdown table-action">
                <button type="button" data-bs-toggle="dropdown">
                    <i class="far fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu">
                    <li>
                        @usercan('units.update')
                        <a  href="#unit-edit-modal" data-bs-toggle="modal" class="units-edit-btn" data-url="{{ route('business.units.update', $unit->id) }}" data-units-name="{{ $unit->unitName }}" data-units-status="{{ $unit->status }}"><i class="fal fa-pencil-alt"></i>{{__('Edit')}}</a>
                        @endusercan
                    </li>
                    <li>
                        @usercan('units.delete')
                        <a href="{{ route('business.units.destroy', $unit->id) }}" class="confirm-action" data-method="DELETE">
                            <i class="fal fa-trash-alt"></i>
                            {{ __('Delete') }}
                        </a>
                        @endusercan
                    </li>
                </ul>
            </div>
        </td>
    </tr>
@endforeach

@foreach ($racks as $rack)
    <tr>
        @usercan('racks.delete')
        <td class="w-60 checkbox d-print-none">
            <input type="checkbox" name="ids[]" class="delete-checkbox-item  multi-delete" value="{{ $rack->id }}">
        </td>
        @endusercan
        <td>{{ ($racks->currentPage() - 1) * $racks->perPage() + $loop->iteration }}</td>
        <td>{{ $rack->name }}</td>
        <td>{{ $rack->shelves->pluck('name')->implode(', ') }}</td>

        <td>
            <label class="switch">
                <input type="checkbox" {{ $rack->status == 1 ? 'checked' : '' }} class="status"
                    data-url="{{ route('business.racks.status', $rack->id) }}">
                <span class="slider round"></span>
            </label>
        </td>

        <td class="d-print-none">
            <div class="dropdown table-action">
                <button type="button" data-bs-toggle="dropdown">
                    <i class="far fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu">

                    @usercan('racks.update')
                    <li>
                        <a href="#rack-edit-modal" data-bs-toggle="modal" class="rack-edit-btn"
                            data-url="{{ route('business.racks.update', $rack->id) }}"
                            data-warehouse-id="{{ $rack->warehouse->id ?? '' }}"
                            data-rack-name="{{ $rack->name }}"
                            data-shelf-datas='@json($rack->shelves->pluck("id")->toArray())'

                            >
                            <i class="fal fa-edit"></i>{{ __('Edit') }}
                        </a>
                    </li>
                    @endusercan

                    @usercan('racks.delete')
                    <li>
                        <a href="{{ route('business.racks.destroy', $rack->id) }}" class="confirm-action"
                            data-method="DELETE">
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

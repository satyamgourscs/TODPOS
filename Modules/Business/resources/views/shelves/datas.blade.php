@foreach ($shelves as $shelf)
    <tr>
        @usercan('shelfs.delete')
        <td class="w-60 checkbox d-print-none">
            <input type="checkbox" name="ids[]" class="delete-checkbox-item multi-delete" value="{{ $shelf->id }}">
        </td>
        @endusercan
        <td>{{ ($shelves->currentPage() - 1) * $shelves->perPage() + $loop->iteration }}</td>
        <td>{{ $shelf->name }}</td>
        <td>
            <label class="switch">
                <input type="checkbox" {{ $shelf->status == 1 ? 'checked' : '' }} class="status"
                    data-url="{{ route('business.shelfs.status', $shelf->id) }}">
                <span class="slider round"></span>
            </label>
        </td>

        <td class="d-print-none">
            <div class="dropdown table-action">
                <button type="button" data-bs-toggle="dropdown">
                    <i class="far fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu">

                    @usercan('shelfs.update')
                    <li>
                        <a href="#shelf-edit-modal" data-bs-toggle="modal" class="shelf-edit-btn"
                            data-url="{{ route('business.shelfs.update', $shelf->id) }}"
                            data-shelf-name="{{ $shelf->name }}">
                            <i class="fal fa-edit"></i>{{ __('Edit') }}
                        </a>
                    </li>
                    @endusercan

                    @usercan('shelfs.delete')
                    <li>
                        <a href="{{ route('business.shelfs.destroy', $shelf->id) }}" class="confirm-action" data-method="DELETE">
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

@foreach ($parties as $party)
    <tr>
        @usercan('parties.delete')
        <td class="w-60 checkbox">
            <input type="checkbox" name="ids[]" class="delete-checkbox-item multi-delete"
                   value="{{ $party->id }}" @if(!$party->canBeDeleted()) disabled @endif>
        </td>
        @endusercan
        <td>{{ ($parties->currentPage() - 1) * $parties->perPage() + $loop->iteration }}</td>
        <td>
            <img src="{{ asset($party->image ?? 'assets/images/logo/upload2.jpg') }}" alt="Img" class="table-product-img">
        </td>
        <td>{{ $party->name }}</td>
        <td>{{ $party->email }}</td>
        <td>{{ $party->type }}</td>
        <td>{{ $party->phone }}</td>
        <td class="text-danger">
            {{ currency_format( $party->due, currency: business_currency()) }}
        </td>
        <td class="d-print-none">
            <div class="dropdown table-action">
                <button type="button" data-bs-toggle="dropdown">
                    <i class="far fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu">
                    <li>
                        @usercan('parties.read')
                        <a href="#parties-view" class="parties-view-btn" data-bs-toggle="modal"
                           data-name="{{ $party->name }}" data-email="{{ $party->email }}"
                           data-phone="{{ $party->phone }}" data-type="{{ $party->type }}"
                           data-due="{{ currency_format($party->due, currency: business_currency()) }}"
                           data-address="{{ $party->address }}">
                            <i class="fal fa-eye"></i>
                            {{ __('View') }}
                        </a>
                        @endusercan
                    </li>
                    <li>
                        <a href="{{ route('business.parties.edit', [$party->id, 'type' => request('type')]) }}"><i class="fal fa-edit"></i>{{ __('Edit') }}</a>
                    </li>
                    <li>
                        @usercan('parties.delete')
                        @if($party->canBeDeleted())
                            <a href="{{ route('business.parties.destroy', $party->id) }}" class="confirm-action"
                               data-method="DELETE">
                                <i class="fal fa-trash-alt"></i>
                                {{ __('Delete') }}
                            </a>
                        @endif
                        @endusercan
                    </li>
                </ul>
            </div>
        </td>
    </tr>
@endforeach

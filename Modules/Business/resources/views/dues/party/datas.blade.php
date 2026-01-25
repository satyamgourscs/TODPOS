@foreach($parties as $party)
    <tr>
        <td>{{ ($parties->currentPage() - 1) * $parties->perPage() + $loop->iteration }}</td>
        <td>{{ $party->name }}</td>
        <td>{{ $party->email }}</td>
        <td>{{ $party->phone }}</td>
        <td>{{ $party->type }}</td>
        <td class="text-danger text-end">
            {{ currency_format($party->due, currency: business_currency()) }}
        </td>
        <td class="d-print-none">
            <div class="dropdown table-action">
                <button type="button" data-bs-toggle="dropdown">
                    <i class="far fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a href="{{ route('business.collect.dues', $party->id) }}">
                            <i class="fal fa-edit"></i>
                            {{ __('Collect Due') }}
                        </a>
                    </li>
                    @if($party->dueCollect)
                        <li>
                            <a href="{{ route('business.collect.dues.invoice', $party->id) }}" target="_blank">
                                <img src="{{ asset('assets/images/icons/Invoic.svg') }}" alt="" >
                                {{ __('Invoice') }}
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        </td>
    </tr>
@endforeach

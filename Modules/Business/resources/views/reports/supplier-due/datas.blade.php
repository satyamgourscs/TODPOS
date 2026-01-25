@foreach($parties as $party)
    <tr>
        <td>{{ ($parties->currentPage() - 1) * $parties->perPage() + $loop->iteration }}</td>
        <td class="text-start">{{ $party->name }}</td>
        <td class="text-start">{{ $party->email }}</td>
        <td class="text-start">{{ $party->phone }}</td>
        <td class="text-start">{{ $party->type }}</td>
        <td class="text-start">
            {{ currency_format( $party->due, currency: business_currency()) }}
        </td>
    </tr>
@endforeach

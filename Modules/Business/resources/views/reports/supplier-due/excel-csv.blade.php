<table>
    <thead>
        <tr>
            <th>{{ __('SL') }}.</th>
            <th class="text-start">{{ __('Name') }}</th>
            <th class="text-start">{{ __('Email') }}</th>
            <th class="text-start">{{ __('Phone') }}</th>
            <th class="text-start">{{ __('Type') }}</th>
            <th class="text-start">{{ __('Due Amount') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($parties as $party)
            <tr>
                <td>{{ $loop->index + 1 }}</td>
                <td class="text-start">{{ $party->name }}</td>
                <td class="text-start">{{ $party->email }}</td>
                <td class="text-start">{{ $party->phone }}</td>
                <td class="text-start">{{ $party->type }}</td>
                <td class="text-start">
                    {{ currency_format( $party->due, currency: business_currency()) }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

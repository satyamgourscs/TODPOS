<table>
    <thead>
        <tr>
            <th>{{ __('SL') }}.</th>
            <th>{{ __('Date') }}</th>
            <th>{{ __('Invoice No') }}</th>
            @if(auth()->user()->accessToMultiBranch())
            <th>{{ __('From Branch') }}</th>
            @endif
            <th>{{ __('From Warehouse') }}</th>
            @if(auth()->user()->accessToMultiBranch())
            <th>{{ __('To Branch') }}</th>
            @endif
            <th>{{ __('To Warehouse') }}</th>
            <th>{{ __('Qty') }}</th>
            <th>{{ __('Stock Values') }}</th>
            <th>{{ __('Status') }}</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($transfers as $transfer)

            @php
                $totalQty = $transfer->transferProducts->sum('quantity');
                $totalStockValue = $transfer->transferProducts->sum(function ($product) {
                    return $product->quantity * $product->unit_price;
                });
            @endphp

            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $transfer->transfer_date }}</td>
                <td>{{ $transfer->invoice_no }}</td>
                @if(auth()->user()->accessToMultiBranch())
                <td>{{ $transfer->toBranch->name ?? '' }}</td>
                @endif
                <td>{{ $transfer->fromWarehouse->name ?? ''}}</td>
                @if(auth()->user()->accessToMultiBranch())
                <td>{{ $transfer->fromBranch->name ?? '' }}</td>
                @endif
                <td>{{ $transfer->toWarehouse->name ?? '' }}</td>
                <td>{{ $totalQty }}</td>
                <td>{{ currency_format($totalStockValue, currency: business_currency()) }}</td>
                <td>{{ ucfirst($transfer->status) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

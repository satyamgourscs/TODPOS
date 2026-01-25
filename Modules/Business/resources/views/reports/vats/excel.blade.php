<table>
    <thead>
        <tr>
            <th>{{ __('Date') }}</th>
            <th>{{ __('Invoice') }}</th>
            <th>{{ __('Customer') }}</th>
            <th>{{ __('Total Amount') }}</th>
            <th>{{ __('Payment Method') }}</th>
            <th>{{ __('Discount') }}</th>
            @foreach ($vats as $vat)
                <th>{{ $vat->name }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        <!-- Show sales data if sales tab is active -->
        @if (Request::get('type') == 'sales')
            @foreach ($sales as $sale)
                <tr>
                    <td>{{ formatted_date($sale->created_at) }}</td>
                    <td>{{ $sale->invoiceNumber }}</td>
                    <td>{{ $sale->party->name ?? '' }}</td>
                    <td>{{ currency_format($sale->totalAmount, currency: business_currency()) }}</td>
                    <td>{{ $sale->payment_type->name ?? '' }}</td>
                    <td>{{ currency_format($sale->discountAmount, currency: business_currency()) }}</td>
                    @foreach ($vats as $vat)
                        <td>{{ $sale->vat_id == $vat->id ? currency_format($sale->vat_amount, currency: business_currency()) : '0' }}</td>
                    @endforeach
                </tr>
            @endforeach
        @endif

        <!-- Show purchase data if purchase tab is active -->
        @if (Request::get('type') == 'purchases')
            @foreach ($purchases as $purchase)
                <tr>
                    <td>{{ formatted_date($purchase->created_at) }}</td>
                    <td>{{ $purchase->invoiceNumber }}</td>
                    <td>{{ $purchase->party->name ?? '' }}</td>
                    <td>{{ currency_format($purchase->totalAmount, currency: business_currency()) }}</td>
                    <td>{{ $purchase->payment_type->name ?? '' }}</td>
                    <td>{{ currency_format($purchase->discountAmount, currency: business_currency()) }}</td>
                    @foreach ($vats as $vat)
                        <td>{{ $purchase->vat_id == $vat->id ? currency_format($purchase->vat_amount, currency: business_currency()) : '0' }}</td>
                    @endforeach
                </tr>
            @endforeach
        @endif
    </tbody>
</table>

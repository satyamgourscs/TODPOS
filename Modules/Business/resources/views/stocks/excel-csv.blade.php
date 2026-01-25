<table>
    <thead>
        <tr>
            <th>{{ __('SL') }}.</th>
            <th>{{ __('Product') }}</th>
            <th>{{ __('Cost') }}</th>
            <th>{{ __('Qty') }}</th>
            <th>{{ __('Sale') }}</th>
            <th>{{ __('Stock Value') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($stocks as $stock)
        <tr>
            <td>{{ $loop->index+1 }}</td>
            <td>{{ $stock->productName }}</td>
            <td>{{ currency_format($stock->productPurchasePrice, currency: business_currency()) }}</td>
            <td>{{ $stock->stocks_sum_product_stock }}</td>
            <td>{{ currency_format($stock->productSalePrice, currency: business_currency()) }}</td>
            <td>{{ currency_format($stock->productSalePrice * $stock->stocks_sum_product_stock, currency: business_currency()) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

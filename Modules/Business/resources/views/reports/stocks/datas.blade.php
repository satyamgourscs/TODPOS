@foreach ($stocks as $stock)
    <tr>
        <td>{{ ($stocks->currentPage() - 1) * $stocks->perPage() + $loop->iteration }}</td>
        <td class="text-start">{{ $stock->productName }}</td>
        <td class="text-start">{{ currency_format($stock->productPurchasePrice, currency: business_currency()) }}</td>
        @if ($stock->stocks_sum_product_stock <= $stock->alert_qty)
            <td class="text-danger text-start">{{ $stock->stocks_sum_product_stock }}</td>
        @else
            <td class="text-success text-start">{{ $stock->stocks_sum_product_stock }}</td>
        @endif
        <td class="text-start">{{ currency_format($stock->productSalePrice, currency: business_currency()) }}</td>
        <td class="text-start">{{ currency_format($stock->productPurchasePrice * $stock->stocks_sum_product_stock, currency: business_currency()) }}</td>
    </tr>
@endforeach

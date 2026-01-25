@foreach ($products as $product)
    @php
        $total_stock = $product->stocks->sum('productStock');
        $firstStock = $product->stocks->first();
        $total_value = $product->stocks->sum(function ($stock) {
            return $stock->productPurchasePrice * $stock->productStock;
        });
    @endphp
    <tr>
        <td>{{ ($products->currentPage() - 1) * $products->perPage() + $loop->iteration }}</td>

        <td class="text-start">
            @php
                $stocks = $product->stocks->map(function ($batch) {
                    return [
                        'batch_no' => $batch->batch_no,
                        'expire_date' => $batch->expire_date ? formatted_date($batch->expire_date) : 'N/A',
                        'productStock' => $batch->productStock ?? 0,
                        'productSalePrice' => $batch->productSalePrice ?? 0,
                        'productDealerPrice' => $batch->productDealerPrice ?? 0,
                        'productPurchasePrice' => $batch->productPurchasePrice ?? 0,
                        'productWholeSalePrice' => $batch->productWholeSalePrice ?? 0,
                    ];
                });
            @endphp
            <a href="javascript:void(0);" class="stock-view-data text-primary" data-stocks='@json($stocks)'>
                {{ $product->productName }}
            </a>
        </td>

        <td class="text-start">{{ $product->productCode }}</td>
        <td class="text-start">{{ $product->category->categoryName ?? ''}}</td>
        @usercan('stocks.price')
        <td class="text-start">{{ currency_format(optional($firstStock)->productPurchasePrice, currency: business_currency()) }}</td>
        @endusercan
        <td class="{{ $total_stock <= $product->alert_qty ? 'text-danger' : 'text-success' }} text-start">
            {{ $total_stock }}
        </td>
        <td class="text-center">{{ currency_format(optional($firstStock)->productSalePrice, currency: business_currency()) }}</td>
        <td class="text-end">{{ currency_format($total_value, currency : business_currency()) }}</td>
    </tr>
@endforeach

<tr>
    <td colspan="{{ auth()->user()->can('stocks.price') || !auth()->user()->visibility ? 7 : 6 }}" class="text-end">
        <strong>{{ __('Total Stock Value:') }}</strong>
    </td>
    <td class="text-end"><strong>{{ currency_format($total_stock_value, currency : business_currency()) }}</strong></td>
</tr>

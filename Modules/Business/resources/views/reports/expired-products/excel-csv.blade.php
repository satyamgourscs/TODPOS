<table>
    <thead>
        <tr>
            <th>{{ __('SL') }}. </th>
            <th>{{ __('Image') }} </th>
            <th>{{ __('Product Name') }} </th>
            <th>{{ __('Code') }} </th>
            <th>{{ __('Brand') }} </th>
            <th>{{ __('Category') }} </th>
            <th>{{ __('Unit') }} </th>
            <th>{{ __('Purchase price') }}</th>
            <th>{{ __('Sale price') }}</th>
            <th>{{ __('Stock') }}</th>
            <th>{{ __('Expired Date') }}</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($expired_products as $product)
        @php
            $nonEmptyStock = $product->stocks->firstWhere('productStock', '>', 0);
            $fallbackStock = $product->stocks->first(); // fallback if no stock > 0
            $stock = $nonEmptyStock ?? $fallbackStock;

            $latestPurchasePrice = $stock?->productPurchasePrice ?? 0;
            $latestSalePrice = $stock?->productSalePrice ?? 0;
        @endphp
            <tr>
                <td>{{ $loop->index + 1 }}</td>
                <td><img src="{{ asset($product->productPicture ?? 'assets/images/logo/upload2.jpg') }}" alt="Img" class="table-product-img"></td>
                <td>{{ $product->productName }}</td>
                <td>{{ $product->productCode }}</td>
                <td>{{ $product->brand->brandName ?? '' }}</td>
                <td>{{ $product->category->categoryName ?? '' }}</td>
                <td>{{ $product->unit->unitName ?? '' }}</td>
                <td>{{ currency_format($latestPurchasePrice, currency: business_currency()) }}</td>
                <td>{{ currency_format($latestSalePrice, currency: business_currency()) }}</td>
                <td>{{ $product->stocks_sum_product_stock }}</td>
                <td>
                    @if ($product->stocks->isNotEmpty() && $product->stocks->first()->expire_date)
                        {{ formatted_date($product->stocks->first()->expire_date) }}
                    @else
                        N/A
                    @endif
                </td>
            </tr>
        @endforeach

    </tbody>
</table>

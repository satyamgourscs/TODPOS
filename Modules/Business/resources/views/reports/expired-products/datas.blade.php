@foreach ($expired_products as $product)

    @php
        $nonEmptyStock = $product->stocks->firstWhere('productStock', '>', 0);
        $fallbackStock = $product->stocks->first(); // fallback if no stock > 0
        $stock = $nonEmptyStock ?? $fallbackStock;

        $latestPurchasePrice = $stock?->productPurchasePrice ?? 0;
        $latestSalePrice = $stock?->productSalePrice ?? 0;
        $latestWholeSalePrice = $stock?->productWholeSalePrice ?? 0;
        $latestDealerPrice = $stock?->productDealerPrice ?? 0;
        $productStock = $product->total_stock ?? 0;
    @endphp

    <tr>
        <td>{{ ($expired_products->currentPage() - 1) * $expired_products->perPage() + $loop->iteration }}</td>

        <td>
            <img src="{{ asset($product->productPicture ?? 'assets/images/logo/upload2.jpg') }}" alt="Img" class="table-product-img">
        </td>

        <td>{{ $product->productName }}</td>
        <td>{{ $product->productCode }}</td>
        <td>{{ $product->brand->brandName ?? '' }}</td>
        <td>{{ $product->category->categoryName ?? '' }}</td>
        <td>{{ $product->unit->unitName ?? '' }}</td>
        @usercan('expired-product-reports.price')
        <td>{{ currency_format($latestPurchasePrice, currency: business_currency()) }}</td>
        @endusercan
        <td>{{ currency_format($latestSalePrice, currency: business_currency()) }}</td>
        <td class="{{ $product->stocks_sum_product_stock <= $product->alert_qty ? 'text-danger' : 'text-success' }}">{{ $product->stocks_sum_product_stock }}</td>
        <td class="text-danger">
            @if ($product->stocks->isNotEmpty() && $product->stocks->first()->expire_date)
                {{ formatted_date($product->stocks->first()->expire_date) }}
            @else
                N/A
            @endif
        </td>

        <td class="d-print-none">
            <div class="dropdown table-action">
                <button type="button" data-bs-toggle="dropdown">
                    <i class="far fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a href="#expire-product-report-view" class="product-view" data-bs-toggle="modal"
                            data-name="{{ $product->productName }}"
                            data-image="{{ asset($product->productPicture ?? 'assets/images/logo/upload2.jpg') }}"
                            data-code="{{ $product->productCode }}"
                            data-brand="{{ $product->brand->brandName ?? '' }}"
                            data-category="{{ $product->category->categoryName ?? '' }}"
                            data-unit="{{ $product->unit->unitName ?? '' }}"
                            data-purchase-price="{{ currency_format($latestPurchasePrice, currency: business_currency()) }}"
                            data-sale-price="{{ currency_format($latestSalePrice, currency: business_currency()) }}"
                            data-wholesale-price="{{ currency_format($latestWholeSalePrice, currency: business_currency()) }}"
                            data-dealer-price="{{ currency_format($latestDealerPrice, currency: business_currency()) }}"
                            data-stock="{{ $product->stocks_sum_product_stock }}"
                            data-product-expire-date="{{ formatted_date($product->stocks->first()->expire_date) }}"
                            data-manufacturer="{{ $product->productManufacturer }}">
                            <i class="fal fa-eye"></i>
                            {{ __('View') }}
                        </a>
                    </li>
                </ul>
            </div>
        </td>
    </tr>
@endforeach

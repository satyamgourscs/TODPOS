@foreach ($products as $product)
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
        @usercan('products.delete')
        <td class="w-60 checkbox d-print-none">
            <input type="checkbox" name="ids[]" class="delete-checkbox-item multi-delete" value="{{ $product->id }}">
        </td>
        @endusercan
        <td>{{ ($products->currentPage() - 1) * $products->perPage() + $loop->iteration }}</td>

        {{-- Hidden Route  --}}
        <input type="hidden" class="product-show-route" data-id="{{ $product->id }}" value="{{ route('business.products.show', $product->id) }}">

        <td><img src="{{ asset($product->productPicture ?? 'assets/images/logo/upload2.jpg') }}" alt="Img" class="table-product-img"></td>

        <td class="d-print-none">
            @php
                $stocks = $product->stocks->map(function ($batch) use ($product) {
                        $hasWeight = $product->category ? $product->category->variationWeight : false;
                    return [
                        'id' => $batch->id,
                        'batch_no' => $batch->batch_no,
                        'expire_date' => $batch->expire_date ? formatted_date($batch->expire_date) : 'N/A',
                        'productStock' => $batch->productStock ?? 0,
                        'productSalePrice' => $batch->productSalePrice ?? 0,
                        'productPurchasePrice' => $batch->productPurchasePrice ?? 0,
                        'productWholeSalePrice' => $batch->productWholeSalePrice ?? 0,
                        'productDealerPrice' => $batch->productDealerPrice ?? 0,
                        'warehouse' => $batch->warehouse->name ?? '',
                        'rack' => $product->rack->name ?? '',
                        'shelf' => $product->shelf->name ?? '',
                        'weight' => $hasWeight ? $product->weight : null,
                        'showWeight' => $hasWeight ? 1 : 0,
                    ];
                });
            @endphp
            <a href="javascript:void(0);" class="stock-view-data text-primary" data-stocks='@json($stocks)'>
                {{ $product->productName }}
            </a>
        </td>

        <td>{{ $product->productCode }}</td>
        <td>{{ $product->brand->brandName ?? '' }}</td>
        <td>{{ $product->category->categoryName ?? '' }}</td>
        @if (moduleCheck('WarehouseAddon'))
        <td>{{ $fallbackStock->warehouse->name ?? '' }}</td>
        @endif
        <td>{{ $product->unit->unitName ?? '' }}</td>
        @usercan('products.price')
        <td>{{ currency_format($latestPurchasePrice, currency: business_currency()) }}</td>
        @endusercan
        <td>{{ currency_format($latestSalePrice, currency: business_currency()) }}</td>
        <td class="{{ $product->total_stock <= $product->alert_qty ? 'text-danger' : 'text-success' }}">
            {{ $product->total_stock }}
        </td>
        <td>{{ $product->rack->name ?? '' }}</td>
        <td>{{ $product->shelf->name ?? '' }}</td>
        <td class="d-print-none">
            <div class="dropdown table-action">
                <button type="button" data-bs-toggle="dropdown">
                    <i class="far fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu">
                    <li>
                        @php
                            $stocks = $product->stocks->map(function ($batch) {
                                return [
                                    'id' => $batch->id,
                                    'batch_no' => $batch->batch_no,
                                    'expire_date' => $batch->expire_date ? formatted_date($batch->expire_date) : 'N/A',
                                    'productStock' => $batch->productStock ?? 0,
                                    'productSalePrice' => $batch->productSalePrice ?? 0,
                                ];
                            });

                            $showExpireDate = $product->stocks->isEmpty();
                        @endphp
                        @usercan('products.read')
                        <a href="#product-view" class="product-view" data-bs-toggle="modal"
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
                            data-stock="{{ $product->total_stock }}" data-low-stock="{{ $product->alert_qty }}"
                            data-manufacturer="{{ $product->productManufacturer }}"
                            data-stocks='@json($stocks)'>
                            <i class="fal fa-eye"></i>
                            {{ __('View') }}
                        </a>
                        @endusercan
                    </li>
                     <li>
                        @usercan('products.read')
                        <a href="{{ route('business.products.show', $product->id) }}">
                            <i class="fal fa-plus-circle"></i>
                            {{ __('Add Stock') }}
                        </a>
                        @endusercan
                    </li>

                    <li>
                        @usercan('products.update')
                        <a href="{{ route('business.products.edit', $product->id) }}">
                            <i class="fal fa-edit"></i>
                            {{ __('Edit') }}
                        </a>
                        @endusercan
                    </li>
                    <li>
                        @usercan('products.delete')
                        <a href="{{ route('business.products.destroy', $product->id) }}" class="confirm-action"
                            data-method="DELETE">
                            <i class="fal fa-trash-alt"></i>
                            {{ __('Delete') }}
                        </a>
                        @endusercan
                    </li>
                </ul>
            </div>
        </td>
    </tr>
@endforeach

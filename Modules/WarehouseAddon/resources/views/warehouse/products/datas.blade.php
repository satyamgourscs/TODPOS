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

        <td>{{ ($products->currentPage() - 1) * $products->perPage() + $loop->iteration }}</td>

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
        <td>{{ $fallbackStock->warehouse->name ?? '' }}</td>
        <td>{{ $product->unit->unitName ?? '' }}</td>
        <td>{{ currency_format($latestPurchasePrice, currency: business_currency()) }}</td>
        <td>{{ currency_format($latestSalePrice, currency: business_currency()) }}</td>
        <td class="{{ $product->total_stock <= $product->alert_qty ? 'text-danger' : 'text-success' }}">{{ $product->total_stock }}</td>
        <td>{{ $product->rack->name ?? '' }}</td>
        <td>{{ $product->shelf->name ?? '' }}</td>
    </tr>
@endforeach

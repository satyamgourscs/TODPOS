@foreach($products as $product)
    @php
        $firstStock = $product->stocks->first();
    @endphp
    <div id="single-product" class="single-product {{ $product->id }}"
        data-product_id="{{ $product->id }}"
        data-product_code="{{ $product->productCode }}"
        data-product_unit_id="{{ $product->unit->id ?? null }}"
        data-product_unit_name="{{ $product->unit->unitName ?? null }}"
        data-product_image="{{ $product->productPicture }}"
        data-brand="{{ $product->brand->brandName ?? ''  }}"
        data-stock="{{ $product->stocks_sum_product_stock ?? 0  }}"
        data-purchase_price="{{ $firstStock->productPurchasePrice ?? 0  }}"
        data-sales_price="{{ $firstStock->productSalePrice ?? 0  }}"
        data-whole_sale_price="{{ $firstStock->productWholeSalePrice ?? 0  }}"
        data-dealer_price="{{ $firstStock->productDealerPrice ?? 0  }}"
    >
        <div class="pro-img">
            <img class='w-100 rounded' src="{{ asset($product->productPicture ?? 'assets/images/products/box.svg') }}" alt="">
        </div>
        <div class="product-con">
            <h6 class="pro-title product_name">{{ $product->productName }}</h6>
            <p class="pro-category">{{ $product->category->categoryName ?? '' }}</p>
            @usercan('purchases.price')
            <div class="price">
                <h6 class="pro-price product_price">{{ currency_format($firstStock->productPurchasePrice ?? 0, currency: business_currency()) }}</h6>
            </div>
            @endusercan
        </div>
    </div>
@endforeach

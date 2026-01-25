@forelse ($products as $product)
    @php
        $firstStock = $product->stocks->first();
        $purchasePrice = $firstStock->productPurchasePrice ?? 0;
        $salePrice = $firstStock->productSalePrice ?? 0;
    @endphp
    <div id="single-product" class="single-product {{ $product->id }}"
         data-product_id="{{ $product->id }}"
         data-default_price="{{ $salePrice }}"
         data-product_code="{{ $product->productCode }}"
         data-product_unit_id="{{ $product->unit->id ?? null }}"
         data-product_unit_name="{{ $product->unit->unitName ?? null }}"
         data-product_image="{{ $product->productPicture }}"
         data-product_name="{{ $product->productName }}"
         data-purchase_price = "{{ $purchasePrice  }}"
         data-batch_count="{{ $product->stocks->count() }}"
         data-stocks='@json($product->stocks)'
         data-route="{{ route('business.carts.store') }}"
    >
        <div class="pro-img w-100">
            <img src="{{ asset($product->productPicture ?? 'assets/images/products/box.svg') }}" alt="">
        </div>
        <div class="product-con">
            <h6 class="pro-title product_name">{{ $product->productName }}</h6>
            <p class="pro-category">{{ $product->category->name ?? '' }}</p>
            <div class="price">
                <h6 class="pro-price product_price">{{ currency_format($salePrice, currency: business_currency()) }}</h6>
            </div>
        </div>
    </div>
@empty
    <div class="alert alert-danger not-found mt-1" role="alert">
        No product found
    </div>
@endforelse

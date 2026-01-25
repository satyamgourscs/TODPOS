<div class="invoice-container-sm">
    <div class="invoice-content invoice-content-size">
        <div class="invoice-logo mb-2">
            <img src="{{ asset(get_business_option('business-settings')['invoice_logo'] ?? 'assets/images/default.svg') ?? '' }}" alt="Logo">
        </div>
        <div>
            <h4 class="company-name">{{ $purchase->business->companyName ?? 'Pos Pro' }}</h4>
            <div class="company-info">
                <p> {{__('Address')}} : {{ $purchase->business->address ?? '' }}</p>
                <p> {{__('Mobile')}} : {{ $purchase->business->phoneNumber ?? '' }}</p>
                <p> {{__('Email')}} : {{ auth()->user()->email ?? '' }}</p>
                @if (!empty($purchase->business->vat_name))
                    <p>{{ $purchase->business->vat_name }} : {{ $purchase->business->vat_no ?? '' }}</p>
                @endif
            </div>
        </div>
        <h3 class="invoice-title my-1">
                {{__('invoice')}}
        </h3>

        <div class="invoice-info">
            <div class="">
                <p> {{__('Invoice')}} : {{ $purchase->invoiceNumber ?? '' }}</p>
                <p> {{__('Name')}} : {{ $purchase->party->name ?? '' }}</p>
                <p> {{__('Mobile')}} : {{ $purchase->party->phone ?? '' }}</p>
            </div>
            <div class="">
                <p class="text-end date"> {{__('Date')}} : {{ formatted_date($purchase->purchaseDate ?? '') }}</p>
                <p class="text-end time"> {{__('Time')}} : {{ formatted_time($purchase->purchaseDate ?? '') }}</p>
                <p class="text-end"> {{__('Purchase By')}} : {{ $purchase->user->name }}</p>
            </div>
        </div>
        @if (!$purchase_returns->isEmpty())
            <table class="ph-invoice-table">
                <thead>
                    <tr>
                        <th class="text-start table-sl"> {{__('SL')}}.</th>
                        <th> {{__('Product')}} </th>
                        <th> {{__('QTY')}} </th>
                        <th> {{__('U.Price')}} </th>
                        <th class="text-end"> {{__('Amount')}} </th>
                    </tr>
                </thead>

                @php
                    $subtotal = 0;
                @endphp
                <tbody>
                    @foreach ($purchase->details as $detail)
                        @php
                            $productTotal = ($detail->productPurchasePrice ?? 0) * ($detail->quantities ?? 0);
                            $subtotal += $productTotal;
                        @endphp
                        <tr>
                            <td class="text-start table-sl">{{ $loop->iteration }}</td>
                            <td>{{ $detail->product->productName ?? '' }}</td>
                            <td class="text-center">{{ $detail->quantities ?? '' }}</td>
                            <td class="text-center">
                                {{ currency_format($detail->productPurchasePrice ?? 0, currency: business_currency()) }}
                            </td>
                            <td class="text-end">{{ currency_format($productTotal, currency: business_currency()) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="2" class="">
                            <div class="payment-type-container">
                                <h6 class="text-start"> {{__('Payment Type')}} :
                                    {{ $purchase->payment_type_id != null ? $purchase->payment_type->name ?? '' : $purchase->paymentType }}
                                </h6>
                            </div>
                        </td>
                        <td colspan="3">
                            <div class="calculate-amount">
                                <div class="d-flex justify-content-between">
                                    <p> {{__('Sub-Total')}} :</p>
                                    <p>{{ currency_format($subtotal, currency: business_currency()) }}</p>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <p> {{__('Vat')}} : </p>
                                    <p>{{ currency_format($purchase->vat_amount, currency: business_currency()) }}
                                    </p>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <p> {{__('Discount')}} :</p>
                                    <p>{{ currency_format($purchase->discountAmount, currency: business_currency()) }}
                                    </p>
                                </div>
                                <div class="d-flex justify-content-between ">
                                    <p> {{__('Shipping Charge')}} :</p>
                                    <p>{{ currency_format($purchase->shipping_charge, currency: business_currency()) }}
                                    </p>
                                </div>

                                <div class="d-flex justify-content-between total-amount">
                                    <p> {{__('Net Payable')}} :</p>
                                    <p>{{ currency_format($subtotal + $purchase->vat_amount - ($purchase->discountAmount + $total_discount) + $purchase->shipping_charge, currency: business_currency()) }}
                                    </p>
                                </div>
                                <div class="d-flex justify-content-between paid">
                                    <p> {{__('Paid')}} :</p>
                                    <p>{{ currency_format($purchase->paidAmount, currency: business_currency()) }}
                                    </p>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <p> {{__('Due')}} : </p>
                                    <p>{{ currency_format($purchase->dueAmount, currency: business_currency()) }}
                                    </p>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <table class="ph-invoice-table mt-2">
                <thead>
                    <tr>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Return Product') }}</th>
                        <th>{{ __('QTY') }}</th>
                        <th class="text-end">{{ __('Amount') }}</th>
                    </tr>
                </thead>
                @php $total_return_amount = 0; @endphp
                <tbody>
                    @foreach ($purchase_returns as $key => $return)
                    @foreach ($return->details as $detail)
                        @php
                            $total_return_amount += $detail->return_amount ?? 0;
                        @endphp
                    <tr>

                        <td class="text-start">{{ formatted_date($return->return_date) }}</td>
                        <td>{{ $detail->purchaseDetail->product->productName ?? '' }}</td>
                        <td class="text-center">{{ $detail->return_qty ?? 0 }}</td>
                        <td class="text-end"> {{ currency_format($detail->return_amount ?? 0, currency: business_currency()) }}</td>
                    </tr>
                    @endforeach
                    @endforeach
                    <tr>
                        <td class="total-due" colspan="2">
                            <div class="payment-type-container">
                                <h6 class="text-center">{{ __('Payment Type') }}: {{ $purchase->payment_type_id != null ? $purchase->payment_type->name ?? '' : $purchase->paymentType }}</h6>

                            </div>
                        </td>
                        <td colspan="3">
                            <div class="calculate-amount">
                                <div class="d-flex justify-content-between">
                                    <p>{{ __('Total Return') }}:</p>
                                    <p>{{ currency_format($total_return_amount, currency: business_currency()) }}</p>
                                </div>
                                <div class="in-border"></div>

                                <div class="d-flex justify-content-between total-amount">
                                    <p>{{ __('Payable') }}:</p>
                                    <p>{{ currency_format($purchase->totalAmount, currency: business_currency()) }}</p>
                                </div>
                                <div class="d-flex justify-content-between paid">
                                    <p>{{ __('Paid') }}:</p>
                                    <p>{{ currency_format($purchase->paidAmount, currency: business_currency()) }}</p>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <p>{{ __('Due') }}:</p>
                                    <p>{{ currency_format($purchase->dueAmount, currency: business_currency()) }}</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        @else
            <table class="ph-invoice-table">
                <thead>
                    <tr>
                        <th class="text-start table-sl"> {{__('SL')}}.</th>
                        <th> {{__('Product')}} </th>
                        <th> {{__('QTY')}} </th>
                        <th> {{__('U.Price')}} </th>
                        <th class="text-end"> {{__('Amount')}} </th>
                    </tr>
                </thead>

                @php
                    $subtotal = 0;
                @endphp
                <tbody>
                    @foreach ($purchase->details as $detail)
                        @php
                            $productTotal = ($detail->productPurchasePrice ?? 0) * ($detail->quantities ?? 0);
                            $subtotal += $productTotal;
                        @endphp
                        <tr>
                            <td class="text-start table-sl">{{ $loop->iteration }}</td>
                            <td>{{ $detail->product->productName ?? '' }}</td>
                            <td class="text-center">{{ $detail->quantities ?? '' }}</td>
                            <td class="text-center">
                                {{ currency_format($detail->productPurchasePrice ?? 0, currency: business_currency()) }}
                            </td>
                            <td class="text-end">{{ currency_format($productTotal, currency: business_currency()) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="2" class="">
                            <div class="payment-type-container">
                                <h6 class="text-start"> {{__('Payment Type')}} :
                                    {{ $purchase->payment_type_id != null ? $purchase->payment_type->name ?? '' : $purchase->paymentType }}
                                </h6>
                            </div>
                        </td>
                        <td colspan="3">
                            <div class="calculate-amount">
                                <div class="d-flex justify-content-between">
                                    <p> {{__('Sub-Total')}} :</p>
                                    <p>{{ currency_format($subtotal, currency: business_currency()) }}</p>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <p> {{__('Vat')}} : </p>
                                    <p>{{ currency_format($purchase->vat_amount, currency: business_currency()) }}
                                    </p>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <p> {{__('Discount')}} :</p>
                                    <p>{{ currency_format($purchase->discountAmount, currency: business_currency()) }}
                                    </p>
                                </div>
                                <div class="d-flex justify-content-between ">
                                    <p> {{__('Shipping Charge')}} :</p>
                                    <p>{{ currency_format($purchase->shipping_charge, currency: business_currency()) }}
                                    </p>
                                </div>

                                <div class="d-flex justify-content-between total-amount">
                                    <p> {{__('Net Payable')}} :</p>
                                    <p>{{ currency_format($subtotal + $purchase->vat_amount - ($purchase->discountAmount + $total_discount) + $purchase->shipping_charge, currency: business_currency()) }}
                                    </p>
                                </div>
                                <div class="d-flex justify-content-between paid">
                                    <p> {{__('Paid')}} :</p>
                                    <p>{{ currency_format($purchase->paidAmount, currency: business_currency()) }}
                                    </p>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <p> {{__('Due')}} : </p>
                                    <p>{{ currency_format($purchase->dueAmount, currency: business_currency()) }}
                                    </p>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        @endif

        <div class="invoice-footer-sm mt-3">
            <h5>{{ get_business_option('business-settings')['gratitude_message'] ?? '' }}</h5>
            @if (!empty(get_business_option('business-settings')['note']))
            <p class="text-center note-pera"> {{ get_business_option('business-settings')['note_label'] ?? '' }}  : {{ get_business_option('business-settings')['note'] ?? '' }}</p>
            @endif
            <div class="scanner">
                <img src="{{ asset(get_business_option('business-settings')['invoice_scanner_logo'] ?? 'assets/images/icons/scanner.svg') }}" alt="">
            </div>
            <h6>{{ get_option('general')['admin_footer_text'] ?? '' }} <a href="{{ get_option('general')['admin_footer_link'] ?? '#' }}" target="_blank">{{ get_option('general')['admin_footer_link_text'] ?? '' }}</h6>
        </div>
    </div>
</div>

@push('js')
    <script>
        $(document).ready(function() {
            window.print();
        })
    </script>
@endpush

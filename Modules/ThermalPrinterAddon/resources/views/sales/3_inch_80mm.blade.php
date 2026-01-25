<div class="invoice-container-sm">
    <div class="invoice-content invoice-content-size">

        <div class="invoice-logo">
            <img src="{{ asset(get_business_option('business-settings')['invoice_logo'] ?? 'assets/images/default.svg') ?? '' }}"
                alt="Logo">
        </div>
        <div class="mt-2">
            <h4 class="company-name">{{ $sale->business->companyName ?? 'Pos Pro' }}</h4>
            <div class="company-info">
                <p> {{__('Address')}} : {{ $sale->business->address ?? '' }}</p>
                <p> {{__('Mobile')}} : {{ $sale->business->phoneNumber ?? '' }}</p>
                <p> {{__('Email')}} : {{ auth()->user()->email ?? '' }}</p>
                @if (!empty($sale->business->vat_name))
                <p>{{ $sale->business->vat_name }} : {{ $sale->business->vat_no ?? '' }}</p>
                @endif
            </div>
        </div>
        <h3 class="invoice-title my-1">
            {{__('invoice')}}
        </h3>

        <div class="invoice-info">
            <div class="">
                <p> {{__('Invoice')}} : {{ $sale->invoiceNumber ?? '' }}</p>
                <p> {{__('Name')}} : {{ $sale->party->name ?? 'Cash' }}</p>
                <p> {{__('Mobile')}} : {{ $sale->party->phone ?? '' }}</p>
            </div>
            <div class="">
                <p class="text-end date"> {{__('Date')}} : {{ formatted_date($sale->saleDate ?? '') }}</p>
                <p class="text-end time"> {{__('Time')}} : {{ formatted_time($sale->saleDate ?? '') }}</p>
                <p class="text-end"> {{__('Sales By')}} : {{ $sale->user->name }}</p>
            </div>
        </div>
        @if (!$sale_returns->isEmpty())
            <table class="ph-invoice-table">
                <thead>
                    <tr>
                        <th class="text-start table-sl">{{ __('SL') }}</th>
                        <th>{{ __('Product') }}</th>
                        <th>{{ __('QTY') }}</th>
                        <th>{{ __('U.Price') }}</th>
                        <th class="text-end">{{ __('Amount') }}</th>
                    </tr>
                </thead>
                @php
                    $subtotal = 0;
                @endphp
                <tbody>
                    @foreach ($sale->details as $detail)
                        @php
                            $productTotal = ($detail->price ?? 0) * ($detail->quantities ?? 0);
                            $subtotal += $productTotal;
                        @endphp
                        <tr>
                            <td class="text-start table-sl">{{ $loop->iteration }}</td>
                            <td>{{ $detail->product->productName ?? '' }}</td>
                            <td class="text-center">{{ $detail->quantities ?? '' }}</td>
                            <td class="text-center">
                                {{ currency_format($detail->price ?? 0, currency: business_currency()) }}</td>
                            <td class="text-end">
                                {{ currency_format($productTotal, currency: business_currency()) }}
                            </td>
                        </tr>
                    @endforeach
                    <tr>
                        <td class="total-due" colspan="2">
                            <div class="payment-type-container">
                                <h6 class="text-center">{{ __('Payment Type') }}:
                                    {{ $sale->payment_type_id != null ? $sale->payment_type->name ?? '' : $sale->paymentType }}
                                </h6>
                                <p class="text-center">{{ $sale->meta['notes'] ?? ($sale->meta['note'] ?? '') }}
                                </p>
                            </div>
                        </td>
                        <td colspan="3">
                            <div class="calculate-amount">
                                <div class="d-flex justify-content-between">
                                    <p>{{ __('Sub-Total') }}:</p>
                                    <p>{{ currency_format($subtotal, currency: business_currency()) }}</p>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <p>{{ __('Vat') }}:</p>
                                    <p> {{ currency_format($sale->tax_amount, currency: business_currency()) }}
                                    </p>
                                </div>
                                <div class="d-flex justify-content-between ">
                                    <p>{{ __('Delivery charge') }}:</p>
                                    <p>{{ currency_format($sale->shipping_charge, currency: business_currency()) }}
                                    </p>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <p>{{ __('Discount') }}
                                        @if ($sale->discount_type == 'percent')
                                            ({{ $sale->discount_percent }}%)
                                        @endif:
                                    </p>
                                    <p> {{ currency_format($sale->discountAmount + $total_discount, currency: business_currency()) }}
                                    </p>
                                </div>
                                <div class="in-border"></div>

                                <div class="d-flex justify-content-between total-amount">
                                    <p>{{ __('Net Payable') }}:</p>
                                    <p> {{ currency_format($subtotal + $sale->tax_amount - ($sale->discountAmount + $total_discount) + $sale->shipping_charge + $sale->rounding_amount, currency: business_currency()) }}
                                    </p>
                                </div>
                                <div class="d-flex justify-content-between paid">
                                    <p>{{ __('Total Payable') }}:</p>
                                    <p> {{ currency_format($subtotal + $sale->tax_amount - ($sale->discountAmount + $total_discount) + $sale->shipping_charge, currency: business_currency()) }}
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
                        <th class="invoice-th">{{ __('Date') }}</th>
                        <th class="invoice-th">{{ __('Return Product') }}</th>
                        <th class="invoice-th">{{ __('QTY') }}</th>
                        <th class="invoice-th text-end">{{ __('Amount') }}</th>
                    </tr>
                </thead>
                @php $total_return_amount = 0; @endphp
                <tbody>
                    @foreach ($sale_returns as $key => $return)
                        @foreach ($return->details as $detail)
                            @php
                                $total_return_amount += $detail->return_amount ?? 0;
                            @endphp
                            <tr>

                                <td class="text-start">{{ formatted_date($return->return_date) }}</td>
                                <td>{{ $detail->saleDetail->product->productName ?? '' }}</td>
                                <td class="text-center">{{ $detail->return_qty ?? 0 }}</td>
                                <td class="text-end">
                                    {{ currency_format($detail->return_amount ?? 0, currency: business_currency()) }}
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                    <tr>
                        <td class="total-due" colspan="2">
                            <div class="payment-type-container">
                                <h6 class="text-center">{{ __('Payment Type') }}:
                                    {{ $sale->payment_type_id != null ? $sale->payment_type->name ?? '' : $sale->paymentType }}
                                </h6>
                                <p class="text-center">{{ $sale->meta['notes'] ?? ($sale->meta['note'] ?? '') }}
                                </p>
                            </div>
                        </td>
                        <td colspan="3">
                            <div class="calculate-amount">
                                <div class="d-flex justify-content-between">
                                    <p>{{ __('Total Return') }}:</p>
                                    <p>{{ currency_format($total_return_amount, currency: business_currency()) }}
                                    </p>
                                </div>
                                <div class="in-border"></div>

                                <div class="d-flex justify-content-between total-amount">
                                    <p>{{ __('Payable') }}:</p>
                                    <p>{{ currency_format($sale->totalAmount, currency: business_currency()) }}
                                    </p>
                                </div>
                                <div class="d-flex justify-content-between paid">
                                    <p>{{ __('Paid') }}:</p>
                                    <p>{{ currency_format($sale->paidAmount, currency: business_currency()) }}</p>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <p>{{ __('Due') }}:</p>
                                    <p>{{ currency_format($sale->dueAmount, currency: business_currency()) }}</p>
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
                        <th class="text-start table-sl" > {{__('SL')}}.</th>
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
                    @foreach ($sale->details as $detail)
                        @php
                            $productTotal = ($detail->price ?? 0) * ($detail->quantities ?? 0);
                            $subtotal += $productTotal;
                        @endphp
                        <tr>
                            <td class="text-start table-sl">{{ $loop->iteration }}</td>
                            <td>{{ $detail->product->productName ?? '' }}</td>
                            <td class="text-center">{{ $detail->quantities ?? '' }}</td>
                            <td class="text-center">
                                {{ currency_format($detail->price ?? 0, currency: business_currency()) }}
                            </td>
                            <td class="text-end">
                                {{ currency_format($productTotal, currency: business_currency()) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="2" class="">
                            <div class="payment-type-container">
                                <h6 class="text-start"> {{__('Payment Type')}} :
                                    {{ $sale->payment_type_id != null ? $sale->payment_type->name ?? '' : $sale->paymentType }}
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
                                    <p> {{__('Vat')}} :</p>
                                    <p>{{ currency_format($sale->vat_amount, currency: business_currency()) }}
                                    </p>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <p> {{__('Discount')}} :</p>
                                    <p>{{ currency_format($sale->discountAmount, currency: business_currency()) }}
                                    </p>
                                </div>
                                <div class="d-flex justify-content-between ">
                                    <p> {{__('Shipping Charge')}} :</p>
                                    <p>{{ currency_format($sale->shipping_charge, currency: business_currency()) }}
                                    </p>
                                </div>

                                <div class="d-flex justify-content-between total-amount">
                                    <p> {{__('Net Payable')}} :</p>
                                    <p>{{ currency_format($sale->totalAmount, currency: business_currency()) }}
                                    </p>
                                </div>
                                <div class="d-flex justify-content-between paid">
                                    <p> {{__('Paid')}} :</p>
                                    <p>{{ currency_format($sale->paidAmount, currency: business_currency()) }}
                                    </p>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <p> {{__('Due')}} :</p>
                                    <p>{{ currency_format($sale->dueAmount, currency: business_currency()) }}
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
                <p class="text-center note-pera">{{ get_business_option('business-settings')['note_label'] ?? '' }} :
                    {{ get_business_option('business-settings')['note'] ?? '' }}</p>
            @endif
            <div class="scanner">
                <img src="{{ asset(get_business_option('business-settings')['invoice_scanner_logo'] ?? 'assets/images/icons/scanner.svg') }}"
                    alt="">
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

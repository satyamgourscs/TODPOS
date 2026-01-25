<div class="invoice-container-sm">
    <div class="invoice-content invoice-content-size">

        <div class="invoice-logo">
            <img src="{{ asset(get_business_option('business-settings')['invoice_logo'] ?? 'assets/images/default.svg') ?? '' }}"
                alt="">
        </div>
        <div>
            <h4 class="company-name">{{ auth()->user()->business->companyName ?? 'Pos Pro' }}</h4>
            <div class="company-info">
                <p>Address: {{ auth()->user()->business->address ?? '' }}</p>
                <p>Mobile: {{ auth()->user()->business->phoneNumber ?? '' }}</p>
                <p>Email: {{ auth()->user()->email ?? '' }}</p>
                <p>{{ get_business_option('business-settings')['vat_name'] ?? '' }}: {{ get_business_option('business-settings')['vat_no'] ?? '' }}</p>
            </div>
        </div>
        <h3 class="invoice-title my-1">
            invoice
        </h3>

        <div class="invoice-info">
            <div class="">
                <p>Invoice : {{ $party->dueCollect->invoiceNumber ?? '' }}</p>
                <p>Bill To: {{ $party->name ?? 'Guest' }}</p>
                <p>Mobile: {{ $party->phone ?? '' }}</p>
            </div>
            <div class="">
                <p class="text-end date">Date : {{ formatted_date($party->dueCollect->paymentDate ?? '') }}</p>
                <p class="text-end time">Time: {{ formatted_time($party->dueCollect->paymentDate ?? '') }}</p>
                <p class="text-end">Collected By: {{ $party->dueCollect->business->companyName ?? '' }}</p>
            </div>
        </div>
        <table class="ph-invoice-table">
            <thead>
                <tr>
                    <th class="text-start table-sl">SL</th>
                    <th>Total Due</th>
                    <th>Payment Amount</th>
                    <th>Remaining Due</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-start table-sl">1</td>
                    <td>{{ currency_format($due_collect->totalDue ?? 0, currency: business_currency()) }}</td>
                    <td class="text-center">
                        {{ currency_format($due_collect->payDueAmount ?? 0, currency: business_currency()) }}</td>
                    <td class="text-center">
                        {{ currency_format($due_collect->dueAmountAfterPay ?? 0, currency: business_currency()) }}
                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="">
                        <div class="payment-type-container">
                            <h6 class="text-center">Payment Type:
                                {{ $due_collect->payment_type_id != null ? $due_collect->payment_type->name ?? '' : $due_collect->paymentType }}
                            </h6>
                            <p class="text-center">Product sold will not be Exchanged Without invice</p>
                        </div>
                    </td>
                    <td colspan="3">
                        <div class="calculate-amount">

                            <div class="d-flex justify-content-between">
                                <p>Payable Amount:</p>
                                <p>{{ currency_format($due_collect->totalDue ?? 0, currency: business_currency()) }}
                                </p>
                            </div>

                            <div class="d-flex justify-content-between">
                                <p>Received Amount:</p>
                                <p>{{ currency_format($due_collect->payDueAmount ?? 0, currency: business_currency()) }}
                                </p>
                            </div>

                            <div class="d-flex justify-content-between">
                                <p>Change Amt/Due:</p>
                                <p>{{ currency_format($due_collect->dueAmountAfterPay ?? 0, currency: business_currency()) }}
                                </p>
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="invoice-footer-sm mt-3">
            <h5>{{ get_business_option('business-settings')['gratitude_message'] ?? '' }}</h5>
            <div class="scanner">
                <img src="{{ asset('assets/images/icons/scanner.svg') }}" alt="">
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

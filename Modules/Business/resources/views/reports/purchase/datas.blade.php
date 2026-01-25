@foreach($purchases as $purchase)
    <tr>
        <td>{{ ($purchases->currentPage() - 1) * $purchases->perPage() + $loop->iteration }}</td>
        @if(auth()->user()->accessToMultiBranch())
        <td class="text-start">{{ $purchase->branch->name ?? '' }}</td>
        @endif
        <td class="text-start">{{ $purchase->invoiceNumber }}</td>
        <td class="text-start">{{ $purchase->party?->name }}</td>
        <td class="text-start">{{ currency_format($purchase->totalAmount, currency: business_currency()) }}</td>
        <td class="text-start">{{ currency_format($purchase->discountAmount, currency: business_currency()) }}</td>
        <td class="text-start">{{ currency_format($purchase->paidAmount, currency: business_currency()) }}</td>
        <td class="text-start">{{ currency_format($purchase->dueAmount, currency: business_currency()) }}</td>
        <td class="text-start">{{ currency_format($purchase->vat_amount, currency: business_currency()) }}</td>
        <td class="text-start">{{ $purchase->payment_type_id != null ? $purchase->payment_type->name ?? '' : $purchase->paymentType }}</td>
        <td class="text-start">{{ formatted_date($purchase->purchaseDate) }}</td>
    </tr>
@endforeach

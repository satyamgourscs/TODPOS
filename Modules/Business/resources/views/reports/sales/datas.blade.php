@foreach($sales as $sale)
    <tr>
        <td>{{ ($sales->currentPage() - 1) * $sales->perPage() + $loop->iteration }}</td>
        @if(auth()->user()->accessToMultiBranch())
        <td class="text-start">{{ $sale->branch->name ?? '' }}</td>
        @endif
        <td class="text-start">{{ $sale->invoiceNumber }}</td>
        <td class="text-start">{{ $sale->party?->name }}</td>
        <td class="text-start">{{ currency_format($sale->totalAmount, currency: business_currency()) }}</td>
        <td class="text-start">{{ currency_format($sale->discountAmount, currency: business_currency()) }}</td>
        <td class="text-start">{{ currency_format($sale->paidAmount, currency: business_currency()) }}</td>
        <td class="text-start">{{ currency_format($sale->dueAmount, currency: business_currency()) }}</td>
        <td class="text-start">{{ currency_format($sale->vat_amount, currency: business_currency()) }}</td>
        <td class="text-start">{{ $sale->payment_type_id != null ? $sale->payment_type->name ?? '' : $sale->paymentType }}</td>
        <td class="text-start">{{ formatted_date($sale->saleDate) }}</td>
    </tr>
@endforeach

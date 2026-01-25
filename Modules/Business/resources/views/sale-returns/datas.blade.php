@foreach($sales as $sale)
        @php
            $total_return_amount = $sale->saleReturns->sum('total_return_amount');
        @endphp
    <tr>
        <td>{{ ($sales->currentPage() - 1) * $sales->perPage() + $loop->iteration }}</td>
        <td>
            <a href="{{ route('business.sales.invoice', $sale->id) }}" target="_blank" class="text-primary">
                {{ $sale->invoiceNumber }}
            </a>
        </td>
        @if(auth()->user()->accessToMultiBranch())
        <td>{{ $sale->branch->name ?? '' }}</td>
        @endif
        <td>{{ formatted_date($sale->saleDate) }}</td>
        <td>{{ $sale->party->name ?? 'Guest' }}</td>
        <td>{{ currency_format($sale->totalAmount, currency: business_currency()) }}</td>
        <td>{{ currency_format($sale->paidAmount, currency: business_currency()) }}</td>
        <td>{{ currency_format($sale->dueAmount, currency: business_currency()) }}</td>
        <td>{{ currency_format($total_return_amount ?? 0, currency: business_currency()) }}</td>
    </tr>
@endforeach

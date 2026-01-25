@foreach ($sales as $sale)
    <tr>
        @usercan('sales.delete')
        <td class="w-60 checkbox">
            @if(!in_array($sale->id, $salesWithReturns))
            <input type="checkbox" name="ids[]" class="delete-checkbox-item  multi-delete" value="{{ $sale->id }}">
            @endif
        </td>
        @endusercan
        <td>{{ ($sales->currentPage() - 1) * $sales->perPage() + $loop->iteration }}</td>
        <td class="text-start">{{ formatted_date($sale->saleDate) }}</td>
        @if(auth()->user()->accessToMultiBranch())
        <td class="text-start">{{ $sale->branch->name ?? '' }}</td>
        @endif
        <td class="text-start">{{ $sale->invoiceNumber }}</td>
        <td class="text-start">{{ $sale->party->name ?? 'Guest' }}</td>
        <td class="text-start">{{ currency_format($sale->totalAmount, currency: business_currency()) }}</td>
        <td class="text-start">{{ currency_format($sale->discountAmount, currency: business_currency()) }}</td>
        <td class="text-start">{{ currency_format($sale->paidAmount, currency: business_currency()) }}</td>
        <td class="text-start">{{ currency_format($sale->dueAmount, currency: business_currency()) }}</td>
        <td class="text-start">{{ $sale->payment_type_id != null ? $sale->payment_type->name ?? '' : $sale->paymentType }}</td>
        <td>
            @if($sale->details->sum('quantities') == 0)
                <div class="paid-badge">{{ __('Returned') }}</div>
            @elseif($sale->dueAmount == 0)
                <div class="paid-badge">{{ __('Paid') }}</div>
            @elseif($sale->dueAmount > 0 && $sale->dueAmount < $sale->totalAmount)
                <div class="unpaid-badge">{{ __('Partial Paid') }}</div>
            @else
                <div class="unpaid-badge-2">{{ __('Unpaid') }}</div>
            @endif
        </td>

        <td class="d-print-none">
            <div class="dropdown table-action">
                <button type="button" data-bs-toggle="dropdown">
                    <i class="far fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu">
                    @usercan('sales.read')
                    <li>
                        <a target="_blank" href="{{ route('business.sales.invoice', $sale->id) }}">
                            <img src="{{ asset('assets/images/icons/Invoic.svg') }}" alt="">
                            {{ __('Invoice') }}
                        </a>
                    </li>
                    @endusercan
                    @if($sale->details->sum('quantities') != 0)
                    @usercan('sale-returns.read')
                    <li>
                        <a href="{{ route('business.sale-returns.create', ['sale_id' => $sale->id]) }}">
                            <i class="fal fa-undo-alt"></i>
                            {{ __('Sales Return') }}
                        </a>
                    </li>
                    @endusercan
                    @endif
                    @if(!in_array($sale->id, $salesWithReturns))
                    @usercan('sales.update')
                        <li>
                            <a href="{{ route('business.sales.edit', $sale->id) }}">
                                <i class="fal fa-edit"></i>
                                {{ __('Edit') }}
                            </a>
                        </li>
                    @endusercan
                    @usercan('sales.delete')
                    <li>
                        <a href="{{ route('business.sales.destroy', $sale->id) }}" class="confirm-action"
                            data-method="DELETE">
                            <i class="fal fa-trash-alt"></i>
                            {{ __('Delete') }}
                        </a>
                    </li>
                    @endusercan
                    @endif
                </ul>
            </div>
        </td>
    </tr>
@endforeach

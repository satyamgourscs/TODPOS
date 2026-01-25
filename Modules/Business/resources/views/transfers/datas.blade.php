@foreach ($transfers as $transfer)

    @php
        $totalQty = $transfer->transferProducts->sum('quantity');
        $totalStockValue = $transfer->transferProducts->sum(function ($product) {
            return $product->quantity * $product->unit_price;
        });
    @endphp

    <tr>
        @usercan('transfers.delete')
        <td class="w-60 checkbox d-print-none">
            <input type="checkbox" name="ids[]" class="delete-checkbox-item multi-delete" value="{{ $transfer->id }}">
        </td>
        @endusercan
        <td>{{ ($transfers->currentPage() - 1) * $transfers->perPage() + $loop->iteration }}</td>
        <td>{{ $transfer->transfer_date }}</td>
        <td>{{ $transfer->invoice_no }}</td>
        @if(moduleCheck('MultiBranchAddon') && multibranch_active())
        <td>{{ $transfer->fromBranch->name ?? '' }}</td>
        @endif
        @if (moduleCheck('WarehouseAddon'))
        <td>{{ $transfer->fromWarehouse->name ?? '' }}</td>
        @endif
        @if(moduleCheck('MultiBranchAddon') && multibranch_active())
        <td>{{ $transfer->toBranch->name ?? '' }}</td>
        @endif
        @if (moduleCheck('WarehouseAddon'))
        <td>{{ $transfer->toWarehouse->name ?? '' }}</td>
        @endif
        <td>{{ $totalQty }}</td>
        <td>{{ currency_format($totalStockValue, currency: business_currency()) }}</td>
        <td>
            @if ($transfer->status === 'pending')
                <span class="badge bg-warning">{{ ucfirst($transfer->status) }}</span>
            @elseif ($transfer->status === 'completed')
                <span class="badge bg-success">{{ ucfirst($transfer->status) }}</span>
            @elseif ($transfer->status === 'cancelled')
                <span class="badge bg-danger">{{ ucfirst($transfer->status) }}</span>
            @else
                <span class="badge bg-secondary">{{ ucfirst($transfer->status) }}</span>
            @endif
        </td>

        <td class="d-print-none">
            <div class="dropdown table-action">
                <button type="button" data-bs-toggle="dropdown">
                    <i class="far fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu">
                    @usercan('transfers.update')
                    @if ($transfer->status === 'pending')
                    <li>
                        <a href="{{ route('business.transfers.edit', $transfer->id) }}">
                            <i class="fal fa-edit"></i>
                            {{ __('Edit') }}
                        </a>
                    </li>
                    @endif
                    @endusercan
                    @usercan('transfers.delete')
                    <li>
                        <a href="{{ route('business.transfers.destroy', $transfer->id) }}" class="confirm-action"
                            data-method="DELETE">
                            <i class="fal fa-trash-alt"></i>
                            {{ __('Delete') }}
                        </a>
                    </li>
                    @endusercan
                </ul>
            </div>
        </td>
    </tr>
@endforeach

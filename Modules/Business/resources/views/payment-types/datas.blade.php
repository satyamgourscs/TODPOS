@foreach($paymentTypes as $paymentType)
    <tr>
        @usercan('payment-types.delete')
        <td class="w-60 checkbox">
            <input type="checkbox" name="ids[]" class="delete-checkbox-item multi-delete" value="{{ $paymentType->id }}">
        </td>
        @endusercan
        <td>{{ ($paymentTypes->currentPage() - 1) * $paymentTypes->perPage() + $loop->iteration }}</td>

        <td class="text-start">{{ $paymentType->name }}</td>
        <td>
            <label class="switch">
                <input type="checkbox" {{ $paymentType->status == 1 ? 'checked' : '' }} class="status" data-url="{{ route('business.payment-types.status', $paymentType->id) }}">
                <span class="slider round"></span>
            </label>
        </td>
        <td class="d-print-none">
            <div class="dropdown table-action">
                <button type="button" data-bs-toggle="dropdown">
                    <i class="far fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu">
                    <li>
                        @usercan('payment-types.update')
                        <a href="#payment-types-edit-modal" data-bs-toggle="modal" class="payment-types-edit-btn"
                        data-url="{{ route('business.payment-types.update', $paymentType->id) }}"
                        data-payment-types-name="{{ $paymentType->name }}"
                        data-payment-types-status="{{ $paymentType->status }}"
                        ><i class="fal fa-pencil-alt"></i>{{__('Edit')}}</a>
                        @endusercan
                    </li>
                    <li>
                        @usercan('payment-types.delete')
                        <a href="{{ route('business.payment-types.destroy', $paymentType->id) }}" class="confirm-action" data-method="DELETE">
                            <i class="fal fa-trash-alt"></i>
                            {{ __('Delete') }}
                        </a>
                        @endusercan
                    </li>
                </ul>
            </div>
        </td>
    </tr>
@endforeach

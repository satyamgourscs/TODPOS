@foreach($expenses as $expense)
    <tr>
        @usercan('expenses.delete')
        <td class="w-60 checkbox">
            <input type="checkbox" name="ids[]" class="delete-checkbox-item multi-delete" value="{{ $expense->id }}">
        </td>
        @endusercan
        <td>{{ ($expenses->currentPage() - 1) * $expenses->perPage() + $loop->iteration }}</td>
        @if(auth()->user()->accessToMultiBranch())
        <td class="text-start">{{ $expense->branch->name ?? '' }}</td>
        @endif
        <td class="text-start">{{ currency_format($expense->amount, currency: business_currency()) }}</td>
        <td class="text-start">{{ $expense->category?->categoryName }}</td>
        <td class="text-start">{{ $expense->expanseFor }}</td>
        <td class="text-start">{{ $expense->payment_type_id != null ? $expense->payment_type->name ?? '' : $expense->paymentType }}</td>
        <td class="text-start">{{ $expense->referenceNo }}</td>
        <td class="text-start">{{ formatted_date($expense->expenseDate) }}</td>
        <td class="d-print-none">
            <div class="dropdown table-action">
                <button type="button" data-bs-toggle="dropdown">
                    <i class="far fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu">
                    <li>
                        @usercan('expenses.update')
                        <a href="#expenses-edit-modal" data-bs-toggle="modal" class="expense-edit-btn"
                           data-url="{{ route('business.expenses.update', $expense->id) }}"
                           data-expense-category-id="{{ $expense->expense_category_id }}"
                           data-expense-amount="{{ $expense->amount }}"
                           data-expense-for="{{ $expense->expanseFor }}"
                           data-expense-payment-type="{{ $expense->payment_type_id != null ? $expense->payment_type->name ?? '' : $expense->paymentType }}"
                           data-expense-payment-type-id="{{ $expense->payment_type_id }}"
                           data-expense-reference-no="{{ $expense->referenceNo }}"
                           data-expense-date="{{ \Carbon\Carbon::parse($expense->exoenseDate)->format('Y-m-d') }}"
                           data-expense-note="{{ $expense->note }}">
                           <i class="fal fa-pencil-alt"></i>{{ __('Edit') }}
                        </a>
                        @endusercan
                    </li>
                    <li>
                        @usercan('expenses.delete')
                        <a href="{{ route('business.expenses.destroy', $expense->id) }}" class="confirm-action" data-method="DELETE">
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

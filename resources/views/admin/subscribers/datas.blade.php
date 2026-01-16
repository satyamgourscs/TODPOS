@foreach ($subscribers as $subscriber)
    <tr>
        <td>{{ $loop->index + 1 }} <i class="{{ request('id') == $subscriber->id ? 'fas fa-bell text-red' : '' }}"></i>
        </td>
        <td>{{ formatted_date($subscriber->created_at) }}</td>
        <td>{{ $subscriber->business->companyName ?? 'N/A' }}</td>
        <td>{{ $subscriber->business?->category?->name ?? 'N/A' }}</td>
        <td>{{ $subscriber->plan->subscriptionName ?? 'N/A' }}</td>
        <td>{{ formatted_date($subscriber->created_at) }}</td>
        <td>{{ $subscriber->created_at ? formatted_date($subscriber->created_at->addDays($subscriber->duration)) : '' }}</td>
        <td>{{ $subscriber->gateway->name ?? 'N/A' }}</td>
        <td>
            <div class="badge bg-{{ $subscriber->payment_status == 'reject' ? 'danger' : ($subscriber->payment_status == 'unpaid' ? 'warning' : 'primary') }}">
                {{ ucfirst($subscriber->payment_status) }}
            </div>
        </td>
    </tr>
@endforeach

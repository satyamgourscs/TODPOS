@foreach($branches as $branch)
    <tr>
        @usercan('branches.delete')
        <td class="w-60 checkbox">
            <input type="checkbox" name="ids[]" class="delete-checkbox-item  multi-delete" value="{{ $branch->id }}">
        </td>
        @endusercan
        <td>{{ ($branches->currentPage() - 1) * $branches->perPage() + $loop->iteration }}</td>
        <td class="text-start">
            <a href="{{ route('multibranch.switch-branch', $branch->id) }}" class="text-primary fw-bold">
                {{ $branch->name }}
            </a>
        </td>
        <td class="text-start">{{ $branch->phone }}</td>
        <td class="text-start">{{ $branch->email }}</td>
        <td class="text-start">{{ $branch->address }}</td>
         <td>
            <label class="switch">
                <input type="checkbox" {{ $branch->status == 1 ? 'checked' : '' }} class="status" data-url="{{ route('multibranch.branches.status', $branch->id) }}">
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
                        @usercan('branches.update')
                        <a href="#branches-edit-modal" data-bs-toggle="modal" class="branches-edit-btn"
                        data-url="{{ route('multibranch.branches.update', $branch->id) }}"
                        data-name="{{ $branch->name }}"
                        data-phone="{{ $branch->phone }}"
                        data-email="{{ $branch->email }}"
                        data-address="{{ $branch->address }}"
                        data-opening-balance="{{ $branch->branchOpeningBalance }}"
                        data-remaining-balance="{{ $branch->branchRemainingBalance }}"
                        data-desc="{{ $branch->description }}">
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12.1606 3.73679L13.2119 2.68547C13.7925 2.10484 14.7339 2.10484 15.3145 2.68547C15.8951 3.2661 15.8951 4.20748 15.3145 4.78811L14.2632 5.83943M12.1606 3.73679L8.23515 7.66222C7.4512 8.4462 7.05919 8.83815 6.79228 9.31582C6.52535 9.7935 6.2568 10.9214 6 12C7.07857 11.7432 8.2065 11.4746 8.68418 11.2077C9.16185 10.9408 9.5538 10.5488 10.3378 9.76485L14.2632 5.83943M12.1606 3.73679L14.2632 5.83943" stroke="#4B5563" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M15.75 9C15.75 12.1819 15.75 13.773 14.7615 14.7615C13.773 15.75 12.1819 15.75 9 15.75C5.81802 15.75 4.22703 15.75 3.23851 14.7615C2.25 13.773 2.25 12.1819 2.25 9C2.25 5.81802 2.25 4.22703 3.23851 3.23851C4.22703 2.25 5.81802 2.25 9 2.25" stroke="#4B5563" stroke-width="1.2" stroke-linecap="round"/>
                        </svg>
                        {{ __('Edit') }}
                        </a>
                        @endusercan
                    </li>
                     <li>
                        @usercan('branches.update')
                        <a href="{{ route('multibranch.switch-branch', $branch->id) }}" class="branches-edit-btn">
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M5.99902 5.9018V5.51033C5.99902 4.43153 6.76472 3.50454 7.82411 3.30081L13.0741 2.2912C14.4618 2.02435 15.749 3.08765 15.749 4.50071V13.5C15.749 14.9131 14.4618 15.9763 13.0741 15.7095L7.82411 14.6999C6.76473 14.4962 5.99902 13.5692 5.99902 12.4904V12.0989" stroke="#4B5563" stroke-width="1.25" stroke-linecap="square"/>
                        <path d="M9.74899 11.25L11.999 8.99998L9.74899 6.75M11.624 8.99998H2.24902" stroke="#4B5563" stroke-width="1.25" stroke-linecap="square"/>
                        </svg>
                        {{ __('Login') }}
                        </a>
                        @endusercan
                    </li>
                    <li>
                        @usercan('branches.delete')
                        <a href="{{ route('multibranch.branches.destroy', $branch->id) }}" class="confirm-action" data-method="DELETE">
                            <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M14.625 4.125L14.1602 11.6438C14.0414 13.5648 13.9821 14.5253 13.5006 15.2159C13.2625 15.5573 12.956 15.8455 12.6005 16.062C11.8816 16.5 10.9192 16.5 8.99452 16.5C7.06734 16.5 6.10372 16.5 5.38429 16.0612C5.0286 15.8443 4.722 15.5556 4.48401 15.2136C4.00266 14.5219 3.94459 13.5601 3.82846 11.6364L3.375 4.125" stroke="#4B5563" stroke-width="1.2" stroke-linecap="round"/>
                            <path d="M6.75 8.80078H11.25" stroke="#4B5563" stroke-width="1.2" stroke-linecap="round"/>
                            <path d="M7.875 11.7422H10.125" stroke="#4B5563" stroke-width="1.2" stroke-linecap="round"/>
                            <path d="M2.25 4.125H15.75M12.0416 4.125L11.5297 3.0688C11.1896 2.3672 11.0195 2.01639 10.7261 1.79761C10.6611 1.74908 10.5922 1.7059 10.5201 1.66852C10.1953 1.5 9.80542 1.5 9.02572 1.5C8.22645 1.5 7.82685 1.5 7.49662 1.67559C7.42343 1.71451 7.35359 1.75943 7.28783 1.80988C6.99109 2.03753 6.82533 2.40116 6.49381 3.12844L6.03955 4.125" stroke="#4B5563" stroke-width="1.2" stroke-linecap="round"/>
                            </svg>
                            {{ __('Delete') }}
                        </a>
                        @endusercan
                    </li>
                </ul>
            </div>
        </td>
    </tr>
@endforeach

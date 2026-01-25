@foreach($brands as $brand)
    <tr>
        @usercan('brands.delete')
        <td class="w-60 checkbox">
            <input type="checkbox" name="ids[]" class="delete-checkbox-item  multi-delete" value="{{ $brand->id }}">
        </td>
        @endusercan
        <td>{{ ($brands->currentPage() - 1) * $brands->perPage() + $loop->iteration }}</td>
        <td>
            <img src="{{ asset($brand->icon ?? 'assets/images/logo/upload2.jpg') }}" alt="Img" class="table-product-img">
        </td>

        <td class="text-start">{{ $brand->brandName }}</td>
        <td class="text-start">{{ Str::limit($brand->description, 20, '...') }}</td>
        <td>
            <label class="switch">
                <input type="checkbox" {{ $brand->status == 1 ? 'checked' : '' }} class="status" data-url="{{ route('business.brands.status', $brand->id) }}">
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
                        @usercan('brands.update')
                        <a href="#brand-edit-modal" data-bs-toggle="modal" class="brand-edit-btn"
                        data-url="{{ route('business.brands.update', $brand->id) }}"
                        data-brands-name="{{ $brand->brandName }}"
                        data-brands-icon="{{ asset($brand->icon ?? 'assets/images/icons/upload-icon.svg') }}"
                        data-brands-description="{{ $brand->description }}"><i class="fal fa-pencil-alt"></i>{{__('Edit')}}</a>
                        @endusercan
                    </li>
                    <li>
                        @usercan('brands.delete')
                        <a href="{{ route('business.brands.destroy', $brand->id) }}" class="confirm-action" data-method="DELETE">
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

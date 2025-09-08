<x-layouts.admin.master>
    <x-data-display.card>
        <x-slot name="header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title">Supplier Products</h5>
            </div>
        </x-slot>
        <x-data-display.table>
            <x-data-display.thead>
                <th>ID</th>
                <th>Internal ID</th>
                <th>Name</th>
                <th>Country Code</th>
                <th>Currency Code</th>
                <th>Description</th>
                <th>Actions</th>
            </x-data-display.thead>

            <x-data-display.tbody>
                @foreach($products as $product)
                    <tr>
                        <td>{{ $product->id }}</td>
                        <td>{{ $product->internal_id }}</td>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->country_code }}</td>
                        <td>{{ $product->currency_code }}</td>
                        <td>{{ Str::limit($product->description, 50) }}</td>
                        <x-data-display.table-actions>
                            <li>
                                <a href="{{route('supplierProducts.show', $product->id)}}" class="dropdown-item">
                                    <i class="ri-eye-2-line"></i> Details
                                </a>
                            </li>
                            <li>
                                <form method="POST" action="#">
                                    @csrf @method('DELETE')
                                    <button type="button" class="dropdown-item text-danger remove-item-btn">
                                        <i class="ri-delete-bin-line"></i> Delete
                                    </button>
                                </form>
                            </li>
                        </x-data-display.table-actions>
                    </tr>
                @endforeach
            </x-data-display.tbody>
        </x-data-display.table>
    </x-data-display.card>

</x-layouts.admin.master>

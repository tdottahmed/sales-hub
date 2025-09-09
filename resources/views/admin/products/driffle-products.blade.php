<x-layouts.admin.master>
    <x-data-display.card>
        <x-slot name="header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title">Driffle Products</h5>
            </div>
        </x-slot>
        <table class="table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Driffle ID</th>
                <th>Title</th>
                <th>Platform</th>
                <th>Region</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td colspan="6">
                    <form action="{{ route('driffleProducts.index') }}" method="GET" class="mb-3">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control"
                                   placeholder="Search by title, platform or region..." value="{{ request('search') }}">
                            <button class="btn btn-primary" type="submit">Search</button>
                        </div>
                    </form>
                </td>
            </tr>
            @foreach($products as $product)
                <tr>
                    <td>{{ $product->id }}</td>
                    <td>{{ $product->product_id }}</td>
                    <td>{{ $product->title }}</td>
                    <td>{{ $product->platform }}</td>
                    <td>{{ $product->regions }}</td>
                    <td>
                        <div class="dropdown">
                            <button class="btn btn-soft-secondary btn-sm dropdown" type="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ri-more-fill align-middle"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a href="{{route('driffleProducts.show', $product->id)}}" class="dropdown-item">
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
                            </ul>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $products->onEachSide(1)->links('pagination::bootstrap-5') }}
    </x-data-display.card>
</x-layouts.admin.master>

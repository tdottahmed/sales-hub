<x-layouts.admin.master>
    <x-data-display.card>
        <x-slot name="header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title">Mapped Driffle Products</h5>
            </div>
        </x-slot>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Driffle Title</th>
                    <th>Supplier product Title</th>
                    <th>Platform</th>
                    <th>Region</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="7">
                        <form action="{{ route('driffleProducts.index') }}" method="GET" class="mb-3">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control"
                                    placeholder="Search by title, platform or region..."
                                    value="{{ request('search') }}">
                                <button class="btn btn-primary" type="submit">Search</button>
                            </div>
                        </form>
                    </td>
                </tr>
                @foreach ($products as $product)
                    <tr>
                        <td>{{ $product->id }}</td>
                        <td>{{ $product->driffleProduct->title }}</td>
                        <td>{{ $product->product->name }}</td>
                        <td>{{ $product->driffleProduct->platform }}</td>
                        <td>{{ $product->driffleProduct->regions }}</td>
                        <td>
                            @if(!$product->is_created_offer)
                                <form method="POST"
                                      action="{{ route('driffleProducts.createOffer', $product->id) }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="ri-file-add-fill"></i> Create Offer
                                    </button>
                                </form>
                            @else
                                <span class="badge bg-success">Offer Created</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $products->onEachSide(1)->links('pagination::bootstrap-5') }}
    </x-data-display.card>
</x-layouts.admin.master>

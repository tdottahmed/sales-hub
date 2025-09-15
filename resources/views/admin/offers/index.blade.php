<x-layouts.admin.master>
    <x-data-display.card>
        <x-slot name="header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title">All Offers</h5>
            </div>
        </x-slot>
        <table class="table table-responsive">
            <thead>
            <tr>
                <th>ID</th>
                <th>Driffle Title</th>
                <th>Platform</th>
                <th>Region</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($offers as $offer)
                <tr>
                    <td>{{ $offer->id }}</td>
                    <td>{{ $offer->driffleProduct->title }}</td>
                    <td>{{ $offer->driffleProduct->platform }}</td>
                    <td>{{ $offer->driffleProduct->regions }}</td>
                    <td>
                       <div class="d-flex gap-2">
                           <form method="POST"
                                 action="{{ route('driffleProducts.updateOffer', $offer->id) }}">
                               @csrf
                               <button type="submit" class="btn btn-primary btn-sm">
                                   <i class="ri-file-info-fill"></i> Update Offer
                               </button>
                           </form>
                           <form method="POST"
                                 action="{{ route('driffleProducts.updateOfferPrice', $offer->id) }}">
                               @csrf
                               <button type="submit" class="btn btn-primary btn-sm">
                                   <i class="ri-file-info-fill"></i> Update Offer Price
                               </button>
                           </form>
                           <form method="POST"
                                 action="{{ route('driffleProducts.toggleOffer', $offer->id) }}">
                               @csrf
                               <button type="submit" class="btn btn-primary btn-sm">
                                   <i class="ri-file-info-fill"></i> Toggle Offer
                               </button>
                           </form>
                       </div>

                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $offers->onEachSide(1)->links('pagination::bootstrap-5') }}
    </x-data-display.card>
</x-layouts.admin.master>

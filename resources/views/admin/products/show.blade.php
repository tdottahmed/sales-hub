<x-layouts.admin.master>
    <x-data-display.card>
        <x-slot name="header">
            <div class="d-flex justify-content-between align-items-start">
                <div class="d-flex align-items-center gap-3">
                    @if(!empty($product->logo_url))
                        <img src="{{ $product->logo_url }}" alt="{{ $product->name }} logo" class="rounded border bg-white" style="height:64px;width:64px;object-fit:contain;">
                    @endif
                    <div>
                        <h5 class="card-title mb-1">{{ $product->name }}</h5>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge bg-light text-dark">Internal ID: {{ $product->internal_id }}</span>
                            <span class="badge bg-info text-dark">{{ $product->country_code }}</span>
                            <span class="badge bg-success">{{ $product->currency_code }}</span>
                        </div>
                    </div>
                </div>
                <div class="text-muted small text-end">
                    <div>Updated: {{ \Illuminate\Support\Carbon::parse($product->updated_at)->toDayDateTimeString() }}</div>
                    <div>Created: {{ \Illuminate\Support\Carbon::parse($product->created_at)->toDayDateTimeString() }}</div>
                    @if(!empty($product->modified_date))
                        <div>Modified (source): {{ \Illuminate\Support\Carbon::parse($product->modified_date)->toDayDateTimeString() }}</div>
                    @endif
                </div>
            </div>
        </x-slot>

        <div class="row g-4">
            <div class="col-12 col-lg-8">
                @if(!empty($product->description))
                    <div class="card mb-3">
                        <div class="card-header">
                            <strong>Description</strong>
                        </div>
                        <div class="card-body">
                            <p class="mb-0">{!! nl2br(e($product->description)) !!}</p>
                        </div>
                    </div>
                @endif

                @if(!empty($product->redemption_instructions))
                    <div class="card mb-3">
                        <div class="card-header">
                            <strong>Redemption Instructions</strong>
                        </div>
                        <div class="card-body">
                            <div class="mb-0">{!! nl2br(e($product->redemption_instructions)) !!}</div>
                        </div>
                    </div>
                @endif

                @if(!empty($product->terms))
                    <div class="card mb-3">
                        <div class="card-header">
                            <button class="btn btn-sm btn-link p-0" type="button" data-bs-toggle="collapse" data-bs-target="#termsCollapse" aria-expanded="false" aria-controls="termsCollapse">
                                <strong>Terms</strong>
                            </button>
                        </div>
                        <div id="termsCollapse" class="collapse">
                            <div class="card-body">
                                <div class="mb-0">{!! nl2br(e($product->terms)) !!}</div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-12 col-lg-4">
                <div class="card h-100">
                    <div class="card-header">
                        <strong>Product Meta</strong>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-5">Product ID</dt>
                            <dd class="col-7">{{ $product->id }}</dd>

                            <dt class="col-5">Country</dt>
                            <dd class="col-7">{{ $product->country_code }}</dd>

                            <dt class="col-5">Currency</dt>
                            <dd class="col-7">{{ $product->currency_code }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </x-data-display.card>

    <x-data-display.card class="mt-4">
        <x-slot name="header">
            <div class="d-flex justify-content-between align-items-center w-100">
                <h5 class="card-title mb-0">Variations ({{ $product->variations->count() }})</h5>
            </div>
        </x-slot>

        @if($product->variations->isEmpty())
            <div class="alert alert-secondary mb-0">No variations available for this product.</div>
        @else
            <x-data-display.table>
                <x-data-display.thead>
                    <th>Name</th>
                    <th class="text-nowrap">UUID</th>
                    <th class="text-nowrap">External ID</th>
                    <th class="text-end text-nowrap">Min Price</th>
                    <th class="text-end text-nowrap">Max Price</th>
                    <th class="text-end text-nowrap">Min Face Value</th>
                    <th class="text-end text-nowrap">Max Face Value</th>
                    <th class="text-end text-nowrap">Stock</th>
                </x-data-display.thead>

                <x-data-display.tbody>
                    @foreach($product->variations as $variation)
                        <tr>
                            <td class="fw-medium">{{ $variation->name }}</td>
                            <td class="text-muted small">{{ $variation->uuid }}</td>
                            <td class="text-muted small">{{ $variation->external_id }}</td>
                            <td class="text-center">
                                {{ number_format((float)$variation->min_price, 2) }}
                            </td>
                            <td class="text-center">
                                {{ number_format((float)$variation->max_price, 2) }}
                            </td>
                            <td class="text-center">
                                {{ number_format((float)$variation->min_face_value, 2) }}
                            </td>
                            <td class="text-center">
                                {{ number_format((float)$variation->max_face_value, 2) }}
                            </td>
                            <td class="text-center">{{ (int)$variation->count }}</td>
                        </tr>
                    @endforeach
                </x-data-display.tbody>
            </x-data-display.table>
        @endif
    </x-data-display.card>
</x-layouts.admin.master>

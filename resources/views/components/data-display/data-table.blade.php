@props([
    'appendedColumns' => [],
])
<table id="scroll-horizontal" class="table nowrap align-middle dt-responsive" style="width:100%">
    <thead>
        <tr>
            <th scope="col" style="width: 10px;">
                <div class="form-check">
                    <input class="form-check-input fs-15" type="checkbox" id="checkAll" value="option">
                </div>
            </th>
            <th>{{ __('SL No') }}</th>
            @isset($appendedColumns)
                @foreach ($appendedColumns as $column)
                    <th>{{ ucwords(str_replace('_', ' ', $column)) }}</th>
                @endforeach
            @endisset
            @foreach ($columns as $column)
                <th>{{ ucwords(str_replace('_', ' ', $column)) }}</th>
            @endforeach
            <th class="text-center">Action</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($rows as $row)
            <tr>
                <th scope="row">
                    <div class="form-check">
                        <input class="form-check-input fs-15" type="checkbox" name="checkAll" value="option1">
                    </div>
                </th>
                <td>{{ $loop->iteration }}</td>
                @isset($appendedColumns)
                    @foreach ($appendedColumns as $column)
                        <td>{{ $row->$column }}</td>
                    @endforeach
                @endisset
                @foreach ($columns as $column)
                    <td>
                        @if ($column === 'created_at' || $column === 'updated_at')
                            {{ \Carbon\Carbon::parse($row->$column)->diffForHumans() }}
                        @else
                            {{ $row->$column }}
                        @endif
                    </td>
                @endforeach
                <td class="text-center">
                    <x-data-display.data-table-action :actions="$getActions($row)" />
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="{{ count($columns) + 3 }}" class="text-center">
                    No data found
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

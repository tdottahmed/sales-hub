<x-layouts.admin.master>
    <x-data-display.card>
        <x-slot name="header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title">Permissions</h5>
                <x-action.link href="{{ route('permissions.create') }}"
                    icon="ri-add-line">{{ __('Create Permission') }}</x-action.link>
            </div>
        </x-slot>
        <x-data-display.table>
            <x-data-display.thead>
                <th>Name</th>
                <th>Group</th>
                <th>Guard Name</th>
                <th>Created At</th>
                <th>Updated At</th>
                <th>Actions</th>
            </x-data-display.thead>

            <x-data-display.tbody>
                @foreach($permissions as $permission)
                    <tr>
                        <td>{{$permission->name}}</td>
                        <td>{{$permission->group}}</td>
                        <td>{{$permission->guard_name}}</td>
                        <td>{{$permission->created_at}}</td>
                        <td>{{$permission->updated_at}}</td>
                        <x-data-display.table-actions>
                         <li>
                             <a href="{{ route('permissions.edit', $permission->id) }}" class="dropdown-item">
                                 <i class="ri-edit-box-line"></i> Edit
                             </a>
                         </li>
                          <li>
                              <form method="POST" action="{{ route('permissions.destroy', $permission->id) }}">
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

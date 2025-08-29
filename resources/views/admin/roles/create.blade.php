   <x-layouts.admin.master>
       <x-data-display.card>
           <x-slot name="header">
               <div class="d-flex justify-content-between align-items-center">
                   <h5 class="card-title">{{ __('Create Role') }}</h5>
                   <x-action.link href="{{ route('roles.index') }}"
                       icon="ri-add-line">{{ __('Role List') }}</x-action.link>
               </div>
           </x-slot>
           <x-data-entry.form action="{{ route('roles.store') }}">
               <x-data-entry.input type="text" name="name" label="Name" placeholder="Name" required />
               <x-data-entry.input type="text" name="guard_name" label="Gurd Name" placeholder="Guard Name"
                   required />
               <x-permission-accordion :permissionGroups="$permissionGroups" />
           </x-data-entry.form>
       </x-data-display.card>

   </x-layouts.admin.master>

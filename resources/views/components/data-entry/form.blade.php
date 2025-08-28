@props([
    'action' => '',
    'method' => 'POST',
    'model' => null,
    'fields' => [],
])
@php
    $method = $model ? 'PATCH' : $method;
@endphp

<form action="{{ $action }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if ($model)
        @method('PATCH')
    @endif
    {{-- <!-- Dynamically generate fields -->
    @foreach ($fields as $field)
        <x-input 
            :name="$field['name']" 
            :label="$field['label']" 
            :value="$field['value'] ?? ''" 
            :type="$field['type'] ?? 'text'" 
            :options="$field['options'] ?? []" 
            :required="$field['required'] ?? false" 
        />
    @endforeach --}}
    {{ $slot }}
    <!-- Submit Button -->
    <div class="d-flex justify-content-end gap-2">
        <x-action.link icon="ri-close-line" variant="danger" href="{{ route('dashboard') }}"
            class="ms-2">Cancel</x-action.link>
        <x-action.button variant="primary">
            {{ $model ? 'Update' : 'Submit' }}
        </x-action.button>
    </div>
</form>

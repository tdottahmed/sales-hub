@props([
    'displayId' => false,
])
<div class="form-group mb-3">
    <label for="{{ $name }}">{{ __($label) }}</label>
    <select id="{{ $name }}" name="{{ $name }}" class="form-control js-example-basic-single"
        style="width: 100%;">
        <option value="" disabled>{{ $placeholder }}</option>
        @foreach ($options as $key => $value)
            <option value="{{ $key }}" {{ $key == $selected ? 'selected' : '' }}>
                {{ $displayId ? $key . ' - ' . $value : $value }}
            </option>
        @endforeach
    </select>
    @error($name)
        <span class="text-danger">{{ __($message) }}</span>
    @enderror
</div>

@push('scripts')
    <script>
        $(document).ready(function() {
            $('.js-example-basic-single').select2();
        });
    </script>
@endpush

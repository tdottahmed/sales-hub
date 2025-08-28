<div>
    <label for="{{ $name }}" class="form-label">{{ __($label ?? ucfirst($name)) }}</label>
    <input type="text" id="{{ $name }}" name="{{ $name }}" value="{{ $value ?? old($name) }}"
        placeholder="{{ $placeholder }}" class="form-control flatpickr-input" autocomplete="off" />

    @error($name)
        <span class="text-danger">{{ __($message) }}</span>
    @enderror
</div>
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            flatpickr('#{{ $name }}', {
                dateFormat: "Y-m-d", // Adjust format as needed
                altInput: true,
                altFormat: "F j, Y",
            });
        });
    </script>
@endpush

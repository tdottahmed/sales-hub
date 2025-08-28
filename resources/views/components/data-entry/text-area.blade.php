@props(['name', 'label' => '', 'placeholder' => '', 'value' => ''])

<div class="mb-3">
    @if ($label)
        <label for="{{ $name }}" class="form-label">{{ __($label) }}</label>
        @if ($attributes->has('required'))
            <span class="text-danger">*</span>
        @endif
    @endif
    <!-- Default Input -->
    <textarea name="{{ $name }}" id="{{ $name }}" placeholder="{{ $placeholder }}" cols="10" rows="5"
        value="{{ old($name, $value) }}" {{ $attributes->merge(['class' => 'form-control']) }}
        @error($name) aria-invalid="true" @enderror @if ($attributes->has('required')) required @endif>{{ $value ?? old($name) }}</textarea>

    @error($name)
        <span class="text-danger">{{ __($message) }}</span>
    @enderror
</div>

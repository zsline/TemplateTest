@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'autofocus' => false,
    'id' => null,
    'optional' => false,
])

@php
    $id = $id ?? $name;
    $hasError = $errors->has($name);
    $inputValue = old($name, $value);
@endphp

<div class="form-group">
    @if ($label)
        <label for="{{ $id }}" class="form-label">
            {{ $label }}
            @if ($optional)
                <span class="form-label-optional">(опционально)</span>
            @endif
        </label>
    @endif

    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $id }}"
        value="{{ $type !== 'password' ? $inputValue : '' }}"
        @if ($placeholder) placeholder="{{ $placeholder }}" @endif
        @if ($required) required @endif
        @if ($autofocus) autofocus @endif
        {{ $attributes->merge(['class' => 'form-input' . ($hasError ? ' has-error' : '')]) }}
    />

    @error($name)
        <span class="form-error">{{ $message }}</span>
    @enderror
</div>

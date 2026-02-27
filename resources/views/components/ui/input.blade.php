@props([
    'label' => null,
    'name',
    'type' => 'text',
    'value' => null,
    'required' => false,
    'placeholder' => null,
])

<div {{ $attributes->only('class') }}>
    @if($label)
        <label class="form-label" for="{{ $name }}">{{ $label }}</label>
    @endif
    <input
        class="form-control @error($name) is-invalid @enderror"
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ old($name, $value) }}"
        @if($placeholder) placeholder="{{ $placeholder }}" @endif
        @if($required) required @endif
        {{ $attributes->except('class') }}
    >
    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

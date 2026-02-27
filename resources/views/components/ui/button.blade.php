@props([
    'variant' => 'primary',
    'size' => null,
    'icon' => null,
])

@php
    $classes = 'btn';
    $classes .= ' btn-' . $variant;
    if ($size) {
        $classes .= ' btn-' . $size;
    }
@endphp

<button {{ $attributes->merge(['class' => $classes]) }}>
    @if($icon)
        <i class="bi bi-{{ $icon }} me-1" aria-hidden="true"></i>
    @endif
    {{ $slot }}
</button>

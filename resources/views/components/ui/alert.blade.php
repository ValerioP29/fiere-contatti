@props(['variant' => 'success'])
<div {{ $attributes->merge(['class' => 'alert alert-' . $variant . ' rounded-3']) }} role="alert">
    {{ $slot }}
</div>

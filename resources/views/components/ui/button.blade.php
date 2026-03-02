@props([
    'variant' => 'primary',
    'size' => null,
])

@php
$base = 'inline-flex items-center justify-center rounded-lg font-medium transition focus:outline-none disabled:opacity-60';

$sizes = [
    'sm' => 'px-3 py-1.5 text-xs',
    null  => 'px-4 py-2 text-sm',
];

$variants = [
    'primary'          => 'bg-indigo-600 text-white hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1',
    'outline-primary'  => 'border border-indigo-200 bg-indigo-50 text-indigo-700 hover:bg-indigo-100',
    'secondary'        => 'bg-slate-100 text-slate-700 hover:bg-slate-200',
    'outline-secondary'=> 'border border-slate-200 text-slate-700 hover:bg-slate-100',
    'danger'           => 'bg-rose-600 text-white hover:bg-rose-700',
    'outline-danger'   => 'border border-rose-200 text-rose-700 hover:bg-rose-50',
];

$classes = $base
    . ' ' . ($sizes[$size] ?? $sizes[null])
    . ' ' . ($variants[$variant] ?? $variants['primary']);
@endphp

<button {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</button>

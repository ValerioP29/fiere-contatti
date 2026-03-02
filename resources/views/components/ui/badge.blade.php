@props(['variant' => 'secondary'])

@php
$variants = [
    'secondary' => 'bg-slate-100 text-slate-700',
    'info'      => 'bg-sky-100 text-sky-700',
    'success'   => 'bg-emerald-100 text-emerald-700',
    'warning'   => 'bg-amber-100 text-amber-700',
    'danger'    => 'bg-rose-100 text-rose-700',
];
$classes = 'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium '
    . ($variants[$variant] ?? $variants['secondary']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</span>

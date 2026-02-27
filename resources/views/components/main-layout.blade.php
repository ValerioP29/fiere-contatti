@props(['title' => null])

<x-layouts.main :title="$title">
    {{ $slot }}
</x-layouts.main>

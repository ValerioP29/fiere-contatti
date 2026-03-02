@props(['title' => null, 'subtitle' => null])

<div {{ $attributes->merge(['class' => 'app-card']) }}>
    @if($title || $subtitle)
        <div class="{{ $slot->isEmpty() ? 'p-6 text-center' : 'border-b border-slate-100 px-5 py-4' }}">
            @if($title)
                <p class="font-semibold text-slate-900">{{ $title }}</p>
            @endif
            @if($subtitle)
                <p class="mt-0.5 text-sm text-slate-500">{{ $subtitle }}</p>
            @endif
        </div>
    @endif

    @if($slot->isNotEmpty())
        <div class="p-5">
            {{ $slot }}
        </div>
    @endif
</div>

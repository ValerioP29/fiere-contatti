@props(['title' => null, 'subtitle' => null])

<div {{ $attributes->merge(['class' => 'card border-0 shadow-sm rounded-4']) }}>
    @if($title || $subtitle)
        <div class="card-body border-bottom">
            @if($title)
                <h2 class="h5 mb-1">{{ $title }}</h2>
            @endif
            @if($subtitle)
                <p class="text-secondary mb-0">{{ $subtitle }}</p>
            @endif
        </div>
    @endif
    <div class="card-body">
        {{ $slot }}
    </div>
</div>

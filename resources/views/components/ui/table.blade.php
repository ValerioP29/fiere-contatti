<div class="table-responsive rounded-4 overflow-hidden border">
    <table {{ $attributes->merge(['class' => 'table table-hover align-middle mb-0 bg-white']) }}>
        {{ $slot }}
    </table>
</div>

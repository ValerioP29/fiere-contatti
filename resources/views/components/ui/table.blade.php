<div class="overflow-x-auto rounded-lg border border-slate-200">
    <table {{ $attributes->merge(['class' => 'app-table']) }}>
        {{ $slot }}
    </table>
</div>

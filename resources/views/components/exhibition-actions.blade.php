@props(['exhibition', 'dashboardMode' => false])

@php
    $publicUrl = $exhibition->public_token ? route('public.form', ['token' => $exhibition->public_token]) : null;
@endphp

<div class="flex justify-end">
    <div x-data="{ copied: false, failed: false, publicUrl: @js($publicUrl) }" class="w-full max-w-max">
        <div class="flex items-center justify-end gap-2">
            @if($dashboardMode)
                <button type="button"
                        @click="window.dispatchEvent(new CustomEvent('open-contact-modal', { detail: { exhibitionId: {{ $exhibition->id }}, exhibitionName: @js($exhibition->name) } }))"
                        class="inline-flex items-center gap-1.5 rounded-md border border-emerald-200 bg-emerald-50 px-2.5 py-1.5 text-xs font-medium text-emerald-700 transition hover:bg-emerald-100"
                        aria-label="Aggiungi contatto">
                    <x-icons.plus />
                    <span class="hidden md:inline">Aggiungi contatto</span>
                </button>
            @else
            <a href="{{ route('exhibitions.show', ['exhibition' => $exhibition, 'open' => 'create']) }}"
               class="inline-flex items-center gap-1.5 rounded-md border border-emerald-200 bg-emerald-50 px-2.5 py-1.5 text-xs font-medium text-emerald-700 transition hover:bg-emerald-100"
               aria-label="Aggiungi contatto">
                <x-icons.plus />
                <span class="hidden md:inline">Aggiungi contatto</span>
            </a>
            @endif

            <a href="{{ route('exhibitions.show', $exhibition) }}"
               class="inline-flex items-center gap-1.5 rounded-md border border-slate-200 px-2.5 py-1.5 text-xs font-medium text-slate-700 transition hover:bg-slate-100"
               aria-label="Dettagli">
                <x-icons.eye />
                <span class="hidden md:inline">Dettagli</span>
            </a>

            <a href="{{ route('exhibitions.edit', $exhibition) }}"
               class="inline-flex items-center gap-1.5 rounded-md border border-indigo-200 bg-indigo-50 px-2.5 py-1.5 text-xs font-medium text-indigo-700 transition hover:bg-indigo-100"
               aria-label="Modifica">
                <x-icons.pencil />
                <span class="hidden md:inline">Modifica</span>
            </a>

            <form method="POST" action="{{ route('exhibitions.destroy', $exhibition) }}" onsubmit="return confirm('Confermi l\'eliminazione della fiera?')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="inline-flex items-center gap-1.5 rounded-md border border-rose-200 bg-rose-50 px-2.5 py-1.5 text-xs font-medium text-rose-700 transition hover:bg-rose-100"
                        aria-label="Elimina">
                    <x-icons.trash />
                    <span class="hidden md:inline">Elimina</span>
                </button>
            </form>

            <button type="button"
                    @click="if (!publicUrl) return; navigator.clipboard.writeText(publicUrl).then(() => { copied = true; failed = false; setTimeout(() => copied = false, 1500); }).catch(() => { failed = true; copied = false; setTimeout(() => failed = false, 1500); })"
                    :disabled="!publicUrl"
                    class="inline-flex items-center gap-1.5 rounded-md border border-sky-200 bg-sky-50 px-2.5 py-1.5 text-xs font-medium text-sky-700 transition hover:bg-sky-100 disabled:cursor-not-allowed disabled:opacity-50"
                    aria-label="Condividi">
                <x-icons.link />
                <span class="hidden md:inline">Condividi</span>
            </button>
        </div>

        <p x-show="copied" x-cloak class="mt-1 text-right text-xs font-medium text-emerald-600">Link copiato!</p>
        <p x-show="failed" x-cloak class="mt-1 text-right text-xs font-medium text-rose-600">Copia non riuscita.</p>
    </div>
</div>

@props(['exhibition'])

@php
    $publicUrl = $exhibition->public_token ? route('public.form', ['token' => $exhibition->public_token]) : null;
@endphp

<div class="flex justify-end">
    <div x-data="{ copied: false, failed: false, publicUrl: @js($publicUrl) }" class="w-full max-w-max">
        <div class="hidden items-center justify-end gap-2 md:flex">
            <a href="{{ route('contacts.index', ['exhibition' => $exhibition, 'open' => 'create']) }}"
               class="inline-flex items-center gap-1.5 rounded-md border border-emerald-200 bg-emerald-50 px-2.5 py-1.5 text-xs font-medium text-emerald-700 transition hover:bg-emerald-100">
                <x-icons.plus />
                <span>Aggiungi contatto</span>
            </a>

            <a href="{{ route('exhibitions.show', $exhibition) }}"
               class="inline-flex items-center gap-1.5 rounded-md border border-slate-200 px-2.5 py-1.5 text-xs font-medium text-slate-700 transition hover:bg-slate-100">
                <x-icons.eye />
                <span>Dettagli</span>
            </a>

            <a href="{{ route('exhibitions.edit', $exhibition) }}"
               class="inline-flex items-center gap-1.5 rounded-md border border-indigo-200 bg-indigo-50 px-2.5 py-1.5 text-xs font-medium text-indigo-700 transition hover:bg-indigo-100">
                <x-icons.pencil />
                <span>Modifica fiera</span>
            </a>

            <form method="POST" action="{{ route('exhibitions.destroy', $exhibition) }}" onsubmit="return confirm('Confermi l\'eliminazione della fiera?')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="inline-flex items-center gap-1.5 rounded-md border border-rose-200 bg-rose-50 px-2.5 py-1.5 text-xs font-medium text-rose-700 transition hover:bg-rose-100">
                    <x-icons.trash />
                    <span>Elimina fiera</span>
                </button>
            </form>

            <button type="button"
                    @click="if (!publicUrl) return; navigator.clipboard.writeText(publicUrl).then(() => { copied = true; failed = false; setTimeout(() => copied = false, 1500); }).catch(() => { failed = true; copied = false; setTimeout(() => failed = false, 1500); })"
                    :disabled="!publicUrl"
                    class="inline-flex items-center gap-1.5 rounded-md border border-sky-200 bg-sky-50 px-2.5 py-1.5 text-xs font-medium text-sky-700 transition hover:bg-sky-100 disabled:cursor-not-allowed disabled:opacity-50">
                <x-icons.link />
                <span>Condividi link</span>
            </button>
        </div>

        <div class="md:hidden">
            <x-dropdown align="right" width="48">
                <x-slot name="trigger">
                    <button type="button" class="inline-flex items-center gap-2 rounded-md border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700">
                        Azioni
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                </x-slot>

                <x-slot name="content">
                    <x-dropdown-link :href="route('contacts.index', ['exhibition' => $exhibition, 'open' => 'create'])">Aggiungi contatto</x-dropdown-link>
                    <x-dropdown-link :href="route('exhibitions.show', $exhibition)">Dettagli</x-dropdown-link>
                    <x-dropdown-link :href="route('exhibitions.edit', $exhibition)">Modifica fiera</x-dropdown-link>
                    @if($publicUrl)
                        <button type="button" class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100"
                                @click="navigator.clipboard.writeText(publicUrl).then(() => { copied = true; failed = false; setTimeout(() => copied = false, 1500); }).catch(() => { failed = true; copied = false; setTimeout(() => failed = false, 1500); })">
                            Copia link pubblico
                        </button>
                        <x-dropdown-link :href="$publicUrl" target="_blank">Apri form pubblico</x-dropdown-link>
                    @endif
                    <form method="POST" action="{{ route('exhibitions.destroy', $exhibition) }}" onsubmit="return confirm('Confermi l\'eliminazione della fiera?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-rose-700 hover:bg-rose-50">Elimina fiera</button>
                    </form>
                </x-slot>
            </x-dropdown>
        </div>

        <p x-show="copied" x-cloak class="mt-1 text-right text-xs font-medium text-emerald-600">Link copiato!</p>
        <p x-show="failed" x-cloak class="mt-1 text-right text-xs font-medium text-rose-600">Copia non riuscita.</p>
    </div>
</div>

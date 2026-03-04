<x-main-layout :title="'Fiera: '.$exhibition->name">
    <div class="app-container py-6" x-data="{ copied: false, failed: false, publicUrl: @js($publicUrl) }">
        <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Dettagli fiera</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">{{ $exhibition->name }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ $exhibition->display_date }}{{ $exhibition->company ? ' · '.$exhibition->company : '' }}</p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100">
                    ← Torna alla lista
                </a>
                <a href="{{ route('contacts.index', ['exhibition' => $exhibition, 'open' => 'create']) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700 transition hover:bg-emerald-100">
                    <x-icons.plus />
                    Aggiungi contatto
                </a>
                <button type="button"
                        @click="navigator.clipboard.writeText(publicUrl).then(() => { copied = true; failed = false; setTimeout(() => copied = false, 1500); }).catch(() => { failed = true; copied = false; setTimeout(() => failed = false, 1500); })"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-sky-200 bg-sky-50 px-4 py-2 text-sm font-medium text-sky-700 transition hover:bg-sky-100">
                    <x-icons.link />
                    Condividi link form pubblico
                </button>
                <a href="{{ route('exhibitions.edit', $exhibition) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-2 text-sm font-medium text-indigo-700 transition hover:bg-indigo-100">
                    <x-icons.pencil />
                    Modifica
                </a>
                <form method="POST" action="{{ route('exhibitions.destroy', $exhibition) }}" onsubmit="return confirm('Confermi l\'eliminazione della fiera?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-medium text-rose-700 transition hover:bg-rose-100">
                        <x-icons.trash />
                        Elimina
                    </button>
                </form>
            </div>
        </div>

        <p x-show="copied" x-cloak class="mb-4 text-sm font-medium text-emerald-600">Link copiato negli appunti.</p>
        <p x-show="failed" x-cloak class="mb-4 text-sm font-medium text-rose-600">Copia non riuscita, usa il link qui sotto.</p>

        <section class="app-card mb-6 p-6">
            <h2 class="text-lg font-semibold text-slate-900">Informazioni principali</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Nome</p>
                    <p class="mt-1 text-sm text-slate-900">{{ $exhibition->name }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Data</p>
                    <p class="mt-1 text-sm text-slate-900">{{ $exhibition->display_date }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Azienda</p>
                    <p class="mt-1 text-sm text-slate-900">{{ $exhibition->company ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Link pubblico</p>
                    <a href="{{ $publicUrl }}" target="_blank" class="mt-1 inline-flex text-sm font-medium text-indigo-600 hover:text-indigo-500">
                        Apri form pubblico
                    </a>
                </div>
                <div class="sm:col-span-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Note</p>
                    <p class="mt-1 whitespace-pre-line text-sm text-slate-900">{{ $exhibition->note ?: 'Nessuna nota disponibile.' }}</p>
                </div>
            </div>
        </section>

        <section class="app-card p-6">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-lg font-semibold text-slate-900">Contatti raccolti</h2>
                <form method="GET" class="w-full sm:w-80">
                    <input type="text" name="q" value="{{ $q }}" placeholder="Cerca contatto..." class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
                </form>
            </div>

            <div class="overflow-x-auto rounded-lg border border-slate-200">
                <table class="app-table">
                    <thead class="app-thead">
                    <tr>
                        <th class="app-th">Nome</th>
                        <th class="app-th">Email</th>
                        <th class="app-th">Telefono</th>
                        <th class="app-th">Azienda</th>
                        <th class="app-th">Fonte</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($contacts as $contact)
                        <tr class="hover:bg-slate-50">
                            <td class="app-td font-medium text-slate-900">{{ trim($contact->first_name.' '.$contact->last_name) ?: '—' }}</td>
                            <td class="app-td text-slate-700">{{ $contact->email ?: '—' }}</td>
                            <td class="app-td text-slate-700">{{ $contact->phone ?: '—' }}</td>
                            <td class="app-td text-slate-700">{{ $contact->company ?: '—' }}</td>
                            <td class="app-td text-slate-600">{{ $contact->source }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-sm text-slate-500">Nessun contatto trovato.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $contacts->links() }}</div>
        </section>
    </div>
</x-main-layout>

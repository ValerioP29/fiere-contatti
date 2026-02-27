<x-main-layout :title="'Fiera: '.$exhibition->name">
    <div class="app-container py-6">
        <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Fiera</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">{{ $exhibition->name }}</h1>
                <div class="mt-3 flex flex-wrap gap-2">
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">ID #{{ $exhibition->id }}</span>
                    <span class="inline-flex items-center rounded-full bg-indigo-100 px-3 py-1 text-xs font-medium text-indigo-700">{{ $exhibition->display_date }}</span>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('exhibitions.index') }}"
                   class="inline-flex items-center rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100">
                    Torna alla lista
                </a>
                <a href="{{ Route::has('contacts.create') ? route('contacts.create', ['exhibition_id' => $exhibition->id]) : route('contacts.index', $exhibition) }}"
                   class="inline-flex items-center rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700 transition hover:bg-emerald-100">
                    Aggiungi contatto
                </a>
                <a href="{{ route('exhibitions.edit', $exhibition) }}" class="inline-flex items-center rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-2 text-sm font-medium text-indigo-700 transition hover:bg-indigo-100">
                    Modifica
                </a>
                <form method="POST" action="{{ route('exhibitions.destroy', $exhibition) }}" onsubmit="return confirm('Confermi l\'eliminazione della fiera?')">
                    @csrf
                    @method('DELETE')
                    <x-danger-button class="normal-case tracking-normal text-sm">
                        Elimina
                    </x-danger-button>
                </form>
            </div>
        </div>

        <div class="app-card mb-5 flex flex-wrap gap-2 p-2">
            <button type="button" data-tab-trigger="details" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white transition">Dettagli</button>
            <button type="button" data-tab-trigger="link" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100">Link pubblico</button>
            <button type="button" data-tab-trigger="contacts" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100">Contatti raccolti</button>
        </div>

                <section data-tab-panel="details" class="app-card p-6">
            <h2 class="text-lg font-semibold text-slate-900">Dettagli fiera</h2>
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
                <div class="sm:col-span-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Note</p>
                    <p class="mt-1 text-sm text-slate-900 whitespace-pre-line">{{ $exhibition->note ?: 'Nessuna nota disponibile.' }}</p>
                </div>
            </div>
        </section>

                <section data-tab-panel="link" class="hidden rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200/80">
            <h2 class="text-lg font-semibold text-slate-900">Link pubblico</h2>
            <p class="app-subheading">Condividi questo URL per raccogliere contatti dalla landing pubblica.</p>
            <div class="mt-4 flex flex-col gap-3 sm:flex-row">
                <input id="publicUrl" type="text" readonly value="{{ $publicUrl }}" class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-700">
                <button id="copyPublicUrl" type="button" class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-700">
                    Copia
                </button>
            </div>
            <p id="copyFeedback" class="mt-2 text-xs text-emerald-600"></p>
        </section>

                <section data-tab-panel="contacts" class="hidden rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200/80">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-lg font-semibold text-slate-900">Contatti raccolti</h2>
                <form method="GET" class="w-full sm:w-72">
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
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($contacts as $contact)
                        <tr class="hover:bg-slate-50">
                            <td class="app-td font-medium text-slate-900">{{ trim($contact->first_name.' '.$contact->last_name) ?: '—' }}</td>
                            <td class="app-td text-slate-700">{{ $contact->email ?: '—' }}</td>
                            <td class="app-td text-slate-700">{{ $contact->phone ?: '—' }}</td>
                            <td class="app-td text-slate-700">{{ $contact->company ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-sm text-slate-500">Nessun contatto trovato.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $contacts->links() }}
            </div>
        </section>
    </div>

    @push('scripts')
        <script>
            const tabButtons = document.querySelectorAll('[data-tab-trigger]');
            const tabPanels = document.querySelectorAll('[data-tab-panel]');

            tabButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    const target = button.dataset.tabTrigger;

                    tabButtons.forEach((btn) => {
                        btn.classList.remove('bg-slate-900', 'text-white');
                        btn.classList.add('text-slate-600', 'hover:bg-slate-100');
                    });

                    button.classList.add('bg-slate-900', 'text-white');
                    button.classList.remove('text-slate-600', 'hover:bg-slate-100');

                    tabPanels.forEach((panel) => {
                        panel.classList.toggle('hidden', panel.dataset.tabPanel !== target);
                    });
                });
            });

            document.getElementById('copyPublicUrl')?.addEventListener('click', async () => {
                const input = document.getElementById('publicUrl');
                const feedback = document.getElementById('copyFeedback');

                try {
                    await navigator.clipboard.writeText(input.value);
                    feedback.textContent = 'Link copiato negli appunti.';
                } catch (error) {
                    feedback.textContent = 'Copia non riuscita, copia manualmente il link.';
                }
            });

        </script>
    @endpush
</x-main-layout>

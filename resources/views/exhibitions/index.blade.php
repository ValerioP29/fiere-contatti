<x-main-layout title="Fiere">
    <div class="app-container py-6">
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="app-heading">Fiere</h1>
                <p class="app-subheading">Gestisci eventi, date e contatti raccolti in un unico spazio.</p>
            </div>

            <a href="{{ route('exhibitions.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">
                <span class="text-base leading-none">+</span>
                Nuova fiera
            </a>
        </div>

        <section class="app-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="app-table">
                    <thead class="app-thead">
                    <tr>
                        <th class="app-th">ID</th>
                        <th class="app-th">Nome</th>
                        <th class="app-th">Data</th>
                        <th class="app-th">Azienda</th>
                        <th class="px-4 py-3 text-right font-semibold">Azioni</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                    @forelse($exhibitions as $ex)
                        <tr class="transition hover:bg-slate-50">
                            <td class="app-td whitespace-nowrap font-medium text-slate-500">#{{ $ex->id }}</td>
                            <td class="app-td font-semibold text-slate-900">{{ $ex->name }}</td>
                            <td class="app-td whitespace-nowrap">{{ $ex->display_date }}</td>
                            <td class="app-td">{{ $ex->company ?: '—' }}</td>
                            <td class="app-td">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('exhibitions.show', $ex) }}"
                                       class="rounded-md border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:bg-slate-100">
                                        Apri
                                    </a>
                                    <a href="{{ route('exhibitions.edit', $ex) }}" class="rounded-md border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-xs font-medium text-indigo-700 transition hover:bg-indigo-100">
                                        Modifica
                                    </a>
                                    <form method="POST" action="{{ route('exhibitions.destroy', $ex) }}" data-confirm-delete="true">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="rounded-md border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-medium text-rose-700 transition hover:bg-rose-100">
                                            Elimina
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-14 text-center">
                                <h2 class="text-base font-semibold text-slate-900">Nessuna fiera disponibile</h2>
                                <p class="mt-1 text-sm text-slate-500">Crea la prima fiera per iniziare a raccogliere contatti.</p>
                                <a href="{{ route('exhibitions.create') }}" class="mt-4 inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                                    + Nuova fiera
                                </a>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <footer class="border-t border-slate-200 bg-slate-50/70 px-4 py-3 sm:px-6">
                {{ $exhibitions->links() }}
            </footer>
        </section>
    </div>

</x-main-layout>

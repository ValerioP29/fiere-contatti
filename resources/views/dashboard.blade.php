<x-main-layout title="Dashboard">
    <div class="app-container py-6">
        <div class="mb-6">
            <h1 class="app-heading">Dashboard</h1>
            <p class="app-subheading">Panoramica rapida delle fiere e dei contatti raccolti.</p>
        </div>

        <div class="mb-6 grid gap-4 sm:grid-cols-2">
            <section class="app-card p-5">
                <p class="text-sm font-medium text-slate-500">Fiere create</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ $exhibitionsCount }}</p>
            </section>
            <section class="app-card p-5">
                <p class="text-sm font-medium text-slate-500">Contatti raccolti</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ $contactsCount }}</p>
            </section>
        </div>

        <section class="app-card overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3 sm:px-6">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-600">Ultime fiere</h2>
                <a href="{{ route('exhibitions.create') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500">+ Nuova fiera</a>
            </div>

            <div class="overflow-x-auto">
                <table class="app-table">
                    <thead class="app-thead">
                    <tr>
                        <th class="app-th">Nome</th>
                        <th class="app-th">Data</th>
                        <th class="app-th">Azienda</th>
                        <th class="app-th text-right">Azioni</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                    @forelse($latestExhibitions as $exhibition)
                        <tr>
                            <td class="app-td font-semibold text-slate-900">{{ $exhibition->name }}</td>
                            <td class="app-td">{{ $exhibition->display_date }}</td>
                            <td class="app-td">{{ $exhibition->company ?: '—' }}</td>
                            <td class="app-td">
                                <x-exhibition-actions :exhibition="$exhibition" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-sm text-slate-500">Nessuna fiera disponibile. Crea la prima per iniziare.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-main-layout>

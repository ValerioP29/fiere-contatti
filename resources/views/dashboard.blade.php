<x-main-layout title="Dashboard">
    <div class="app-container py-6"
         x-data="{
            openContactModal: {{ (old('return_to') === 'dashboard' && $errors->any()) ? 'true' : 'false' }},
            submitting: false,
            contactFormAction: @js(route('exhibitions.contacts.store', '__EXHIBITION__')),
            selectedExhibitionName: @js(old('exhibition_name', '')),
            form: {
                first_name: @js(old('first_name', '')),
                last_name: @js(old('last_name', '')),
                email: @js(old('email', '')),
                phone: @js(old('phone', '')),
                company: @js(old('company', '')),
                note: @js(old('note', '')),
            },
            openModal(exhibitionId, exhibitionName) {
                this.contactFormAction = @js(route('exhibitions.contacts.store', '__EXHIBITION__')).replace('__EXHIBITION__', exhibitionId);
                this.selectedExhibitionName = exhibitionName;
                this.openContactModal = true;
            }
         }"
         @open-contact-modal.window="openModal($event.detail.exhibitionId, $event.detail.exhibitionName)">
        <div class="mb-6">
            <h1 class="app-heading">Dashboard</h1>
            <p class="app-subheading">Panoramica rapida delle fiere e dei contatti raccolti.</p>
        </div>

        <div class="mb-6 grid gap-4 sm:grid-cols-2">
            <section class="app-card p-4">
                <p class="text-sm font-medium text-text-muted">Fiere create</p>
                <p class="mt-2 text-2xl font-bold text-text sm:text-3xl">{{ $exhibitionsCount }}</p>
            </section>
            <section class="app-card p-4">
                <p class="text-sm font-medium text-text-muted">Contatti raccolti</p>
                <p class="mt-2 text-2xl font-bold text-text sm:text-3xl">{{ $contactsCount }}</p>
            </section>
        </div>

        <section class="app-card overflow-hidden">
            <div class="flex items-center justify-between border-b border-border px-4 py-3 sm:px-6">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-text-muted">Ultime fiere</h2>
                <a href="{{ route('exhibitions.create') }}" class="btn btn-ghost">+ Nuova fiera</a>
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
                    <tbody class="app-tbody">
                    @forelse($latestExhibitions as $exhibition)
                        <tr class="hover:bg-primary/5">
                            <td class="app-td font-semibold text-text">{{ $exhibition->name }}</td>
                            <td class="app-td">{{ $exhibition->display_date }}</td>
                            <td class="app-td">{{ $exhibition->company ?: '—' }}</td>
                            <td class="app-td">
                                <x-exhibition-actions :exhibition="$exhibition" :dashboard-mode="true" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-sm text-text-muted">Nessuna fiera disponibile. Crea la prima per iniziare.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <div x-show="openContactModal" x-cloak class="fixed inset-0 z-40 flex items-center justify-center bg-secondary/50 p-4" @keydown.escape.window="openContactModal = false">
            <div class="app-card w-full max-w-2xl p-5 shadow-card">
                <div class="mb-3 flex items-center justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-text">Aggiungi contatto</h3>
                        <p class="text-sm text-text-muted" x-text="selectedExhibitionName ? `Fiera: ${selectedExhibitionName}` : ''"></p>
                    </div>
                    <button type="button" @click="openContactModal = false" class="text-text-muted hover:text-text">✕</button>
                </div>

                <form method="POST" :action="contactFormAction" enctype="multipart/form-data" @submit="submitting = true" class="space-y-3">
                    @csrf
                    <input type="hidden" name="return_to" value="dashboard">
                    <input type="hidden" name="exhibition_name" :value="selectedExhibitionName">

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-text">Nome</label>
                            <input name="first_name" required x-model="form.first_name" class="input">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-text">Cognome</label>
                            <input name="last_name" required x-model="form.last_name" class="input">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-text">Email</label>
                            <input name="email" type="email" x-model="form.email" class="input">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-text">Telefono</label>
                            <input name="phone" x-model="form.phone" class="input">
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-text">Azienda</label>
                        <input name="company" x-model="form.company" class="input">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-text">Note</label>
                        <textarea name="note" rows="3" x-model="form.note" class="input"></textarea>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-text">Allegato</label>
                        <input name="contact_file" type="file" accept=".pdf,.jpg,.jpeg,.png,.webp" class="input">
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="openContactModal = false" class="btn btn-secondary">Annulla</button>
                        <button type="submit" :disabled="submitting" class="btn btn-primary">Salva</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-main-layout>

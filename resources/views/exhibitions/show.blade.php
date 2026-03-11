<x-main-layout :title="'Fiera: '.$exhibition->name">
    <div class="app-container py-6"
         x-data="{
            copied: false,
            failed: false,
            publicUrl: @js($publicUrl),
            open: {{ ($errors->any() || request('open') === 'create') ? 'true' : 'false' }},
            submitting: false,
            contactId: @js(old('contact_id')),
            form: {
                first_name: @js(old('first_name', '')),
                last_name: @js(old('last_name', '')),
                email: @js(old('email', '')),
                phone: @js(old('phone', '')),
                company: @js(old('company', '')),
                note: @js(old('note', '')),
            },
            formAction: @js(old('contact_id') ? route('exhibitions.contacts.update', [$exhibition, old('contact_id')]) : route('exhibitions.contacts.store', $exhibition)),
            formMethod: @js(old('contact_id') ? 'PUT' : 'POST'),
            openCreate() {
                this.contactId = null;
                this.formAction = @js(route('exhibitions.contacts.store', $exhibition));
                this.formMethod = 'POST';
                this.form = { first_name: '', last_name: '', email: '', phone: '', company: '', note: '' };
                this.open = true;
            },
            openEdit(data) {
                this.contactId = data.id;
                this.formAction = `{{ route('exhibitions.contacts.update', [$exhibition, '__CONTACT__']) }}`.replace('__CONTACT__', data.id);
                this.formMethod = 'PUT';
                this.form = {
                    first_name: data.first_name ?? '',
                    last_name: data.last_name ?? '',
                    email: data.email ?? '',
                    phone: data.phone ?? '',
                    company: data.company ?? '',
                    note: data.note ?? '',
                };
                this.open = true;
            },
            async generatePublicLink() {
                try {
                    const response = await fetch(@js(route('exhibitions.public-link', $exhibition)), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': @js(csrf_token()),
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ regenerate: false }),
                    });

                    if (!response.ok) {
                        throw new Error('request_failed');
                    }

                    const data = await response.json();
                    this.publicUrl = data.url ?? null;
                } catch (_) {
                    this.failed = true;
                    this.copied = false;
                    setTimeout(() => this.failed = false, 1500);
                }
            }
         }">
        <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-text-muted">Dettagli fiera</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-text sm:text-3xl">{{ $exhibition->name }}</h1>
                <p class="mt-1 text-sm text-text-muted">{{ $exhibition->display_date }}{{ $exhibition->company ? ' · '.$exhibition->company : '' }}</p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                    ← Torna alla lista
                </a>
                <button @click="openCreate()" type="button" class="btn btn-primary">
                    <x-icons.plus />
                    Aggiungi contatto
                </button>
                <button type="button"
                        @click="if (!publicUrl) return; navigator.clipboard.writeText(publicUrl).then(() => { copied = true; failed = false; setTimeout(() => copied = false, 1500); }).catch(() => { failed = true; copied = false; setTimeout(() => failed = false, 1500); })"
                        :disabled="!publicUrl"
                        class="btn btn-secondary disabled:cursor-not-allowed disabled:opacity-50">
                    <x-icons.link />
                    Condividi form pubblico
                </button>
                <a href="{{ route('exhibitions.edit', $exhibition) }}" class="btn btn-secondary">
                    <x-icons.pencil />
                    Modifica
                </a>
                <form method="POST" action="{{ route('exhibitions.destroy', $exhibition) }}" onsubmit="return confirm('Confermi l\'eliminazione della fiera?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <x-icons.trash />
                        Elimina
                    </button>
                </form>
            </div>
        </div>

        <p x-show="copied" x-cloak class="mb-4 text-sm font-medium text-success">Link copiato negli appunti.</p>
        <p x-show="failed" x-cloak class="mb-4 text-sm font-medium text-danger">Copia non riuscita, usa il link qui sotto.</p>

        <section class="app-card mb-6 p-6">
            <h2 class="text-lg font-semibold text-text">Informazioni principali</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-text-muted">Nome</p>
                    <p class="mt-1 text-sm text-text">{{ $exhibition->name }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-text-muted">Data</p>
                    <p class="mt-1 text-sm text-text">{{ $exhibition->display_date }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-text-muted">Azienda</p>
                    <p class="mt-1 text-sm text-text">{{ $exhibition->company ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-text-muted">Link pubblico</p>
                    @if($publicUrl)
                        <a href="{{ $publicUrl }}" target="_blank" class="link mt-1 inline-flex text-sm">
                            Apri form pubblico
                        </a>
                    @else
                        <p class="mt-1 text-sm text-text-muted">Link pubblico non generato</p>
                        <button type="button" @click="generatePublicLink()" class="btn btn-secondary mt-2 px-3 py-1.5 text-xs">
                            Genera link pubblico
                        </button>
                    @endif
                </div>
                <div class="sm:col-span-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-text-muted">Note</p>
                    <p class="mt-1 whitespace-pre-line text-sm text-text">{{ $exhibition->note ?: 'Nessuna nota disponibile.' }}</p>
                </div>
            </div>
        </section>

        <section class="app-card p-6">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-lg font-semibold text-text">Contatti raccolti</h2>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('exhibitions.contacts.export', $exhibition) }}?q={{ urlencode($q) }}" class="btn btn-secondary">
                        Esporta Excel
                    </a>
                    <form method="GET" class="w-full sm:w-72">
                        <input type="text" name="q" value="{{ $q }}" placeholder="Cerca contatto..." class="input">
                    </form>
                </div>
            </div>

            <div class="overflow-x-auto rounded-lg border border-border">
                <table class="app-table">
                    <thead class="app-thead">
                    <tr>
                        <th class="app-th">Nome</th>
                        <th class="app-th">Email</th>
                        <th class="app-th">Telefono</th>
                        <th class="app-th">Azienda</th>
                        <th class="app-th">Fonte</th>
                        <th class="app-th text-right">Azioni</th>
                    </tr>
                    </thead>
                    <tbody class="app-tbody">
                    @forelse($contacts as $contact)
                        <tr class="hover:bg-primary/5">
                            <td class="app-td font-medium text-text">{{ trim($contact->first_name.' '.$contact->last_name) ?: '—' }}</td>
                            <td class="app-td text-text">{{ $contact->email ?: '—' }}</td>
                            <td class="app-td text-text">{{ $contact->phone ?: '—' }}</td>
                            <td class="app-td text-text">{{ $contact->company ?: '—' }}</td>
                            <td class="app-td text-text-muted">{{ $contact->source }}</td>
                            <td class="app-td">
                                <div class="flex justify-end gap-2">
                                    @if($contact->file_path)
                                        <a href="{{ route('exhibitions.contacts.download', [$exhibition, $contact]) }}" class="btn btn-secondary px-2 py-1 text-xs">
                                            Download file
                                        </a>
                                    @endif
                                    <button type="button"
                                            @click="openEdit({{ Illuminate\Support\Js::from(['id' => $contact->id, 'first_name' => $contact->first_name, 'last_name' => $contact->last_name, 'email' => $contact->email, 'phone' => $contact->phone, 'company' => $contact->company, 'note' => $contact->note]) }})"
                                            class="btn btn-secondary px-2 py-1 text-xs">
                                        Modifica
                                    </button>
                                    <form method="POST" action="{{ route('exhibitions.contacts.destroy', [$exhibition, $contact]) }}" onsubmit="return confirm('Eliminare questo contatto?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger px-2 py-1 text-xs">Elimina</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-sm text-text-muted">Nessun contatto trovato.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $contacts->links() }}</div>
        </section>

        <div x-show="open" x-cloak class="fixed inset-0 z-40 flex items-center justify-center bg-secondary/50 p-4" @keydown.escape.window="open = false">
            <div x-cloak class="app-card w-full max-w-2xl p-5 shadow-card">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-text" x-text="contactId ? 'Modifica contatto' : 'Aggiungi contatto'"></h3>
                    <button type="button" @click="open = false" class="text-text-muted hover:text-text">✕</button>
                </div>

                <form method="POST" :action="formAction" enctype="multipart/form-data" @submit="submitting = true" class="space-y-3">
                    @csrf
                    <input type="hidden" name="contact_id" :value="contactId">
                    <template x-if="formMethod === 'PUT'"><input type="hidden" name="_method" value="PUT"></template>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-text">Nome</label>
                            <input name="first_name" required x-model="form.first_name" class="input">
                            @error('first_name')<p class="text-xs text-danger">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-text">Cognome</label>
                            <input name="last_name" required x-model="form.last_name" class="input">
                            @error('last_name')<p class="text-xs text-danger">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="mb-1 block text-sm font-medium text-text">Email</label><input name="email" type="email" x-model="form.email" class="input"></div>
                        <div><label class="mb-1 block text-sm font-medium text-text">Telefono</label><input name="phone" x-model="form.phone" class="input"></div>
                    </div>
                    <div><label class="mb-1 block text-sm font-medium text-text">Azienda</label><input name="company" x-model="form.company" class="input"></div>
                    <div><label class="mb-1 block text-sm font-medium text-text">Note</label><textarea name="note" rows="3" x-model="form.note" class="input"></textarea></div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-text">Allegato</label>
                        <input name="contact_file" type="file" accept=".pdf,.jpg,.jpeg,.png,.webp" class="input">
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="open = false" class="btn btn-secondary">Annulla</button>
                        <button type="submit" :disabled="submitting" class="btn btn-primary">Salva</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-main-layout>

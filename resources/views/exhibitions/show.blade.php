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
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Dettagli fiera</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">{{ $exhibition->name }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ $exhibition->display_date }}{{ $exhibition->company ? ' · '.$exhibition->company : '' }}</p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100">
                    ← Torna alla lista
                </a>
                <button @click="openCreate()" type="button" class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700 transition hover:bg-emerald-100">
                    <x-icons.plus />
                    Aggiungi contatto
                </button>
                <button type="button"
                        @click="if (!publicUrl) return; navigator.clipboard.writeText(publicUrl).then(() => { copied = true; failed = false; setTimeout(() => copied = false, 1500); }).catch(() => { failed = true; copied = false; setTimeout(() => failed = false, 1500); })"
                        :disabled="!publicUrl"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-sky-200 bg-sky-50 px-4 py-2 text-sm font-medium text-sky-700 transition hover:bg-sky-100 disabled:cursor-not-allowed disabled:opacity-50">
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
                    @if($publicUrl)
                        <a href="{{ $publicUrl }}" target="_blank" class="mt-1 inline-flex text-sm font-medium text-indigo-600 hover:text-indigo-500">
                            Apri form pubblico
                        </a>
                    @else
                        <p class="mt-1 text-sm text-slate-500">Link pubblico non generato</p>
                        <button type="button" @click="generatePublicLink()" class="mt-2 inline-flex rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:bg-slate-100">
                            Genera link pubblico
                        </button>
                    @endif
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
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('exhibitions.contacts.export', $exhibition) }}?q={{ urlencode($q) }}" class="inline-flex items-center rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100">
                        Esporta Excel
                    </a>
                    <form method="GET" class="w-full sm:w-72">
                        <input type="text" name="q" value="{{ $q }}" placeholder="Cerca contatto..." class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
                    </form>
                </div>
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
                        <th class="app-th text-right">Azioni</th>
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
                            <td class="app-td">
                                <div class="flex justify-end gap-2">
                                    @if($contact->file_path)
                                        <a href="{{ route('exhibitions.contacts.download', [$exhibition, $contact]) }}" class="rounded-md border border-slate-200 px-2 py-1 text-xs text-slate-700 hover:bg-slate-100">
                                            Download file
                                        </a>
                                    @endif
                                    <button type="button"
                                            @click='openEdit({{ Illuminate\Support\Js::from(["id" => $contact->id, "first_name" => $contact->first_name, "last_name" => $contact->last_name, "email" => $contact->email, "phone" => $contact->phone, "company" => $contact->company, "note" => $contact->note]) }})'
                                            class="rounded-md border border-indigo-200 bg-indigo-50 px-2 py-1 text-xs text-indigo-700 hover:bg-indigo-100">
                                        Modifica
                                    </button>
                                    <form method="POST" action="{{ route('exhibitions.contacts.destroy', [$exhibition, $contact]) }}" onsubmit="return confirm('Eliminare questo contatto?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-md border border-rose-200 bg-rose-50 px-2 py-1 text-xs text-rose-700 hover:bg-rose-100">Elimina</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-500">Nessun contatto trovato.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $contacts->links() }}</div>
        </section>

        <div x-show="open" x-cloak class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/50 p-4" @keydown.escape.window="open = false">
            <div class="w-full max-w-2xl rounded-xl bg-white p-5 shadow-xl">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-900" x-text="contactId ? 'Modifica contatto' : 'Aggiungi contatto'"></h3>
                    <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600">✕</button>
                </div>

                <form method="POST" :action="formAction" enctype="multipart/form-data" @submit="submitting = true" class="space-y-3">
                    @csrf
                    <input type="hidden" name="contact_id" :value="contactId">
                    <template x-if="formMethod === 'PUT'"><input type="hidden" name="_method" value="PUT"></template>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Nome</label>
                            <input name="first_name" required x-model="form.first_name" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @error('first_name')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Cognome</label>
                            <input name="last_name" required x-model="form.last_name" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @error('last_name')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="mb-1 block text-sm font-medium text-slate-700">Email</label><input name="email" type="email" x-model="form.email" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                        <div><label class="mb-1 block text-sm font-medium text-slate-700">Telefono</label><input name="phone" x-model="form.phone" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                    </div>
                    <div><label class="mb-1 block text-sm font-medium text-slate-700">Azienda</label><input name="company" x-model="form.company" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                    <div><label class="mb-1 block text-sm font-medium text-slate-700">Note</label><textarea name="note" rows="3" x-model="form.note" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea></div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Allegato</label>
                        <input name="contact_file" type="file" accept=".pdf,.jpg,.jpeg,.png,.webp" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="open = false" class="rounded-lg border border-slate-200 px-4 py-2 text-sm">Annulla</button>
                        <button type="submit" :disabled="submitting" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white">Salva</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-main-layout>

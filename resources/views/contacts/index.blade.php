<x-main-layout :title="'Contatti — '.$exhibition->name">
    <div class="app-container py-6"
         x-data="{
            open: {{ ($errors->any() || ($openCreate ?? false)) ? 'true' : 'false' }},
            submitting: false,
            contactId: null,
            form: { first_name: '', last_name: '', email: '', phone: '', company: '', note: '' },
            openCreate() {
                this.contactId = null;
                this.form = { first_name: '', last_name: '', email: '', phone: '', company: '', note: '' };
                this.open = true;
            },
            openEdit(data) {
                this.contactId = data.id;
                this.form = {
                    first_name: data.first_name,
                    last_name:  data.last_name,
                    email:      data.email,
                    phone:      data.phone,
                    company:    data.company,
                    note:       data.note,
                };
                this.open = true;
            },
            closePanel() { this.open = false; }
         }">

        {{-- Header --}}
        <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <a href="{{ route('exhibitions.show', $exhibition) }}"
                   class="mb-1 inline-flex items-center gap-1 text-xs font-medium text-slate-500 transition hover:text-slate-700">
                    ← Torna alla fiera
                </a>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">{{ $exhibition->name }}</h1>
                <p class="mt-1 text-sm text-slate-500">
                    {{ $exhibition->display_date }}{{ $exhibition->company ? ' · '.$exhibition->company : '' }}
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('contacts.export', $exhibition) }}?q={{ urlencode($q) }}"
                   class="inline-flex items-center rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100">
                    Export Excel
                </a>
                <button @click="openCreate()" type="button"
                        class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700">
                    + Aggiungi contatto
                </button>
            </div>
        </div>

        {{-- Search --}}
        <div class="app-card mb-4 p-4">
            <form id="searchForm" method="GET" action="{{ route('contacts.index', $exhibition) }}"
                  class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <div class="flex flex-1 items-center rounded-lg border border-slate-300 bg-white px-3 focus-within:border-indigo-500 focus-within:ring-2 focus-within:ring-indigo-100">
                    <svg class="mr-2 h-4 w-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                    </svg>
                    <input id="contactSearch" name="q" value="{{ $q }}"
                           placeholder="Cerca nome, email, telefono, azienda, note…"
                           class="flex-1 bg-transparent py-2 text-sm outline-none">
                </div>
                <div class="flex gap-2">
                    <button type="submit"
                            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700">
                        Cerca
                    </button>
                    <a href="{{ route('contacts.index', $exhibition) }}"
                       class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        {{-- Mobile cards --}}
        <div class="space-y-3 md:hidden">
            @forelse($contacts as $c)
                <div class="app-card p-4">
                    <div class="mb-2 flex items-start justify-between gap-2">
                        <div>
                            <p class="font-semibold text-slate-900">{{ $c->first_name }} {{ $c->last_name }}</p>
                            <p class="text-sm text-slate-500">{{ $c->email ?: 'Nessuna email' }}</p>
                        </div>
                        @if($c->source === 'public')
                            <span class="inline-flex shrink-0 items-center rounded-full bg-sky-100 px-2 py-0.5 text-xs font-medium text-sky-700">Pubblico</span>
                        @else
                            <span class="inline-flex shrink-0 items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">Interno</span>
                        @endif
                    </div>
                    @if($c->phone || $c->company)
                        <div class="mb-2 space-y-0.5 text-sm text-slate-600">
                            @if($c->phone)<p><span class="font-medium">Tel:</span> {{ $c->phone }}</p>@endif
                            @if($c->company)<p><span class="font-medium">Azienda:</span> {{ $c->company }}</p>@endif
                        </div>
                    @endif
                    @if($c->note)
                        <p class="mb-3 line-clamp-2 text-xs text-slate-500">{{ $c->note }}</p>
                    @endif
                    <div class="flex flex-wrap gap-2">
                        @if($c->file_path)
                            <a href="{{ route('contacts.file.download', [$exhibition, $c]) }}"
                               class="rounded-md border border-indigo-200 px-2.5 py-1 text-xs font-medium text-indigo-700 transition hover:bg-indigo-50">Download</a>
                            <a href="{{ route('contacts.file.preview', [$exhibition, $c]) }}" target="_blank"
                               class="rounded-md border border-slate-200 px-2.5 py-1 text-xs font-medium text-slate-700 transition hover:bg-slate-50">Preview</a>
                        @endif
                        <button type="button"
                                data-contact="{{ e(json_encode(['id' => $c->id, 'first_name' => $c->first_name, 'last_name' => $c->last_name, 'email' => $c->email ?? '', 'phone' => $c->phone ?? '', 'company' => $c->company ?? '', 'note' => $c->note ?? ''])) }}"
                                @click="openEdit(JSON.parse($el.dataset.contact))"
                                class="rounded-md border border-slate-200 px-2.5 py-1 text-xs font-medium text-slate-700 transition hover:bg-slate-50">
                            Modifica
                        </button>
                        <form method="POST" action="{{ route('contacts.destroy', [$exhibition, $c]) }}"
                              @submit.prevent="if(confirm('Eliminare questo contatto?')) $el.submit()">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="rounded-md border border-rose-200 px-2.5 py-1 text-xs font-medium text-rose-700 transition hover:bg-rose-50">
                                Elimina
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="app-card p-10 text-center">
                    <p class="text-sm text-slate-500">Nessun contatto trovato.</p>
                    <button @click="openCreate()" type="button"
                            class="mt-3 inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700">
                        + Aggiungi il primo contatto
                    </button>
                </div>
            @endforelse
        </div>

        {{-- Desktop table --}}
        <div class="hidden md:block">
            <div class="overflow-x-auto rounded-lg border border-slate-200">
                <table class="app-table">
                    <thead class="app-thead">
                        <tr>
                            <th class="app-th">Nome</th>
                            <th class="app-th">Email</th>
                            <th class="app-th">Telefono</th>
                            <th class="app-th">Azienda</th>
                            <th class="app-th">Note</th>
                            <th class="app-th">File</th>
                            <th class="app-th text-right">Azioni</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($contacts as $c)
                            <tr class="hover:bg-slate-50">
                                <td class="app-td">
                                    <p class="font-semibold text-slate-900">{{ $c->first_name }} {{ $c->last_name }}</p>
                                    @if($c->source === 'public')
                                        <span class="inline-flex items-center rounded-full bg-sky-100 px-2 py-0.5 text-xs font-medium text-sky-700">Pubblico</span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">Interno</span>
                                    @endif
                                </td>
                                <td class="app-td text-slate-600">{{ $c->email ?: '—' }}</td>
                                <td class="app-td text-slate-600">{{ $c->phone ?: '—' }}</td>
                                <td class="app-td text-slate-600">{{ $c->company ?: '—' }}</td>
                                <td class="app-td max-w-[200px] text-slate-500">
                                    <div class="truncate" title="{{ $c->note }}">{{ $c->note ?: '—' }}</div>
                                </td>
                                <td class="app-td">
                                    @if($c->file_path)
                                        <div class="flex gap-1.5">
                                            <a href="{{ route('contacts.file.download', [$exhibition, $c]) }}"
                                               class="rounded-md border border-indigo-200 px-2.5 py-1 text-xs font-medium text-indigo-700 transition hover:bg-indigo-50">Download</a>
                                            <a href="{{ route('contacts.file.preview', [$exhibition, $c]) }}" target="_blank"
                                               class="rounded-md border border-slate-200 px-2.5 py-1 text-xs font-medium text-slate-700 transition hover:bg-slate-50">Preview</a>
                                        </div>
                                    @else
                                        <span class="text-sm text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="app-td text-right">
                                    <div class="flex justify-end gap-1.5">
                                        <button type="button"
                                                data-contact="{{ e(json_encode(['id' => $c->id, 'first_name' => $c->first_name, 'last_name' => $c->last_name, 'email' => $c->email ?? '', 'phone' => $c->phone ?? '', 'company' => $c->company ?? '', 'note' => $c->note ?? ''])) }}"
                                                @click="openEdit(JSON.parse($el.dataset.contact))"
                                                class="rounded-md border border-slate-200 px-2.5 py-1 text-xs font-medium text-slate-700 transition hover:bg-slate-50">
                                            Modifica
                                        </button>
                                        <form method="POST" action="{{ route('contacts.destroy', [$exhibition, $c]) }}"
                                              @submit.prevent="if(confirm('Eliminare questo contatto?')) $el.submit()">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="rounded-md border border-rose-200 px-2.5 py-1 text-xs font-medium text-rose-700 transition hover:bg-rose-50">
                                                Elimina
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-sm text-slate-500">
                                    Nessun contatto trovato.
                                    <button @click="openCreate()" type="button"
                                            class="ml-1 font-medium text-indigo-600 hover:underline">
                                        Aggiungi il primo
                                    </button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="mt-4">{{ $contacts->links() }}</div>

        {{-- Slide-over backdrop --}}
        <div x-show="open"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-40 bg-slate-900/50"
             @click="closePanel()"
             x-cloak></div>

        {{-- Slide-over panel --}}
        <div x-show="open"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full"
             class="fixed inset-y-0 right-0 z-50 flex w-full flex-col bg-white shadow-2xl sm:max-w-md"
             x-cloak>
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <h2 class="text-base font-semibold text-slate-900"
                    x-text="contactId ? 'Modifica contatto' : 'Nuovo contatto'"></h2>
                <button type="button" @click="closePanel()"
                        class="rounded-md p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-5">
                <form method="POST"
                      enctype="multipart/form-data"
                      :action="contactId
                          ? `{{ url('exhibitions/'.$exhibition->id.'/contacts') }}/${contactId}`
                          : `{{ route('contacts.store', $exhibition) }}`"
                      @submit="submitting = true"
                      class="space-y-4">
                    @csrf
                    <input type="hidden" name="_method" :value="contactId ? 'PUT' : 'POST'">

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700" for="first_name">
                                Nome <span class="text-rose-500">*</span>
                            </label>
                            <input id="first_name" name="first_name" type="text" required
                                   x-model="form.first_name"
                                   class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 {{ $errors->has('first_name') ? 'border-rose-400 ring-rose-100' : 'border-slate-300 focus:border-indigo-500 focus:ring-indigo-100' }}">
                            @error('first_name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700" for="last_name">
                                Cognome <span class="text-rose-500">*</span>
                            </label>
                            <input id="last_name" name="last_name" type="text" required
                                   x-model="form.last_name"
                                   class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 {{ $errors->has('last_name') ? 'border-rose-400 ring-rose-100' : 'border-slate-300 focus:border-indigo-500 focus:ring-indigo-100' }}">
                            @error('last_name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700" for="email">Email</label>
                            <input id="email" name="email" type="email"
                                   x-model="form.email"
                                   class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 {{ $errors->has('email') ? 'border-rose-400 ring-rose-100' : 'border-slate-300 focus:border-indigo-500 focus:ring-indigo-100' }}">
                            @error('email')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700" for="phone">Telefono</label>
                            <input id="phone" name="phone" type="text"
                                   x-model="form.phone"
                                   class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 {{ $errors->has('phone') ? 'border-rose-400 ring-rose-100' : 'border-slate-300 focus:border-indigo-500 focus:ring-indigo-100' }}">
                            @error('phone')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700" for="company">Azienda</label>
                        <input id="company" name="company" type="text"
                               x-model="form.company"
                               class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 {{ $errors->has('company') ? 'border-rose-400 ring-rose-100' : 'border-slate-300 focus:border-indigo-500 focus:ring-indigo-100' }}">
                        @error('company')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700" for="note">Note</label>
                        <textarea id="note" name="note" rows="3"
                                  x-model="form.note"
                                  class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 {{ $errors->has('note') ? 'border-rose-400 ring-rose-100' : 'border-slate-300 focus:border-indigo-500 focus:ring-indigo-100' }}"></textarea>
                        @error('note')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700" for="contact_file">Allegato</label>
                        <input id="contact_file" name="contact_file" type="file"
                               accept=".pdf,.jpg,.jpeg,.png,.webp"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-500 focus:outline-none file:mr-3 file:rounded file:border-0 file:bg-slate-100 file:px-3 file:py-1 file:text-xs file:font-medium file:text-slate-700 hover:file:bg-slate-200 @error('contact_file') border-rose-400 @enderror">
                        <p class="mt-1 text-xs text-slate-400">Opzionale · PDF, JPG, PNG, WebP · max 10 MB</p>
                        @error('contact_file')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="pt-2">
                        <button type="submit"
                                :disabled="submitting"
                                class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-indigo-700 disabled:opacity-60">
                            <span x-show="!submitting">Salva</span>
                            <span x-show="submitting" x-cloak>Salvataggio…</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    @push('scripts')
        <script>
            let searchTimer;
            const searchInput = document.getElementById('contactSearch');
            searchInput?.addEventListener('input', () => {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => {
                    document.getElementById('searchForm').submit();
                }, 450);
            });
        </script>
    @endpush
</x-main-layout>

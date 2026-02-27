<x-main-layout :title="'Contatti - '.$exhibition->name">
    <div class="page-header d-flex flex-column flex-md-row justify-content-between gap-3 align-items-md-center">
        <div>
            <a class="text-decoration-none text-secondary small" href="{{ route('exhibitions.index') }}"><i class="bi bi-arrow-left"></i> Torna alle fiere</a>
            <h1 class="h3 mb-1 mt-1">{{ $exhibition->name }}</h1>
            <div class="text-secondary">{{ $exhibition->display_date }} · {{ $exhibition->company }}</div>
        </div>
        <div class="actions d-flex gap-2">
            <a class="btn btn-outline-secondary" href="{{ route('contacts.export', $exhibition) }}?q={{ urlencode($q) }}"><i class="bi bi-file-earmark-spreadsheet me-1"></i>Export</a>
            <x-ui/button variant="primary" icon="plus-lg" data-bs-toggle="offcanvas" data-bs-target="#contactCanvas">Aggiungi contatto</x-ui/button>
        </div>
    </div>

    <x-ui.card class="mb-3">
        <form id="searchForm" method="GET" action="{{ route('contacts.index', $exhibition) }}" class="row g-2 align-items-center">
            <div class="col-12 col-md">
                <label for="contactSearch" class="form-label visually-hidden">Cerca contatti</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input id="contactSearch" class="form-control" name="q" value="{{ $q }}" placeholder="Cerca su nome, cognome, email, telefono, azienda, note...">
                </div>
            </div>
            <div class="col-12 col-md-auto d-flex gap-2">
                <button class="btn btn-primary" type="submit">Cerca</button>
                <a class="btn btn-outline-secondary" href="{{ route('contacts.index', $exhibition) }}">Reset</a>
            </div>
        </form>
    </x-ui.card>

    <div class="d-md-none row g-3 mb-3">
        @forelse($contacts as $c)
            <div class="col-12">
                <x-ui.card>
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h2 class="h6 mb-0">{{ $c->first_name }} {{ $c->last_name }}</h2>
                            <p class="text-secondary small mb-0">{{ $c->email ?: 'Nessuna email' }}</p>
                        </div>
                        <x-ui.badge variant="{{ $c->source === 'public' ? 'info' : 'secondary' }}">{{ $c->source }}</x-ui.badge>
                    </div>
                    <p class="small mb-1"><strong>Telefono:</strong> {{ $c->phone ?: '-' }}</p>
                    <p class="small mb-1"><strong>Azienda:</strong> {{ $c->company ?: '-' }}</p>
                    <p class="small text-secondary mb-2">{{ $c->note ?: 'Nessuna nota' }}</p>
                    <div class="d-flex flex-wrap gap-2 mb-2">
                        @if($c->file_path)
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('contacts.file.download', [$exhibition, $c]) }}">Download</a>
                            <a class="btn btn-sm btn-outline-secondary" target="_blank" href="{{ route('contacts.file.preview', [$exhibition, $c]) }}">Preview</a>
                        @endif
                        <button class="btn btn-sm btn-primary js-edit-contact"
                                data-id="{{ $c->id }}"
                                data-first_name="{{ e($c->first_name) }}"
                                data-last_name="{{ e($c->last_name) }}"
                                data-email="{{ e($c->email ?? '') }}"
                                data-phone="{{ e($c->phone ?? '') }}"
                                data-company="{{ e($c->company ?? '') }}"
                                data-note="{{ e($c->note ?? '') }}"
                                data-bs-toggle="offcanvas" data-bs-target="#contactCanvas">Modifica</button>
                    </div>
                    <form method="POST" action="{{ route('contacts.destroy', [$exhibition, $c]) }}" data-confirm-delete="true">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger w-100" type="submit">Elimina</button>
                    </form>
                </x-ui.card>
            </div>
        @empty
            <div class="col-12"><x-ui.card title="Nessun contatto" subtitle="Aggiungi il primo contatto di questa fiera." /></div>
        @endforelse
    </div>

    <div class="d-none d-md-block mb-3">
        <x-ui.table>
            <thead>
            <tr>
                <th class="p-3">Nome</th>
                <th class="p-3">Email</th>
                <th class="p-3">Telefono</th>
                <th class="p-3">Azienda</th>
                <th class="p-3">Note</th>
                <th class="p-3">File</th>
                <th class="p-3 text-end">Azioni</th>
            </tr>
            </thead>
            <tbody>
            @forelse($contacts as $c)
                <tr>
                    <td class="p-3">
                        <div class="fw-semibold">{{ $c->first_name }} {{ $c->last_name }}</div>
                        <div class="small text-secondary">{{ $c->source === 'public' ? 'Da form pubblico' : 'Inserito interno' }}</div>
                    </td>
                    <td class="p-3">{{ $c->email }}</td>
                    <td class="p-3">{{ $c->phone }}</td>
                    <td class="p-3">{{ $c->company }}</td>
                    <td class="p-3" style="max-width:280px;"><div class="text-truncate" title="{{ $c->note }}">{{ $c->note }}</div></td>
                    <td class="p-3">
                        @if($c->file_path)
                            <div class="btn-group btn-group-sm">
                                <a class="btn btn-outline-primary" href="{{ route('contacts.file.download', [$exhibition, $c]) }}">Download</a>
                                <a class="btn btn-outline-secondary" target="_blank" href="{{ route('contacts.file.preview', [$exhibition, $c]) }}">Preview</a>
                            </div>
                        @else
                            <span class="text-secondary small">Nessuno</span>
                        @endif
                    </td>
                    <td class="p-3 text-end">
                        <div class="btn-group">
                            <button class="btn btn-primary btn-sm js-edit-contact"
                                    data-id="{{ $c->id }}"
                                    data-first_name="{{ e($c->first_name) }}"
                                    data-last_name="{{ e($c->last_name) }}"
                                    data-email="{{ e($c->email ?? '') }}"
                                    data-phone="{{ e($c->phone ?? '') }}"
                                    data-company="{{ e($c->company ?? '') }}"
                                    data-note="{{ e($c->note ?? '') }}"
                                    data-bs-toggle="offcanvas" data-bs-target="#contactCanvas"><i class="bi bi-pencil"></i></button>
                            <form method="POST" action="{{ route('contacts.destroy', [$exhibition, $c]) }}" data-confirm-delete="true">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm" type="submit"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="p-4 text-center text-secondary">Nessun contatto.</td></tr>
            @endforelse
            </tbody>
        </x-ui.table>
    </div>

    <div class="mt-3">{{ $contacts->links() }}</div>

    <div class="offcanvas offcanvas-end" tabindex="-1" id="contactCanvas">
        <div class="offcanvas-header border-bottom">
            <h2 class="h5 offcanvas-title mb-0">Gestione contatto</h2>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            <form id="contactForm" method="POST" enctype="multipart/form-data" action="{{ route('contacts.store', $exhibition) }}" class="vstack gap-3">
                @csrf
                <input type="hidden" id="contactMethod" name="_method" value="POST">

                <div class="row g-2">
                    <div class="col-6"><x-ui.input name="first_name" label="Nome" required /></div>
                    <div class="col-6"><x-ui.input name="last_name" label="Cognome" required /></div>
                </div>

                <div class="row g-2">
                    <div class="col-6"><x-ui.input name="email" type="email" label="Email" /></div>
                    <div class="col-6"><x-ui.input name="phone" label="Telefono" /></div>
                </div>

                <x-ui.input name="company" label="Azienda" />

                <div>
                    <label class="form-label" for="note">Note</label>
                    <textarea class="form-control @error('note') is-invalid @enderror" name="note" id="note" rows="3">{{ old('note') }}</textarea>
                    @error('note')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label class="form-label" for="contact_file">Allegato contatto</label>
                    <input class="form-control @error('contact_file') is-invalid @enderror" id="contact_file" name="contact_file" type="file" accept=".pdf,.jpg,.jpeg,.png,.webp">
                    @error('contact_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="text-secondary small mt-1">Opzionale, max 10MB.</div>
                </div>

                <x-ui/button id="saveContactBtn" type="submit" variant="primary" class="w-100" icon="check2">Salva</x-ui/button>
            </form>
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

            document.querySelectorAll('.js-edit-contact').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const id = btn.dataset.id;
                    document.getElementById('first_name').value = btn.dataset.first_name || '';
                    document.getElementById('last_name').value = btn.dataset.last_name || '';
                    document.getElementById('email').value = btn.dataset.email || '';
                    document.getElementById('phone').value = btn.dataset.phone || '';
                    document.getElementById('company').value = btn.dataset.company || '';
                    document.getElementById('note').value = btn.dataset.note || '';

                    const form = document.getElementById('contactForm');
                    form.action = `/exhibitions/{{ $exhibition->id }}/contacts/${id}`;
                    document.getElementById('contactMethod').value = 'PUT';
                });
            });

            document.getElementById('contactCanvas').addEventListener('show.bs.offcanvas', (ev) => {
                const trigger = ev.relatedTarget;
                if (trigger && trigger.classList.contains('btn-primary') && !trigger.classList.contains('js-edit-contact')) {
                    const form = document.getElementById('contactForm');
                    form.action = `/exhibitions/{{ $exhibition->id }}/contacts`;
                    document.getElementById('contactMethod').value = 'POST';
                    form.reset();
                }
            });

            document.getElementById('contactForm').addEventListener('submit', () => {
                const btn = document.getElementById('saveContactBtn');
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Salvataggio...';
            });
        </script>
    @endpush
</x-main-layout>

<x-main-layout :title="'Contatti - '.$exhibition->name">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div>
            <a class="text-decoration-none text-secondary" href="{{ route('exhibitions.index') }}">
                <i class="bi bi-arrow-left"></i> Fiere
            </a>
            <h1 class="h3 mb-0 mt-1">{{ $exhibition->name }}</h1>
            <div class="text-secondary small">{{ $exhibition->date->format('d/m/Y') }} · {{ $exhibition->company }}</div>
        </div>

        <div class="d-flex gap-2">
            <a class="btn btn-outline-light" href="{{ route('contacts.export', $exhibition) }}?q={{ urlencode($q) }}">
                <i class="bi bi-file-earmark-spreadsheet"></i> Export Excel (CSV)
            </a>
            <button class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#contactCanvas">
                <i class="bi bi-plus"></i> Aggiungi contatto
            </button>
        </div>
    </div>

    <form class="mb-3" method="GET" action="{{ route('contacts.index', $exhibition) }}">
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input class="form-control" name="q" value="{{ $q }}" placeholder="Cerca per nome, email, telefono, azienda...">
            <a class="btn btn-outline-secondary" href="{{ route('contacts.index', $exhibition) }}">Reset</a>
        </div>
    </form>

    <div class="table-responsive rounded-4 overflow-hidden">
        <table class="table table-striped table-light align-middle mb-0">
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
                        <div class="text-secondary small">{{ $c->source === 'public' ? 'Da form pubblico' : 'Inserito interno' }}</div>
                    </td>
                    <td class="p-3">{{ $c->email }}</td>
                    <td class="p-3">{{ $c->phone }}</td>
                    <td class="p-3">{{ $c->company }}</td>
                    <td class="p-3" style="max-width: 320px;">
                        <div class="text-truncate" title="{{ $c->note }}">{{ $c->note }}</div>
                    </td>
                    <td class="p-3">
                        @if($c->business_card_path)
                            <a class="btn btn-sm btn-outline-primary"
                               target="_blank"
                               href="{{ asset('storage/'.$c->business_card_path) }}">
                                <i class="bi bi-paperclip"></i> Apri
                            </a>
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
                                    data-bs-toggle="offcanvas" data-bs-target="#contactCanvas">
                                <i class="bi bi-pencil"></i>
                            </button>

                            <form method="POST"
                                  action="{{ route('contacts.destroy', [$exhibition, $c]) }}"
                                  onsubmit="return confirm('Eliminare il contatto?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm" type="submit">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="p-4 text-center text-secondary">Nessun contatto.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $contacts->links() }}
    </div>

    <!-- Offcanvas create/edit contatto -->
    <div class="offcanvas offcanvas-end text-bg-dark" tabindex="-1" id="contactCanvas">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title">Gestione contatto</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            <form id="contactForm" method="POST" enctype="multipart/form-data" action="{{ route('contacts.store', $exhibition) }}">
                @csrf
                <input type="hidden" id="contactMethod" name="_method" value="POST">

                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label">Nome</label>
                        <input class="form-control" name="first_name" id="c_first_name" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Cognome</label>
                        <input class="form-control" name="last_name" id="c_last_name" required>
                    </div>
                </div>

                <div class="row g-2 mt-1">
                    <div class="col-6">
                        <label class="form-label">Email</label>
                        <input class="form-control" name="email" id="c_email" type="email">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Telefono</label>
                        <input class="form-control" name="phone" id="c_phone">
                    </div>
                </div>

                <div class="mt-2">
                    <label class="form-label">Azienda</label>
                    <input class="form-control" name="company" id="c_company">
                </div>

                <div class="mt-2">
                    <label class="form-label">Note</label>
                    <textarea class="form-control" name="note" id="c_note" rows="3"></textarea>
                </div>

                <div class="mt-2">
                    <label class="form-label">Biglietto da visita (file)</label>
                    <input class="form-control" name="business_card" type="file" accept=".jpg,.jpeg,.png,.pdf,.webp">
                    <div class="text-secondary small mt-1">Opzionale. Max 5MB.</div>
                </div>

                <button class="btn btn-primary w-100 mt-3" type="submit">Salva</button>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            document.querySelectorAll('.js-edit-contact').forEach(btn => {
                btn.addEventListener('click', () => {
                    const id = btn.dataset.id;

                    document.getElementById('c_first_name').value = btn.dataset.first_name || '';
                    document.getElementById('c_last_name').value = btn.dataset.last_name || '';
                    document.getElementById('c_email').value = btn.dataset.email || '';
                    document.getElementById('c_phone').value = btn.dataset.phone || '';
                    document.getElementById('c_company').value = btn.dataset.company || '';
                    document.getElementById('c_note').value = btn.dataset.note || '';

                    const form = document.getElementById('contactForm');
                    form.action = `/exhibitions/{{ $exhibition->id }}/contacts/${id}`;
                    document.getElementById('contactMethod').value = 'PUT';
                });
            });

            // Reset form quando apri per creare
            const canvas = document.getElementById('contactCanvas');
            canvas.addEventListener('show.bs.offcanvas', (ev) => {
                const trigger = ev.relatedTarget;
                if (trigger && trigger.classList.contains('btn-primary') && !trigger.classList.contains('js-edit-contact')) {
                    const form = document.getElementById('contactForm');
                    form.action = `/exhibitions/{{ $exhibition->id }}/contacts`;
                    document.getElementById('contactMethod').value = 'POST';
                    form.reset();
                }
            });
        </script>
    @endpush
</x-main-layout>
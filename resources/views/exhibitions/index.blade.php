<x-main-layout title="Fiere">
    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h1 class="h3 mb-1">Fiere</h1>
            <p class="text-secondary mb-0">Gestisci eventi, link pubblici e contatti raccolti.</p>
        </div>
        <div class="actions d-flex gap-2">
            <x-ui/button variant="primary" icon="plus-lg" data-bs-toggle="offcanvas" data-bs-target="#exhibitionCanvas">Nuova fiera</x-ui/button>
        </div>
    </div>

    <div class="d-md-none row g-3 mb-3">
        @forelse($exhibitions as $ex)
            <div class="col-12">
                <x-ui.card class="h-100">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h2 class="h6 mb-1">{{ $ex->name }}</h2>
                            <p class="small text-secondary mb-0">{{ $ex->display_date }}</p>
                            <p class="small text-secondary mb-0">{{ $ex->company }}</p>
                        </div>
                        <x-ui.badge variant="light" class="text-dark">#{{ $ex->id }}</x-ui.badge>
                    </div>
                    <div class="d-grid gap-2">
                        <a class="btn btn-warning" href="{{ route('contacts.index', $ex) }}"><i class="bi bi-people me-1"></i>Contatti</a>
                        <div class="btn-group" role="group">
                            <button class="btn btn-outline-secondary js-copy-link" data-id="{{ $ex->id }}" data-regenerate="0"><i class="bi bi-link-45deg me-1"></i>Link</button>
                            <button class="btn btn-outline-secondary js-copy-link" data-id="{{ $ex->id }}" data-regenerate="1"><i class="bi bi-arrow-repeat"></i></button>
                        </div>
                        <button class="btn btn-primary js-edit"
                                data-id="{{ $ex->id }}"
                                data-name="{{ e($ex->name) }}"
                                data-date="{{ optional($ex->date)->format('Y-m-d') }}"
                                data-start_date="{{ optional($ex->start_date)->format('Y-m-d') }}"
                                data-end_date="{{ optional($ex->end_date)->format('Y-m-d') }}"
                                data-company="{{ e($ex->company ?? '') }}"
                                data-bs-toggle="offcanvas" data-bs-target="#exhibitionCanvas">
                            <i class="bi bi-pencil me-1"></i>Modifica
                        </button>
                        <form method="POST" action="{{ route('exhibitions.destroy', $ex) }}" data-confirm-delete="true">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger w-100" type="submit"><i class="bi bi-trash me-1"></i>Elimina</button>
                        </form>
                    </div>
                </x-ui.card>
            </div>
        @empty
            <div class="col-12">
                <x-ui.card title="Nessuna fiera" subtitle="Inizia creando la prima fiera."></x-ui.card>
            </div>
        @endforelse
    </div>

    <div class="d-none d-md-block mb-3">
        <x-ui.table>
            <thead>
            <tr>
                <th class="p-3">ID</th>
                <th class="p-3">Nome</th>
                <th class="p-3">Data</th>
                <th class="p-3">Azienda</th>
                <th class="p-3 text-end">Azioni</th>
            </tr>
            </thead>
            <tbody>
            @forelse($exhibitions as $ex)
                <tr>
                    <td class="p-3">{{ $ex->id }}</td>
                    <td class="p-3 fw-semibold">{{ $ex->name }}</td>
                    <td class="p-3">{{ $ex->display_date }}</td>
                    <td class="p-3">{{ $ex->company }}</td>
                    <td class="p-3 text-end">
                        <div class="btn-group">
                            <a class="btn btn-warning" href="{{ route('contacts.index', $ex) }}" title="Contatti"><i class="bi bi-person-lines-fill text-white"></i></a>
                            <button class="btn btn-secondary js-copy-link" data-id="{{ $ex->id }}" data-regenerate="0" title="Genera/Copia link pubblico"><i class="bi bi-link-45deg"></i></button>
                            <button class="btn btn-outline-secondary js-copy-link" data-id="{{ $ex->id }}" data-regenerate="1" title="Rigenera link pubblico"><i class="bi bi-arrow-repeat"></i></button>
                            <button class="btn btn-primary js-edit"
                                    data-id="{{ $ex->id }}"
                                    data-name="{{ e($ex->name) }}"
                                    data-date="{{ optional($ex->date)->format('Y-m-d') }}"
                                    data-start_date="{{ optional($ex->start_date)->format('Y-m-d') }}"
                                    data-end_date="{{ optional($ex->end_date)->format('Y-m-d') }}"
                                    data-company="{{ e($ex->company ?? '') }}"
                                    data-bs-toggle="offcanvas" data-bs-target="#exhibitionCanvas"
                                    title="Modifica"><i class="bi bi-pencil"></i></button>
                            <form method="POST" action="{{ route('exhibitions.destroy', $ex) }}" data-confirm-delete="true">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger" type="submit" title="Elimina"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="p-4 text-center text-secondary">Nessuna fiera.</td></tr>
            @endforelse
            </tbody>
        </x-ui.table>
    </div>

    <div class="mt-3">{{ $exhibitions->links() }}</div>

    <div class="offcanvas offcanvas-end" tabindex="-1" id="exhibitionCanvas">
        <div class="offcanvas-header border-bottom">
            <h2 class="h5 offcanvas-title mb-0">Gestione fiera</h2>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            <form id="exhibitionForm" method="POST" action="{{ route('exhibitions.store') }}" class="vstack gap-3">
                @csrf
                <input type="hidden" id="formMethod" name="_method" value="POST">

                <x-ui.input name="name" label="Nome" required />
                <x-ui.input name="date" label="Data singola" type="date" />

                <div class="row g-2">
                    <div class="col-6"><x-ui.input name="start_date" label="Data inizio" type="date" /></div>
                    <div class="col-6"><x-ui.input name="end_date" label="Data fine" type="date" /></div>
                </div>

                <x-ui.input name="company" label="Azienda" />

                <p class="small text-secondary mb-0">Compila una data singola oppure un range.</p>
                <x-ui/button id="saveExhibitionBtn" type="submit" variant="primary" class="w-100" icon="check2">Salva</x-ui/button>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            document.querySelectorAll('.js-edit').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const id = btn.dataset.id;
                    document.getElementById('name').value = btn.dataset.name || '';
                    document.getElementById('date').value = btn.dataset.date || '';
                    document.getElementById('start_date').value = btn.dataset.start_date || '';
                    document.getElementById('end_date').value = btn.dataset.end_date || '';
                    document.getElementById('company').value = btn.dataset.company || '';

                    const form = document.getElementById('exhibitionForm');
                    form.action = `/exhibitions/${id}`;
                    document.getElementById('formMethod').value = 'PUT';
                });
            });

            document.getElementById('exhibitionCanvas').addEventListener('show.bs.offcanvas', (ev) => {
                const trigger = ev.relatedTarget;
                if (trigger && trigger.classList.contains('btn-primary') && !trigger.classList.contains('js-edit')) {
                    const form = document.getElementById('exhibitionForm');
                    form.action = '/exhibitions';
                    document.getElementById('formMethod').value = 'POST';
                    form.reset();
                }
            });

            document.querySelectorAll('.js-copy-link').forEach((btn) => {
                btn.addEventListener('click', async () => {
                    const id = btn.dataset.id;
                    const regenerate = btn.dataset.regenerate === '1';
                    const original = btn.innerHTML;
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span>';

                    try {
                        const response = await fetch(`/exhibitions/${id}/public-link`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ regenerate }),
                        });
                        const data = await response.json();
                        await navigator.clipboard.writeText(data.url);
                        btn.innerHTML = '<i class="bi bi-check2"></i>';
                    } finally {
                        setTimeout(() => {
                            btn.disabled = false;
                            btn.innerHTML = original;
                        }, 900);
                    }
                });
            });

            document.getElementById('exhibitionForm').addEventListener('submit', () => {
                const btn = document.getElementById('saveExhibitionBtn');
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Salvataggio...';
            });
        </script>
    @endpush
</x-main-layout>

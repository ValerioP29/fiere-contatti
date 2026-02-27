<x-main-layout title="Fiere">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="h3 mb-0">Fiere</h1>

        <button class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#exhibitionCanvas">
            <i class="bi bi-plus"></i> Aggiungi fiera
        </button>
    </div>

    <div class="table-responsive rounded-4 overflow-hidden">
        <table class="table table-striped table-light align-middle mb-0">
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
                    <td class="p-3">{{ $ex->date->format('d/m/Y') }}</td>
                    <td class="p-3">{{ $ex->company }}</td>
                    <td class="p-3 text-end">
                        <div class="btn-group">
                            <a class="btn btn-warning" href="{{ route('contacts.index', $ex) }}" title="Contatti">
                                <i class="bi bi-person-lines-fill text-white"></i>
                            </a>

                            <button class="btn btn-secondary js-copy-link" data-id="{{ $ex->id }}" title="Condividi link form">
                                <i class="bi bi-link-45deg"></i>
                            </button>

                            <button class="btn btn-primary js-edit"
                                    data-id="{{ $ex->id }}"
                                    data-name="{{ e($ex->name) }}"
                                    data-date="{{ $ex->date->format('Y-m-d') }}"
                                    data-company="{{ e($ex->company ?? '') }}"
                                    data-bs-toggle="offcanvas" data-bs-target="#exhibitionCanvas"
                                    title="Modifica">
                                <i class="bi bi-pencil"></i>
                            </button>

                            <form method="POST" action="{{ route('exhibitions.destroy', $ex) }}"
                                  onsubmit="return confirm('Eliminare la fiera? Verranno eliminati anche i contatti.');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger" type="submit" title="Elimina">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="p-4 text-center text-secondary">Nessuna fiera.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $exhibitions->links() }}
    </div>

    <!-- Offcanvas create/edit -->
    <div class="offcanvas offcanvas-end text-bg-dark" tabindex="-1" id="exhibitionCanvas">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="canvasTitle">Gestione fiera</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            <form id="exhibitionForm" method="POST" action="{{ route('exhibitions.store') }}">
                @csrf
                <input type="hidden" id="formMethod" name="_method" value="POST">

                <div class="mb-3">
                    <label class="form-label">Nome</label>
                    <input class="form-control" name="name" id="ex_name" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Data</label>
                    <input class="form-control" type="date" name="date" id="ex_date" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Azienda</label>
                    <input class="form-control" name="company" id="ex_company">
                </div>

                <button class="btn btn-primary w-100" type="submit" id="saveBtn">Salva</button>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            // Edit fiera: cambia action + method
            document.querySelectorAll('.js-edit').forEach(btn => {
                btn.addEventListener('click', () => {
                    const id = btn.dataset.id;
                    document.getElementById('ex_name').value = btn.dataset.name || '';
                    document.getElementById('ex_date').value = btn.dataset.date || '';
                    document.getElementById('ex_company').value = btn.dataset.company || '';

                    const form = document.getElementById('exhibitionForm');
                    form.action = `/exhibitions/${id}`;
                    document.getElementById('formMethod').value = 'PUT';
                });
            });

            // Create reset quando apri dal tasto “Aggiungi”
            const canvas = document.getElementById('exhibitionCanvas');
            canvas.addEventListener('show.bs.offcanvas', (ev) => {
                const trigger = ev.relatedTarget;
                if (trigger && trigger.classList.contains('btn-primary') && !trigger.classList.contains('js-edit')) {
                    const form = document.getElementById('exhibitionForm');
                    form.action = `/exhibitions`;
                    document.getElementById('formMethod').value = 'POST';
                    form.reset();
                }
            });

            // Genera/copia link pubblico
            document.querySelectorAll('.js-copy-link').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const id = btn.dataset.id;
                    const res = await fetch(`/exhibitions/${id}/public-link`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        }
                    });
                    const data = await res.json();
                    await navigator.clipboard.writeText(data.url);
                    btn.classList.remove('btn-secondary');
                    btn.classList.add('btn-success');
                    btn.innerHTML = '<i class="bi bi-check2"></i>';
                    setTimeout(() => {
                        btn.classList.add('btn-secondary');
                        btn.classList.remove('btn-success');
                        btn.innerHTML = '<i class="bi bi-link-45deg"></i>';
                    }, 1200);
                });
            });
        </script>
    @endpush
</x-main-layout>
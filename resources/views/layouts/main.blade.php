<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Contatti fiere' }}</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .focus-ring:focus-visible, .btn:focus-visible, .form-control:focus-visible {
            outline: 3px solid rgba(13, 110, 253, .35);
            outline-offset: 2px;
        }
        .page-header { margin-bottom: 1rem; }
        @media (max-width: 767.98px) {
            .page-header .actions { width: 100%; }
            .page-header .actions .btn { flex: 1; }
        }
    </style>
</head>
<body class="bg-body-tertiary text-dark">
<nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="{{ route('exhibitions.index') }}">Contatti fiere</a>

        <div class="ms-auto d-flex gap-2 align-items-center">
            @auth
                <span class="text-secondary small d-none d-md-inline">{{ auth()->user()->email }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-ui/button type="submit" variant="outline-secondary" size="sm" icon="box-arrow-right">Logout</x-ui/button>
                </form>
            @endauth
        </div>
    </div>
</nav>

<main class="container py-4">
    @if (session('status'))
        <x-ui.alert variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    @if ($errors->any())
        <x-ui.alert variant="danger">
            <strong>Controlla i campi inseriti:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-ui.alert>
    @endif

    {{ $slot }}
</main>

<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="h5 modal-title mb-0">Conferma eliminazione</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
            </div>
            <div class="modal-body">
                Questa azione non può essere annullata. Vuoi continuare?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annulla</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteSubmit">Elimina</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    (() => {
        let targetForm = null;
        const modalEl = document.getElementById('confirmDeleteModal');
        const confirmModal = new bootstrap.Modal(modalEl);

        document.querySelectorAll('form[data-confirm-delete="true"]').forEach((form) => {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                targetForm = form;
                confirmModal.show();
            });
        });

        document.getElementById('confirmDeleteSubmit').addEventListener('click', () => {
            if (targetForm) {
                targetForm.submit();
            }
        });
    })();
</script>

@stack('scripts')
</body>
</html>

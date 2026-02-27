<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inserisci contatto - {{ $exhibition->name }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-5" style="max-width: 820px;">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4 p-md-5">
            <h1 class="h4 mb-1">Inserisci contatto</h1>
            <div class="text-secondary mb-4">
                Fiera: <strong>{{ $exhibition->name }}</strong> ({{ $exhibition->date->format('d/m/Y') }})
            </div>

            <form method="POST" action="{{ route('public.store', ['token' => $token]) }}" enctype="multipart/form-data">
                @csrf

                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label">Nome</label>
                        <input class="form-control" name="first_name" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Cognome</label>
                        <input class="form-control" name="last_name" required>
                    </div>
                </div>

                <div class="row g-2 mt-1">
                    <div class="col-6">
                        <label class="form-label">Email</label>
                        <input class="form-control" type="email" name="email">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Telefono</label>
                        <input class="form-control" name="phone">
                    </div>
                </div>

                <div class="mt-2">
                    <label class="form-label">Azienda</label>
                    <input class="form-control" name="company">
                </div>

                <div class="mt-2">
                    <label class="form-label">Note</label>
                    <textarea class="form-control" name="note" rows="3"></textarea>
                </div>

                <div class="mt-2">
                    <label class="form-label">Caricamento file (opzionale)</label>
                    <input class="form-control" name="business_card" type="file" accept=".jpg,.jpeg,.png,.pdf,.webp">
                </div>

                <button class="btn btn-primary btn-lg w-100 mt-4" type="submit">Invia</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inserisci contatto - {{ $exhibition->name }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-body-tertiary">
<div class="container py-4 py-md-5" style="max-width: 760px;">
    <div class="text-center mb-3">
        <h1 class="h3 mb-1">Inserisci il tuo contatto</h1>
        <p class="text-secondary mb-0">{{ $exhibition->name }} · {{ $exhibition->display_date }}</p>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4 p-md-5">
            @if ($errors->any())
                <div class="alert alert-danger rounded-3" role="alert">
                    <strong>Controlla i dati inseriti:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('public.store', ['token' => $token]) }}" enctype="multipart/form-data" class="vstack gap-3" id="publicForm">
                @csrf

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="first_name">Nome</label>
                        <input class="form-control @error('first_name') is-invalid @enderror" id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                        @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="last_name">Cognome</label>
                        <input class="form-control @error('last_name') is-invalid @enderror" id="last_name" name="last_name" value="{{ old('last_name') }}" required>
                        @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="email">Email</label>
                        <input class="form-control @error('email') is-invalid @enderror" id="email" type="email" name="email" value="{{ old('email') }}">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="phone">Telefono</label>
                        <input class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div>
                    <label class="form-label" for="company">Azienda</label>
                    <input class="form-control @error('company') is-invalid @enderror" id="company" name="company" value="{{ old('company') }}">
                    @error('company')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label class="form-label" for="note">Note</label>
                    <textarea class="form-control @error('note') is-invalid @enderror" id="note" name="note" rows="3">{{ old('note') }}</textarea>
                    @error('note')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label class="form-label" for="contact_file">Allega un file (opzionale)</label>
                    <input class="form-control @error('contact_file') is-invalid @enderror" id="contact_file" name="contact_file" type="file" accept=".pdf,.jpg,.jpeg,.png,.webp">
                    @error('contact_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="small text-secondary mt-1">Formati supportati: PDF/JPG/PNG/WEBP · max 10MB</div>
                </div>

                <button class="btn btn-primary btn-lg w-100" id="publicSubmitBtn" type="submit">
                    <i class="bi bi-send me-1"></i> Invia contatto
                </button>
            </form>
        </div>
    </div>
</div>
<script>
    document.getElementById('publicForm').addEventListener('submit', () => {
        const btn = document.getElementById('publicSubmitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Invio in corso...';
    });
</script>
</body>
</html>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Grazie</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-body-tertiary">
<div class="container py-5" style="max-width: 760px;">
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4 p-md-5 text-center">
            <div class="display-6 text-success mb-3"><i class="bi bi-check-circle-fill"></i></div>
            <h1 class="h4 mb-2">Grazie! Contatto inviato correttamente</h1>
            <p class="text-secondary mb-0">Abbiamo registrato il tuo contatto per la fiera <strong>{{ $exhibition->name }}</strong>.</p>
        </div>
    </div>
</div>
</body>
</html>

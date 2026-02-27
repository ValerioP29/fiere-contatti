<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Grazie</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-5" style="max-width: 820px;">
    <div class="alert alert-success shadow-sm rounded-4 p-4">
        <h1 class="h4 mb-1">Grazie!</h1>
        <div>Il contatto è stato inserito correttamente per la fiera <strong>{{ $exhibition->name }}</strong>.</div>
    </div>
</div>
</body>
</html>
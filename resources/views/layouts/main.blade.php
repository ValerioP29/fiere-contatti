<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Contatti fiere' }}</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-dark text-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-black border-bottom border-secondary">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="{{ route('exhibitions.index') }}">Contatti fiere</a>

        <div class="ms-auto d-flex gap-2 align-items-center">
            @auth
                <span class="text-secondary small d-none d-md-inline">{{ auth()->user()->email }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-outline-warning btn-sm" type="submit">Logout</button>
                </form>
            @endauth
        </div>
    </div>
</nav>

<main class="container py-4">
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    {{ $slot }}
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

@stack('scripts')
</body>
</html>
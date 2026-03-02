<!DOCTYPE html>
<html lang="it" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Grazie — {{ $exhibition->name }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-slate-100 font-sans antialiased text-slate-900">

<div class="mx-auto max-w-2xl px-4 py-8 sm:py-12">
    <div class="app-card p-8 text-center sm:p-12">
        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100">
            <svg class="h-7 w-7 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
            </svg>
        </div>
        <h1 class="text-xl font-bold text-slate-900">Grazie! Contatto inviato correttamente</h1>
        <p class="mt-2 text-sm text-slate-500">
            Abbiamo registrato il tuo contatto per la fiera <strong class="text-slate-700">{{ $exhibition->name }}</strong>.
        </p>
    </div>
</div>

</body>
</html>

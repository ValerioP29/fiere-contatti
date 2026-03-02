<!DOCTYPE html>
<html lang="it" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inserisci il tuo contatto — {{ $exhibition->name }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 font-sans antialiased text-slate-900">

<div class="mx-auto max-w-2xl px-4 py-8 sm:py-12">

    <div class="mb-6 text-center">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Inserisci il tuo contatto</h1>
        <p class="mt-1 text-sm text-slate-500">
            {{ $exhibition->name }}
            @if($exhibition->display_date !== '-')
                · {{ $exhibition->display_date }}
            @endif
        </p>
    </div>

    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            <strong>Controlla i dati inseriti:</strong>
            <ul class="mt-2 list-disc ps-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="app-card p-6 sm:p-8"
         x-data="{ submitting: false }">
        <form method="POST"
              action="{{ route('public.store', ['token' => $token]) }}"
              enctype="multipart/form-data"
              @submit="submitting = true"
              class="space-y-5">
            @csrf

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700" for="first_name">
                        Nome <span class="text-rose-500">*</span>
                    </label>
                    <input id="first_name" name="first_name" type="text" required
                           value="{{ old('first_name') }}"
                           class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 {{ $errors->has('first_name') ? 'border-rose-400 ring-rose-100' : 'border-slate-300 focus:border-indigo-500 focus:ring-indigo-100' }}">
                    @error('first_name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700" for="last_name">
                        Cognome <span class="text-rose-500">*</span>
                    </label>
                    <input id="last_name" name="last_name" type="text" required
                           value="{{ old('last_name') }}"
                           class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 {{ $errors->has('last_name') ? 'border-rose-400 ring-rose-100' : 'border-slate-300 focus:border-indigo-500 focus:ring-indigo-100' }}">
                    @error('last_name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700" for="email">Email</label>
                    <input id="email" name="email" type="email"
                           value="{{ old('email') }}"
                           class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 {{ $errors->has('email') ? 'border-rose-400 ring-rose-100' : 'border-slate-300 focus:border-indigo-500 focus:ring-indigo-100' }}">
                    @error('email')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700" for="phone">Telefono</label>
                    <input id="phone" name="phone" type="text"
                           value="{{ old('phone') }}"
                           class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 {{ $errors->has('phone') ? 'border-rose-400 ring-rose-100' : 'border-slate-300 focus:border-indigo-500 focus:ring-indigo-100' }}">
                    @error('phone')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700" for="company">Azienda</label>
                <input id="company" name="company" type="text"
                       value="{{ old('company') }}"
                       class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 {{ $errors->has('company') ? 'border-rose-400 ring-rose-100' : 'border-slate-300 focus:border-indigo-500 focus:ring-indigo-100' }}">
                @error('company')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700" for="note">Note</label>
                <textarea id="note" name="note" rows="3"
                          class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 {{ $errors->has('note') ? 'border-rose-400 ring-rose-100' : 'border-slate-300 focus:border-indigo-500 focus:ring-indigo-100' }}">{{ old('note') }}</textarea>
                @error('note')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700" for="contact_file">
                    Allega un file <span class="font-normal text-slate-400">(opzionale)</span>
                </label>
                <input id="contact_file" name="contact_file" type="file"
                       accept=".pdf,.jpg,.jpeg,.png,.webp"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-500 focus:outline-none file:mr-3 file:rounded file:border-0 file:bg-slate-100 file:px-3 file:py-1 file:text-xs file:font-medium file:text-slate-700 hover:file:bg-slate-200 @error('contact_file') border-rose-400 @enderror">
                <p class="mt-1 text-xs text-slate-400">Formati supportati: PDF, JPG, PNG, WebP · max 10 MB</p>
                @error('contact_file')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <button type="submit"
                    :disabled="submitting"
                    class="w-full rounded-lg bg-indigo-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60">
                <span x-show="!submitting">Invia contatto</span>
                <span x-show="submitting" x-cloak>Invio in corso…</span>
            </button>
        </form>
    </div>
</div>

</body>
</html>

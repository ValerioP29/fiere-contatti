<x-main-layout :title="'Modifica fiera: '.$exhibition->name">
    <div class="app-container max-w-3xl py-6">
        <div class="app-card p-6 sm:p-8">
            <h1 class="text-2xl font-bold text-slate-900">Modifica fiera</h1>
            <p class="app-subheading">Aggiorna le informazioni della fiera.</p>

            <form method="POST" action="{{ route('exhibitions.update', $exhibition) }}" class="mt-6 space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="mb-1 block text-sm font-medium text-slate-700">Nome <span class="text-rose-500">*</span></label>
                    <input id="name"
                           name="name"
                           type="text"
                           required
                           autofocus
                           value="{{ old('name', $exhibition->name) }}"
                           class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 {{ $errors->has('name') ? 'border-rose-400 ring-rose-100' : 'border-slate-300 focus:border-indigo-500 focus:ring-indigo-100' }}">
                    @error('name')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="date" class="mb-1 block text-sm font-medium text-slate-700">Data</label>
                    <input id="date"
                           name="date"
                           type="date"
                           value="{{ old('date', optional($exhibition->date)->format('Y-m-d')) }}"
                           class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 {{ $errors->has('date') ? 'border-rose-400 ring-rose-100' : 'border-slate-300 focus:border-indigo-500 focus:ring-indigo-100' }}">
                    @error('date')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="company" class="mb-1 block text-sm font-medium text-slate-700">Azienda</label>
                    <input id="company"
                           name="company"
                           type="text"
                           value="{{ old('company', $exhibition->company) }}"
                           class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 {{ $errors->has('company') ? 'border-rose-400 ring-rose-100' : 'border-slate-300 focus:border-indigo-500 focus:ring-indigo-100' }}">
                    @error('company')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="note" class="mb-1 block text-sm font-medium text-slate-700">Notes</label>
                    <textarea id="note"
                              name="note"
                              rows="4"
                              class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 {{ $errors->has('note') ? 'border-rose-400 ring-rose-100' : 'border-slate-300 focus:border-indigo-500 focus:ring-indigo-100' }}">{{ old('note', $exhibition->note) }}</textarea>
                    @error('note')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <x-primary-button class="normal-case tracking-normal text-sm">
                        Salva
                    </x-primary-button>
                    <a href="{{ route('exhibitions.show', $exhibition) }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Annulla</a>
                </div>
            </form>
        </div>
    </div>
</x-main-layout>

<x-main-layout :title="'Modifica: '.$exhibition->name">
    <div class="app-container max-w-3xl py-6">
        <div class="app-card p-6 sm:p-8">
            <h1 class="text-2xl font-bold text-slate-900">Modifica</h1>
            <p class="app-subheading">Aggiorna le informazioni della fiera.</p>

            @php
                $currentMode = old('date_mode',
                    ($exhibition->start_date || $exhibition->end_date) ? 'range' : 'single'
                );
            @endphp

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

                {{-- Date con toggle singola / intervallo --}}
                <div x-data="{ mode: '{{ $currentMode }}' }">
                    <div class="mb-2 flex items-center gap-4">
                        <span class="text-sm font-medium text-slate-700">Data</span>
                        <div class="flex rounded-lg border border-slate-200 overflow-hidden text-xs font-medium">
                            <button type="button"
                                    @click="mode = 'single'"
                                    :class="mode === 'single' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-50'"
                                    class="px-3 py-1.5 transition">Singola</button>
                            <button type="button"
                                    @click="mode = 'range'"
                                    :class="mode === 'range' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-50'"
                                    class="px-3 py-1.5 transition border-l border-slate-200">Intervallo</button>
                        </div>
                    </div>
                    <input type="hidden" name="date_mode" :value="mode">

                    <div x-show="mode === 'single'" x-transition>
                        <input id="date"
                               name="date"
                               type="date"
                               value="{{ old('date', optional($exhibition->date)->format('Y-m-d')) }}"
                               class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 {{ $errors->has('date') ? 'border-rose-400 ring-rose-100' : 'border-slate-300 focus:border-indigo-500 focus:ring-indigo-100' }}">
                        @error('date')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div x-show="mode === 'range'" x-transition class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label for="start_date" class="mb-1 block text-xs font-medium text-slate-600">Dal</label>
                            <input id="start_date"
                                   name="start_date"
                                   type="date"
                                   value="{{ old('start_date', optional($exhibition->start_date)->format('Y-m-d')) }}"
                                   class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 {{ $errors->has('start_date') ? 'border-rose-400 ring-rose-100' : 'border-slate-300 focus:border-indigo-500 focus:ring-indigo-100' }}">
                            @error('start_date')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="end_date" class="mb-1 block text-xs font-medium text-slate-600">Al</label>
                            <input id="end_date"
                                   name="end_date"
                                   type="date"
                                   value="{{ old('end_date', optional($exhibition->end_date)->format('Y-m-d')) }}"
                                   class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 {{ $errors->has('end_date') ? 'border-rose-400 ring-rose-100' : 'border-slate-300 focus:border-indigo-500 focus:ring-indigo-100' }}">
                            @error('end_date')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
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
                    <label for="note" class="mb-1 block text-sm font-medium text-slate-700">Note</label>
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

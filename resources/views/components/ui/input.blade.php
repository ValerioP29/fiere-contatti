@props([
    'label' => null,
    'name',
    'type' => 'text',
    'value' => null,
    'required' => false,
    'placeholder' => null,
])

<div {{ $attributes->only('class') }}>
    @if($label)
        <label class="mb-1 block text-sm font-medium text-slate-700" for="{{ $name }}">
            {{ $label }}
            @if($required) <span class="text-rose-500">*</span> @endif
        </label>
    @endif
    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ old($name, $value) }}"
        @if($placeholder) placeholder="{{ $placeholder }}" @endif
        @if($required) required @endif
        class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 {{ $errors->has($name) ? 'border-rose-400 ring-rose-100' : 'border-slate-300 focus:border-indigo-500 focus:ring-indigo-100' }}"
        {{ $attributes->except('class') }}
    >
    @error($name)
        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
    @enderror
</div>

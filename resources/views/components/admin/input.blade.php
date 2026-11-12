@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'hint' => null,
    'required' => false,
    'prefix' => null,
])

@php($id = 'input-'.($prefix ? "{$prefix}-" : '').$name)

<div {{ $attributes->only('class') }}>
    <label for="{{ $id }}" class="a-label">
        {{ $label }}
        @if ($required)
            <span class="text-orange-deep" aria-hidden="true">*</span>
        @endif
    </label>
    <input
        id="{{ $id }}"
        type="{{ $type }}"
        name="{{ $name }}"
        @unless ($type === 'password') value="{{ old($name, $value) }}" @endunless
        {{ $attributes->except('class')->class('a-input') }}
        @if ($required) required @endif
        @error($name) aria-invalid="true" @enderror
    >
    @if ($hint)
        <p class="a-hint">{{ $hint }}</p>
    @endif
    @error($name)
        <p class="a-error">{{ $message }}</p>
    @enderror
</div>

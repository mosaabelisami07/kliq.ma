@props([
    'name',
    'label',
    'type' => 'text',
    'textarea' => false,
    'options' => null,
    'placeholder' => null,
    'optional' => false,
    'hint' => null,
])

{{-- A labelled form control with server-rendered and JavaScript error slots. --}}
@php
    $id = "field-{$name}";
    $describedBy = trim(($hint ? "{$id}-hint " : '')."{$id}-error");
@endphp

<div {{ $attributes->only('class') }}>
    <label for="{{ $id }}" class="text-sm font-medium">
        {{ $label }}
        @if ($optional)
            <span class="font-normal text-muted">({{ __('site.contact.optional') }})</span>
        @endif
    </label>

    @if ($hint)
        <p id="{{ $id }}-hint" class="mt-0.5 text-xs text-muted">{{ $hint }}</p>
    @endif

    @if ($textarea)
        <textarea
            id="{{ $id }}"
            name="{{ $name }}"
            rows="5"
            {{ $attributes->except('class')->class('field resize-y') }}
            aria-describedby="{{ $describedBy }}"
            @error($name) aria-invalid="true" @enderror
        >{{ old($name) }}</textarea>
    @elseif ($options !== null)
        <select
            id="{{ $id }}"
            name="{{ $name }}"
            {{ $attributes->except('class')->class('field') }}
            aria-describedby="{{ $describedBy }}"
            @error($name) aria-invalid="true" @enderror
        >
            <option value="">{{ $placeholder }}</option>
            @foreach ($options as $value => $optionLabel)
                <option value="{{ $value }}" @selected((string) old($name) === (string) $value)>{{ $optionLabel }}</option>
            @endforeach
        </select>
    @else
        <input
            id="{{ $id }}"
            type="{{ $type }}"
            name="{{ $name }}"
            value="{{ old($name) }}"
            @if ($placeholder) placeholder="{{ $placeholder }}" @endif
            {{ $attributes->except('class')->class('field') }}
            aria-describedby="{{ $describedBy }}"
            @error($name) aria-invalid="true" @enderror
        >
    @endif

    <p id="{{ $id }}-error" class="mt-1.5 text-sm text-red-700 dark:text-red-400" data-error-for="{{ $name }}" @unless ($errors->has($name)) hidden @endunless>
        {{ $errors->first($name) }}
    </p>
</div>

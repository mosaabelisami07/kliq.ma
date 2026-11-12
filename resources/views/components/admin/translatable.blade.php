@props([
    'name',
    'label',
    'value' => [],
    'textarea' => false,
    'rows' => 3,
    'required' => false,
    'hint' => null,
    'maxlength' => null,
    'prefix' => null,
])

{{-- One input per locale behind EN / FR / AR tabs. Only the fallback locale can be required. --}}
@php
    $locales = config('kliq.locales');
    $fallback = config('app.fallback_locale');
    $dotted = str_replace(['[', ']'], ['.', ''], $name);
    $id = 'tr-'.($prefix ? "{$prefix}-" : '').str_replace(['[', ']', '.'], '-', $name);
    $value ??= [];
    $hasError = $errors->has($dotted) || collect(array_keys($locales))->contains(fn ($locale) => $errors->has("{$dotted}.{$locale}"));
@endphp

<div {{ $attributes->class('') }} data-translatable>
    <div class="flex flex-wrap items-center justify-between gap-2">
        <label for="{{ $id }}-{{ $fallback }}" class="a-label">
            {{ $label }}
            @if ($required)
                <span class="text-orange-deep" aria-hidden="true">*</span>
            @endif
        </label>
        <div class="flex gap-1 rounded-full bg-cream p-0.5 ring-1 ring-line" role="tablist" aria-label="{{ $label }} language">
            @foreach (array_reverse($locales, true) as $locale => $meta)
                <button type="button" class="lang-tab" role="tab" data-lang-tab="{{ $locale }}" aria-selected="{{ $locale === $fallback ? 'true' : 'false' }}" title="{{ $meta['name'] }}">{{ strtoupper($locale) }}</button>
            @endforeach
        </div>
    </div>

    @foreach ($locales as $locale => $meta)
        @php($current = old("{$dotted}.{$locale}", $value[$locale] ?? ''))
        <div data-lang-panel="{{ $locale }}" @if ($locale !== $fallback) hidden @endif>
            @if ($textarea)
                <textarea id="{{ $id }}-{{ $locale }}" name="{{ $name }}[{{ $locale }}]" rows="{{ $rows }}" class="a-input resize-y" dir="{{ $meta['dir'] }}" lang="{{ $locale }}" @if ($maxlength) maxlength="{{ $maxlength }}" @endif @if ($required && $locale === $fallback) required @endif @if ($hasError) aria-invalid="true" @endif>{{ $current }}</textarea>
            @else
                <input id="{{ $id }}-{{ $locale }}" type="text" name="{{ $name }}[{{ $locale }}]" value="{{ $current }}" class="a-input" dir="{{ $meta['dir'] }}" lang="{{ $locale }}" @if ($maxlength) maxlength="{{ $maxlength }}" @endif @if ($required && $locale === $fallback) required @endif @if ($hasError) aria-invalid="true" @endif>
            @endif
        </div>
    @endforeach

    @if ($hint)
        <p class="a-hint">{{ $hint }} @unless ($required) Leave a language empty to use the English text. @endunless</p>
    @endif

    @foreach ([$dotted, ...array_map(fn ($locale) => "{$dotted}.{$locale}", array_keys($locales))] as $key)
        @error($key)
            <p class="a-error">{{ $message }}</p>
        @enderror
    @endforeach
</div>

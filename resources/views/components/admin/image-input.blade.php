@props([
    'name',
    'label',
    'current' => null,
    'hint' => 'JPG, PNG or WebP, up to 5 MB.',
    'accept' => 'image/jpeg,image/png,image/webp',
    'removable' => true,
    'prefix' => null,
    'fit' => 'object-cover',
])

{{-- File input with a preview of the current image and a "remove" option. --}}
@php($key = ($prefix ? "{$prefix}-" : "").$name)
<div {{ $attributes->class('') }}>
    <label for="file-{{ $key }}" class="a-label">{{ $label }}</label>
    <div class="mt-2 flex flex-wrap items-start gap-4">
        <div class="grid aspect-[4/3] w-36 shrink-0 place-items-center overflow-hidden rounded-xl bg-cream ring-1 ring-line">
            <img id="preview-{{ $key }}" src="{{ $current }}" alt="" class="size-full {{ $fit }}" @unless ($current) hidden @endunless onerror="this.hidden = true">
            @unless ($current)
                <x-icon name="image" class="size-7 text-muted" />
            @endunless
        </div>
        <div class="min-w-0 flex-1">
            <input type="file" name="{{ $name }}" id="file-{{ $key }}" accept="{{ $accept }}" data-preview="preview-{{ $key }}" class="block w-full text-sm file:me-3 file:cursor-pointer file:rounded-full file:border-0 file:bg-ink file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-orange hover:file:text-black" @error($name) aria-invalid="true" @enderror>
            <p class="a-hint">{{ $hint }}</p>
            @if ($current && $removable)
                <label class="mt-2 inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" name="remove_{{ $name }}" value="1" class="accent-orange"> Remove current image
                </label>
            @endif
            @error($name)
                <p class="a-error">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>

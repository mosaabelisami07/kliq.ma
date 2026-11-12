@props([
    'tone' => 'ink',
    'seed' => 1,
    'streaks' => 3,
    'src' => null,
    'alt' => '',
    'eager' => false,
    'parallax' => null,
    'vt' => null,
])

{{--
    A framed visual. The generative light-trail artwork is always drawn and
    the image (a full URL) is laid over it, so a missing or broken file
    falls back to the artwork instead of a broken-image icon.
--}}
@php
    $positioned = preg_match('/\b(absolute|fixed|sticky)\b/', (string) $attributes->get('class'));
@endphp

<div {{ $attributes->class(['media overflow-hidden', 'relative' => ! $positioned]) }} @if ($vt) data-vt="{{ $vt }}" @endif>
    <div @class(['media__inner absolute inset-x-0', 'inset-y-0' => ! $parallax, '-inset-y-[12%]' => $parallax]) @if ($parallax) data-parallax="{{ $parallax }}" @endif>
        <x-art :tone="$tone" :seed="$seed" :streaks="$streaks" class="absolute inset-0" />
        @if ($src)
            <img
                src="{{ $src }}"
                alt="{{ $alt }}"
                @if ($eager) fetchpriority="high" @else loading="lazy" @endif
                decoding="async"
                class="absolute inset-0 size-full object-cover"
                onerror="this.remove()"
            >
        @elseif ($alt)
            <span class="sr-only">{{ $alt }}</span>
        @endif
    </div>

    {{ $slot }}
</div>

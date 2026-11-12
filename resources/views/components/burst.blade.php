{{-- The Kliq "click spark": eight rays around a dot. --}}
<svg {{ $attributes->merge(['class' => 'burst']) }} viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">
    @for ($ray = 0; $ray < 8; $ray++)
        <rect x="10.9" y="{{ $ray % 2 ? 3.4 : 0.6 }}" width="2.2" height="{{ $ray % 2 ? 5.6 : 8.4 }}" rx="1.1" transform="rotate({{ $ray * 45 }} 12 12)" />
    @endfor
    <circle cx="12" cy="12" r="2.6" />
</svg>

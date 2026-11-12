@props(['name'])

{{-- A Lucide line icon (ISC licence) from App\Support\Icons. --}}
<svg {{ $attributes->merge(['class' => 'size-5']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">{!! \App\Support\Icons::PATHS[$name] ?? \App\Support\Icons::PATHS['sparkles'] !!}</svg>

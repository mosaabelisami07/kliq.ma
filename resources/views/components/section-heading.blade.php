@props([
    'label',
    'title' => null,
    'text' => null,
    'id' => null,
])

<div {{ $attributes->class('max-w-2xl') }}>
    <x-section-label data-reveal>{{ $label }}</x-section-label>

    @if ($title)
        <h2 @if ($id) id="{{ $id }}" @endif class="mt-5 font-display text-[clamp(2.1rem,4.4vw,3.6rem)] leading-[1.02] font-bold tracking-[-0.035em]" data-split>
            {{ $title }}
        </h2>
    @endif

    @if ($text)
        <p class="mt-5 text-lg text-muted" data-reveal style="--delay: 150">{{ $text }}</p>
    @endif
</div>

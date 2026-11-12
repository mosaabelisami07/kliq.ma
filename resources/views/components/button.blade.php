@props([
    'href',
    'variant' => 'solid',
    'arrow' => true,
    'magnetic' => true,
])

<a href="{{ $href }}" {{ $attributes->class(['btn', "btn--{$variant}"]) }} @if ($magnetic) data-magnetic="0.25" @endif>
    <span class="btn__roll">
        <span>{{ $slot }}</span>
        <span aria-hidden="true">{{ $slot }}</span>
    </span>

    @if ($arrow)
        <x-arrow class="btn__arrow size-4" />
    @endif
</a>

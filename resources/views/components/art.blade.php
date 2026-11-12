@props([
    'tone' => 'ink',
    'seed' => 1,
    'streaks' => 3,
])

@php
    // Deterministic pseudo-random values so each seed always draws the same artwork.
    $pick = fn (int $salt, int $min, int $max): int => $min + (crc32("{$seed}:{$salt}") % ($max - $min + 1));

    $streakStyles = collect(range(1, $streaks))->map(fn (int $i): string => sprintf(
        '--t:%d%%;--l:%d%%;--w:%d%%;--h:%d%%;--r:%ddeg;--b:%dpx;--d:%ds;--dl:-%ds',
        $pick($i * 10 + 1, 6, 80),
        $pick($i * 10 + 2, -35, 5),
        $pick($i * 10 + 3, 90, 150),
        $pick($i * 10 + 4, 6, 22),
        $pick($i * 10 + 5, -26, 26),
        $pick($i * 10 + 6, 7, 22),
        $pick($i * 10 + 7, 6, 13),
        $pick($i * 10 + 8, 0, 9),
    ));

    $blobStyle = sprintf('--x:%d%%;--y:%d%%;--s:%d%%', $pick(91, 25, 70), $pick(92, 25, 70), $pick(93, 50, 85));
@endphp

<span {{ $attributes->merge(['class' => "art art--{$tone}"]) }} aria-hidden="true">
    <span class="art__blob" style="{{ $blobStyle }}"></span>
    @foreach ($streakStyles as $style)
        <span class="art__streak" style="{{ $style }}"></span>
    @endforeach
    <span class="art__grain"></span>
</span>

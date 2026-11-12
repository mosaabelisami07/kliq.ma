<p {{ $attributes->merge(['class' => 'flex items-center gap-2 text-xs font-semibold tracking-[0.16em] text-accent uppercase']) }}>
    <x-burst class="size-3.5 text-orange" />
    <span>{{ $slot }}</span>
</p>

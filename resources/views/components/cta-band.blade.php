@props([
    'title',
    'text' => null,
    'action',
])

<section class="container-site py-20 md:py-24">
    <div class="relative isolate flex flex-col items-start gap-10 overflow-hidden rounded-[2rem] bg-panel p-8 md:flex-row md:items-end md:justify-between md:p-14" data-reveal>
        <x-art tone="hero" :seed="314" :streaks="3" class="absolute inset-y-0 end-0 -z-10 w-2/3 opacity-50" />

        <div>
            <h2 class="max-w-3xl font-display text-[clamp(2rem,4.2vw,3.75rem)] leading-[1.02] font-bold tracking-[-0.04em]">{{ $title }}</h2>
            @if ($text)
                <p class="mt-5 max-w-md text-lg text-muted">{{ $text }}</p>
            @endif
        </div>

        <x-button :href="route('home').'#contact'" variant="orange">{{ $action }}</x-button>
    </div>
</section>

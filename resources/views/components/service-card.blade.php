@props([
    'service',
    'index' => 0,
])

<article {{ $attributes->class('lift group relative flex flex-col rounded-[1.75rem] border border-ink/15 p-7 md:p-8') }} data-reveal style="--delay: {{ ($index % 3) * 100 }}">
    @if ($image = $service->imageUrl())
        <img src="{{ $image }}" alt="" loading="lazy" decoding="async" class="mb-6 aspect-[16/9] w-full rounded-2xl object-cover" onerror="this.remove()">
    @endif

    <div class="flex items-start justify-between gap-4">
        <span class="icon-tile"><x-icon :name="$service->icon ?? 'sparkles'" class="size-8" /></span>
        <span class="text-sm font-medium text-muted tabular-nums">{{ sprintf('%02d', $index + 1) }}</span>
    </div>

    <h3 class="mt-6 font-display text-xl leading-snug font-semibold tracking-tight md:text-[1.4rem]">
        <a href="{{ route('services.show', $service) }}" class="card-link outline-none">{{ $service->localized('title') }}</a>
    </h3>
    <p class="mt-3 text-muted">{{ $service->localized('summary') }}</p>

    <span class="mt-auto inline-flex items-center gap-2 pt-6 text-sm font-semibold text-accent">
        {{ __('site.common.learn_more') }}
        <x-arrow class="size-4 transition-transform duration-500 ease-out-expo group-hover:translate-x-1 rtl:group-hover:-translate-x-1" />
    </span>
</article>

@php
    $title = $service->localized('title');
    $summary = $service->localized('summary');
    $included = $service->localizedLines('deliverables');
@endphp

<x-layouts.app :title="$title" :description="$summary" :image="$service->imageUrl()">
    <article>
        <header class="container-site pt-[calc(var(--header-h)+3rem)] md:pt-[calc(var(--header-h)+4.5rem)]">
            <a href="{{ route('home') }}#services" class="link-arrow inline-flex items-center gap-2 text-sm text-muted transition-colors hover:text-ink">
                <x-arrow class="size-4 rotate-180" />
                {{ __('site.services.label') }}
            </a>

            <div class="mt-10 grid items-end gap-10 lg:grid-cols-12">
                <div class="lg:col-span-8">
                    <span class="icon-tile" data-reveal><x-icon :name="$service->icon ?? 'sparkles'" class="size-12" /></span>
                    <h1 class="mt-8 font-display text-[clamp(2.5rem,6.5vw,5.5rem)] leading-[1] font-bold tracking-[-0.045em]" data-split>{{ $title }}</h1>
                    @if ($summary)
                        <p class="mt-7 max-w-2xl text-xl text-muted" data-reveal style="--delay: 200">{{ $summary }}</p>
                    @endif
                </div>
                <div class="lg:col-span-4 lg:justify-self-end" data-reveal style="--delay: 300">
                    <x-button :href="route('home').'#contact'" variant="orange">{{ __('site.nav.cta') }}</x-button>
                </div>
            </div>
        </header>

        @if ($image = $service->imageUrl())
            <div class="mt-12 px-2 md:px-3">
                <x-media :src="$image" :alt="$title" tone="ember" :seed="$service->id" :eager="true" class="aspect-[16/9] rounded-[2rem] lg:aspect-[21/8]" />
            </div>
        @endif

        <section class="container-site grid gap-12 py-16 md:py-24 lg:grid-cols-12">
            @if ($body = $service->localized('body'))
                <div class="space-y-5 text-lg leading-relaxed text-ink/85 lg:col-span-7" data-reveal>
                    @foreach (preg_split('/\R{2,}/', $body) as $paragraph)
                        <p>{!! nl2br(e($paragraph)) !!}</p>
                    @endforeach
                </div>
            @endif

            @if ($included)
                <div class="surface h-fit p-7 lg:col-span-5 lg:col-start-8" data-reveal style="--delay: 150">
                    <h2 class="font-display text-xl font-semibold">{{ __('site.services.included') }}</h2>
                    <ul class="mt-5 flex flex-col gap-3">
                        @foreach ($included as $item)
                            <li class="flex items-start gap-3">
                                <span class="mt-0.5 grid size-6 shrink-0 place-items-center rounded-full bg-orange text-black"><x-icon name="check" class="size-3.5" /></span>
                                <span>{{ $item }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </section>

        @if ($others->isNotEmpty())
            <section class="container-site pb-8">
                <x-section-label data-reveal>{{ __('site.services.other') }}</x-section-label>
                <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($others->take(3) as $other)
                        <x-service-card :service="$other" :index="$loop->index" />
                    @endforeach
                </div>
            </section>
        @endif
    </article>

    <x-cta-band :title="__('site.services.cta_title')" :text="__('site.services.cta_text')" :action="__('site.nav.cta')" />
</x-layouts.app>

@php
    $title = $project->localized('title');
    $summary = $project->localized('summary');
    $services = $project->localizedLines('services_provided');
@endphp

<x-layouts.app :title="$title" :description="$summary" :image="$project->thumbnailUrl()">
    <article>
        <header class="container-site pt-[calc(var(--header-h)+3rem)] md:pt-[calc(var(--header-h)+4.5rem)]">
            <a href="{{ route('projects.index') }}" class="link-arrow inline-flex items-center gap-2 text-sm text-muted transition-colors hover:text-ink">
                <x-arrow class="size-4 rotate-180" />
                {{ __('site.work.back') }}
            </a>

            <div class="mt-10 grid items-center gap-10 lg:grid-cols-12 lg:gap-14">
                <div class="lg:col-span-6">
                    <div class="flex flex-wrap items-center gap-3 text-sm" data-reveal>
                        @if ($project->category)
                            <span class="tag">{{ $project->category->localized('name') }}</span>
                        @endif
                        @if ($project->year)
                            <span class="text-muted tabular-nums">{{ $project->year }}</span>
                        @endif
                    </div>

                    <h1 class="mt-6 font-display text-[clamp(2.5rem,6vw,5.5rem)] leading-[0.98] font-bold tracking-[-0.05em]" dir="auto" data-split>
                        {{ $title }}
                    </h1>

                    @if ($summary)
                        <p class="mt-6 max-w-xl text-lg text-muted md:text-xl" data-reveal style="--delay: 200">{{ $summary }}</p>
                    @endif
                </div>

                <div class="lg:col-span-6" data-reveal style="--delay: 150">
                    @if ($video = $project->video())
                        <div @class([
                            'overflow-hidden rounded-[2rem] bg-black shadow-[0_40px_70px_-40px_rgb(23_23_23/0.45)]',
                            'mx-auto aspect-[9/16] max-h-[80svh] max-w-sm' => $video->isPortrait(),
                            'aspect-video w-full' => ! $video->isPortrait(),
                        ]) style="view-transition-name: project-{{ $project->slug }}">
                            <iframe
                                src="{{ $video->embedUrl() }}"
                                title="{{ $title }}"
                                class="block size-full border-0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                referrerpolicy="strict-origin-when-cross-origin"
                                allowfullscreen
                            ></iframe>
                        </div>
                    @else
                    <x-media
                        :src="$project->thumbnailUrl()"
                        :alt="$title"
                        tone="ember"
                        :seed="$project->artSeed()"
                        :streaks="5"
                        :eager="true"
                        class="aspect-[4/3] w-full rounded-[2rem] shadow-[0_40px_70px_-40px_rgb(23_23_23/0.45)]"
                        style="view-transition-name: project-{{ $project->slug }}"
                    />
                    @endif
                </div>
            </div>
        </header>

        <section class="container-site py-16 md:py-24">
            <div class="grid gap-12 lg:grid-cols-12">
                <dl class="grid h-fit grid-cols-2 gap-8 lg:col-span-4 lg:grid-cols-1" data-reveal>
                    @if ($project->client)
                        <div>
                            <dt class="text-xs font-semibold tracking-[0.16em] text-muted uppercase">{{ __('site.common.client') }}</dt>
                            <dd class="mt-2 text-lg font-medium" dir="auto">{{ $project->client }}</dd>
                        </div>
                    @endif
                    @if ($project->category)
                        <div>
                            <dt class="text-xs font-semibold tracking-[0.16em] text-muted uppercase">{{ __('site.common.category') }}</dt>
                            <dd class="mt-2 text-lg font-medium">{{ $project->category->localized('name') }}</dd>
                        </div>
                    @endif
                    @if ($project->year)
                        <div>
                            <dt class="text-xs font-semibold tracking-[0.16em] text-muted uppercase">{{ __('site.common.year') }}</dt>
                            <dd class="mt-2 text-lg font-medium tabular-nums">{{ $project->year }}</dd>
                        </div>
                    @endif
                    @if ($services)
                        <div class="col-span-2 lg:col-span-1">
                            <dt class="text-xs font-semibold tracking-[0.16em] text-muted uppercase">{{ __('site.work.services') }}</dt>
                            <dd class="mt-3 flex flex-wrap gap-2">
                                @foreach ($services as $service)
                                    <span class="chip-tag">{{ $service }}</span>
                                @endforeach
                            </dd>
                        </div>
                    @endif
                    @if ($project->external_url)
                        <div class="col-span-2 lg:col-span-1">
                            <a href="{{ $project->external_url }}" class="btn btn--orange" target="_blank" rel="noopener noreferrer">
                                {{ __('site.work.visit') }}
                                <x-icon name="external-link" class="size-4" />
                            </a>
                        </div>
                    @endif
                </dl>

                @if ($body = $project->localized('body'))
                    <div class="space-y-5 text-lg leading-relaxed text-ink/85 lg:col-span-7 lg:col-start-6" data-reveal>
                        @foreach (preg_split('/\R{2,}/', $body) as $paragraph)
                            <p>{!! nl2br(e($paragraph)) !!}</p>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

        @if ($project->images->isNotEmpty())
            <section class="container-site pb-16" aria-label="{{ __('site.work.gallery') }}">
                <x-section-label data-reveal>{{ __('site.work.gallery') }}</x-section-label>
                <div class="mt-8 columns-1 gap-4 sm:columns-2 lg:columns-3">
                    @foreach ($project->images as $image)
                        <figure class="mb-4 break-inside-avoid" data-reveal>
                            <img src="{{ $image->url() }}" alt="{{ $image->caption ?? $title }}" loading="lazy" decoding="async" class="w-full rounded-[1.5rem] bg-ink/5" onerror="this.closest('figure').remove()">
                            @if ($image->caption)
                                <figcaption class="mt-2 text-sm text-muted">{{ $image->caption }}</figcaption>
                            @endif
                        </figure>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($next)
            <section class="container-site pt-12">
                <a href="{{ route('projects.show', $next) }}" class="group block border-t border-line pt-10" data-cursor="{{ __('site.common.next') }}" data-morph>
                    <p class="text-sm font-semibold tracking-[0.16em] text-muted uppercase">{{ __('site.work.next') }}</p>
                    <div class="mt-6 flex flex-col gap-8 md:flex-row md:items-end md:justify-between">
                        <h2 class="font-display text-[clamp(2.5rem,7vw,6rem)] leading-[0.98] font-bold tracking-[-0.05em] transition-colors duration-500 group-hover:text-orange">
                            {{ $next->localized('title') }}
                        </h2>
                        <x-media :src="$next->thumbnailUrl()" tone="ember" :seed="$next->artSeed()" :vt="'project-'.$next->slug" class="aspect-[16/10] w-full shrink-0 rounded-[1.5rem] md:w-[24rem]" />
                    </div>
                </a>
            </section>
        @endif
    </article>

    <x-cta-band :title="__('site.services.cta_title')" :text="__('site.services.cta_text')" :action="__('site.nav.cta')" />
</x-layouts.app>

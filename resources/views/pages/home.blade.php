@php
    $visual = fn (int $index) => $heroVisuals->get($index);
    $values = $site->items('values');
    $steps = $site->items('process_steps');
    $reasons = $site->items('why_items');
@endphp

<x-layouts.app>
    {{-- Hero ---------------------------------------------------------------- --}}
    <section class="relative overflow-hidden pt-[var(--header-h)]" data-hero aria-labelledby="hero-title">
        <div class="hero__art pointer-events-none absolute inset-0 -z-10 opacity-70">
            <x-art tone="hero" :seed="7" :streaks="4" class="absolute inset-[-6%]" />
        </div>

        {{-- Project imagery drifting behind the headline --}}
        <div class="pointer-events-none absolute inset-0 -z-10" data-reveal style="--delay: 300" aria-hidden="true">
            <x-media :src="$visual(0)?->thumbnailUrl()" tone="ember" :seed="11" :eager="true" class="hero-swim absolute start-[3%] top-[16%] w-36 rotate-[-6deg] rounded-[1.5rem] opacity-25 md:opacity-40 shadow-[0_30px_60px_-30px_rgb(23_23_23/0.55)] aspect-[4/5] md:w-48 lg:w-56" style="--sx: 26px; --sy: 18px; --dur: 17s" />
            <x-media :src="$visual(1)?->thumbnailUrl()" tone="ink" :seed="23" class="hero-swim absolute end-[4%] top-[13%] w-32 rotate-[5deg] rounded-[1.5rem] opacity-25 md:opacity-40 shadow-[0_30px_60px_-30px_rgb(23_23_23/0.5)] aspect-square md:w-44 lg:w-52" style="--sx: -22px; --sy: 24px; --dur: 21s; --swim-delay: -6s" />
            <x-media :src="$visual(2)?->thumbnailUrl()" tone="paper" :seed="37" class="hero-swim absolute start-[9%] bottom-[7%] w-40 rotate-[4deg] rounded-[1.5rem] opacity-35 shadow-[0_30px_60px_-30px_rgb(23_23_23/0.5)] aspect-[4/3] max-md:hidden lg:w-56" style="--sx: 30px; --sy: -16px; --dur: 19s; --swim-delay: -11s" />
            <x-media :src="$visual(3)?->thumbnailUrl()" tone="ember" :seed="41" class="hero-swim absolute end-[8%] bottom-[8%] w-44 rotate-[-4deg] rounded-[1.5rem] opacity-35 shadow-[0_30px_60px_-30px_rgb(23_23_23/0.5)] aspect-[4/3] max-md:hidden lg:w-60" style="--sx: -28px; --sy: -20px; --dur: 23s; --swim-delay: -3s" />
            <x-media :src="$visual(4)?->thumbnailUrl()" tone="ink" :seed="53" class="hero-swim absolute start-[21%] top-[15%] w-28 rotate-[8deg] rounded-[1.25rem] opacity-30 aspect-square max-lg:hidden" style="--sx: 18px; --sy: 22px; --dur: 25s; --swim-delay: -14s" />
            <x-media :src="$visual(5)?->thumbnailUrl()" tone="paper" :seed="67" class="hero-swim absolute end-[3%] top-[44%] w-28 rotate-[-7deg] rounded-[1.25rem] opacity-30 aspect-[4/5] max-lg:hidden" style="--sx: -20px; --sy: -24px; --dur: 20s; --swim-delay: -8s" />

            <span class="hero-swim absolute start-[9%] top-[56%] grid size-12 place-items-center rounded-2xl bg-card text-accent opacity-60 shadow-lg ring-1 ring-line max-lg:hidden" style="--sx: 14px; --sy: -18px; --dur: 13s"><x-icon name="chart-line" class="size-6" /></span>
            <span class="hero-swim absolute end-[30%] top-[18%] grid size-11 place-items-center rounded-2xl bg-orange text-black opacity-60 shadow-lg max-lg:hidden" style="--sx: -16px; --sy: 14px; --dur: 15s; --swim-delay: -5s"><x-icon name="megaphone" class="size-5" /></span>

            {{-- Soft glow keeps the centred text readable over the imagery --}}
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_42%_48%_at_50%_50%,var(--paper)_35%,transparent_100%)]"></div>
        </div>

        <div class="container-site flex flex-col items-center py-20 text-center md:py-24 lg:min-h-[calc(100svh-var(--header-h))] lg:justify-center">
            <div class="mx-auto max-w-4xl">
                <p class="eyebrow justify-center" data-reveal>{{ $site->text('hero_eyebrow') }}</p>

                <h1 id="hero-title" class="mt-7 font-display text-[clamp(2.75rem,6.2vw,5.4rem)] leading-[0.98] font-bold tracking-[-0.045em]" data-split>
                    {!! $site->highlighted('hero_title') !!}
                </h1>

                <p class="mx-auto mt-7 max-w-2xl text-lg text-muted md:text-xl" data-reveal style="--delay: 250">{{ $site->text('hero_text') }}</p>

                <div class="mt-9 flex flex-wrap items-center justify-center gap-3" data-reveal style="--delay: 380">
                    <x-button href="#work" variant="orange">{{ __('site.hero.explore') }}</x-button>
                    <x-button href="#contact" variant="outline">{{ __('site.hero.talk') }}</x-button>
                </div>

                @if ($services->isNotEmpty())
                    <ul class="mt-10 flex flex-wrap justify-center gap-2" data-reveal style="--delay: 500" aria-label="{{ __('site.services.label') }}">
                        @foreach ($services->take(4) as $service)
                            <li><a href="{{ route('services.show', $service) }}" title="{{ $service->localized('title') }}" class="chip-tag gap-1.5 !bg-card ring-1 ring-line transition hover:ring-orange max-sm:size-11 max-sm:justify-center max-sm:!p-0"><x-icon :name="$service->icon ?? 'sparkles'" class="size-3.5 text-accent max-sm:size-5" /><span class="max-sm:sr-only">{{ $service->localized('title') }}</span></a></li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </section>

    {{-- Brands -------------------------------------------------------------- --}}
    @if ($brands->isNotEmpty())
        <section id="brands" class="py-16 md:py-24" aria-labelledby="brands-title">
            <div class="container-site">
                <div class="text-center">
                    <x-section-label class="justify-center" data-reveal>{{ __('site.brands.label') }}</x-section-label>
                    <h2 id="brands-title" class="mx-auto mt-4 max-w-3xl font-display text-[clamp(1.9rem,3.6vw,3rem)] leading-[1.05] font-bold tracking-[-0.03em]" data-split>{{ $site->text('brands_title') }}</h2>
                </div>

            </div>

            {{-- Two endless rows moving in opposite directions; each track is doubled for a seamless loop. --}}
            <div class="mt-8 flex flex-col md:mt-10">
                @foreach ([$brands, $brands->reverse()->values()] as $row)
                    @php $isEcho = ! $loop->first; @endphp
                    <div @class(['marquee py-3', 'marquee--reverse' => $isEcho]) data-reveal style="--delay: {{ $loop->index * 120 }}" @if ($isEcho) aria-hidden="true" @endif>
                        @foreach ([false, true] as $isCopy)
                            <ul class="marquee__track gap-3 pe-3 md:gap-5 md:pe-5" @if ($isCopy) aria-hidden="true" @endif>
                                @foreach ($row as $brand)
                                    <li class="w-40 shrink-0 md:w-56">
                                        <div class="group relative grid h-20 place-items-center px-5 transition duration-500 ease-out-expo hover:scale-110 md:h-24">
                                            @if ($logo = $brand->logoUrl())
                                                <img src="{{ $logo }}" alt="{{ $isCopy || $isEcho ? '' : $brand->name }}" loading="lazy" decoding="async" class="max-h-[3rem] max-w-[80%] object-contain md:max-h-[4rem] dark:invert dark:hue-rotate-180" onerror="this.replaceWith(Object.assign(document.createElement('span'), { className: 'font-display text-lg font-bold text-[#171717]', textContent: {{ Js::from($brand->name) }} }))">
                                            @else
                                                <span class="text-center font-display text-lg leading-tight font-bold tracking-tight text-[#171717] md:text-xl" dir="auto">{{ $brand->name }}</span>
                                            @endif
                                            @if ($brand->url)
                                                <a href="{{ $brand->url }}" target="_blank" rel="noopener noreferrer" class="card-link rounded-2xl" @if ($isCopy || $isEcho) tabindex="-1" @endif><span class="sr-only">{{ $brand->name }}</span></a>
                                            @endif
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- About --------------------------------------------------------------- --}}
    <section id="about" class="section relative" aria-labelledby="about-title">
        <x-section-divider class="absolute inset-x-0 top-10 lg:top-14" />
        <div class="container-site grid gap-12 lg:grid-cols-12 lg:gap-16">
            <div class="lg:col-span-5">
                <div class="lg:sticky lg:top-[calc(var(--header-h)+2rem)]">
                    <x-section-heading :label="__('site.about.label')" :title="$site->text('about_title')" id="about-title" />
                    <p class="mt-6 text-lg text-muted" data-reveal>{{ $site->text('about_text') }}</p>
                </div>
            </div>

            <div class="lg:col-span-7">
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach (['mission' => ['target', 'about_mission'], 'approach' => ['compass', 'about_approach']] as $key => [$icon, $setting])
                        @if ($text = $site->text($setting))
                            <div class="surface p-6" data-reveal style="--delay: {{ $loop->index * 120 }}">
                                <span class="icon-tile"><x-icon :name="$icon" class="size-8" /></span>
                                <h3 class="mt-5 font-display text-lg font-semibold">{{ __("site.about.{$key}") }}</h3>
                                <p class="mt-2 text-muted">{{ $text }}</p>
                            </div>
                        @endif
                    @endforeach
                </div>

                @if ($values)
                    <h3 class="mt-12 text-sm font-semibold tracking-[0.14em] text-accent uppercase" data-reveal>{{ __('site.about.values') }}</h3>
                    <ul class="mt-5 grid gap-x-8 gap-y-6 sm:grid-cols-2">
                        @foreach ($values as $value)
                            <li class="flex gap-4" data-reveal style="--delay: {{ $loop->index * 80 }}">
                                <span class="shrink-0 pt-0.5 text-accent"><x-icon :name="$value['icon']" class="size-6" /></span>
                                <div>
                                    <p class="font-semibold">{{ $value['title'] }}</p>
                                    @if ($value['text'])
                                        <p class="mt-1 text-sm text-muted">{{ $value['text'] }}</p>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </section>

    {{-- Services ------------------------------------------------------------ --}}
    @if ($services->isNotEmpty())
        <section id="services" class="section bg-[color-mix(in_srgb,var(--panel)_60%,var(--paper))]" aria-labelledby="services-title">
            <div class="container-site">
                <div class="flex flex-wrap items-end justify-between gap-6">
                    <x-section-heading :label="__('site.services.label')" :title="$site->text('services_title')" :text="$site->text('services_text')" id="services-title" />
                    <x-button href="#contact" variant="solid" class="max-sm:hidden">{{ __('site.nav.cta') }}</x-button>
                </div>

                <div class="mt-12" data-carousel>
                    <div class="carousel-track" data-carousel-track tabindex="0" role="region" aria-roledescription="carousel" aria-label="{{ __('site.services.label') }}">
                        @foreach ($services as $service)
                            <div class="carousel-slide" role="group" aria-roledescription="slide" aria-label="{{ $loop->iteration }} / {{ $loop->count }}">
                                <x-service-card :service="$service" :index="$loop->index" class="h-full" />
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6 flex items-center justify-between gap-6">
                        <div class="flex flex-wrap items-center gap-2" data-carousel-dots></div>
                        <div class="flex gap-2">
                            <button type="button" class="carousel-button" data-carousel-prev aria-label="{{ __('site.common.prev') }}"><x-icon name="chevron-left" class="size-5 rtl:rotate-180" /></button>
                            <button type="button" class="carousel-button" data-carousel-next aria-label="{{ __('site.common.next') }}"><x-icon name="chevron-right" class="size-5 rtl:rotate-180" /></button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- Our work ------------------------------------------------------------ --}}
    <section id="work" class="section relative" aria-labelledby="work-title">
        <x-section-divider class="absolute inset-x-0 top-10 lg:top-14" />
        <div class="container-site">
            <div class="flex flex-wrap items-end justify-between gap-8">
                <x-section-heading :label="__('site.work.label')" :title="$site->text('work_title')" :text="$site->text('work_text')" id="work-title" />

                @if ($categories->count() > 1)
                    <nav aria-label="{{ __('site.work.filter') }}" data-reveal>
                        <ul class="flex flex-wrap gap-2">
                            <li>
                                <a href="{{ route('projects.index') }}" class="filter-pill">{{ __('site.work.all') }}</a>
                            </li>
                            @foreach ($categories as $category)
                                <li>
                                    <a href="{{ route('projects.index', ['category' => $category->slug]) }}" class="filter-pill">{{ $category->localized('name') }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </nav>
                @endif
            </div>

            @if ($projects->isNotEmpty())
                <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($projects as $project)
                        <x-project-card :project="$project" :delay="($loop->index % 3) * 100" />
                    @endforeach
                </div>

                <div class="mt-14 flex justify-center">
                    <x-button :href="route('projects.index')" variant="outline">{{ __('site.work.view_all') }}</x-button>
                </div>
            @else
                <p class="mt-14 rounded-[1.75rem] border border-dashed border-line py-16 text-center text-lg text-muted">{{ __('site.work.empty') }}</p>
            @endif
        </div>
    </section>

    {{-- Process ------------------------------------------------------------- --}}
    @if ($steps)
        <section id="process" class="px-2 md:px-3" aria-labelledby="process-title">
            <div class="section rounded-[2.5rem] bg-panel">
                <div class="container-site">
                    <x-section-heading :label="__('site.process.label')" :title="$site->text('process_title')" :text="$site->text('process_text')" id="process-title" class="mx-auto text-center [&>p:first-child]:justify-center" />

                    <ol class="process-track relative mt-16 grid gap-10 lg:grid-cols-5 lg:gap-6">
                        @foreach ($steps as $step)
                            <li class="relative flex gap-5 lg:flex-col lg:items-center lg:text-center" data-reveal style="--delay: {{ $loop->index * 110 }}">
                                <span class="relative z-10 grid size-16 shrink-0 place-items-center rounded-full border-2 border-orange bg-card text-accent shadow-[0_0_0_8px_var(--panel)]">
                                    <x-icon :name="$step['icon']" class="size-7" />
                                </span>
                                <div>
                                    <p class="text-xs font-semibold tracking-[0.14em] text-accent uppercase">{{ __('site.process.step') }} {{ sprintf('%02d', $loop->iteration) }}</p>
                                    <h3 class="mt-1.5 font-display text-xl font-semibold">{{ $step['title'] }}</h3>
                                    @if ($step['text'])
                                        <p class="mt-2 text-sm text-muted">{{ $step['text'] }}</p>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </div>
        </section>
    @endif

    {{-- Why choose KLIQ ----------------------------------------------------- --}}
    <section id="why" class="section relative" aria-labelledby="why-title">
        <x-section-divider class="absolute inset-x-0 top-10 lg:top-14" />
        <div class="container-site">
            <div class="relative isolate overflow-hidden rounded-[2.5rem] bg-[#171717] p-8 text-white md:p-14">
                <x-art tone="ink" :seed="130" :streaks="3" class="absolute inset-0 -z-10 opacity-40" />

                <div class="grid gap-12 lg:grid-cols-12">
                    <div class="lg:col-span-5">
                        <x-section-label class="!text-orange" data-reveal>{{ __('site.why.label') }}</x-section-label>
                        <h2 id="why-title" class="mt-5 font-display text-[clamp(2.1rem,4.4vw,3.6rem)] leading-[1.02] font-bold tracking-[-0.035em]" data-split>{{ $site->text('why_title') }}</h2>
                        <p class="mt-6 text-lg text-white/70" data-reveal>{{ $site->text('why_text') }}</p>
                        <x-button href="#contact" variant="orange" class="mt-9" data-reveal>{{ __('site.nav.cta') }}</x-button>
                    </div>

                    @if ($reasons)
                        <ul class="grid gap-4 sm:grid-cols-2 lg:col-span-7">
                            @foreach ($reasons as $reason)
                                <li class="rounded-[1.5rem] border border-white/10 bg-white/[0.04] p-6 backdrop-blur transition-colors duration-500 hover:border-orange/60" data-reveal style="--delay: {{ $loop->index * 100 }}">
                                    <span class="text-orange"><x-icon :name="$reason['icon']" class="size-8" /></span>
                                    <h3 class="mt-5 font-display text-xl font-semibold">{{ $reason['title'] }}</h3>
                                    @if ($reason['text'])
                                        <p class="mt-2 text-white/65">{{ $reason['text'] }}</p>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- Contact ------------------------------------------------------------- --}}
    @php
        $email = $site->get('contact_email');
        $phone = $site->get('contact_phone');
        $whatsapp = $site->whatsappUrl();
        $socials = $site->socialLinks();
    @endphp
    <section id="contact" class="section !pt-6" aria-labelledby="contact-title">
        <div class="container-site grid gap-12 lg:grid-cols-12">
            <div class="flex flex-col lg:col-span-5">
                <x-section-heading :label="__('site.contact.label')" :title="$site->text('contact_title')" :text="$site->text('contact_text')" id="contact-title" />

                @if ($socials || $email)
                    <div class="mt-8" data-reveal>
                        <p class="text-sm font-medium text-muted">{{ __('site.contact.info.follow') }}</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @if ($email)
                                <a href="mailto:{{ $email }}?subject={{ rawurlencode(__('site.contact.info.mail_subject')) }}" class="block rounded-[25%] shadow-[0_8px_18px_-10px_rgb(23_23_23/0.55)] ring-1 ring-black/5 transition duration-300 hover:-translate-y-1 hover:shadow-[0_14px_24px_-12px_rgb(23_23_23/0.6)]" aria-label="{{ __('site.contact.info.email') }}" title="{{ $email }}"><x-social-icon network="email" /></a>
                            @endif
                            @foreach ($socials as $network => $social)
                                <a href="{{ $social['url'] }}" class="block rounded-[25%] shadow-[0_8px_18px_-10px_rgb(23_23_23/0.55)] ring-1 ring-black/5 transition duration-300 hover:-translate-y-1 hover:shadow-[0_14px_24px_-12px_rgb(23_23_23/0.6)]" target="_blank" rel="noopener me" aria-label="{{ $social['label'] }}" title="{{ $social['label'] }}"><x-social-icon :network="$network" /></a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <ul class="mt-8 flex flex-1 flex-col gap-3" data-reveal>
                    @if ($phone)
                        <li><a href="tel:{{ preg_replace('/[^\d+]/', '', $phone) }}" class="surface lift flex items-center gap-4 !rounded-2xl p-4"><span class="icon-tile"><x-icon name="phone" /></span><span><span class="block text-xs text-muted">{{ __('site.contact.info.phone') }}</span><span class="font-semibold" dir="ltr">{{ $phone }}</span></span></a></li>
                    @endif
                    @if ($whatsapp)
                        <li><a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="surface lift flex items-center gap-4 !rounded-2xl p-4"><span class="icon-tile"><x-icon name="message-circle" /></span><span><span class="block text-xs text-muted">{{ __('site.contact.info.whatsapp') }}</span><span class="font-semibold" dir="ltr">{{ $site->get('whatsapp') }}</span></span></a></li>
                    @endif
                    @if ($address = $site->text('address'))
                        <li class="surface flex flex-1 flex-col overflow-hidden !rounded-2xl">
                            @if ($embed = $site->mapEmbedUrl())
                                <iframe
                                    src="{{ $embed }}"
                                    title="{{ __('site.contact.info.map_title', ['place' => $address]) }}"
                                    class="block min-h-64 w-full flex-1 border-0 lg:min-h-40"
                                    loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade"
                                    allowfullscreen
                                ></iframe>
                            @endif
                            <a @if ($map = $site->get('map_url')) href="{{ $map }}" target="_blank" rel="noopener" @endif class="flex items-center gap-4 p-4 transition-colors hover:bg-panel/60">
                                <span class="icon-tile"><x-icon name="map-pin" /></span>
                                <span>
                                    <span class="block text-xs text-muted">{{ __('site.contact.info.address') }}</span>
                                    <span class="font-semibold">{{ $address }}</span>
                                    @if ($map)
                                        <span class="mt-0.5 flex items-center gap-1 text-xs text-accent">{{ __('site.contact.info.map') }} <x-icon name="external-link" class="size-3" /></span>
                                    @endif
                                </span>
                            </a>
                        </li>
                    @endif
                </ul>

            </div>

            <div class="lg:col-span-7" data-reveal style="--delay: 150">
                <x-contact-form :services="$services" />
            </div>
        </div>
    </section>
</x-layouts.app>

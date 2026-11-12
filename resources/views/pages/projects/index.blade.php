<x-layouts.app :title="__('site.work.index_title')" :description="__('site.work.index_intro')">
    <section class="container-site pt-[calc(var(--header-h)+3.5rem)] pb-12 md:pt-[calc(var(--header-h)+5rem)]">
        <a href="{{ route('home') }}#work" class="link-arrow inline-flex items-center gap-2 text-sm text-muted transition-colors hover:text-ink">
            <x-arrow class="size-4 rotate-180" />
            {{ __('site.common.back_home') }}
        </a>

        <x-section-label class="mt-10" data-reveal>{{ __('site.work.label') }}</x-section-label>
        <h1 class="mt-5 font-display text-[clamp(2.75rem,7vw,6rem)] leading-[0.98] font-bold tracking-[-0.045em]">
            <span data-split>{{ __('site.work.index_title') }}</span><sup class="ms-2 align-top text-[0.25em] font-semibold tracking-normal text-accent" dir="ltr">({{ $projects->count() }})</sup>
        </h1>

        <div class="mt-10 flex flex-wrap items-end justify-between gap-8">
            <p class="max-w-xl text-lg text-muted" data-reveal>{{ __('site.work.index_intro') }}</p>

            @if ($categories->count() > 1)
                <nav aria-label="{{ __('site.work.filter') }}" data-filters data-reveal style="--delay: 150">
                    <ul class="flex flex-wrap gap-2">
                        <li>
                            <a href="{{ route('projects.index') }}" data-filter="" @class(['filter-pill', 'is-active' => ! $category]) aria-current="{{ $category ? 'false' : 'true' }}">
                                {{ __('site.work.all') }} <sup>{{ $projects->count() }}</sup>
                            </a>
                        </li>
                        @foreach ($categories as $item)
                            <li>
                                <a href="{{ route('projects.index', ['category' => $item->slug]) }}" data-filter="{{ $item->slug }}" @class(['filter-pill', 'is-active' => $category === $item->slug]) aria-current="{{ $category === $item->slug ? 'true' : 'false' }}">
                                    {{ $item->localized('name') }} <sup>{{ $item->projects_count }}</sup>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </nav>
            @endif
        </div>
    </section>

    <section class="container-site pb-24">
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3" data-filter-grid>
            @foreach ($projects as $project)
                <x-project-card
                    :project="$project"
                    :delay="($loop->index % 3) * 100"
                    data-filter-item="{{ $project->category?->slug }}"
                    :hidden="$category && $project->category?->slug !== $category"
                />
            @endforeach
        </div>

        <p class="py-24 text-center text-xl text-muted" data-filter-empty @if ($projects->contains(fn ($project) => ! $category || $project->category?->slug === $category)) hidden @endif>
            {{ __('site.work.empty') }}
        </p>
    </section>

    <x-cta-band :title="__('site.services.cta_title')" :text="__('site.services.cta_text')" :action="__('site.nav.cta')" />
</x-layouts.app>

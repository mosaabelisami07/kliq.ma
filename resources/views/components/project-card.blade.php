@props([
    'project',
    'delay' => 0,
])

@php
    $title = $project->localized('title');
    $services = $project->localizedLines('services_provided');
@endphp

<article {{ $attributes->class('project-card group lift relative flex flex-col overflow-hidden rounded-[1.5rem] border border-ink/15 p-2.5') }} data-reveal style="--delay: {{ $delay }}">
    <x-media
        :src="$project->thumbnailUrl()"
        :alt="$title"
        tone="ember"
        :seed="$project->artSeed()"
        :vt="'project-'.$project->slug"
        class="aspect-[4/3] rounded-[1.1rem]"
    >
        @if ($project->category)
            <span class="tag tag--glass absolute start-3 top-3">{{ $project->category->localized('name') }}</span>
        @endif
        @if ($project->video())
            <span class="absolute inset-0 grid place-items-center" aria-hidden="true">
                <span class="grid size-14 place-items-center rounded-full bg-white/90 text-black shadow-[0_10px_30px_-10px_rgb(0_0_0/0.6)] backdrop-blur transition duration-500 ease-out-expo group-hover:scale-110 group-hover:bg-orange">
                    <x-icon name="play" class="ms-0.5 size-5 fill-current" />
                </span>
            </span>
        @endif
        <span class="absolute end-3 bottom-3 grid size-10 place-items-center rounded-full bg-white text-black opacity-0 transition duration-500 ease-out-expo group-hover:opacity-100 group-focus-within:opacity-100 max-md:opacity-100">
            <x-arrow class="size-4 -rotate-45 rtl:rotate-45" />
        </span>
    </x-media>

    <div class="flex flex-1 flex-col px-2.5 pt-5 pb-3">
        @if ($project->client)
            <p class="text-sm text-muted" dir="auto">{{ $project->client }}</p>
        @endif
        <h3 class="mt-1 font-display text-xl font-semibold tracking-tight md:text-2xl">
            <a href="{{ route('projects.show', $project) }}" class="card-link outline-none" data-cursor="{{ __('site.common.view') }}" data-morph>{{ $title }}</a>
        </h3>
        @if ($summary = $project->localized('summary'))
            <p class="mt-2 line-clamp-2 text-muted">{{ $summary }}</p>
        @endif

        @if ($services)
            <ul class="mt-auto flex flex-wrap gap-1.5 pt-5" aria-label="{{ __('site.work.services') }}">
                @foreach (array_slice($services, 0, 3) as $service)
                    <li class="chip-tag">{{ $service }}</li>
                @endforeach
            </ul>
        @endif
    </div>
</article>

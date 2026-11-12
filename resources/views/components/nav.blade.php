@php
    $home = route('home');
    $onHome = request()->routeIs('home');
    $links = [
        'top' => 'home',
        'about' => 'about',
        'services' => 'services',
        'work' => 'work',
        'process' => 'process',
        'contact' => 'contact',
    ];
@endphp

<header class="site-header fixed inset-x-0 top-0 z-50" data-header>
    <div class="container-site flex h-[var(--header-h)] items-center justify-between gap-4">
        <a href="{{ $home }}#top" class="relative z-10 text-[1.6rem]" aria-label="KLIQ — {{ __('site.nav.home') }}">
            <x-site-logo />
        </a>

        <nav aria-label="{{ __('site.nav.main') }}" class="hidden lg:block">
            <ul class="flex items-center">
                @foreach ($links as $section => $label)
                    <li>
                        <a href="{{ $home }}#{{ $section }}" class="nav-link !px-3 xl:!px-4" @if ($onHome) data-spy="{{ $section }}" @endif>
                            {{ __("site.nav.{$label}") }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <div class="relative z-10 flex items-center gap-2">
            <x-locale-switcher class="hidden sm:flex" />
            <x-theme-toggle />
            <x-button :href="$home.'#contact'" variant="orange" :arrow="false" class="hidden !min-h-11 lg:inline-flex">
                {{ __('site.nav.cta') }}
            </x-button>
            <button
                type="button"
                class="menu-toggle grid size-11 place-items-center rounded-full bg-ink text-paper lg:hidden"
                data-menu-toggle
                data-label-open="{{ __('site.nav.menu') }}"
                data-label-close="{{ __('site.nav.close') }}"
                aria-label="{{ __('site.nav.menu') }}"
                aria-expanded="false"
                aria-controls="mobile-menu"
            >
                <span class="block">
                    <span></span>
                    <span></span>
                </span>
            </button>
        </div>
    </div>
</header>

<div id="mobile-menu" class="mobile-menu fixed inset-0 z-40 flex flex-col overflow-y-auto bg-[#171717] px-5 pt-[calc(var(--header-h)+2rem)] pb-8 text-white lg:hidden">
    <div class="pointer-events-none absolute inset-0 opacity-60">
        <x-art tone="ink" :seed="88" :streaks="4" class="absolute inset-0" />
    </div>

    <nav aria-label="{{ __('site.nav.main') }}" class="relative">
        <ul class="flex flex-col gap-1">
            @foreach ($links as $section => $label)
                <li class="overflow-hidden">
                    <a
                        href="{{ $home }}#{{ $section }}"
                        class="mobile-menu__link font-display text-[clamp(2.5rem,11vw,4.5rem)] leading-[1.08] font-bold tracking-tight transition-colors hover:text-orange"
                        style="--i: {{ $loop->index }}"
                    >
                        {{ __("site.nav.{$label}") }}
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    <div class="relative mt-auto flex flex-col gap-6 pt-12">
        <x-button :href="$home.'#contact'" variant="orange" class="self-start">{{ __('site.nav.cta') }}</x-button>
        <div class="flex flex-wrap items-end justify-between gap-6">
            <div class="flex flex-col gap-1 text-sm text-white/70">
                @if ($email = $site->get('contact_email'))
                    <a href="mailto:{{ $email }}?subject={{ rawurlencode(__('site.contact.info.mail_subject')) }}" class="text-lg text-white">{{ $email }}</a>
                @endif
                <span>{{ $site->text('address') }}</span>
            </div>
            <x-locale-switcher class="flex border-white/20 [&_.is-active]:!bg-white [&_.is-active]:!text-black [&_a]:text-white/60" />
        </div>
    </div>
</div>

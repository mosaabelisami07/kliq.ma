@props([
    'title' => null,
    'description' => null,
    'image' => null,
])

@php
    $locale = app()->getLocale();
    $locales = config('kliq.locales');
    $route = request()->route();
    $siteTitle = $site->text('seo_title');
    $pageTitle = $title ? "{$title} — KLIQ" : $siteTitle;
    $pageDescription = $description ?: $site->text('seo_description');
    $ogImage = $image ?? $site->imageUrl('og_image') ?? asset('og-image.png');
    $favicon = $site->imageUrl('favicon');
    $organization = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => 'KLIQ',
        'url' => url('/'),
        'logo' => $site->imageUrl('logo') ?? asset('web-app-manifest-512x512.png'),
        'email' => $site->get('contact_email'),
        'telephone' => $site->get('contact_phone'),
        'sameAs' => array_values($site->socials()) ?: null,
    ]);
@endphp

<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $locales[$locale]['dir'] }}" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    <meta name="theme-color" content="#0A0A0A">
    <link rel="canonical" href="{{ url()->current() }}">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="KLIQ">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:locale" content="{{ $locale }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    @if ($favicon)
        <link rel="icon" href="{{ $favicon }}">
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="48x48">
        <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
        <link rel="icon" href="{{ asset('favicon-96x96.png') }}" type="image/png" sizes="96x96">
    @endif
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}" sizes="180x180">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <meta name="apple-mobile-web-app-title" content="KLIQ">
    <meta name="application-name" content="KLIQ">

    @if ($route?->getName())
        @foreach ($locales as $code => $meta)
            <link rel="alternate" hreflang="{{ $code }}" href="{{ route($route->getName(), [...$route->parameters(), 'locale' => $code]) }}">
        @endforeach
    @endif

    <script type="application/ld+json">@json($organization, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)</script>

    <script>
        (function () {
            var root = document.documentElement;
            var theme = null;
            root.classList.add('js');
            try { theme = localStorage.getItem('kliq-theme'); } catch (e) {}
            if (theme !== 'light' && theme !== 'dark') {
                theme = 'dark';
            }
            root.dataset.theme = theme;
            try {
                if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches && !sessionStorage.getItem('kliq-intro')) {
                    root.classList.add('is-loading');
                }
            } catch (e) {}
            setTimeout(function () {
                if (!window.__kliq) { root.classList.add('no-motion'); root.classList.remove('is-loading'); }
            }, 3500);
        })();
    </script>

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-paper font-sans text-ink antialiased" id="top">
    <a href="#main" class="sr-only z-[300] rounded-full bg-orange px-5 py-3 font-semibold text-black focus:not-sr-only focus:fixed focus:start-4 focus:top-4">
        {{ __('site.nav.skip') }}
    </a>

    <x-loader />
    <x-cursor />
    <x-nav />

    <main id="main">
        {{ $slot }}
    </main>

    <x-footer />
</body>
</html>

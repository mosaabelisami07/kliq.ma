@php
    $route = request()->route();
    $query = request()->getQueryString();

    $urlFor = function (string $code) use ($route, $query): string {
        $url = $route?->getName()
            ? route($route->getName(), [...$route->parameters(), 'locale' => $code])
            : url($code);

        return $query ? "{$url}?{$query}" : $url;
    };
@endphp

<div {{ $attributes->class('locale-switch items-center gap-0.5 rounded-full border border-line p-1 text-xs font-semibold') }} role="group" aria-label="{{ __('site.nav.language') }}">
    @foreach (config('kliq.locales') as $code => $meta)
        <a
            href="{{ $urlFor($code) }}"
            hreflang="{{ $code }}"
            lang="{{ $code }}"
            title="{{ $meta['name'] }}"
            @class(['is-active' => $code === app()->getLocale()])
            @if ($code === app()->getLocale()) aria-current="true" @endif
        >
            <span aria-hidden="true">{{ $meta['short'] }}</span>
            <span class="sr-only">{{ $meta['name'] }}</span>
        </a>
    @endforeach
</div>

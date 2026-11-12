{{-- Intro shown once per session; hidden unless the head script adds .is-loading. --}}
<div class="loader fixed inset-0 z-[250] place-items-center bg-black text-white" aria-hidden="true">
    <div class="flex flex-col items-center">
        <div class="loader__word text-[clamp(5rem,22vw,16rem)] leading-none" dir="ltr">
            <x-logo />
        </div>
    </div>

    <div class="absolute inset-x-4 bottom-6 flex items-end justify-between gap-6 text-xs tracking-[0.16em] text-white/60 uppercase md:inset-x-8">
        <span>{{ __('site.loader.tagline') }}</span>
        <span class="font-brand text-4xl font-bold tracking-tight text-white tabular-nums" data-loader-count dir="ltr">000</span>
    </div>

    <div class="loader__bar absolute inset-x-0 bottom-0 h-1"><span></span></div>
</div>

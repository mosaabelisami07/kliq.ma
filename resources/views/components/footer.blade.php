@php
    $home = route('home');
    $links = ['top' => 'home', 'about' => 'about', 'services' => 'services', 'work' => 'work', 'process' => 'process', 'contact' => 'contact'];
    $footerServices = \App\Models\Service::query()->active()->get(['id', 'slug', 'title']);
    $socials = $site->socialLinks();
    $email = $site->get('contact_email');
    $phone = $site->get('contact_phone');
    $whatsapp = $site->whatsappUrl();
@endphp

<footer class="relative mx-2 mt-10 overflow-hidden rounded-t-[2.5rem] bg-[#171717] text-white md:mx-3">
    <div class="pointer-events-none absolute -top-1/3 end-[-10%] hidden h-[90%] w-[70%] opacity-50 [mask-image:radial-gradient(closest-side,#000_40%,transparent)] md:block">
        <x-art tone="ink" :seed="404" :streaks="3" class="absolute inset-0 !bg-transparent" />
    </div>

    <div class="container-site relative pt-12 md:pt-20">
        <div class="flex flex-col gap-8 border-b border-white/15 pb-10 md:flex-row md:items-end md:justify-between md:gap-10 md:pb-12">
            <div class="max-w-md">
                <a href="{{ $home }}#top" class="inline-block text-[2.1rem] md:text-[2.4rem]" aria-label="KLIQ — {{ __('site.nav.home') }}">
                    <x-site-logo />
                </a>
                <p class="mt-4 text-white/70 md:mt-5">{{ $site->text('footer_text') }}</p>
            </div>
            <x-button :href="$home.'#contact'" variant="orange" class="self-start md:shrink-0 md:self-auto">{{ __('site.nav.cta') }}</x-button>
        </div>

        {{-- Mobile: Navigate | Contact, then Services. Desktop: Navigate · Services · Contact. --}}
        <div class="grid grid-cols-2 gap-x-6 gap-y-10 py-10 md:py-14 lg:grid-cols-12 lg:gap-x-10">
            <div class="lg:col-span-2">
                <p class="text-xs font-semibold tracking-[0.16em] text-white/50 uppercase">{{ __('site.footer.nav') }}</p>
                <ul class="mt-4 flex flex-col gap-2.5">
                    @foreach ($links as $section => $label)
                        <li><a href="{{ $home }}#{{ $section }}" class="link-underline text-white/85 hover:text-white">{{ __("site.nav.{$label}") }}</a></li>
                    @endforeach
                </ul>
            </div>

            <div class="min-w-0 lg:order-3 lg:col-span-4">
                <p class="text-xs font-semibold tracking-[0.16em] text-white/50 uppercase">{{ __('site.footer.contact') }}</p>
                <ul class="mt-4 flex flex-col gap-2.5 break-words text-white/85">
                    @if ($email)
                        <li><a href="mailto:{{ $email }}?subject={{ rawurlencode(__('site.contact.info.mail_subject')) }}" class="link-underline hover:text-white">{{ $email }}</a></li>
                    @endif
                    @if ($phone)
                        <li><a href="tel:{{ preg_replace('/[^\d+]/', '', $phone) }}" class="link-underline hover:text-white" dir="ltr">{{ $phone }}</a></li>
                    @endif
                    @if ($whatsapp)
                        <li><a href="{{ $whatsapp }}" class="link-underline hover:text-white" target="_blank" rel="noopener">WhatsApp</a></li>
                    @endif
                    <li class="text-white/60">{{ $site->text('address') }}</li>
                    @if ($map = $site->get('map_url'))
                        <li><a href="{{ $map }}" class="link-underline hover:text-white" target="_blank" rel="noopener">{{ __('site.contact.info.map') }}</a></li>
                    @endif
                </ul>

                @if ($socials || $email)
                    <p class="mt-8 text-xs font-semibold tracking-[0.16em] text-white/50 uppercase">{{ __('site.footer.social') }}</p>
                    <ul class="mt-3 flex flex-wrap gap-2">
                        @if ($email)
                            <li><a href="mailto:{{ $email }}?subject={{ rawurlencode(__('site.contact.info.mail_subject')) }}" class="block rounded-[25%] shadow-[0_8px_18px_-10px_rgb(23_23_23/0.55)] ring-1 ring-black/5 transition duration-300 hover:-translate-y-1 hover:shadow-[0_14px_24px_-12px_rgb(23_23_23/0.6)]" aria-label="{{ __('site.contact.info.email') }}" title="{{ $email }}"><x-social-icon network="email" class="size-10" /></a></li>
                        @endif
                        @foreach ($socials as $network => $social)
                            <li><a href="{{ $social['url'] }}" class="block rounded-[25%] shadow-[0_8px_18px_-10px_rgb(23_23_23/0.55)] ring-1 ring-black/5 transition duration-300 hover:-translate-y-1 hover:shadow-[0_14px_24px_-12px_rgb(23_23_23/0.6)]" target="_blank" rel="noopener me" aria-label="{{ $social['label'] }}" title="{{ $social['label'] }}"><x-social-icon :network="$network" class="size-10" /></a></li>
                        @endforeach
                    </ul>
                @endif
            </div>

            @if ($footerServices->isNotEmpty())
                <div class="col-span-2 lg:order-2 lg:col-span-6">
                    <p class="text-xs font-semibold tracking-[0.16em] text-white/50 uppercase">{{ __('site.footer.services') }}</p>
                    <ul class="mt-4 grid grid-cols-2 gap-x-6 gap-y-2.5 text-[0.95rem] md:text-base">
                        @foreach ($footerServices as $service)
                            <li><a href="{{ route('services.show', $service) }}" class="link-underline text-white/85 hover:text-white">{{ $service->localized('title') }}</a></li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <div class="flex flex-col-reverse items-start gap-3 border-t border-white/15 py-6 text-sm text-white/55 sm:flex-row sm:items-center sm:justify-between sm:py-8">
            <p>© {{ now()->year }} KLIQ. {{ __('site.footer.rights') }}</p>
            <a href="#top" class="link-underline text-white">{{ __('site.footer.top') }} ↑</a>
        </div>
    </div>
</footer>

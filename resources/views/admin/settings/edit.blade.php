@php
    $socials = (array) $settings->get('socials', []);
@endphp

<x-layouts.admin title="Settings">
    <x-admin.page-header title="Settings" description="Contact details, social links, branding and SEO. Empty contact fields are simply hidden on the website." />

    @if ($errors->any())
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800" role="alert">Please fix the highlighted fields below.</div>
    @endif

    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="grid gap-6 lg:grid-cols-2">
        @csrf
        @method('PUT')

        <section class="a-card flex flex-col gap-5 p-6" aria-labelledby="contact-title">
            <h2 id="contact-title" class="font-display text-lg font-bold">Contact information</h2>
            <x-admin.input name="contact_email" type="email" label="Public email" :value="$settings->get('contact_email')" placeholder="hello@example.com" />
            <x-admin.input name="contact_phone" type="tel" label="Phone number" :value="$settings->get('contact_phone')" placeholder="+212 …" />
            <x-admin.input name="whatsapp" type="tel" label="WhatsApp number" :value="$settings->get('whatsapp')" hint="International format, e.g. +212 6…" />
            <x-admin.translatable name="address" label="Address / location" :value="$settings->translations('address')" />
            <x-admin.input name="map_url" type="url" label="Google Maps link" :value="$settings->get('map_url')" placeholder="https://maps.app.goo.gl/…" />
            <x-admin.input name="map_location" label="Map location" :value="$settings->get('map_location')" placeholder="33.5274,-7.6500 or a full address" hint="Shown as the map on the contact section. Use coordinates (from the Google Maps link) or an address." />
        </section>

        <section class="a-card flex flex-col gap-5 p-6" aria-labelledby="social-title">
            <h2 id="social-title" class="font-display text-lg font-bold">Social media</h2>
            <p class="a-hint !mt-0">Only filled-in profiles are shown on the website.</p>
            @foreach (config('kliq.settings.socials') as $network => $label)
                <x-admin.input :name="'socials['.$network.']'" type="url" :label="$label" :value="old('socials.'.$network, $socials[$network] ?? null)" placeholder="https://" />
                @error('socials.'.$network) <p class="a-error -mt-4">{{ $message }}</p> @enderror
            @endforeach
        </section>

        <section class="a-card flex flex-col gap-5 p-6" aria-labelledby="notify-title">
            <h2 id="notify-title" class="font-display text-lg font-bold">Inquiry notifications</h2>
            <x-admin.switch name="notify_on_inquiry" label="Email me when a new inquiry arrives" :checked="(bool) $settings->get('notify_on_inquiry')" />
            <x-admin.input name="notification_email" type="email" label="Notification email" :value="$settings->get('notification_email')" :hint="'Where new-inquiry emails are sent'.(config('kliq.notification_email') ? ' (default: '.config('kliq.notification_email').').' : '.').' Mail delivery uses the MAIL_* settings in .env.'" />
        </section>

        <section class="a-card flex flex-col gap-5 p-6" aria-labelledby="seo-title">
            <h2 id="seo-title" class="font-display text-lg font-bold">SEO</h2>
            <x-admin.translatable name="seo_title" label="Site title" :value="$settings->translations('seo_title')" maxlength="70" hint="Shown in browser tabs and search results (up to 70 characters)." />
            <x-admin.translatable name="seo_description" label="Meta description" :value="$settings->translations('seo_description')" :textarea="true" :rows="3" maxlength="170" hint="Up to 170 characters." />
        </section>

        <section class="a-card flex flex-col gap-6 p-6 lg:col-span-2" aria-labelledby="brand-title">
            <h2 id="brand-title" class="font-display text-lg font-bold">Branding</h2>
            <div class="grid gap-6 md:grid-cols-3">
                <x-admin.image-input name="logo" label="Logo" :current="$settings->imageUrl('logo')" fit="object-contain p-3 bg-white" hint="PNG or WebP with a transparent background, up to 2 MB. Leave empty to use the KLIQ wordmark." />
                <x-admin.image-input name="favicon" label="Favicon" :current="$settings->imageUrl('favicon')" accept="image/png,image/x-icon,.ico" hint="Square PNG or ICO, up to 512 KB." />
                <x-admin.image-input name="og_image" label="Social sharing image" :current="$settings->imageUrl('og_image')" hint="1200 × 630 px JPG or PNG, up to 4 MB." />
            </div>
        </section>

        <div class="flex justify-end lg:col-span-2">
            <button type="submit" class="a-btn a-btn--primary min-w-40" data-busy-label="Saving…">Save settings</button>
        </div>
    </form>
</x-layouts.admin>

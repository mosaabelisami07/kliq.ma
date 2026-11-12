@props(['services'])

{{--
    Works without JavaScript (regular POST + redirect). With JavaScript it is
    sent with fetch(): the button shows a loading state, errors appear next to
    their fields and a success panel replaces the form.
--}}
@php
    $sent = session('inquiry_sent');
    $serviceOptions = $services->mapWithKeys(fn ($service) => [$service->id => $service->localized('title')])
        ->put('other', __('site.contact.form.service_other'))
        ->all();
    $budgetOptions = collect(config('kliq.budgets'))->mapWithKeys(fn (string $budget) => [$budget => __("site.contact.budgets.{$budget}")])->all();
@endphp

<div class="surface relative h-full overflow-hidden p-6 md:p-10" data-contact>
    <div class="flex min-h-[28rem] flex-col items-start justify-center gap-5 outline-none" role="status" tabindex="-1" data-contact-success @unless ($sent) hidden @endunless>
        <span class="grid size-16 place-items-center rounded-full bg-orange text-black">
            <x-icon name="check" class="size-8" />
        </span>
        <h3 class="font-display text-3xl font-bold tracking-tight md:text-4xl" data-contact-success-title>{{ __('site.contact.success_title') }}</h3>
        <p class="max-w-md text-lg text-muted" data-contact-success-text>{{ __('site.contact.success') }}</p>
        <a href="{{ route('home') }}#contact" class="btn btn--outline mt-2" data-contact-reset>{{ __('site.contact.send_another') }}</a>
    </div>

    <form
        method="POST"
        action="{{ route('contact.store') }}"
        class="flex flex-col gap-6"
        novalidate
        data-contact-form
        data-throttled="{{ __('site.contact.errors.throttled') }}"
        data-failed="{{ __('site.contact.errors.generic') }}"
        @if ($sent) hidden @endif
    >
        @csrf
        <input type="hidden" name="started_at" value="{{ Crypt::encryptString((string) now()->timestamp) }}">
        <input type="hidden" name="form_token" value="{{ Str::uuid() }}" data-contact-token>

        <h3 class="font-display text-2xl font-semibold tracking-tight">{{ __('site.contact.form_title') }}</h3>

        <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900 dark:bg-red-950 dark:text-red-200" data-contact-alert data-summary="{{ __('site.contact.errors.summary') }}" tabindex="-1" @unless ($errors->any()) hidden @endunless>
            {{ $errors->first('form') ?: __('site.contact.errors.summary') }}
        </div>

        <div class="grid gap-6 sm:grid-cols-2">
            <x-field name="name" :label="__('site.contact.form.name')" autocomplete="name" maxlength="120" required />
            <x-field name="company" :label="__('site.contact.form.company')" autocomplete="organization" maxlength="120" :optional="true" />
            <x-field name="email" type="email" :label="__('site.contact.form.email')" autocomplete="email" maxlength="190" required />
            <x-field name="phone" type="tel" :label="__('site.contact.form.phone')" autocomplete="tel" maxlength="40" :optional="true" dir="ltr" />
            <x-field name="service" :label="__('site.contact.form.service')" :options="$serviceOptions" :placeholder="__('site.contact.form.service_placeholder')" :optional="true" />
            <!-- <x-field name="budget" :label="__('site.contact.form.budget')" :options="$budgetOptions" :placeholder="__('site.contact.form.budget_placeholder')" :optional="true" /> -->
        </div>

        <x-field name="message" :textarea="true" :label="__('site.contact.form.message')" :hint="__('site.contact.form.message_hint')" minlength="20" maxlength="5000" required />

        {{-- Honeypot: hidden from people, tempting for bots. --}}
        <div class="absolute -start-[9999px] h-px w-px overflow-hidden" aria-hidden="true">
            <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
        </div>

        <button type="submit" class="btn btn--orange self-start" data-sending="{{ __('site.contact.form.sending') }}">
            <span class="spinner" aria-hidden="true"></span>
            <span data-label>{{ __('site.contact.form.submit') }}</span>
            <x-arrow class="btn__arrow size-4" />
        </button>
    </form>
</div>

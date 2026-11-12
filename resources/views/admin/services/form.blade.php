@php
    $editing = $service->exists;
    $selectedIcon = old('icon', $service->icon);
@endphp

<x-layouts.admin :title="$editing ? 'Edit service' : 'New service'">
    <x-admin.page-header :title="$editing ? $service->localized('title', 'en') : 'New service'" :back="route('admin.services.index')">
        @if ($editing && $service->is_active)
            <a href="{{ route('services.show', ['locale' => config('app.locale'), 'service' => $service]) }}" target="_blank" class="a-btn a-btn--ghost"><x-icon name="external-link" class="size-4" /> View on website</a>
        @endif
    </x-admin.page-header>

    @if ($errors->any())
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800" role="alert">Please fix the highlighted fields below.</div>
    @endif

    <form method="POST" action="{{ $editing ? route('admin.services.update', $service) : route('admin.services.store') }}" enctype="multipart/form-data" class="grid gap-6 lg:grid-cols-3">
        @csrf
        @if ($editing)
            @method('PUT')
        @endif

        <div class="flex flex-col gap-6 lg:col-span-2">
            <section class="a-card flex flex-col gap-5 p-6">
                <h2 class="font-semibold">Service information</h2>
                <x-admin.translatable name="title" label="Title" :value="$service->title ?? []" :required="true" maxlength="120" />
                <x-admin.translatable name="summary" label="Short description" :value="$service->summary ?? []" :textarea="true" :rows="2" maxlength="300" hint="Shown on the service card." />
                <x-admin.translatable name="body" label="Details" :value="$service->body ?? []" :textarea="true" :rows="6" hint="Shown on the service page. Leave a blank line between paragraphs." />
                <x-admin.translatable name="deliverables" label="What's included" :value="$service->deliverables ?? []" :textarea="true" :rows="4" hint="One item per line." />
            </section>

            <section class="a-card p-6">
                <fieldset>
                    <legend class="a-label">Icon</legend>
                    <div class="mt-3 grid grid-cols-6 gap-2 sm:grid-cols-10">
                        @foreach ($icons as $icon)
                            <label class="cursor-pointer" title="{{ $icon }}">
                                <input type="radio" name="icon" value="{{ $icon }}" class="peer sr-only" @checked($selectedIcon === $icon)>
                                <span class="grid aspect-square place-items-center rounded-xl ring-1 ring-line transition peer-checked:bg-orange peer-checked:text-black peer-checked:ring-orange peer-focus-visible:outline-2 peer-focus-visible:outline-orange hover:bg-cream">
                                    <x-icon :name="$icon" class="size-5" />
                                    <span class="sr-only">{{ $icon }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('icon') <p class="a-error">{{ $message }}</p> @enderror
                </fieldset>
            </section>
        </div>

        <div class="flex flex-col gap-6">
            <section class="a-card flex flex-col gap-5 p-6">
                <h2 class="font-semibold">Visibility</h2>
                <x-admin.switch name="is_active" label="Active" :checked="$service->is_active" hint="Inactive services are hidden from the website and the contact form." />
                <button type="submit" class="a-btn a-btn--primary w-full" data-busy-label="Saving…">{{ $editing ? 'Save service' : 'Create service' }}</button>
            </section>

            <section class="a-card flex flex-col gap-5 p-6">
                <x-admin.image-input name="image" label="Image (optional)" :current="$service->imageUrl()" hint="Shown above the card and on the service page. JPG, PNG or WebP up to 5 MB." />
                <x-admin.input name="slug" label="URL slug" :value="$service->slug" hint="Generated from the English title when empty." />
            </section>
        </div>
    </form>
</x-layouts.admin>

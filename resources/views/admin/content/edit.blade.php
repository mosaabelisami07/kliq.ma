@php
    $labels = [
        'hero_eyebrow' => ['Small label above the heading', false, 1],
        'hero_title' => ['Heading', false, 2, 'Wrap words in *asterisks* to show them in orange.'],
        'hero_text' => ['Supporting text', true, 3],
        'brands_title' => ['Heading', false, 1],
        'about_title' => ['Heading', false, 1],
        'about_text' => ['About the agency', true, 4],
        'about_mission' => ['Our mission', true, 3],
        'about_approach' => ['Our approach', true, 3],
        'services_title' => ['Heading', false, 1],
        'services_text' => ['Introduction', true, 2],
        'work_title' => ['Heading', false, 1],
        'work_text' => ['Introduction', true, 2],
        'process_title' => ['Heading', false, 1],
        'process_text' => ['Introduction', true, 2],
        'why_title' => ['Heading', false, 1],
        'why_text' => ['Text', true, 3],
        'contact_title' => ['Heading', false, 1],
        'contact_text' => ['Introduction', true, 3],
        'footer_text' => ['Short agency description', true, 2],
    ];
    $listLabels = ['values' => 'Brand values', 'process_steps' => 'Process steps', 'why_items' => 'Reasons to choose KLIQ'];
@endphp

<x-layouts.admin title="Website content">
    <x-admin.page-header title="Website content" description="Edit the text of each home page section. Services and projects are managed on their own pages.">
        <a href="{{ route('home', ['locale' => config('app.locale')]) }}" target="_blank" class="a-btn a-btn--ghost"><x-icon name="external-link" class="size-4" /> View home page</a>
    </x-admin.page-header>

    @if ($errors->any())
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800" role="alert">Please fix the highlighted fields below.</div>
    @endif

    <form method="POST" action="{{ route('admin.content.update') }}" class="grid gap-6 lg:grid-cols-[13rem_1fr]">
        @csrf
        @method('PUT')

        <nav class="hidden lg:block" aria-label="Sections">
            <ul class="sticky top-8 flex flex-col gap-1 text-sm">
                @foreach (array_keys($sections) as $section)
                    <li><a href="#section-{{ Str::slug($section) }}" class="block rounded-lg px-3 py-2 text-muted hover:bg-white hover:text-ink">{{ $section }}</a></li>
                @endforeach
                <li class="mt-4"><button type="submit" class="a-btn a-btn--primary w-full" data-busy-label="Saving…">Save all changes</button></li>
            </ul>
        </nav>

        <div class="flex flex-col gap-6">
            @foreach ($sections as $section => $keys)
                <section id="section-{{ Str::slug($section) }}" class="a-card flex scroll-mt-8 flex-col gap-5 p-6" aria-labelledby="heading-{{ Str::slug($section) }}">
                    <h2 id="heading-{{ Str::slug($section) }}" class="font-display text-lg font-bold">{{ $section }}</h2>

                    @foreach ($keys as $key)
                        @php([$label, $textarea, $rows] = $labels[$key])
                        <x-admin.translatable
                            :name="$key"
                            :label="$label"
                            :value="$settings->translations($key)"
                            :textarea="$textarea || $rows > 1"
                            :rows="$rows"
                            :required="$key === 'hero_title'"
                            :hint="$labels[$key][3] ?? null"
                        />
                    @endforeach

                    @foreach (array_keys($lists, $section, true) as $list)
                        <fieldset class="rounded-2xl bg-cream/60 p-4 ring-1 ring-line">
                            <legend class="px-1 text-sm font-semibold">{{ $listLabels[$list] }}</legend>
                            <p class="a-hint !mt-0 mb-4">Rows without an English or translated title are hidden on the website.</p>
                            <div class="flex flex-col gap-4">
                                @foreach ($settings->itemTranslations($list) as $index => $item)
                                    <div class="grid gap-4 rounded-xl bg-white p-4 ring-1 ring-line md:grid-cols-[9rem_1fr]">
                                        <div>
                                            <label for="{{ $list }}-{{ $index }}-icon" class="a-label">#{{ $index + 1 }} icon</label>
                                            <div class="flex items-center gap-2">
                                                <span class="mt-1.5 grid size-10 shrink-0 place-items-center rounded-lg bg-orange/15 text-orange-deep"><x-icon :name="old($list.'.'.$index.'.icon', $item['icon'])" class="size-5" /></span>
                                                <select id="{{ $list }}-{{ $index }}-icon" name="{{ $list }}[{{ $index }}][icon]" class="a-input">
                                                    @foreach ($icons as $icon)
                                                        <option value="{{ $icon }}" @selected(old("{$list}.{$index}.icon", $item['icon']) === $icon)>{{ $icon }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="flex flex-col gap-3">
                                            <x-admin.translatable :name="$list.'['.$index.'][title]'" label="Title" :value="$item['title']" maxlength="120" />
                                            <x-admin.translatable :name="$list.'['.$index.'][text]'" label="Text" :value="$item['text']" :textarea="true" :rows="2" maxlength="500" />
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </fieldset>
                    @endforeach
                </section>
            @endforeach

            <div class="sticky bottom-4 z-10 flex justify-end lg:hidden">
                <button type="submit" class="a-btn a-btn--primary shadow-xl" data-busy-label="Saving…">Save all changes</button>
            </div>
        </div>
    </form>
</x-layouts.admin>

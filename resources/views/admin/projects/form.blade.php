@php
    $editing = $project->exists;
@endphp

<x-layouts.admin :title="$editing ? 'Edit project' : 'New project'">
    <x-admin.page-header :title="$editing ? $project->localized('title', 'en') : 'New project'" :back="route('admin.projects.index')">
        @if ($editing && $project->is_published)
            <a href="{{ route('projects.show', ['locale' => config('app.locale'), 'project' => $project]) }}" target="_blank" class="a-btn a-btn--ghost"><x-icon name="external-link" class="size-4" /> View on website</a>
        @endif
    </x-admin.page-header>

    @if ($errors->any())
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800" role="alert">Please fix the highlighted fields below.</div>
    @endif

    <form method="POST" action="{{ $editing ? route('admin.projects.update', $project) : route('admin.projects.store') }}" enctype="multipart/form-data" class="grid gap-6 lg:grid-cols-3">
        @csrf
        @if ($editing)
            @method('PUT')
        @endif

        <div class="flex flex-col gap-6 lg:col-span-2">
            <section class="a-card flex flex-col gap-5 p-6">
                <h2 class="font-semibold">Project information</h2>
                <x-admin.translatable name="title" label="Title" :value="$project->title ?? []" :required="true" maxlength="150" />
                <x-admin.input name="client" label="Client or brand" :value="$project->client" maxlength="150" hint="Leave empty if the client should not be named." />
                <x-admin.translatable name="summary" label="Short description" :value="$project->summary ?? []" :textarea="true" :rows="2" maxlength="300" hint="Shown on the project card." />
                <x-admin.translatable name="body" label="Project details" :value="$project->body ?? []" :textarea="true" :rows="8" hint="Shown on the project page. Leave a blank line between paragraphs." />
                <x-admin.translatable name="services_provided" label="Services provided" :value="$project->services_provided ?? []" :textarea="true" :rows="3" hint="One service per line." />
            </section>

            <section class="a-card flex flex-col gap-5 p-6">
                <h2 class="font-semibold">Images</h2>
                <x-admin.image-input name="thumbnail" label="Thumbnail" :current="$project->thumbnailUrl()" hint="Used on cards and as the main image. JPG, PNG or WebP up to 5 MB; a 4:3 landscape image works best." />

                <div>
                    <label for="images" class="a-label">Add gallery images</label>
                    <input type="file" id="images" name="images[]" multiple accept="image/jpeg,image/png,image/webp" class="mt-2 block w-full text-sm file:me-3 file:cursor-pointer file:rounded-full file:border-0 file:bg-ink file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-orange hover:file:text-black">
                    <p class="a-hint">Up to 12 images at a time, 5 MB each.</p>
                    @foreach ($errors->get('images*') as $messages)
                        <p class="a-error">{{ $messages[0] }}</p>
                    @endforeach
                </div>

                @if ($editing && $project->images->isNotEmpty())
                    <ul class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                        @foreach ($project->images as $image)
                            <li class="group relative overflow-hidden rounded-xl ring-1 ring-line">
                                <img src="{{ $image->url() }}" alt="" class="aspect-[4/3] w-full object-cover" loading="lazy">
                                <button
                                    type="submit"
                                    form="delete-image-{{ $image->id }}"
                                    class="a-btn a-btn--danger a-btn--icon absolute end-2 top-2 !size-8 opacity-90"
                                    title="Remove image"
                                ><x-icon name="trash" class="size-3.5" /><span class="sr-only">Remove image</span></button>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>

        <div class="flex flex-col gap-6">
            <section class="a-card flex flex-col gap-5 p-6">
                <h2 class="font-semibold">Publishing</h2>
                <x-admin.switch name="is_published" label="Published" :checked="$project->is_published" hint="Draft projects are hidden from the website." />
                <x-admin.switch name="is_featured" label="Featured" :checked="$project->is_featured" />
                <button type="submit" class="a-btn a-btn--primary w-full" data-busy-label="Saving…">{{ $editing ? 'Save project' : 'Create project' }}</button>
            </section>

            <section class="a-card flex flex-col gap-5 p-6">
                <h2 class="font-semibold">Details</h2>
                <div>
                    <label for="category_id" class="a-label">Category</label>
                    <select id="category_id" name="category_id" class="a-input">
                        <option value="">No category</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $project->category_id) == $category->id)>{{ $category->localized('name', 'en') }}</option>
                        @endforeach
                    </select>
                    <p class="a-hint"><a href="{{ route('admin.categories.index') }}" class="underline">Manage categories</a></p>
                    @error('category_id') <p class="a-error">{{ $message }}</p> @enderror
                </div>
                <x-admin.input name="year" type="number" label="Year" :value="$project->year" min="1990" :max="now()->year + 1" />
                <x-admin.input name="external_url" type="url" label="External link" :value="$project->external_url" placeholder="https://" hint="Optional link to the live project or case study." />
                <x-admin.input name="video_url" type="url" label="Video link" :value="$project->video_url" placeholder="https://youtu.be/…" hint="Optional YouTube or Instagram reel link. It plays on the project page; the cover image is still used on cards." />
                <x-admin.input name="slug" label="URL slug" :value="$project->slug" hint="Generated from the English title when empty." />
            </section>
        </div>
    </form>

    @if ($editing)
        @foreach ($project->images as $image)
            <form id="delete-image-{{ $image->id }}" method="POST" action="{{ route('admin.projects.images.destroy', [$project, $image]) }}" data-confirm="This image will be removed from the project gallery." hidden>
                @csrf
                @method('DELETE')
            </form>
        @endforeach
    @endif
</x-layouts.admin>

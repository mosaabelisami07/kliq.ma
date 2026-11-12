<x-layouts.admin title="Categories">
    <x-admin.page-header title="Project categories" description="Used to group portfolio projects and as filters on the website. Filters only show categories with published projects." :back="route('admin.projects.index')" />

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="flex flex-col gap-4 lg:col-span-2">
            @forelse ($categories as $category)
                <details class="a-card group" @if ($errors->any() && old('category_id') == $category->id) open @endif>
                    <summary class="flex cursor-pointer list-none items-center gap-3 p-5">
                        <span class="grid size-9 place-items-center rounded-xl bg-orange/15 text-orange-deep"><x-icon name="tag" class="size-4" /></span>
                        <span class="flex-1">
                            <span class="block font-semibold">{{ $category->localized('name', 'en') }}</span>
                            <span class="block text-xs text-muted">{{ $category->projects_count }} {{ Str::plural('project', $category->projects_count) }} · /{{ $category->slug }}</span>
                        </span>
                        <span class="a-btn a-btn--ghost a-btn--sm"><x-icon name="pencil" class="size-3.5" /> Edit</span>
                    </summary>

                    <div class="border-t border-line p-5">
                        <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="flex flex-col gap-4">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="category_id" value="{{ $category->id }}">
                            <x-admin.translatable name="name" label="Name" :value="$category->name" :required="true" maxlength="60" :prefix="'cat'.$category->id" />
                            <x-admin.input name="slug" label="Slug" :value="$category->slug" :prefix="'cat'.$category->id" />
                            <div class="flex flex-wrap justify-between gap-2">
                                <button type="submit" class="a-btn a-btn--dark">Save</button>
                                <button type="submit" form="delete-category-{{ $category->id }}" class="a-btn a-btn--danger"><x-icon name="trash" class="size-4" /> Delete</button>
                            </div>
                        </form>
                        <form id="delete-category-{{ $category->id }}" method="POST" action="{{ route('admin.categories.destroy', $category) }}" data-confirm="The category “{{ $category->localized('name', 'en') }}” will be deleted. Its projects stay, without a category." hidden>
                            @csrf
                            @method('DELETE')
                        </form>
                    </div>
                </details>
            @empty
                <div class="a-card"><x-admin.empty-state icon="tag" title="No categories yet" text="Add a category on the right." /></div>
            @endforelse
        </div>

        <form method="POST" action="{{ route('admin.categories.store') }}" class="a-card flex h-fit flex-col gap-4 p-6">
            @csrf
            <h2 class="font-semibold">Add a category</h2>
            <x-admin.translatable name="name" label="Name" :required="true" maxlength="60" prefix="new" />
            <button type="submit" class="a-btn a-btn--primary"><x-icon name="plus" class="size-4" /> Add category</button>
        </form>
    </div>
</x-layouts.admin>

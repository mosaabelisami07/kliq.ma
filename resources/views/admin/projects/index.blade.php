<x-layouts.admin title="Projects">
    <x-admin.page-header title="Projects" description="Portfolio items. Only published projects appear on the website.">
        <a href="{{ route('admin.categories.index') }}" class="a-btn a-btn--ghost"><x-icon name="tag" class="size-4" /> Categories</a>
        <a href="{{ route('admin.projects.create') }}" class="a-btn a-btn--primary"><x-icon name="plus" class="size-4" /> New project</a>
    </x-admin.page-header>

    <div class="a-card overflow-hidden">
        @if ($projects->isEmpty())
            <x-admin.empty-state icon="briefcase" title="No projects yet" text="Add your first portfolio project to show it on the website.">
                <a href="{{ route('admin.projects.create') }}" class="a-btn a-btn--primary"><x-icon name="plus" class="size-4" /> New project</a>
            </x-admin.empty-state>
        @else
            <div class="overflow-x-auto">
                <table class="a-table">
                    <thead>
                        <tr>
                            <th scope="col">Order</th>
                            <th scope="col">Project</th>
                            <th scope="col" class="hidden md:table-cell">Category</th>
                            <th scope="col">Status</th>
                            <th scope="col"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($projects as $project)
                            <tr>
                                <td class="w-20">
                                    <div class="flex gap-1">
                                        @foreach (['up' => 'arrow-up', 'down' => 'arrow-down'] as $direction => $icon)
                                            <form method="POST" action="{{ route('admin.projects.move', $project) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="direction" value="{{ $direction }}">
                                                <button type="submit" class="a-btn a-btn--ghost a-btn--icon !size-8 disabled:opacity-30" title="Move {{ $direction }}" @disabled(($direction === 'up' && $loop->parent->first) || ($direction === 'down' && $loop->parent->last))>
                                                    <x-icon :name="$icon" class="size-3.5" /><span class="sr-only">Move {{ $direction }}</span>
                                                </button>
                                            </form>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="min-w-64">
                                    <div class="flex items-center gap-3">
                                        <span class="grid aspect-[4/3] w-16 shrink-0 place-items-center overflow-hidden rounded-lg bg-cream ring-1 ring-line">
                                            @if ($url = $project->thumbnailUrl())
                                                <img src="{{ $url }}" alt="" class="size-full object-cover" loading="lazy" onerror="this.remove()">
                                            @else
                                                <x-icon name="image" class="size-5 text-muted" />
                                            @endif
                                        </span>
                                        <span class="min-w-0">
                                            <a href="{{ route('admin.projects.edit', $project) }}" class="block font-semibold hover:underline">{{ $project->localized('title', 'en') }}</a>
                                            <span class="block text-xs text-muted">{{ $project->client ?: '—' }} · {{ $project->images_count }} {{ Str::plural('image', $project->images_count) }}</span>
                                        </span>
                                    </div>
                                </td>
                                <td class="hidden md:table-cell">{{ $project->category?->localized('name', 'en') ?? '—' }}</td>
                                <td>
                                    <form method="POST" action="{{ route('admin.projects.publish', $project) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" @class(['inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold', 'bg-emerald-100 text-emerald-800' => $project->is_published, 'bg-neutral-200 text-neutral-700' => ! $project->is_published]) title="{{ $project->is_published ? 'Click to unpublish' : 'Click to publish' }}">
                                            <x-icon :name="$project->is_published ? 'eye' : 'eye-off'" class="size-3.5" />
                                            {{ $project->is_published ? 'Published' : 'Draft' }}
                                        </button>
                                    </form>
                                    @if ($project->is_featured)
                                        <span class="mt-1 inline-flex items-center gap-1 text-xs text-orange-deep"><x-icon name="star" class="size-3" /> Featured</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex justify-end gap-1">
                                        @if ($project->is_published)
                                            <a href="{{ route('projects.show', ['locale' => config('app.locale'), 'project' => $project]) }}" target="_blank" class="a-btn a-btn--ghost a-btn--icon" title="View on website"><x-icon name="external-link" class="size-4" /><span class="sr-only">View</span></a>
                                        @endif
                                        <a href="{{ route('admin.projects.edit', $project) }}" class="a-btn a-btn--ghost a-btn--icon" title="Edit"><x-icon name="pencil" class="size-4" /><span class="sr-only">Edit</span></a>
                                        <form method="POST" action="{{ route('admin.projects.destroy', $project) }}" data-confirm="“{{ $project->localized('title', 'en') }}” and all its images will be permanently deleted.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="a-btn a-btn--danger a-btn--icon" title="Delete"><x-icon name="trash" class="size-4" /><span class="sr-only">Delete</span></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-layouts.admin>

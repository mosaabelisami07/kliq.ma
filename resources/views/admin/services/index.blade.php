<x-layouts.admin title="Services">
    <x-admin.page-header title="Services" description="Only active services are shown on the website and in the contact form.">
        <a href="{{ route('admin.services.create') }}" class="a-btn a-btn--primary"><x-icon name="plus" class="size-4" /> New service</a>
    </x-admin.page-header>

    <div class="a-card overflow-hidden">
        @if ($services->isEmpty())
            <x-admin.empty-state icon="layers" title="No services yet" text="Add the services KLIQ offers so visitors can learn about them and request them.">
                <a href="{{ route('admin.services.create') }}" class="a-btn a-btn--primary"><x-icon name="plus" class="size-4" /> New service</a>
            </x-admin.empty-state>
        @else
            <div class="overflow-x-auto">
                <table class="a-table">
                    <thead>
                        <tr>
                            <th scope="col">Order</th>
                            <th scope="col">Service</th>
                            <th scope="col" class="hidden md:table-cell">Inquiries</th>
                            <th scope="col">Status</th>
                            <th scope="col"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($services as $service)
                            <tr>
                                <td class="w-20">
                                    <div class="flex gap-1">
                                        @foreach (['up' => 'arrow-up', 'down' => 'arrow-down'] as $direction => $icon)
                                            <form method="POST" action="{{ route('admin.services.move', $service) }}">
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
                                        <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-orange/15 text-orange-deep"><x-icon :name="$service->icon ?? 'sparkles'" class="size-5" /></span>
                                        <span class="min-w-0">
                                            <a href="{{ route('admin.services.edit', $service) }}" class="block font-semibold hover:underline">{{ $service->localized('title', 'en') }}</a>
                                            <span class="block max-w-md truncate text-xs text-muted">{{ $service->localized('summary', 'en') }}</span>
                                        </span>
                                    </div>
                                </td>
                                <td class="hidden md:table-cell">
                                    <a href="{{ route('admin.inquiries.index', ['service' => $service->id]) }}" class="hover:underline">{{ $service->inquiries_count }}</a>
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('admin.services.toggle', $service) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" @class(['inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold', 'bg-emerald-100 text-emerald-800' => $service->is_active, 'bg-neutral-200 text-neutral-700' => ! $service->is_active]) title="{{ $service->is_active ? 'Click to deactivate' : 'Click to activate' }}">
                                            <x-icon :name="$service->is_active ? 'eye' : 'eye-off'" class="size-3.5" />
                                            {{ $service->is_active ? 'Active' : 'Inactive' }}
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <div class="flex justify-end gap-1">
                                        <a href="{{ route('admin.services.edit', $service) }}" class="a-btn a-btn--ghost a-btn--icon" title="Edit"><x-icon name="pencil" class="size-4" /><span class="sr-only">Edit</span></a>
                                        <form method="POST" action="{{ route('admin.services.destroy', $service) }}" data-confirm="“{{ $service->localized('title', 'en') }}” will be deleted. Past inquiries keep the service name.">
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

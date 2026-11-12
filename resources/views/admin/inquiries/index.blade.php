<x-layouts.admin title="Inquiries">
    <x-admin.page-header title="Inquiries" description="Everyone who contacted KLIQ through the website.">
        <a href="{{ route('admin.inquiries.export', request()->query()) }}" class="a-btn a-btn--ghost"><x-icon name="download" class="size-4" /> Export CSV</a>
    </x-admin.page-header>

    <form method="GET" action="{{ route('admin.inquiries.index') }}" class="a-card mb-5 grid gap-3 p-4 md:grid-cols-[1fr_auto_auto_auto_auto]" role="search">
        <label class="relative">
            <span class="sr-only">Search</span>
            <x-icon name="search" class="pointer-events-none absolute start-3 top-1/2 mt-[0.175rem] size-4 -translate-y-1/2 text-muted" />
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search name, email or company" class="a-input !ps-9">
        </label>
        <label>
            <span class="sr-only">Status</span>
            <select name="status" class="a-input">
                <option value="">All statuses</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </label>
        <label>
            <span class="sr-only">Service</span>
            <select name="service" class="a-input">
                <option value="">All services</option>
                @foreach ($services as $service)
                    <option value="{{ $service->id }}" @selected(($filters['service'] ?? '') == $service->id)>{{ $service->localized('title', 'en') }}</option>
                @endforeach
                <option value="none" @selected(($filters['service'] ?? '') === 'none')>Other / none</option>
            </select>
        </label>
        <label>
            <span class="sr-only">Sort</span>
            <select name="sort" class="a-input">
                <option value="newest">Newest first</option>
                <option value="oldest" @selected(($filters['sort'] ?? '') === 'oldest')>Oldest first</option>
            </select>
        </label>
        <div class="flex items-end gap-2">
            <button type="submit" class="a-btn a-btn--dark">Filter</button>
            @if (array_filter($filters))
                <a href="{{ route('admin.inquiries.index') }}" class="a-btn a-btn--ghost">Reset</a>
            @endif
        </div>
    </form>

    <div class="a-card overflow-hidden">
        @if ($inquiries->isEmpty())
            <x-admin.empty-state
                icon="inbox"
                :title="array_filter($filters) ? 'No inquiries match these filters' : 'No inquiries yet'"
                :text="array_filter($filters) ? 'Try a different search or reset the filters.' : 'Messages sent through the website contact form will appear here.'"
            />
        @else
            <div class="overflow-x-auto">
                <table class="a-table">
                    <thead>
                        <tr>
                            <th scope="col">Client</th>
                            <th scope="col" class="hidden md:table-cell">Service</th>
                            <th scope="col" class="hidden lg:table-cell">Budget</th>
                            <th scope="col">Status</th>
                            <th scope="col">Received</th>
                            <th scope="col"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($inquiries as $inquiry)
                            <tr @class(['bg-orange/[0.06]' => $inquiry->isUnread()])>
                                <td class="min-w-56">
                                    <a href="{{ route('admin.inquiries.show', $inquiry) }}" class="group flex items-center gap-3">
                                        @if ($inquiry->isUnread())
                                            <span class="size-2 shrink-0 rounded-full bg-orange" title="Unread"><span class="sr-only">Unread</span></span>
                                        @endif
                                        <span class="min-w-0">
                                            <span @class(['block group-hover:underline', 'font-bold' => $inquiry->isUnread(), 'font-medium' => ! $inquiry->isUnread()])>{{ $inquiry->name }}@if ($inquiry->company)<span class="font-normal text-muted"> · {{ $inquiry->company }}</span>@endif</span>
                                            <span class="block truncate text-xs text-muted">{{ $inquiry->email }}</span>
                                        </span>
                                    </a>
                                </td>
                                <td class="hidden md:table-cell">{{ $inquiry->serviceLabel() ?? '—' }}</td>
                                <td class="hidden lg:table-cell">{{ $inquiry->budget ? __("site.contact.budgets.{$inquiry->budget}", [], 'en') : '—' }}</td>
                                <td><x-admin.status-badge :status="$inquiry->status" /></td>
                                <td class="whitespace-nowrap text-muted" title="{{ $inquiry->created_at->toDayDateTimeString() }}">{{ $inquiry->created_at->format('M j, Y · H:i') }}</td>
                                <td>
                                    <div class="flex justify-end gap-1">
                                        <a href="mailto:{{ $inquiry->email }}" class="a-btn a-btn--ghost a-btn--icon" title="Email {{ $inquiry->name }}"><x-icon name="mail" class="size-4" /><span class="sr-only">Email</span></a>
                                        @if ($tel = $inquiry->phoneHref())
                                            <a href="{{ $tel }}" class="a-btn a-btn--ghost a-btn--icon" title="Call {{ $inquiry->phone }}"><x-icon name="phone" class="size-4" /><span class="sr-only">Call</span></a>
                                        @endif
                                        <a href="{{ route('admin.inquiries.show', $inquiry) }}" class="a-btn a-btn--ghost a-btn--icon" title="View"><x-icon name="eye" class="size-4" /><span class="sr-only">View</span></a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="mt-5">{{ $inquiries->links() }}</div>
</x-layouts.admin>

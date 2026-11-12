@php
    $cards = [
        ['Total inquiries', $stats['total'], 'inbox', route('admin.inquiries.index'), false],
        ['New', $stats['new'], 'mail', route('admin.inquiries.index', ['status' => 'new']), true],
        ['In progress', $stats['in_progress'], 'clock', route('admin.inquiries.index', ['status' => 'in_progress']), false],
        ['Replied', $stats['replied'], 'check', route('admin.inquiries.index', ['status' => 'replied']), false],
        ['Published projects', $stats['published_projects'], 'briefcase', route('admin.projects.index'), false],
        ['Active services', $stats['active_services'], 'layers', route('admin.services.index'), false],
    ];
    $chartMax = max(1, $chart->max('total'));
@endphp

<x-layouts.admin title="Dashboard">
    <x-admin.page-header :title="'Hello, '.Str::before(auth()->user()->name, ' ')" description="Here is what is happening on the KLIQ website.">
        <a href="{{ route('admin.projects.create') }}" class="a-btn a-btn--ghost"><x-icon name="plus" class="size-4" /> New project</a>
        <a href="{{ route('admin.inquiries.index') }}" class="a-btn a-btn--primary"><x-icon name="inbox" class="size-4" /> View inquiries</a>
    </x-admin.page-header>

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-3 xl:grid-cols-6">
        @foreach ($cards as [$label, $value, $icon, $url, $highlight])
            <a href="{{ $url }}" @class([
                'a-card group p-5 transition hover:-translate-y-0.5 hover:shadow-lg',
                '!border-orange !bg-orange text-black' => $highlight && $value > 0,
            ])>
                <span @class(['grid size-9 place-items-center rounded-xl', 'bg-black/10' => $highlight && $value > 0, 'bg-orange/15 text-orange-deep' => ! ($highlight && $value > 0)])><x-icon :name="$icon" class="size-[1.1rem]" /></span>
                <p class="mt-4 font-display text-3xl font-bold tabular-nums">{{ number_format($value) }}</p>
                <p @class(['mt-0.5 text-sm', 'text-black/70' => $highlight && $value > 0, 'text-muted' => ! ($highlight && $value > 0)])>{{ $label }}</p>
            </a>
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        <section class="a-card overflow-hidden xl:col-span-2" aria-labelledby="recent-title">
            <div class="flex items-center justify-between border-b border-line px-5 py-4">
                <h2 id="recent-title" class="font-semibold">Recent inquiries</h2>
                <a href="{{ route('admin.inquiries.index') }}" class="text-sm font-semibold text-orange-deep hover:underline">See all</a>
            </div>

            @if ($recent->isEmpty())
                <x-admin.empty-state title="No inquiries yet" text="Messages sent through the website contact form will appear here." />
            @else
                <ul class="divide-y divide-line">
                    @foreach ($recent as $inquiry)
                        <li>
                            <a href="{{ route('admin.inquiries.show', $inquiry) }}" @class(['flex items-center gap-4 px-5 py-3.5 transition hover:bg-cream', 'bg-orange/[0.06]' => $inquiry->isUnread()])>
                                <span class="grid size-10 shrink-0 place-items-center rounded-full bg-ink text-sm font-semibold text-white">{{ Str::upper(Str::substr($inquiry->name, 0, 1)) }}</span>
                                <span class="min-w-0 flex-1">
                                    <span class="flex items-center gap-2">
                                        <span @class(['truncate', 'font-bold' => $inquiry->isUnread(), 'font-medium' => ! $inquiry->isUnread()])>{{ $inquiry->name }}</span>
                                        @if ($inquiry->isUnread())
                                            <span class="size-2 shrink-0 rounded-full bg-orange" aria-label="Unread"></span>
                                        @endif
                                    </span>
                                    <span class="block truncate text-sm text-muted">{{ $inquiry->serviceLabel() ?? Str::limit($inquiry->message, 60) }}</span>
                                </span>
                                <span class="hidden shrink-0 sm:block"><x-admin.status-badge :status="$inquiry->status" /></span>
                                <span class="shrink-0 text-xs text-muted" title="{{ $inquiry->created_at->toDayDateTimeString() }}">{{ $inquiry->created_at->diffForHumans(short: true) }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <div class="flex flex-col gap-6">
            <section class="a-card p-5" aria-labelledby="chart-title">
                <h2 id="chart-title" class="font-semibold">Inquiries, last 14 days</h2>
                <p class="text-sm text-muted">{{ $chart->sum('total') }} received</p>
                <div class="mt-5 flex h-32 items-end gap-1.5" role="img" aria-label="Daily inquiries for the last 14 days">
                    @foreach ($chart as $day)
                        <div class="group relative flex h-full flex-1 flex-col justify-end" title="{{ $day['label'] }}: {{ $day['total'] }}">
                            <span @class(['block w-full rounded-t-md transition-colors', 'bg-orange group-hover:bg-ink' => $day['total'], 'bg-line' => ! $day['total']]) style="height: {{ $day['total'] ? max(6, round($day['total'] / $chartMax * 100)) : 4 }}%"></span>
                        </div>
                    @endforeach
                </div>
                <div class="mt-2 flex justify-between text-[0.7rem] text-muted"><span>{{ $chart->first()['label'] }}</span><span>Today</span></div>
            </section>

            <section class="a-card p-5" aria-labelledby="status-title">
                <h2 id="status-title" class="font-semibold">By status</h2>
                <ul class="mt-4 flex flex-col gap-2.5">
                    @foreach (\App\Enums\InquiryStatus::cases() as $status)
                        <li>
                            <a href="{{ route('admin.inquiries.index', ['status' => $status->value]) }}" class="flex items-center justify-between gap-3 text-sm hover:underline">
                                <x-admin.status-badge :status="$status" />
                                <span class="font-semibold tabular-nums">{{ $statusCounts[$status->value] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>

            <section class="a-card p-5" aria-labelledby="quick-title">
                <h2 id="quick-title" class="font-semibold">Quick actions</h2>
                <div class="mt-4 grid grid-cols-2 gap-2">
                    <a href="{{ route('admin.services.create') }}" class="a-btn a-btn--ghost a-btn--sm">Add service</a>
                    <a href="{{ route('admin.content.edit') }}" class="a-btn a-btn--ghost a-btn--sm">Edit content</a>
                    <a href="{{ route('admin.settings.edit') }}" class="a-btn a-btn--ghost a-btn--sm">Contact details</a>
                    <a href="{{ route('admin.inquiries.export') }}" class="a-btn a-btn--ghost a-btn--sm">Export CSV</a>
                </div>
            </section>
        </div>
    </div>
</x-layouts.admin>

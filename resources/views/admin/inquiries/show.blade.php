@php
    $details = array_filter([
        'Email' => $inquiry->email,
        'Phone' => $inquiry->phone,
        'Company' => $inquiry->company,
        'Service' => $inquiry->serviceLabel(),
        'Budget' => $inquiry->budget ? __("site.contact.budgets.{$inquiry->budget}", [], 'en') : null,
        'Preferred contact' => $inquiry->preferred_contact ? __("site.contact.methods.{$inquiry->preferred_contact}", [], 'en') : null,
        'Website language' => $inquiry->locale ? config("kliq.locales.{$inquiry->locale}.name") : null,
        'Received' => $inquiry->created_at->format('l, F j, Y \a\t H:i'),
    ]);
@endphp

<x-layouts.admin :title="'Inquiry from '.$inquiry->name">
    <x-admin.page-header :title="$inquiry->name" :description="$inquiry->company" :back="route('admin.inquiries.index')">
        <a href="mailto:{{ $inquiry->email }}?subject={{ rawurlencode('Re: your inquiry to KLIQ') }}" class="a-btn a-btn--primary"><x-icon name="mail" class="size-4" /> Reply by email</a>
        @if ($tel = $inquiry->phoneHref())
            <a href="{{ $tel }}" class="a-btn a-btn--ghost"><x-icon name="phone" class="size-4" /> Call</a>
        @endif
    </x-admin.page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="flex flex-col gap-6 lg:col-span-2">
            <section class="a-card p-6" aria-labelledby="message-title">
                <div class="flex items-center justify-between gap-4">
                    <h2 id="message-title" class="font-semibold">Project details</h2>
                    <x-admin.status-badge :status="$inquiry->status" />
                </div>
                <div class="mt-4 text-[0.95rem] leading-relaxed whitespace-pre-line">{{ $inquiry->message }}</div>
            </section>

            <section class="a-card p-6" aria-labelledby="details-title">
                <h2 id="details-title" class="font-semibold">Contact details</h2>
                <dl class="mt-4 grid gap-x-6 gap-y-4 sm:grid-cols-2">
                    @foreach ($details as $label => $value)
                        <div>
                            <dt class="text-xs font-semibold tracking-wide text-muted uppercase">{{ $label }}</dt>
                            <dd class="mt-1 break-words">
                                @if ($label === 'Email')
                                    <a href="mailto:{{ $value }}" class="text-orange-deep hover:underline">{{ $value }}</a>
                                @elseif ($label === 'Phone')
                                    <a href="{{ $inquiry->phoneHref() }}" class="text-orange-deep hover:underline" dir="ltr">{{ $value }}</a>
                                @else
                                    {{ $value }}
                                @endif
                            </dd>
                        </div>
                    @endforeach
                </dl>
            </section>
        </div>

        <div class="flex flex-col gap-6">
            <form method="POST" action="{{ route('admin.inquiries.update', $inquiry) }}" class="a-card flex flex-col gap-5 p-6">
                @csrf
                @method('PATCH')
                <h2 class="font-semibold">Manage</h2>

                <div>
                    <label for="status" class="a-label">Status</label>
                    <select id="status" name="status" class="a-input">
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected(old('status', $inquiry->status->value) === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                    @error('status') <p class="a-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="notes" class="a-label">Internal notes</label>
                    <textarea id="notes" name="notes" rows="7" class="a-input resize-y" placeholder="Only visible to administrators.">{{ old('notes', $inquiry->notes) }}</textarea>
                    <p class="a-hint flex items-center gap-1"><x-icon name="eye-off" class="size-3.5" /> Private — never shown on the website.</p>
                    @error('notes') <p class="a-error">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="a-btn a-btn--dark">Save changes</button>
            </form>

            <form method="POST" action="{{ route('admin.inquiries.destroy', $inquiry) }}" data-confirm="The inquiry from {{ $inquiry->name }} and its notes will be permanently deleted." class="a-card p-6">
                @csrf
                @method('DELETE')
                <h2 class="font-semibold">Delete inquiry</h2>
                <p class="mt-1 text-sm text-muted">This cannot be undone.</p>
                <button type="submit" class="a-btn a-btn--danger mt-4"><x-icon name="trash" class="size-4" /> Delete</button>
            </form>
        </div>
    </div>
</x-layouts.admin>

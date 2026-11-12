@props(['title', 'description' => null, 'back' => null])

<div class="mb-8 flex flex-wrap items-end justify-between gap-4">
    <div>
        @if ($back)
            <a href="{{ $back }}" class="mb-3 inline-flex items-center gap-1.5 text-sm text-muted hover:text-ink"><x-icon name="chevron-left" class="size-4" /> Back</a>
        @endif
        <h1 class="font-display text-3xl font-bold tracking-tight">{{ $title }}</h1>
        @if ($description)
            <p class="mt-1 text-muted">{{ $description }}</p>
        @endif
    </div>

    @if ($slot->isNotEmpty())
        <div class="flex flex-wrap items-center gap-2">{{ $slot }}</div>
    @endif
</div>

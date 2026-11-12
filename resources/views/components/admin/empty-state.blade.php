@props(['icon' => 'inbox', 'title', 'text' => null])

<div {{ $attributes->class('flex flex-col items-center px-6 py-16 text-center') }}>
    <span class="grid size-14 place-items-center rounded-2xl bg-orange/15 text-orange-deep"><x-icon :name="$icon" class="size-7" /></span>
    <p class="mt-4 font-semibold">{{ $title }}</p>
    @if ($text)
        <p class="mt-1 max-w-sm text-sm text-muted">{{ $text }}</p>
    @endif
    @if ($slot->isNotEmpty())
        <div class="mt-5">{{ $slot }}</div>
    @endif
</div>

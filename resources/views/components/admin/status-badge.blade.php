@props(['status'])

<span {{ $attributes->class(['inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold whitespace-nowrap', $status->badgeClasses()]) }}>
    {{ $status->label() }}
</span>

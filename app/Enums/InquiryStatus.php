<?php

namespace App\Enums;

enum InquiryStatus: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case InProgress = 'in_progress';
    case Replied = 'replied';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Contacted => 'Contacted',
            self::InProgress => 'In progress',
            self::Replied => 'Replied',
            self::Closed => 'Closed',
        };
    }

    /**
     * Badge classes for the admin tables.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::New => 'bg-orange text-black',
            self::Contacted => 'bg-sky-100 text-sky-800',
            self::InProgress => 'bg-amber-100 text-amber-800',
            self::Replied => 'bg-emerald-100 text-emerald-800',
            self::Closed => 'bg-neutral-200 text-neutral-700',
        };
    }
}

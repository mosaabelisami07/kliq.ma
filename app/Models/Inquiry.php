<?php

namespace App\Models;

use App\Enums\InquiryStatus;
use Database\Factories\InquiryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'service_id', 'service_name', 'name', 'company', 'email', 'phone', 'budget',
    'preferred_contact', 'message', 'status', 'notes', 'locale', 'submission_token',
    'ip_address', 'user_agent', 'read_at',
])]
#[Hidden(['notes', 'submission_token', 'ip_address', 'user_agent'])]
class Inquiry extends Model
{
    /** @use HasFactory<InquiryFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Service, $this>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Inquiries no administrator has opened yet.
     *
     * @param  Builder<Inquiry>  $query
     */
    #[Scope]
    protected function unread(Builder $query): void
    {
        $query->whereNull('read_at');
    }

    /**
     * Search by client name, email or company.
     *
     * @param  Builder<Inquiry>  $query
     */
    #[Scope]
    protected function search(Builder $query, ?string $term): void
    {
        $term = trim((string) $term);

        if ($term === '') {
            return;
        }

        $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term).'%';

        $query->where(fn (Builder $query) => $query
            ->where('name', 'like', $like)
            ->orWhere('email', 'like', $like)
            ->orWhere('company', 'like', $like));
    }

    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    /**
     * The requested service's current name, or the name it had when submitted.
     */
    public function serviceLabel(): ?string
    {
        return $this->service?->localized('title', 'en') ?? $this->service_name;
    }

    /**
     * The phone number reduced to characters valid in a tel: link.
     */
    public function phoneHref(): ?string
    {
        return $this->phone ? 'tel:'.preg_replace('/[^\d+]/', '', $this->phone) : null;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => InquiryStatus::class,
            'read_at' => 'datetime',
        ];
    }
}

<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use App\Support\Media;
use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\RouteKey;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['slug', 'title', 'summary', 'body', 'deliverables', 'icon', 'image', 'is_active', 'sort_order'])]
#[RouteKey('slug')]
class Service extends Model
{
    /** @use HasFactory<ServiceFactory> */
    use HasFactory, HasTranslations;

    /**
     * @return HasMany<Inquiry, $this>
     */
    public function inquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class);
    }

    /**
     * Services visitors may see, in their display order.
     *
     * @param  Builder<Service>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    public function imageUrl(): ?string
    {
        return Media::url($this->image);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'title' => 'array',
            'summary' => 'array',
            'body' => 'array',
            'deliverables' => 'array',
            'is_active' => 'boolean',
        ];
    }
}

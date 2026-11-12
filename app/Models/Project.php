<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use App\Support\Media;
use App\Support\VideoEmbed;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\RouteKey;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'category_id', 'slug', 'title', 'client', 'summary', 'body', 'services_provided',
    'year', 'thumbnail', 'external_url', 'video_url', 'is_published', 'is_featured', 'sort_order',
])]
#[RouteKey('slug')]
class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory, HasTranslations;

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return HasMany<ProjectImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProjectImage::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Projects visitors may see, in their display order.
     *
     * @param  Builder<Project>  $query
     */
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('is_published', true)->orderBy('sort_order')->orderBy('id');
    }

    public function thumbnailUrl(): ?string
    {
        return Media::url($this->thumbnail);
    }

    /**
     * The embeddable player for the project's video link, if it has a supported one.
     */
    public function video(): ?VideoEmbed
    {
        return VideoEmbed::fromUrl($this->video_url);
    }

    /**
     * Seed for the generative artwork shown when the project has no image.
     */
    public function artSeed(): int
    {
        return $this->id * 7 + 3;
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
            'services_provided' => 'array',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'year' => 'integer',
        ];
    }
}

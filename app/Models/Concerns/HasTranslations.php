<?php

namespace App\Models\Concerns;

use App\Support\Translation;

/**
 * Reads attributes stored as {"en": "...", "fr": "...", "ar": "..."} JSON
 * (cast to arrays on the model). Missing translations fall back to the
 * fallback locale, then to the first filled translation.
 */
trait HasTranslations
{
    /**
     * The attribute's text in the given (or current) locale.
     */
    public function localized(string $attribute, ?string $locale = null): ?string
    {
        return Translation::pick($this->getAttribute($attribute), $locale);
    }

    /**
     * A newline-separated translatable attribute as a list of trimmed lines.
     *
     * @return list<string>
     */
    public function localizedLines(string $attribute, ?string $locale = null): array
    {
        return collect(preg_split('/\R/', (string) $this->localized($attribute, $locale)))
            ->map(fn (string $line): string => trim($line, " \t-•"))
            ->filter()
            ->values()
            ->all();
    }
}

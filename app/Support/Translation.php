<?php

namespace App\Support;

class Translation
{
    /**
     * Pick one locale from a {"en": "...", "fr": "..."} map, falling back to
     * the fallback locale, then to the first filled translation.
     *
     * @param  array<string, string|null>|string|null  $translations
     */
    public static function pick(array|string|null $translations, ?string $locale = null): ?string
    {
        if (! is_array($translations)) {
            return $translations;
        }

        $locale ??= app()->getLocale();

        foreach ([$locale, config('app.fallback_locale')] as $candidate) {
            if (filled($translations[$candidate] ?? null)) {
                return $translations[$candidate];
            }
        }

        return collect($translations)->first(fn (?string $value): bool => filled($value));
    }
}

<?php

namespace App\Support;

/**
 * Validation rules for a field submitted as field[en], field[fr], field[ar].
 * Only the fallback locale can be required; other languages fall back to it.
 */
class TranslatableRules
{
    /**
     * @return array<string, list<string>>
     */
    public static function for(string $field, bool $required = false, int $max = 255): array
    {
        $rules = [$field => [$required ? 'required' : 'nullable', 'array']];

        foreach (array_keys(config('kliq.locales')) as $locale) {
            $rules["{$field}.{$locale}"] = [
                $required && $locale === config('app.fallback_locale') ? 'required' : 'nullable',
                'string',
                "max:{$max}",
            ];
        }

        return $rules;
    }

    /**
     * Attribute names like "title (FR)" for validation messages.
     *
     * @return array<string, string>
     */
    public static function attributes(string $field, string $label): array
    {
        return collect(array_keys(config('kliq.locales')))
            ->mapWithKeys(fn (string $locale): array => ["{$field}.{$locale}" => "{$label} (".strtoupper($locale).')'])
            ->all();
    }

    /**
     * Keep only known locales and trimmed, non-empty values.
     *
     * @param  array<string, mixed>|null  $value
     * @return array<string, string>
     */
    public static function clean(?array $value): array
    {
        return collect($value ?? [])
            ->only(array_keys(config('kliq.locales')))
            ->map(fn (mixed $text): string => trim((string) $text))
            ->filter(fn (string $text): bool => $text !== '')
            ->all();
    }
}

<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Lang;

/**
 * Website content and settings edited in the dashboard.
 *
 * Values saved in the settings table win. Translatable copy that was never
 * saved falls back to lang/{locale}/site.php "defaults"; contact details and
 * social links have no fallback and stay hidden until they are entered.
 */
class SiteSettings
{
    private const CACHE_KEY = 'kliq.settings';

    /**
     * @var Collection<string, mixed>|null
     */
    private ?Collection $stored = null;

    /**
     * All saved values, keyed by setting key.
     *
     * @return Collection<string, mixed>
     */
    public function stored(): Collection
    {
        return $this->stored ??= collect(Cache::rememberForever(
            self::CACHE_KEY,
            fn (): array => Setting::query()->pluck('value', 'key')->all(),
        ));
    }

    public function has(string $key): bool
    {
        return $this->stored()->has($key);
    }

    /**
     * A scalar setting such as an email address or a toggle.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->stored()->get($key) ?? config("kliq.settings.defaults.{$key}", $default);
    }

    /**
     * A translatable text in the current (or given) locale.
     */
    public function text(string $key, ?string $locale = null): ?string
    {
        if ($this->has($key)) {
            return Translation::pick($this->stored()->get($key), $locale);
        }

        return $this->defaultText($key, $locale);
    }

    /**
     * A translatable text with *starred* words wrapped in an accent span.
     * The text is escaped before the markup is added.
     */
    public function highlighted(string $key): string
    {
        return preg_replace('/\*(.+?)\*/u', '<span class="text-orange">$1</span>', e((string) $this->text($key)));
    }

    /**
     * Every locale's version of a translatable text, for the dashboard form.
     *
     * @return array<string, string|null>
     */
    public function translations(string $key): array
    {
        $stored = $this->stored()->get($key);

        return collect(array_keys(config('kliq.locales')))
            ->mapWithKeys(fn (string $locale): array => [
                $locale => $this->has($key) ? ($stored[$locale] ?? null) : $this->defaultText($key, $locale),
            ])
            ->all();
    }

    /**
     * A repeating list (process steps, values…) localized for display.
     * Rows without a title are skipped.
     *
     * @return list<array{icon: string, title: string, text: string|null}>
     */
    public function items(string $key, ?string $locale = null): array
    {
        return collect($this->itemTranslations($key))
            ->map(fn (array $item): array => [
                'icon' => $item['icon'],
                'title' => (string) Translation::pick($item['title'], $locale),
                'text' => Translation::pick($item['text'], $locale),
            ])
            ->filter(fn (array $item): bool => $item['title'] !== '')
            ->values()
            ->all();
    }

    /**
     * A repeating list with every locale's text, padded to its row count.
     *
     * @return list<array{icon: string, title: array<string, string|null>, text: array<string, string|null>}>
     */
    public function itemTranslations(string $key): array
    {
        $definition = config("kliq.settings.lists.{$key}");
        $locales = array_keys(config('kliq.locales'));
        $stored = $this->stored()->get($key);

        return collect(range(0, $definition['rows'] - 1))
            ->map(function (int $row) use ($key, $definition, $locales, $stored): array {
                if ($this->has($key)) {
                    $item = $stored[$row] ?? [];

                    return [
                        'icon' => $item['icon'] ?? $definition['icons'][$row] ?? 'sparkles',
                        'title' => $item['title'] ?? [],
                        'text' => $item['text'] ?? [],
                    ];
                }

                $defaults = collect($locales)->mapWithKeys(fn (string $locale): array => [
                    $locale => Lang::get("site.defaults.{$key}", [], $locale)[$row] ?? [],
                ]);

                return [
                    'icon' => $definition['icons'][$row] ?? 'sparkles',
                    'title' => $defaults->map(fn (array $item): ?string => $item['title'] ?? null)->all(),
                    'text' => $defaults->map(fn (array $item): ?string => $item['text'] ?? null)->all(),
                ];
            })
            ->all();
    }

    /**
     * Social profiles that have been filled in, as label => URL.
     *
     * @return array<string, string>
     */
    public function socials(): array
    {
        $links = (array) $this->get('socials', []);

        return collect(config('kliq.settings.socials'))
            ->filter(fn (string $label, string $network): bool => filled($links[$network] ?? null))
            ->mapWithKeys(fn (string $label, string $network): array => [$label => $links[$network]])
            ->all();
    }

    /**
     * Saved social profiles keyed by network, in the configured order.
     *
     * @return array<string, array{label: string, url: string}>
     */
    public function socialLinks(): array
    {
        $labels = config('kliq.settings.socials');

        return collect($this->socials())
            ->mapWithKeys(fn (string $url, string $label): array => [array_search($label, $labels, true) => ['label' => $label, 'url' => $url]])
            ->all();
    }

    public function imageUrl(string $key): ?string
    {
        return Media::url($this->get($key));
    }

    /**
     * Google Maps embed for the saved location. The URL is built here rather
     * than accepted from the dashboard, so only Google Maps can be framed.
     */
    public function mapEmbedUrl(): ?string
    {
        $location = trim((string) $this->get('map_location'));

        if ($location === '') {
            return null;
        }

        return 'https://maps.google.com/maps?'.http_build_query([
            'q' => $location,
            'z' => 16,
            'hl' => app()->getLocale(),
            'output' => 'embed',
        ]);
    }

    /**
     * WhatsApp chat link for the saved number, if any.
     */
    public function whatsappUrl(): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $this->get('whatsapp'));

        return $digits !== '' ? "https://wa.me/{$digits}" : null;
    }

    /**
     * Address where new-inquiry emails go, if notifications are enabled.
     */
    public function notificationRecipient(): ?string
    {
        if (! $this->get('notify_on_inquiry')) {
            return null;
        }

        return $this->get('notification_email') ?: config('kliq.notification_email');
    }

    /**
     * Save several settings at once.
     *
     * @param  array<string, mixed>  $values
     */
    public function save(array $values): void
    {
        foreach ($values as $key => $value) {
            Setting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }

        $this->flush();
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
        $this->stored = null;
    }

    private function defaultText(string $key, ?string $locale): ?string
    {
        $locale ??= app()->getLocale();
        $line = "site.defaults.{$key}";

        return Lang::has($line, $locale, false) ? Lang::get($line, [], $locale) : null;
    }
}

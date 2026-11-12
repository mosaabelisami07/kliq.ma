<?php

namespace App\Support;

/**
 * Turns a YouTube or Instagram video link into the platform's embeddable player URL.
 */
class VideoEmbed
{
    /**
     * @param  array{provider: 'youtube'|'instagram', id: string}  $video
     */
    private function __construct(private array $video) {}

    public static function fromUrl(?string $url): ?self
    {
        if (blank($url)) {
            return null;
        }

        $parts = parse_url(trim($url));
        $host = strtolower(preg_replace('/^(www\.|m\.)/i', '', $parts['host'] ?? ''));
        $path = $parts['path'] ?? '';

        if (! in_array($parts['scheme'] ?? '', ['http', 'https'], true)) {
            return null;
        }

        if ($host === 'youtu.be' && preg_match('#^/([\w-]{11})#', $path, $match)) {
            return new self(['provider' => 'youtube', 'id' => $match[1]]);
        }

        if (in_array($host, ['youtube.com', 'youtube-nocookie.com'], true)) {
            parse_str($parts['query'] ?? '', $query);

            if ($path === '/watch' && is_string($query['v'] ?? null) && preg_match('/^[\w-]{11}$/', $query['v'])) {
                return new self(['provider' => 'youtube', 'id' => $query['v']]);
            }

            if (preg_match('#^/(?:shorts|embed|live)/([\w-]{11})#', $path, $match)) {
                return new self(['provider' => 'youtube', 'id' => $match[1]]);
            }
        }

        if ($host === 'instagram.com' && preg_match('#^/(?:[\w.]+/)?(?:reel|reels|p|tv)/([\w-]+)#', $path, $match)) {
            return new self(['provider' => 'instagram', 'id' => $match[1]]);
        }

        return null;
    }

    /**
     * URL of the platform's embeddable player.
     */
    public function embedUrl(): string
    {
        return match ($this->video['provider']) {
            'youtube' => "https://www.youtube-nocookie.com/embed/{$this->video['id']}?rel=0",
            'instagram' => "https://www.instagram.com/reel/{$this->video['id']}/embed/",
        };
    }

    /**
     * Instagram reels are portrait; YouTube videos are landscape.
     */
    public function isPortrait(): bool
    {
        return $this->video['provider'] === 'instagram';
    }
}

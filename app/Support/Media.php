<?php

namespace App\Support;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Stores admin uploads on the media disk and turns stored paths into URLs.
 */
class Media
{
    public static function disk(): Filesystem
    {
        return Storage::disk(config('kliq.media_disk'));
    }

    /**
     * Public URL of a stored file, or null when there is none.
     */
    public static function url(?string $path): ?string
    {
        return filled($path) ? static::disk()->url($path) : null;
    }

    /**
     * Store an upload under a random name and return its path.
     */
    public static function store(UploadedFile $file, string $directory): string
    {
        return $file->store($directory, ['disk' => config('kliq.media_disk'), 'visibility' => 'public']);
    }

    /**
     * Store a replacement upload, deleting the previous file.
     */
    public static function replace(?string $previous, UploadedFile $file, string $directory): string
    {
        static::delete($previous);

        return static::store($file, $directory);
    }

    public static function delete(?string $path): void
    {
        if (filled($path)) {
            static::disk()->delete($path);
        }
    }
}

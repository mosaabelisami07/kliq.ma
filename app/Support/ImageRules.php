<?php

namespace App\Support;

/**
 * Validation for uploaded images. Raster formats only: SVG can carry scripts,
 * so it is never accepted, and both the extension and the sniffed MIME type
 * must match.
 */
class ImageRules
{
    /**
     * @return list<string>
     */
    public static function upload(int $maxKilobytes = 5120): array
    {
        return [
            'nullable',
            'file',
            'image',
            'mimes:jpg,jpeg,png,webp',
            'mimetypes:image/jpeg,image/png,image/webp',
            "max:{$maxKilobytes}",
            'dimensions:max_width=8000,max_height=8000',
        ];
    }

    /**
     * Favicons may also be .ico files.
     *
     * @return list<string>
     */
    public static function favicon(): array
    {
        return [
            'nullable',
            'file',
            'mimes:png,ico',
            'mimetypes:image/png,image/x-icon,image/vnd.microsoft.icon',
            'max:512',
        ];
    }
}

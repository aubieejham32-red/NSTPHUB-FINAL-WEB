<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Build image URLs from the current API host, never from FILESYSTEM_DISK.url.
 * Laravel's public disk can return an absolute APP_URL, and concatenating that
 * with the request host produces an unusable URL in an Android build.
 */
final class PublicMediaUrl
{
    public static function forRequest(Request $request, ?string $storedPath): ?string
    {
        $path = trim((string) $storedPath);

        if ($path === '') {
            return null;
        }

        if (preg_match('~^(?:https?://|data:)~i', $path)) {
            return $path;
        }

        $path = ltrim(str_replace('\\', '/', $path), '/');

        foreach (['storage/app/public/', 'public/storage/', 'app/public/', 'storage/', 'public/'] as $prefix) {
            if (str_starts_with($path, $prefix)) {
                $path = substr($path, strlen($prefix));
                break;
            }
        }

        if ($path === '' || str_contains($path, '//') || preg_match('~(?:^|/)\.\.?(/|$)~', $path)) {
            return null;
        }

        return rtrim($request->getSchemeAndHttpHost(), '/').'/storage/'.$path;
    }
}

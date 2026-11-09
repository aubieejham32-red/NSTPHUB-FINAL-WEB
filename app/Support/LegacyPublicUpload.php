<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Some older deployments committed uploads to public/storage as real files,
 * while the current upload disk reads storage/app/public (or a Railway volume).
 * Allow a narrowly scoped recovery of those existing files without resolving
 * arbitrary paths outside public/storage.
 */
final class LegacyPublicUpload
{
    public static function recover(string $path): ?string
    {
        $path = str_replace('\\', '/', trim($path));

        if ($path === '' || str_starts_with($path, '/') || str_contains($path, '//')
            || preg_match('~(?:^|/)\.\.?(/|$)~', $path)) {
            return null;
        }

        $root = realpath(public_path('storage'));
        $file = realpath(public_path('storage/'.$path));

        if (!$root || !$file || !str_starts_with($file, $root.DIRECTORY_SEPARATOR)
            || !is_file($file)) {
            return null;
        }

        // Best effort: put the legacy file on the persistent disk so subsequent
        // requests and future Railway deployments use the configured storage.
        try {
            $disk = Storage::disk('public');

            if (!$disk->exists($path)) {
                $contents = file_get_contents($file);

                if ($contents !== false) {
                    $disk->put($path, $contents);
                }
            }
        } catch (Throwable $exception) {
            Log::warning('Could not migrate legacy public upload.', [
                'path' => $path,
                'message' => $exception->getMessage(),
            ]);
        }

        // Even if the persistent disk is not writable yet, the current request
        // can still display the recovered legacy file.
        return $file;
    }
}

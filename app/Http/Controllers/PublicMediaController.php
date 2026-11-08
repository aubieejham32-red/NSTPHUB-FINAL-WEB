<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Web-server fallback for public images when a host cannot serve symlinks.
 * Never reads the private local disk (student signatures or excuse evidence).
 */
class PublicMediaController extends Controller
{
    public function show(Request $request, string $path): BinaryFileResponse
    {
        $path = ltrim(str_replace('\\', '/', $path), '/');

        if (
            $path === ''
            || str_contains($path, '//')
            || preg_match('~(?:^|/)\.\.?($|/)~', $path)
            || !preg_match('~\.(?:png|jpe?g|webp|gif)$~i', $path)
        ) {
            abort(404);
        }

        $isPublicImageDirectory = false;
        foreach ([
            'universities/',
            'student-profile-photos/',
            'university-admin/',
            'profile_photos/',
            'superadmins/',
        ] as $prefix) {
            if (str_starts_with($path, $prefix)) {
                $isPublicImageDirectory = true;
                break;
            }
        }

        if (!$isPublicImageDirectory) {
            abort(404);
        }

        $disk = Storage::disk('public');

        if (!$disk->exists($path)) {
            abort(404);
        }

        return response()->file($disk->path($path), [
            'Cache-Control' => 'public, max-age=300',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}

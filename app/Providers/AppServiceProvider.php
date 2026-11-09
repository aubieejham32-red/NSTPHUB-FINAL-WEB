<?php

namespace App\Providers;

use App\Mail\Transports\GmailApiTransport;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // Photos are limited by the authenticated student, not by a shared
        // campus / carrier / reverse-proxy IP address. The limit still prevents
        // automated excessive uploads.
        RateLimiter::for('student-profile-photo-upload', function (Request $request) {
            $studentId = $request->user()?->getAuthIdentifier();

            return Limit::perMinute(20)->by(
                $studentId !== null
                    ? 'student-photo:'.$studentId
                    : 'student-photo-ip:'.$request->ip()
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Prepare DomPDF Font Directory
        |--------------------------------------------------------------------------
        |
        | Create the directory automatically when the application runs.
        | This is particularly important for Railway deployments.
        |
        */

        try {
            File::ensureDirectoryExists(
                storage_path('fonts'),
                0775,
                true
            );
        } catch (\Throwable $exception) {
            Log::error('Unable to prepare DomPDF font directory.', [
                'message' => $exception->getMessage(),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Register Gmail API Mail Transport
        |--------------------------------------------------------------------------
        */

        Mail::extend('gmail-api', function (array $config) {
            return new GmailApiTransport();
        });
    }
}

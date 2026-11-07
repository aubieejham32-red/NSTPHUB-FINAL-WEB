<?php

namespace App\Providers;

use App\Mail\Transports\GmailApiTransport;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
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
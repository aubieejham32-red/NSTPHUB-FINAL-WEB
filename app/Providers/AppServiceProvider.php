<?php

namespace App\Providers;

use App\Mail\Transports\GmailApiTransport;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        /*
        |--------------------------------------------------------------------------
        | Register NSTP HUB Gmail API Transport
        |--------------------------------------------------------------------------
        |
        | Sends emails through Google's HTTPS API instead of SMTP.
        |
        */

        Mail::extend('gmail-api', function (array $config) {
            return new GmailApiTransport();
        });
    }
}
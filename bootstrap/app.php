<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\EnforceCoordinatorModulePermission;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        $middleware->alias([
            'coordinator.module' => EnforceCoordinatorModulePermission::class,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Railway HTTPS Proxy Configuration
        |--------------------------------------------------------------------------
        |
        | Railway uses a reverse proxy that handles HTTPS connections.
        |
        | Laravel needs to trust the forwarded headers so it correctly
        | recognizes HTTPS requests and generates secure URLs.
        |
        | This is important for:
        |
        | - Google OAuth authentication
        | - Secure redirects
        | - Session handling
        | - HTTPS URL generation
        |
        */

        $middleware->trustProxies(at: '*');


        /*
        |--------------------------------------------------------------------------
        | Sanctum
        |--------------------------------------------------------------------------
        |
        | Allows Laravel Sanctum to properly handle stateful requests.
        |
        */

        $middleware->statefulApi();


        /*
        |--------------------------------------------------------------------------
        | Cookie Encryption Exceptions
        |--------------------------------------------------------------------------
        */

        $middleware->encryptCookies(except: [
            'appearance',
            'sidebar_state',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Web Middleware
        |--------------------------------------------------------------------------
        */

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Guest Redirect
        |--------------------------------------------------------------------------
        |
        | Laravel's default authentication system normally redirects
        | unauthenticated users to /login.
        |
        | NSTP HUB uses separate login pages for:
        |
        | - University Administrator
        | - Instructor / Coordinator
        | - Super Administrator
        |
        | This prevents Laravel from trying to load:
        |
        | resources/js/pages/auth/Login.vue
        |
        | which does not belong to our custom NSTP HUB authentication flow.
        |
        */

        $middleware->redirectGuestsTo(
            function (Request $request): string {

                /*
                |--------------------------------------------------------------------------
                | University Administrator
                |--------------------------------------------------------------------------
                */

                if (
                    $request->is('university-admin') ||
                    $request->is('university-admin/*')
                ) {
                    return route('university-admin.login');
                }


                /*
                |--------------------------------------------------------------------------
                | Instructor / Coordinator
                |--------------------------------------------------------------------------
                */

                if (
                    $request->is('instructor-coordinator') ||
                    $request->is('instructor-coordinator/*')
                ) {
                    return route('instructor-coordinator.login');
                }


                /*
                |--------------------------------------------------------------------------
                | Super Administrator
                |--------------------------------------------------------------------------
                */

                if (
                    $request->is('superadmin') ||
                    $request->is('superadmin/*')
                ) {
                    return route('superadmin.login');
                }


                /*
                |--------------------------------------------------------------------------
                | Default
                |--------------------------------------------------------------------------
                |
                | If an authenticated area doesn't belong to one of the
                | portals above, return the user to the NSTP HUB role page.
                |
                */

                return route('role');
            }
        );

    })
    ->withExceptions(function (Exceptions $exceptions): void {

        //

    })
    ->create();

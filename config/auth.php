<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    */

    'defaults' => [
        'guard' => env(
            'AUTH_GUARD',
            'web'
        ),

        'passwords' => env(
            'AUTH_PASSWORD_BROKER',
            'users'
        ),
    ],


    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Guards:
    |
    | web              = Normal User
    | superadmin       = Super Administrator
    | university_admin = University Administrator
    | instructor       = NSTP Instructor
    | coordinator      = NSTP Coordinator
    |
    */

    'guards' => [

        /*
        |--------------------------------------------------------------------------
        | Default User
        |--------------------------------------------------------------------------
        */

        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],


        /*
        |--------------------------------------------------------------------------
        | Super Administrator
        |--------------------------------------------------------------------------
        */

        'superadmin' => [
            'driver' => 'session',
            'provider' => 'super_admins',
        ],


        /*
        |--------------------------------------------------------------------------
        | University Administrator
        |--------------------------------------------------------------------------
        */

        'university_admin' => [
            'driver' => 'session',
            'provider' => 'university_admins',
        ],


        /*
        |--------------------------------------------------------------------------
        | Instructor
        |--------------------------------------------------------------------------
        */

        'instructor' => [
            'driver' => 'session',
            'provider' => 'instructors',
        ],


        /*
        |--------------------------------------------------------------------------
        | Coordinator
        |--------------------------------------------------------------------------
        |
        | Coordinator roles:
        |
        | coordinator-attendance
        | coordinator-announcement
        | coordinator-schedule
        |
        | These are NOT separate guards.
        |
        */

        'coordinator' => [
            'driver' => 'session',
            'provider' => 'coordinators',
        ],

    ],


    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    */

    'providers' => [

        /*
        |--------------------------------------------------------------------------
        | Default Users
        |--------------------------------------------------------------------------
        */

        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],


        /*
        |--------------------------------------------------------------------------
        | Super Administrators
        |--------------------------------------------------------------------------
        */

        'super_admins' => [
            'driver' => 'eloquent',
            'model' => App\Models\SuperAdmin::class,
        ],


        /*
        |--------------------------------------------------------------------------
        | University Administrators
        |--------------------------------------------------------------------------
        */

        'university_admins' => [
            'driver' => 'eloquent',
            'model' => App\Models\UniversityAdministrator::class,
        ],


        /*
        |--------------------------------------------------------------------------
        | Instructors
        |--------------------------------------------------------------------------
        */

        'instructors' => [
            'driver' => 'eloquent',
            'model' => App\Models\Instructor::class,
        ],


        /*
        |--------------------------------------------------------------------------
        | Coordinators
        |--------------------------------------------------------------------------
        */

        'coordinators' => [
            'driver' => 'eloquent',
            'model' => App\Models\Coordinator::class,
        ],

    ],


    /*
    |--------------------------------------------------------------------------
    | Password Reset Brokers
    |--------------------------------------------------------------------------
    */

    'passwords' => [

        /*
        |--------------------------------------------------------------------------
        | Default Users
        |--------------------------------------------------------------------------
        */

        'users' => [
            'provider' => 'users',

            'table' => env(
                'AUTH_PASSWORD_RESET_TOKEN_TABLE',
                'password_reset_tokens'
            ),

            'expire' => 60,

            'throttle' => 60,
        ],


        /*
        |--------------------------------------------------------------------------
        | Instructors
        |--------------------------------------------------------------------------
        */

        'instructors' => [
            'provider' => 'instructors',

            'table' => env(
                'AUTH_PASSWORD_RESET_TOKEN_TABLE',
                'password_reset_tokens'
            ),

            'expire' => 60,

            'throttle' => 60,
        ],


        /*
        |--------------------------------------------------------------------------
        | Coordinators
        |--------------------------------------------------------------------------
        */

        'coordinators' => [
            'provider' => 'coordinators',

            'table' => env(
                'AUTH_PASSWORD_RESET_TOKEN_TABLE',
                'password_reset_tokens'
            ),

            'expire' => 60,

            'throttle' => 60,
        ],

    ],


    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    */

    'password_timeout' => env(
        'AUTH_PASSWORD_TIMEOUT',
        10800
    ),

];
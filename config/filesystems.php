<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            // Private uploads also need to survive Railway redeployments.
            // Mount a Railway volume at /app/storage/app (or set the override).
            'root' => env('NSTPHUB_PRIVATE_UPLOAD_ROOT')
                ?: (env('RAILWAY_VOLUME_MOUNT_PATH')
                    ? rtrim(env('RAILWAY_VOLUME_MOUNT_PATH'), '/').'/private'
                    : storage_path('app/private')),
            // Private local files must not claim the public /storage URL.
            // Uploaded photos/logos use the public disk below.
            'serve' => false,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            // Railway automatically supplies RAILWAY_VOLUME_MOUNT_PATH when a
            // persistent volume is attached. Keep public and private files in
            // separate directories on that volume. WAMP remains unchanged.
            // NSTPHUB_PUBLIC_UPLOAD_ROOT still overrides the default, if set.
            'root' => env('NSTPHUB_PUBLIC_UPLOAD_ROOT')
                ?: (env('RAILWAY_VOLUME_MOUNT_PATH')
                    ? rtrim(env('RAILWAY_VOLUME_MOUNT_PATH'), '/').'/public'
                    : storage_path('app/public')),
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',

            // Laravel can serve files from storage/app/public even when
            // public/storage is a normal directory instead of a symlink.
            // This keeps uploaded logos/profile photos working on Windows,
            // shared hosting, and local development without storage:link.
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => env('NSTPHUB_PUBLIC_UPLOAD_ROOT')
            ?: (env('RAILWAY_VOLUME_MOUNT_PATH')
                ? rtrim(env('RAILWAY_VOLUME_MOUNT_PATH'), '/').'/public'
                : storage_path('app/public')),
    ],

];

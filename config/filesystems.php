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
            'root' => storage_path('app/private'),
            // Deliberately NOT served — private files on this disk (KYC documents, deposit
            // proof-of-payment uploads) are only ever served through authenticated admin
            // controller actions (see Admin\KycController::document(), Admin\DepositController::
            // proof()), never through a generic public URL. This also frees up the /storage
            // URI below for the `public` disk to use instead (only one disk may serve there).
            'serve' => false,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            // Lets Laravel serve every file on this disk (payment-method QR codes, avatars,
            // branding logos, NFT images, etc.) directly at /storage/{path} on its own,
            // without depending on the `public/storage` symlink `php artisan storage:link`
            // creates. That symlink is unreliable on a lot of shared hosting (Hostinger
            // included) — some control panels block symlinks outright, others silently drop
            // them on a redeploy/file-manager re-upload — so every image on the platform was
            // breaking the moment that symlink wasn't present. This built-in Laravel "serve"
            // route is a permanent fix that doesn't depend on the filesystem supporting
            // symlinks at all; `storage:link` is still run during setup as a harmless,
            // slightly faster fallback (the webserver serves the symlinked file directly
            // instead of routing through PHP) wherever the host does support it.
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
        public_path('storage') => storage_path('app/public'),
    ],

];

<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        // pakai guard "web" sebagai default, tapi provider-nya admin
        'guard' => 'web',
        'passwords' => 'admins',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    */
    'guards' => [
        // Guard sesi utama aplikasi (pakai provider admins)
        'web' => [
            'driver' => 'session',
            'provider' => 'admins',
        ],

        // Alias guard admin (kalau butuh pemisahan, tetap ke provider admins)
        'admin' => [
            'driver' => 'session',
            'provider' => 'admins',
        ],

        // Jika suatu saat butuh API token:
        // 'api' => [
        //     'driver' => 'token',
        //     'provider' => 'admins',
        //     'hash' => false,
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    */
    'providers' => [
        // Hanya 1 provider: admins → App\Models\Admin
        'admins' => [
            'driver' => 'eloquent',
            'model' => App\Models\Admin::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    */
    'passwords' => [
        'admins' => [
            'provider' => 'admins',
            'table' => 'password_reset_tokens',
            'expire' => 60, // minutes
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    */
    'password_timeout' => 10800, // 3 hours
];
